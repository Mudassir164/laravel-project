<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{
    User,
    WebUser,
    Role,
    Game,
    GameSubmission,
    GameQuestion,
    Subscription,
    SubscriptionItem,
    UserReward,
};
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\Web\{
    AddPubOwnerRequest,
};
use App\Traits\StripeTrait;
use Carbon\Carbon;
use Laravel\Cashier\Subscription as CashierSubscription;

class UserController extends Controller
{
    use StripeTrait;

    protected $user;
    protected $webUser;
    private $subscription;
    private $subscriptionItem;
    private $game;
    private $gameSubmission;
    private $userReward;

    public function __construct(
        User $user,
        WebUser $webUser,
        Subscription $subscription,
        SubscriptionItem $subscriptionItem,
        Game $game,
        GameSubmission $gameSubmission,
        UserReward $userReward
    ) {
        $this->user = $user;
        $this->webUser = $webUser;
        $this->subscription = $subscription;
        $this->subscriptionItem = $subscriptionItem;
        $this->game = $game;
        $this->gameSubmission = $gameSubmission;
        $this->userReward = $userReward;
    }

    public function index(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;

            // paginated user data
            $data = $this->user->getAllUser([], $request->all())->paginate($limit);

            $data->getCollection()->transform(function ($user) {
                $user->game_played = GameSubmission::where('user_id', $user->id)->count();
                return $user;
            });

            $dataArray = $data->toArray();
            $set['data'] = $dataArray['data'];
            unset($dataArray['data']);
            $set['page'] = $dataArray;

            return $this->responseToClient($set);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function appUser(Request $request, $id)
    {
        try {
            $limit = $request->get('limit', 10);
            $search = $request->get('search');

            $games = $this->gameSubmission->getGameSubmissions(
                [
                    'user_id' => $id
                ],
                [
                    'search' => $search
                ]
            );

            $rewards = $this->userReward->getAllRewards(['user_id' => $id]);

            $games = $games->paginate($limit);
            $data = $games->items();
            $page = $games->toArray();
            unset($page['data']);

            return $this->responseToClient(compact('data', 'page', 'rewards'));
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function appUserStatusUpdate($id)
    {
        try {
            $data = $this->user->getFullUserData($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'User not found!'], 404);
            }
            $this->user->updateUser($id, ['status' => $data->status ? 0 : 1]);
            $data = $this->user->getFullUserData($id);
            $response = compact('data');
            $response['message'] = 'Status changed successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function webUserList(Request $request, string $id)
    {
        try {
            $limit = $request->limit ?? 10;

            $query = $this->webUser->where('role_id', $id)->with(['city', 'country', 'role']);

            if ($id == 2) {
                $query->withCount('organizersCreated as organizers_count');
            }

            $data = $query->paginate($limit)->toArray();
            $response['data'] = $data['data'];
            unset($data['data']);
            $response['page'] = $data;

            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function webUserStatusUpdate($id)
    {
        try {
            $data = $this->webUser->getFullUserData($id);
            if (!$data) {
                return $this->responseToClient(['message' => 'User not found!'], 404);
            }
            $this->webUser->updateUser($id, ['status' => $data->status ? 0 : 1]);
            $data = $this->webUser->getFullUserData($id);
            $response = compact('data');
            $response['message'] = 'Status changed successfully!';
            return $this->responseToClient($response);
        } catch (Exception $exception) {
            return $this->responseToClient(['message' => $exception->getMessage()], 400);
        }
    }

    public function addPubOwner(AddPubOwnerRequest $request)
    {
        try {
            $data = $this->webUser->addPubOwner($request);
            $response = compact('data');
            $response['message'] = 'Pub Owner Added Successfully!. Password send on email';
            return $this->responseToClient($response);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    // public function newUsers()
    // {
    //     try {
    //         $data = $this->user->count();
    //         $response = compact('data');
    //         $response['message'] = 'New App Users';
    //         return $this->responseToClient($response);
    //     } catch (Exception $th) {
    //         return $this->responseToClient(['message' => $th->getMessage()], 400);
    //     }
    // }

    // public function activeUsers()
    // {
    //     try {
    //         $data = $this->user->where('status', 1)->count();
    //         $response = compact('data');
    //         $response['message'] = 'Active App Users';
    //         return $this->responseToClient($response);
    //     } catch (Exception $th) {
    //         return $this->responseToClient(['message' => $th->getMessage()], 400);
    //     }
    // }

    public function userSubscription(Request $request)
    {
        try {
            $data = [];
            $totalSales = 0;
            $subs = $this->subscription->getAllUserSubscription($request);
            $page = $subs->toArray();
            unset($page['data']);
            foreach ($subs as $key => $subscription) {
                $stripeSubscription = $this->getStripeSingleSubscription($subscription->stripe_id);
                $data[$key]['subscription'] = $stripeSubscription->toArray() ?? [];
                $data[$key]['subscription']['product'] = $this->getStripeProduct($stripeSubscription->plan->product);
                $data[$key]['user'] = $subscription->webUser;
                $totalSales += ($stripeSubscription->plan->amount / 100);
            }
            $res = $this->subscriptionItem->getMostSubscribeProduct();
            $mostPopularProduct = $res['popular_product'];
            // $totalSales = $res['total_sales'];
            $response = compact('data', 'mostPopularProduct', 'totalSales', 'page');
            return $this->responseToClient($response);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function activeUserSubscription(Request $request)
    {
        try {
            $data = [];
            $user = $this->webUser->getFullUserData(auth()->user()->id);
            $activeSubscription = $user->subscriptions()
                ->where('stripe_status', 'active')
                ->orWhere(function ($query) {
                    $query->where('stripe_status', 'trialing')
                        ->where('trial_ends_at', '>', now());
                })
                ->first();
            $invoices = $user->invoices()->toArray();

            if ($activeSubscription) {
                $data['activeSubscription'] = $this->getStripeSingleSubscription($activeSubscription->stripe_id);
                $data['activeSubscription']['product'] = $this->getStripeProduct($activeSubscription->items[0]->stripe_product)->toArray();
            } else {
                $data['activeSubscription'] = null;
            }

            foreach ($invoices as $key => $invoice) {
                $data['invoices'][$key]['invoice']['id'] = $invoice['lines']['data'][0]['invoice'];
                $data['invoices'][$key]['invoice']['date'] = Carbon::parse($invoice['created'])->format('M d, Y');
                $data['invoices'][$key]['invoice']['amount'] = $invoice['amount_paid'] / 100;
            }

            return $this->responseToClient($data);
        } catch (Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
