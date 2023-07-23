<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserParameterCountException;
use Restive\Parsers\ParserWhereNotBetween;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserWhereNotBetweenTests extends TestCase
{

    /** @test */

    public function instantiate_and_tokenize_query()
    {
        $parser = new ParserWhereNotBetween('id:1:10');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function invalid_request_values()
    {
        $parser = new ParserWhereNotBetween( '');
        $this->expectException(ParserParameterCountException::class);
        $parser->tokenize();
    }

    /** @test */
    public function build_some_queries()
    {
        $parser = new ParserWhereNotBetween('id:1:10');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertEquals('select * from "users" where "id" not between ? and ? and "users"."deleted_at" is null', $query->toSql());
    }
}