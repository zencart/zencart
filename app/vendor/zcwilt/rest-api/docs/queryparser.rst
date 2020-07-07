Query Parser
============

The query parser allows for complex filtering, sorting, the use of child relations and more.

Currently the filter parser supports

- where
- orWhere
- whereIn
- orWhereIn
- whereNotIn
- orWhereNotIn
- whereBetween
- whereNotBetween
- orWhereBetween
- orWhereNotBetween
- withTrashed
- onlyTrashed

Sorting allows for multiple sort targets for ascending and descending sorts.

Includes allow for loading child models.

Joins are also supported

Query results by default return all columns for the query, however you can use the columns filter to restrict which
columns are returned.



.. toctree::
    :maxdepth: 2
    :hidden:

    queryparserwhere
    queryparsersoftdeletes
    queryparsersorting
    queryparsercolumns
    queryparserincludes
    queryparserjoins


URL Parameter Format
--------------------

Most examples you will see in the documentation show URL parameters in a simple format

e.g ``{api-uri}?columns=id,name&where=id:eq:1``

However this format will break if you need to include multiple copies of a parser

consider this

``{api-uri}?columns=id,name&where=id:eq:1&orWhereBetween=age:(10,15)&orWhereBetween=age:(50,60)``

The above query will not work as the PHP/Laravel request will only be able to choose one of the orWhereBetween clauses.

In these cases you will need to use the standard bracket notation for GET URLS

e.g.

``{api-uri}?columns=id,name&where=id:eq:1&orWhereBetween[]=age:(10,15)&orWhereBetween[]=age:(50,60)``


.. note:: You only need to use bracket notation e.g. ``&orWhereBetween[]=age:(10,15)`` if you have multiple parsers of a certain type.
    However for simplicity and consistency, we would suggest using bracket notation all the time.



