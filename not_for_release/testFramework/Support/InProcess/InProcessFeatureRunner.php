<?php

namespace Tests\Support\InProcess;

abstract class InProcessFeatureRunner
{
    public function __construct(
        private readonly ?ApplicationStateResetter $stateResetter = null,
    ) {
    }

    public function handle(FeatureRequest $request): FeatureResponse
    {
        $this->stateResetter()?->reset();

        return $this->dispatch($request);
    }

    abstract protected function dispatch(FeatureRequest $request): FeatureResponse;

    private function stateResetter(): ApplicationStateResetter
    {
        return $this->stateResetter ?? new NullApplicationStateResetter();
    }
}
