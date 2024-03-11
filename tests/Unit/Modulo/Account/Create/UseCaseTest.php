<?php

namespace Tests\Unit\Modulo\Account\Create;

use Domain\Generics\Gateways\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Gateways\Logger\Logger;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Create\Entities\CreatedAccountEntity;
use Domain\Modules\Account\Create\Entities\NewAccountEntity;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Create\Request\CreateAccountRequest;
use Domain\Modules\Account\Create\Responses\SuccessResponse;
use Domain\Modules\Account\Create\UseCase;
use Mockery;
use Tests\TestCase;

class UseCaseTest extends TestCase
{
    private readonly CreateAccountGateway $adapterMock;
    private readonly Logger $loggerMock;
    private readonly UseCaseInstrumentation $instrumentationMock;
    private UseCase $useCase;
    private CreateAccountRequest $request;
    private CreatedAccountEntity $createdAccountEntity;
    private SuccessResponse $expectedUseCaseResponse;

    public function setUp(): void
    {
        parent::setUp();
        $this->adapterMock = Mockery::mock(CreateAccountGateway::class);
        $this->loggerMock = Mockery::mock(Logger::class);
        $this->instrumentationMock = Mockery::mock(UseCaseInstrumentation::class);

        $this->request = new CreateAccountRequest(
            accountEntity: new NewAccountEntity(
                name: "teste", balance: 10.0
            )
        );
        $this->useCase = new UseCase(
            createAccountGateway: $this->adapterMock,
            logger: $this->loggerMock,
            instrumentation: $this->instrumentationMock
        );
    }

    public function testShouldExecuteUseCaseAndReturnSuccess()
    {

        $this->createdAccountEntity = new CreatedAccountEntity(
            id: 1
        );

        $this->expectedUseCaseResponse = new SuccessResponse(
            $this->createdAccountEntity
        );

        $this->adapterMock->shouldReceive('createAccount')
            ->with($this->request->accountEntity)
            ->andReturn($this->createdAccountEntity)
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

        $this->adapterMock->shouldReceive('createAccount')
            ->with($this->request->accountEntity)
            ->andThrow($expectedException)
            ->times(1);


        $this->instrumentationMock->shouldReceive('useCaseStarted')->withNoArgs()->times(1);
        $this->instrumentationMock->shouldReceive('useCaseFailed')->withArgs([$expectedException])->times(1);
        $this->loggerMock->shouldReceive("error")->withArgs(["", [$expectedException]])->times(1);
        $useCaseResponse = $this->useCase->execute($this->request);

        $this->assertInstanceOf(ErrorResponse::class, $useCaseResponse);
    }
}
