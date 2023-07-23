<?php

namespace Tests\Unit;
use Tests\TestCase;

class ConsoleTests extends TestCase
{

    /** @test */
    public function it_tests_an_incorrect_make_request_console_command()
    {
        $this->expectException(\Exception::class);
        $this->artisan('restive:make-request');
    }

    /** @test */
    public function it_tests_a_correct_make_request_console_command()
    {
        $this->artisan('restive:make-request TestRequest')
            ->assertSuccessful();
    }
}