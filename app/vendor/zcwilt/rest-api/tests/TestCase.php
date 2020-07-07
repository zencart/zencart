<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\Models\ZcwiltPost;
use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Illuminate\Support\Facades\Request;
use Tests\Fixtures\Models\ZcwiltUser;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);


    }

    public function createTables()
    {
        Schema::dropIfExists('zcwilt_dummy');
        Schema::create('zcwilt_dummy', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email', 100)->unique();
            $table->integer('age');
            $table->timestamps();
        });
        Schema::dropIfExists('zcwilt_dummy1');
        Schema::create('zcwilt_dummy1', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email', 100)->unique();
            $table->integer('age');
            $table->timestamps();
        });

        Schema::dropIfExists('zcwilt_users');
        Schema::create('zcwilt_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email', 100)->unique();
            $table->integer('age');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::dropIfExists('zcwilt_posts');
        Schema::create('zcwilt_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('comment');
            $table->boolean('published');
            $table->timestamps();
        });
    }

    public function seedTables()
    {
        $userTableTestData = $this->createUserTableTestData();

        foreach ($userTableTestData as $user) {
            $userCreated = ZcwiltUser::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'age' => $user['age'],
                'deleted_at' => $user['deleted_at'],
            ]);
            foreach ($user['posts'] as $post) {
                ZcwiltPost::create([
                    'user_id' => $userCreated->id,
                    'comment' => $post['comment'],
                    'published' => $post['published']
                ]);
            }
        }
    }

    public function getRequestResults($request = null)
    {
        if (!isset($request)) {
            $request = Request::instance();
        }
        $api = new ApiQueryParser(new ParserFactory());
        $api->parseRequest($request);
        $api->buildParsers();
        $query = $api->buildQuery(new ZcwiltUser);
        $result = $query->get()->toArray();
        return $result;
    }

    private function createUserTableTestData()
    {
        $data = [];
        $n = rand(16, 30);
        $softDeleted = 0;
        for ($i = 0; $i < $n; $i++) {
            $softDeleted = !$softDeleted;
            $name = 'name' . $i;
            $email = $name . '@gmail.com';
            $age = rand(15, 76);
            $deleted_at = ($softDeleted) ? now() : null;
            $posts = $this->createUserPostsTestData($i);
            $data[] = ['name' => $name, 'email' => $email, 'age' => $age, 'deleted_at' => $deleted_at, 'posts' => $posts];
        }
        return $data;
    }

    private function createUserPostsTestData($userIndex)
    {
        $data = [];
        $n = rand(1, 5);
        $published = 0;
        for ($i = 0; $i < $n; $i++) {
            $published = !$published;
            $comment = 'Comment ' . $i . ' for index ' . $userIndex;
            $data[] = ['comment' => $comment, 'published' => $published];
        }
        return $data;
    }
}
