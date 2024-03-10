<?php

namespace App\Providers;

use App\Adapters\Logger\LoggerAdapter;
use App\Adapters\Modules\Account\AccountAdapter;
use App\Adapters\Modules\Account\AuthorizedTransactionAdapter;
use App\Adapters\Modules\Account\TransactionManagementAdapter;
use Domain\Generics\Logger\Logger;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
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

        $this->app->bind(CreateAccountGateway::class, function () {
            return new AccountAdapter();
        });

        $this->app->bind(AuthorizeTransactionGateway::class, function () {
            return new AuthorizedTransactionAdapter();
        });

        $this->app->bind(TransactionManagementGateway::class, function () {
            return new TransactionManagementAdapter();
        });
    }
}
