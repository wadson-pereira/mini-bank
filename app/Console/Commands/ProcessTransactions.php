<?php

namespace App\Console\Commands;

use App\Adapters\Instrumentation\UseCaseInstrumentationAdapter;
use Domain\Generics\Gateways\Logger\Logger;
use Domain\Generics\Gateways\Transaction\TransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Domain\Modules\Account\Transaction\Request\TransactionRequest;
use Domain\Modules\Account\Transaction\UseCase;
use Illuminate\Console\Command;

class ProcessTransactions extends Command
{
    protected $signature = 'app:process-scheduled-transaction';
    protected $description = 'Processes all transactions scheduled for current day';

    public function __construct(
        private readonly TransactionManagementGateway $transactionManagementGateway,
        private readonly AuthorizeTransactionGateway  $authorizeTransactionGateway,
        private readonly Logger                       $logger,
        private readonly TransactionGateway           $transactionGateway
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $useCase = new UseCase(
                transactionManagementGateway: $this->transactionManagementGateway,
                authorizeTransactionGateway: $this->authorizeTransactionGateway,
                transactionGateway: $this->transactionGateway,
                logger: $this->logger,
                instrumentation: new UseCaseInstrumentationAdapter(useCaseClass: UseCase::class)
            );
            $collection = $this->transactionManagementGateway->getScheduledTransactionForToday();
            foreach ($collection->all() as $entity) {
                $useCase->execute(new TransactionRequest(
                    transactionEntity: $entity
                ));
            }
        } catch (\Throwable $exception) {
            $this->output->error('Fail to process transactions');
        }
    }
}
