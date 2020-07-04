Query Parser Includes
=====================

.. warning:: This feature is a bit experimental at the moment. In terms of testing i've only tried with a simple one to many relationsship. e.g. ``user->posts``.

example

::

    {api-uri}?includes[]=posts

The above assumes the query is being done on a model that has a relationship defined

