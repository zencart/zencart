<?php

namespace DanHarrin\LivewireRateLimiting\Tests;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Livewire\Livewire;

class RateLimitingTest extends TestCase
{
    /** @test */
    public function can_rate_limit()
    {
        $component = Livewire::test(Component::class);

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0)
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0);

        sleep(1);

        $component
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }

    /** @test */
    public function can_hit_and_clear_rate_limiter()
    {
        Livewire::test(Component::class)
            ->call('hit')
            ->call('hit')
            ->call('hit')
            ->call('limit')
            ->assertNotSet('secondsUntilAvailable', 0)
            ->call('clear')
            ->call('limit')
            ->assertSet('secondsUntilAvailable', 0);
    }
}

class Component extends \Livewire\Component
{
    use \DanHarrin\LivewireRateLimiting\WithRateLimiting;

    public $secondsUntilAvailable;

    public function clear()
    {
        $this->clearRateLimiter('limit');
    }

    public function hit()
    {
        $this->hitRateLimiter('limit', 1);
    }

    public function limit()
    {
        try {
            $this->rateLimit(3, 1);
        } catch (TooManyRequestsException $exception) {
            return $this->secondsUntilAvailable = $exception->secondsUntilAvailable;
        }

        $this->secondsUntilAvailable = 0;
    }

    public function render()
    {
        return view('component');
    }
}