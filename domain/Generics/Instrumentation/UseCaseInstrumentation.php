<?php

namespace  Domain\Generics\Instrumentation;

interface UseCaseInstrumentation
{
    public function useCaseStarted();
    public function useCaseFinished();
    public function useCaseFailed(\Throwable $throwable);
}
