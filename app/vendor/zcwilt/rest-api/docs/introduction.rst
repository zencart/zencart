Introduction
============

zcwilt/rest-api is written for Laravel and provides a query parser and api controllers for CRUD type actions.

The query parser allows for complex filtering and sorting, converting the URI query into eloquent queries.

The API controller supports resource creation, reading, updating and deletion.

Reading, updating and deletion can all access the query parser.

e.g.

::

    delete ?whereIn[]=id:(1,2)

    update ?whereBetween=age:30:60  @todo

although note that the filtering can be more complex than just a simple ``where``

Some examples of filtering/Sorting etc

::

    {api-uri}?where[]=id:eq:2

    {api-uri}?whereIn[]=id:(1,2)

    {api-uri}?sort[]=id,-name

