<?php

namespace App\Providers;

use App\Adapters\HttpClient\HttpClient;
use App\Adapters\Logger\LoggerAdapter;
use App\Adapters\Modules\Account\AccountAdapter;
use App\Adapters\Modules\Account\AuthorizedTransactionAdapter;
use App\Adapters\Modules\Account\TransactionManagementAdapter;
use Domain\Generics\Logger\Logger;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Transaction\Entities\AuthorizeServiceEnvs;
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
            return new AuthorizedTransactionAdapter(
                client: new HttpClient(),
                apiEnv: new AuthorizeServiceEnvs(
                    baseUrl: config('services.transaction_authorizer.base_url'),
                    authToken: config('services.transaction_authorizer.api_key')
                ));
        });

        $this->app->bind(TransactionManagementGateway::class, function () {
            return new TransactionManagementAdapter();
        });
    }
}
