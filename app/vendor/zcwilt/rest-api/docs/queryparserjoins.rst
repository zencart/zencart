Query Parser Joins
==================

Example

::

    {api-uri}?join[]=joinType:tableName:leftKey:rightKey

The join clause takes 4 parameters

- joinType - can be 1 of ``inner``, ``left`` or ``cross``
- tableName - the table to join on
- leftkey - the table field used on the left side of the Join on clause
- rightKey - the table field used on the right side of the Join on clause


More examples

::

    {api-uri}?join[]=inner:posts:posts.user_id:users.id
    same as
    $model->join('posts', 'posts.user_id', '=', 'users.id', 'inner');
