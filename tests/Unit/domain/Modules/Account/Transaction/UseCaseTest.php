<?php

namespace Tests\Unit\domain\Modules\Account\Transaction;

use Carbon\Carbon;
use Domain\Generics\Gateways\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Gateways\Logger\Logger;
use Domain\Generics\Gateways\Transaction\TransactionGateway;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;
use Domain\Modules\Account\Transaction\Exceptions\InsufficientFundsException;
use Domain\Modules\Account\Transaction\Exceptions\TransactionWasNotAuthorized;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Domain\Modules\Account\Transaction\Request\TransactionRequest;
use Domain\Modules\Account\Transaction\Responses\SuccessResponse;
use Domain\Modules\Account\Transaction\UseCase;
use Mockery;
use Tests\TestCase;

class UseCaseTest extends TestCase
{
    private readonly TransactionManagementGateway $transactionManagementGatewayMock;
    private readonly AuthorizeTransactionGateway $authorizeTransactionGatewayMock;
    private readonly TransactionGateway $transactionGateway;
    private readonly Logger $loggerMock;
    private readonly UseCaseInstrumentation $instrumentationMock;
    private UseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(now());
        $this->transactionManagementGatewayMock = Mockery::mock(TransactionManagementGateway::class);
        $this->authorizeTransactionGatewayMock = Mockery::mock(AuthorizeTransactionGateway::class);
        $this->transactionGateway = Mockery::mock(TransactionGateway::class);
        $this->loggerMock = Mockery::mock(Logger::class);
        $this->instrumentationMock = Mockery::mock(UseCaseInstrumentation::class);


        $this->useCase = new UseCase(
            transactionManagementGateway: $this->transactionManagementGatewayMock,
            authorizeTransactionGateway: $this->authorizeTransactionGatewayMock,
            transactionGateway: $this->transactionGateway,
            logger: $this->loggerMock,
            instrumentation: $this->instrumentationMock
        );
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionCompleted()
    {

        $request = new TransactionRequest(
            new TransactionEntity(
                transactionId: 1,
                sender: new Account(id: 1),
                receiver: new Account(
                    id: 2
                ),
                amount: new Amount(1.00),
                scheduledFor: Carbon::now()->toDateString()
            )
        );

        $processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::COMPLETED
        );

        $expectedUseCaseResponse = new SuccessResponse(
            $processedTransactionEntity
        );

        $this->transactionGateway->shouldReceive('commit');
        $this->transactionGateway->shouldReceive('begin');
        $this->transactionManagementGatewayMock->shouldReceive('getAccountBalance')
            ->withArgs([$request->transactionEntity->sender])
            ->andReturn(new Amount(12.00))
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: true))
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$request->transactionEntity, ProcessingStatus::COMPLETED])
            ->andReturn($processedTransactionEntity)
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('decreaseAccountBalance')
            ->withArgs([$processedTransactionEntity->sender, $processedTransactionEntity->amount])
            ->times(1);
        $this->transactionManagementGatewayMock->shouldReceive('increasesAccountBalance')
            ->withArgs([$processedTransactionEntity->receiver, $processedTransactionEntity->amount])
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionScheduled()
    {
        $request = new TransactionRequest(
            new TransactionEntity(
                transactionId: null,
                sender: new Account(id: 1),
                receiver: new Account(
                    id: 2
                ),
                amount: new Amount(1.00),
                scheduledFor: Carbon::now()->addDay()->toDateString()
            )
        );

        $processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::SCHEDULED
        );

        $expectedUseCaseResponse = new SuccessResponse(
            $processedTransactionEntity
        );

        $this->transactionGateway->shouldReceive('commit');
        $this->transactionGateway->shouldReceive('begin');
        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$request->transactionEntity, ProcessingStatus::SCHEDULED])
            ->andReturn($processedTransactionEntity)
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionInsufficientFunds()
    {


        $request = new TransactionRequest(
            new TransactionEntity(
                transactionId: 1,
                sender: new Account(id: 1),
                receiver: new Account(
                    id: 2
                ),
                amount: new Amount(1.00),
                scheduledFor: Carbon::now()->addDay()->toDateString()
            )
        );
        $this->transactionGateway->shouldReceive('begin');
        $this->transactionGateway->shouldReceive('commit');
        $processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::INSUFFICIENT_FUNDS
        );

        $expectedUseCaseResponse = new ErrorResponse(
            new InsufficientFundsException()
        );

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$request->transactionEntity, ProcessingStatus::INSUFFICIENT_FUNDS])
            ->andReturn($processedTransactionEntity)
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('getAccountBalance')
            ->withArgs([$request->transactionEntity->sender])
            ->andReturn(new Amount(0))
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: true))
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($request);

        $this->assertInstanceOf(ErrorResponse::class, $useCaseResponse);
        $this->assertEquals($expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionUnauthorized()
    {


        $request = new TransactionRequest(
            new TransactionEntity(
                transactionId: 1,
                sender: new Account(id: 1),
                receiver: new Account(
                    id: 2
                ),
                amount: new Amount(1.00),
                scheduledFor: Carbon::now()->addDay()->toDateString()
            )
        );

        $processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::UNAUTHORIZED
        );

        $expectedUseCaseResponse = new ErrorResponse(
            new TransactionWasNotAuthorized()
        );

        $this->transactionGateway->shouldReceive('begin');
        $this->transactionGateway->shouldReceive('commit');

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$request->transactionEntity, ProcessingStatus::UNAUTHORIZED])
            ->andReturn($processedTransactionEntity)
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: false))
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($request);

        $this->assertInstanceOf(ErrorResponse::class, $useCaseResponse);
        $this->assertEquals($expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnError()
    {
        $expectedException = new \Exception();

        $request = new TransactionRequest(
            new TransactionEntity(
                transactionId: 1,
                sender: new Account(id: 1),
                receiver: new Account(
                    id: 2
                ),
                amount: new Amount(1.00),
                scheduledFor: Carbon::now()->addDay()->toDateString()
            )
        );

        $this->transactionGateway->shouldReceive('begin');
        $this->transactionGateway->shouldReceive('rollback');


        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$request->transactionEntity])
            ->andThrow($expectedException)
            ->times(1);


        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFailed')->withArgs([$expectedException])->times(1);
        $this->loggerMock->shouldReceive("error")->withArgs(["", [$expectedException]])->times(1);
        $useCaseResponse = $this->useCase->execute($request);

        $this->assertInstanceOf(ErrorResponse::class, $useCaseResponse);
    }
}
