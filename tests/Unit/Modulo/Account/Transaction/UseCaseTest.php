<?php

namespace Tests\Unit\Modulo\Account\Transaction;

use Carbon\Carbon;
use Domain\Generics\Gateways\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Gateways\Logger\Logger;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;
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
    private readonly Logger $loggerMock;
    private readonly UseCaseInstrumentation $instrumentationMock;
    private UseCase $useCase;
    private TransactionRequest $request;
    private ProcessedTransactionEntity $processedTransactionEntity;
    private SuccessResponse $expectedUseCaseResponse;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(now());
        $this->transactionManagementGatewayMock = Mockery::mock(TransactionManagementGateway::class);
        $this->authorizeTransactionGatewayMock = Mockery::mock(AuthorizeTransactionGateway::class);
        $this->loggerMock = Mockery::mock(Logger::class);
        $this->instrumentationMock = Mockery::mock(UseCaseInstrumentation::class);


        $this->useCase = new UseCase(
            transactionManagementGateway: $this->transactionManagementGatewayMock,
            authorizeTransactionGateway: $this->authorizeTransactionGatewayMock,
            logger: $this->loggerMock,
            instrumentation: $this->instrumentationMock
        );
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionCompleted()
    {

        $this->request = new TransactionRequest(
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

        $this->processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::COMPLETED
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->processedTransactionEntity
        );

        $this->transactionManagementGatewayMock->shouldReceive('getAccountBalance')
            ->withArgs([$this->request->transactionEntity->sender])
            ->andReturn(new Amount(12.00))
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$this->request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: true))
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$this->request->transactionEntity, ProcessingStatus::COMPLETED])
            ->andReturn($this->processedTransactionEntity)
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('decreaseAccountBalance')
            ->withArgs([$this->processedTransactionEntity->sender, $this->processedTransactionEntity->amount])
            ->times(1);
        $this->transactionManagementGatewayMock->shouldReceive('increasesAccountBalance')
            ->withArgs([$this->processedTransactionEntity->receiver, $this->processedTransactionEntity->amount])
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($this->expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionScheduled()
    {
        Carbon::setTestNow(now());

        $this->request = new TransactionRequest(
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

        $this->processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::SCHEDULED
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->processedTransactionEntity
        );

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$this->request->transactionEntity, ProcessingStatus::SCHEDULED])
            ->andReturn($this->processedTransactionEntity)
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($this->expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionInsufficientFunds()
    {


        $this->request = new TransactionRequest(
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

        $this->processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::INSUFFICIENT_FUNDS
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->processedTransactionEntity
        );

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$this->request->transactionEntity, ProcessingStatus::INSUFFICIENT_FUNDS])
            ->andReturn($this->processedTransactionEntity)
            ->times(1);

        $this->transactionManagementGatewayMock->shouldReceive('getAccountBalance')
            ->withArgs([$this->request->transactionEntity->sender])
            ->andReturn(new Amount(0))
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$this->request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: true))
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($this->expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnSuccessCaseTransactionUnauthorized()
    {


        $this->request = new TransactionRequest(
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

        $this->processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::UNAUTHORIZED
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->processedTransactionEntity
        );

        $this->transactionManagementGatewayMock->shouldReceive('processTransaction')
            ->withArgs([$this->request->transactionEntity, ProcessingStatus::UNAUTHORIZED])
            ->andReturn($this->processedTransactionEntity)
            ->times(1);

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$this->request->transactionEntity])
            ->andReturn(new TransactionAuthorization(authorized: false))
            ->times(1);

        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFinished')->withNoArgs()->times(1);

        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(SuccessResponse::class, $useCaseResponse);
        $this->assertEquals($this->expectedUseCaseResponse, $useCaseResponse);
    }

    public function testShouldExecuteUseCaseAndReturnError()
    {
        $expectedException = new \Exception();

        $this->request = new TransactionRequest(
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

        $this->processedTransactionEntity = new ProcessedTransactionEntity(
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            processingStatus: ProcessingStatus::UNAUTHORIZED
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->processedTransactionEntity
        );

        $this->authorizeTransactionGatewayMock->shouldReceive('getTransactionAuthorization')
            ->withArgs([$this->request->transactionEntity])
            ->andThrow($expectedException)
            ->times(1);


        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFailed')->withArgs([$expectedException])->times(1);
        $this->loggerMock->shouldReceive("error")->withArgs(["", [$expectedException]])->times(1);
        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(ErrorResponse::class, $useCaseResponse);
    }
}
