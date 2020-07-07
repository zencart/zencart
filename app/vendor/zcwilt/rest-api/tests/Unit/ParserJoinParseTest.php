<?php

namespace Tests\Unit;

use Zcwilt\Api\Exceptions\ParserInvalidParameterException;
use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\ParserFactory;
use Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

class ParserJoinParseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createTables();
        $this->seedTables();
    }
    public function testJoinParserParseTestNoParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('join');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('');
    }
    public function testJoinParserParseTestInvalidNumParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('join');
        $this->expectException(ParserParameterCountException::class);
        $parser->parse('inner:table:keyLeft');
    }

    public function testJoinParserParseTestInvalidJoinParam()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('join');
        $this->expectException(ParserInvalidParameterException::class);
        $parser->parse('bad:table:keyLeft:keyRight');
    }

    public function testJoinParserParseTestWithParams()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser('join');
        $parser->parse('inner:table:keyLeft:keyRight');
        $tokenized = $parser->getTokenized();
        $this->assertTrue($tokenized[0] === 'inner');
        $this->assertTrue($tokenized[1] === 'table');
        $this->assertTrue($tokenized[2] === 'keyLeft');
        $this->assertTrue($tokenized[3] === 'keyRight');
    }

    public function testJoinParserWithDummyData()
    {
        $testResult = ZcWiltUser::join('zcwilt_posts', 'zcwilt_posts.user_id', '=', 'zcwilt_users.id', 'inner')->get()->toArray();
        Request::instance()->query->set('join', 'inner:zcwilt_posts:zcwilt_posts.user_id:zcwilt_users.id');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::join('zcwilt_posts', 'zcwilt_posts.user_id', '=', 'zcwilt_users.id', 'left')->get()->toArray();
        Request::instance()->query->set('join', 'left:zcwilt_posts:zcwilt_posts.user_id:zcwilt_users.id');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::join('zcwilt_posts', 'zcwilt_posts.user_id', '=', 'zcwilt_users.id', 'cross')->get()->toArray();
        Request::instance()->query->set('join', 'cross:zcwilt_posts:zcwilt_posts.user_id:zcwilt_users.id');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));

        $testResult = ZcWiltUser::join('zcwilt_posts', 'zcwilt_posts.user_id', '=', 'zcwilt_users.id', 'inner')->where('published', '=', 1)->get()->toArray();
        Request::instance()->query->set('join', 'inner:zcwilt_posts:zcwilt_posts.user_id:zcwilt_users.id');
        Request::instance()->query->set('where', 'published:eq:1');
        $result  = $this->getRequestResults();
        $this->assertTrue($result[0]['age'] === $testResult[0]['age']);
        $this->assertTrue(count($result) === count($testResult));
    }
}
