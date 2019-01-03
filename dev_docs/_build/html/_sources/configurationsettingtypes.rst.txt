Setting Types
=============

The following setting types are available.

- text
- boolean
- selectFromArray
- selectFromModel
- simpleDate


text Type
---------

A simple text input box. Requires no ``setting_definition`` field entry.


boolean Type
------------

Displays a checkbox. Requires no ``setting_definition`` field entry.

.. note:: Boolean Type stores either ``on`` or ``off`` as the value in the database. You may need to convert this to true/false depending on how you are using the value.




selectFromArray Type
--------------------

Displays a select dropdown. Requires an entry in ``setting_definition`` field.

The entry is a json encoded array.

e.g. unencoded the array would look like :-

::

    'options' => [
        ['id' => 1, 'text' => 'DEFINE_FOR_OPTION1'],
        ['id' => 2, 'text' => 'DEFINE_FOR_OPTION2'],
        ['id' => 3, 'text' => 'DEFINE_FOR_OPTION3'],
    ]

.. note:: `text` values can be plain strings but for multi-language usage should be language defines.


selectFromModel Type
--------------------

Displays a select dropdown. Requires an entry in ``setting_definition`` field.

The entry is a json encoded array.

e.g. unencoded the array would look like :-

::

    'model' => 'banner',
    'id' => 'banners_id',
    'text' => 'banners_title'


- `model` is a reference to an Eloquent model in ``app/Model``
- `id` would normally be the models primary key
- `text` would be a model field used to provide the select option text


simpleDate Type
---------------

Displays a `flatpickr` date dropdown. Requires no ``setting_definition`` field entry.

