API Controllers
===============


Routes
------

zcwilt/rest-api uses resource controllers. To define your routes for each controller you want, you will need to add the following to your api routes file.

::

    Route::resource('modelName', 'controller');
    Route::delete('modelName', 'controller@destroyByQuery');
    Route::put('modelName', 'controller@updateByQuery');


``modelName`` is the model name you want to have api access.


``Controller`` is the name of your controller class.

As an example. To use the dummy simple controller supplied by the project, your routing entries would be.

::

    Route::resource('dummySimple', 'Api\DummySimpleController');
    Route::delete('dummySimple', 'Api\DummySimpleController@destroyByQuery');
    Route::put('dummySimple', 'Api\DummySimpleController@updateByQuery');


or to use the default User model that comes with Laravel

::

    Route::resource('user', 'Api\UserController');
    Route::delete('user', 'Api\UserController@destroyByQuery');
    Route::put('user', 'Api\UserController@updateByQuery');


For each Laravel Model that you want to use in the API you will need to create a Controller

Controller Definition
---------------------

As mentioned above, for each Laravel model that you want to provide API access to you will need to create a Controller.

This should be placed in the standard laravel location
e.g. App/Http/Controllers or a sub directory. Our suggestion is to use App/Http/Controllers/Api

The controller definition is fairly simple

.. code-block:: php

    <?php
    namespace App\Http\Controllers\Api;

    use Zcwilt\Api\Controllers\ApiController;

    class DummySimpleController extends ApiController
    {
        protected $modelName = '\\Zcwilt\Api\\Models\\DummySimple';
    }

.. note:: The ``protected $modelName`` defines the Eloquent Model that will be used by the controller. The factory class used will try and resolve the model
    from either your projects App folder or from the App/Models folder, If the Model is in one of these folders there is no need to namespace the model name. e.g. you could just do
    ::

        protected $modelName = 'ModelName';


Api Endpoints
-------------

The api endpoints provided by the resource controller and extra controller methods provide the following route/actions


::

    GET api/modelname -> controller@index : allows for query filtering


::

    GET api/modelname/{id} -> controller@show

::

    POST api/modelname -> controller@store
    The request body should be an array of field/values
    e.g ['name' => 'foo', 'email' => 'bar@test.com']

::

    PUT api/modelname/{id} -> controller@update
    The request body should be an array of field/values
    e.g ['name' => 'foo', 'email' => 'bar@test.com']

::

    PUT api/modelname -> controller@updateByQuery : allows for query filtering
    The request body should be an array of parser clauses however  field name/values
    should be set in a fields array
    e.g ['where' => 'status:eq:1', 'fields' => [name' => 'foo', 'email' => 'bar@test.com']]

::

    DELETE api/modelname/{id} - controller@destroy

::

    DELETE api/modelname - controller@destroyByQuery : allows for query filtering
    The request body should be an array of parser clauses
    e.g ['where' => ''status:eq:1]


Pagination
----------

All results from the ``index`` route are paginated using the standard Laravel paginator
Therefore you can add a ``page`` and ``per_page`` parameter to those queries.
You can also return all results by adding ``paginate=no`` to the query string.


Exception Handling
------------------

@todo