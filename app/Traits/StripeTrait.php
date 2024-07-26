<?php

namespace App\Traits;

use App\Models\User;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentMethod;
use Stripe\Product;
use Stripe\Subscription;

trait StripeTrait
{

    /**
     * @param Request $request
     * @return $this|false|string
     */

    public function getSubscriptionPlans()
    {
        $key = config('services.stripe.secret');
        $stripe = new \Stripe\StripeClient($key);
        $plansraw = $stripe->plans->all();
        $plans = $plansraw->data;
        foreach ($plans as $plan) {
            $prod = $stripe->products->retrieve(
                $plan->product,
                []
            );
            $plan->product = $prod;
        }
        return $plans;
    }

    public function createSubscription($stripeToken, $priceId)
    {
        $user = WebUser::find(auth()->user()->id);
        $key = config('services.stripe.secret');
        Stripe::setApiKey($key);

        $paymentMethod = PaymentMethod::create([
            'type' => 'card',
            'card' => ['token' => $stripeToken],
        ]);

        // Create or get Stripe customer
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod->id);

        // Create a new subscription
        $subscription = $user->newSubscription('default', $priceId)->create($paymentMethod->id, [
            'email' => $user->email,
        ]);

        return $subscription;
    }

    public function getStripeSingleSubscription($id)
    {
        $key = config('services.stripe.secret');
        Stripe::setApiKey($key);
        // Fetch the subscription by ID
        $subscription = Subscription::retrieve($id);
        return $subscription;
    }

    public function getStripeProduct($id)
    {
        $key = config('services.stripe.secret');
        Stripe::setApiKey($key);
        // Fetch the subscription by ID
        $product = Product::retrieve($id);
        return $product;
    }
}
