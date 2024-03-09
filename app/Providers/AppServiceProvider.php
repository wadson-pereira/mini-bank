<?php

namespace App\Providers;

use App\Adapters\Logger\LoggerAdapter;
use App\Adapters\Modules\Product\ProductAdapter;
use App\Adapters\Modules\Sale\SaleAdapter;
use Domain\Generics\Logger\Logger;
use Domain\Modules\Product\List\gateways\ProductGateway;
use Domain\Modules\Sale\Create\Entities\CreatedSale;
use Domain\Modules\Sale\Create\Gateways\CreateSaleGateway;
use Domain\Modules\Sale\List\Gateways\ListSalesGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app->bind(Logger::class, function () {
            return new LoggerAdapter();
        });
    }
}
