<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Parsers\ParserWith;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserWithTests extends TestCase
{

    /** @test */

    public function instantiate_and_tokenize_query()
    {
        $parser = new ParserWith('foo,bar');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function invalid_request_values()
    {
        $parser = new ParserWith('');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->tokenize();
    }

    /** @test */
    public function build_some_queries()
    {
        $parser = new ParserWith('foo,bar');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertEquals('select * from "users" where "users"."deleted_at" is null', $query->toSql());
    }
}