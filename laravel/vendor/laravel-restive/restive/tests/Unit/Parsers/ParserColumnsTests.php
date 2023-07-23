<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Parsers\ParserColumns;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserColumnsTests extends TestCase
{

    /** @test */
    public function instantiate_and_tokenize_query()
    {
        $parser = new ParserColumns('foo,bar');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
        $this->assertEquals('select "foo", "bar" from "users" where "users"."deleted_at" is null', $query->toSql());
    }

    /** @test */
    public function invalid_request_values()
    {
        $parser = new ParserColumns('');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->tokenize();

        $parser = new ParserColumns('bar');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->tokenize();

    }

    /** @test */
    public function build_some_queries()
    {
        $parser = new ParserColumns('foo,bar');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertEquals('select "foo", "bar" from "users" where "users"."deleted_at" is null', $query->toSql());
    }
}