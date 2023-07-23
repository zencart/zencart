<?php


namespace Tests\Unit\Parsers;
use Illuminate\Database\Eloquent\Builder;
use Restive\Parsers\ParserOnlyTrashed;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class ParserOnlyTrashedTests extends TestCase
{

    /** @test */
    public function instantiate_and_tokenize_query()
    {
        $parser = new ParserOnlyTrashed('true');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function build_some_queries()
    {
        $parser = new ParserOnlyTrashed('true');
        $model = new User();
        $parser->tokenize();
        $query = $parser->buildQuery($model->query());
        $this->assertEquals('select * from "users" where "users"."deleted_at" is not null', $query->toSql());
    }

}