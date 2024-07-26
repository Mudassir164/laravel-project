<?php

namespace App\Models;

use App\Traits\StripeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionItem extends Model
{
    use HasFactory, StripeTrait;

    public function getMostSubscribeProduct()
    {
        $mostSubscribeProduct = null;
        $subscriptionItems = $this::all();

        $mostPopularProduct = $subscriptionItems->groupBy('stripe_product')
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->first();

        if ($mostPopularProduct) {
            $productId = $mostPopularProduct->first()->stripe_product;
            if ($productId) {
                $mostSubscribeProduct   =   $this->getStripeProduct($productId);
            }
        }
        $totalSales = $subscriptionItems->sum('price') ?? 0;

        return [
            'popular_product' => $mostSubscribeProduct, 'total_sales' => $totalSales
        ];
    }
}
