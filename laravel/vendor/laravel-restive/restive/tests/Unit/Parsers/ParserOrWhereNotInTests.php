<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Parsers\ParserOrWhereNotIn;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserOrWhereNotInTests extends TestCase
{

    /** @test */

    public function instantiate_and_tokenize_query()
    {
        $parser = new ParserOrWhereNotIn('id:(3,4)');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function invalid_request_values()
    {
        $parser = new ParserOrWhereNotIn('');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->tokenize();
    }

    /** @test */
    public function build_some_queries()
    {
        $parser = new ParserOrWhereNotIn('id:(1,10)');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertEquals('select * from "users" where ("id" not in (?, ?)) and "users"."deleted_at" is null', $query->toSql());
    }
}