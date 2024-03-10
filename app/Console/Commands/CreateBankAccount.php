<?php

namespace App\Console\Commands;

use App\Adapters\Instrumentation\UseCaseInstrumentationAdapter;
use Domain\Generics\Logger\Logger;
use Domain\Modules\Account\Create\Entities\NewAccountEntity;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Create\Request\CreateAccountRequest;
use Domain\Modules\Account\Create\Responses\SuccessResponse;
use Domain\Modules\Account\Create\UseCase;
use Illuminate\Console\Command;

class CreateBankAccount extends Command
{

    protected $signature = 'app:create-bank-account {name} {balance}';
    protected $description = 'Create an Bank Account by name and amount';

    public function __construct(
        private readonly CreateAccountGateway $createAccountGateway,
        private readonly Logger               $logger)
    {
        parent::__construct();

    }

    public function handle(): void
    {
        try {
            $name = $this->argument('name');
            $balance = $this->argument('balance');
            $useCase = new UseCase(
                createAccountGateway: $this->createAccountGateway,
                logger: $this->logger,
                instrumentation: new UseCaseInstrumentationAdapter(
                    useCaseClass: UseCase::class
                )
            );
            $useCaseResponse = $useCase->execute(
                new CreateAccountRequest(
                    new NewAccountEntity(name: $name, balance: $balance)
                )
            );

            if ($useCaseResponse instanceof SuccessResponse) {
                $this->output->info("account_id: {$useCaseResponse->createdAccount->id}");
            } else {
                $this->output->error("Failed to create account");
            }
        } catch (\Throwable $exception) {
            $this->output->error('Fail to create bank account');
        }
    }
}
