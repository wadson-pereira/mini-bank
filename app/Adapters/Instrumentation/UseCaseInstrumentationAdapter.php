<?php

namespace App\Adapters\Instrumentation;

use Domain\Generics\Instrumentation\UseCaseInstrumentation;
use Prometheus\Counter;
use Prometheus\Histogram;

class UseCaseInstrumentationAdapter implements UseCaseInstrumentation
{
    private $exporter;
    private $startTime;
    private Counter $useCaseSuccessCounter;
    private Counter $useCaseFailedCounter;
    private Histogram $useCaseExecutionTime;

    public function __construct(
        public string $useCaseClass
    ) {
        $this->exporter = app('prometheus');
        $this->useCaseSuccessCounter = $this->exporter->getOrRegisterCounter(
            'use_case_success',
            'It counts use case executions successfully.',
            [
                'use_case'
            ]
        );
        $this->useCaseFailedCounter = $this->exporter->getOrRegisterCounter(
            'use_case_failed',
            'It counts use case executions failed.',
            [
                'use_case',
                'exception'
            ]
        );
        $this->useCaseExecutionTime = $this->exporter->getOrRegisterHistogram(
            'use_case_execution_time_seconds',
            'It observe use case execution time.',
            [
                'use_case'
            ]
        );
    }

    public function useCaseStarted()
    {
        $this->startTime = microtime(true);
    }

    public function useCaseFinished()
    {
        $time = microtime(true) - $this->startTime;
        $this->useCaseSuccessCounter->incBy(1, [$this->useCaseClass]);
        $this->useCaseExecutionTime->observe(
            $time,
            [$this->useCaseClass]
        );
    }

    public function useCaseFailed(\Throwable $throwable)
    {
        $this->useCaseFailedCounter->incBy(1, [
            $this->useCaseClass,
            get_class($throwable)
        ]);
    }
}
