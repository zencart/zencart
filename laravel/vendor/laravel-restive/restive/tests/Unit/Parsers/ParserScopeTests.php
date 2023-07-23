<?php

namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Parsers\ParserScope;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserScopeTests extends TestCase
{
    /** @test */
    public function it_instantiates_and_tokenizes_query()
    {
        $parser = new ParserScope('teenager');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function it_instantiates_an_invalid_scope()
    {
        $parser = new ParserScope('invalid');
        $model = new User();
        $this->expectException(ParserInvalidParameterException::class);
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

}