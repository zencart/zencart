<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Parsers\ParserNull;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserNullTests extends TestCase
{

    /** @test */
    public function instantiate_and_build_query()
    {
        $parser = new ParserNull('');
        $model = new User();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

}