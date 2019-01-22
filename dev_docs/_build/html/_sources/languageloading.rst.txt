Language Loading
================

Legacy versions of Zen Cart use PHP defines to create multi language web sites.

PHP defines are `idempotent` in the sense that once a define has been set it cannot be altered using another define.

e.g. Doing

define('SOME_KEY', 'Original Value');
define('SOME_KEY', 'New Value');

will not change the value of `SOME_KEY` to `New Value`

In fact doing so will generate a PHP warning.

This fact meant the current method for loading defines is somewhat cumbersome.

Furthermore the difficulty in correctly overriding these defines leads developers/store owners etc to
edit the original define files, leading to problems updating sites to new versions.

To mitigate this, a new way of defining language strings in V2 haas been introduced.

As it may take some time to convert V2 code legacy defines will still be honoured.

The structure of a language file is a simple php array of key/value pairs.

e.g.

::

    <?php
        return [
            'header-title-logoff', 'Logoff',
            'header-title-login', 'Login',
            'some-key', 'some-value',
        ]


Two things should be noted here.

First the naming of the file has no meaning in terms of loading the file.
Unlike legacy code, language strings are loaded on demand, instead of up front.

Secondly, as these are not ``defines`` in the coding sense, we don't use uppercase constants for the key.

To access a language conversion we use a helper method

e.g.

if the file above was called auth.php we would do

::

    echo trans('auth.header-title-logoff');

Also note, to access a legacy define you can also do

::

    echo trans('SOME_DEFINE_NAME')

although if you are converting legacy code, we would argue it's better to just go ahead and create
a new language define file using the new system.

Overrides
---------

Even given the new system, people will still want to override the base language definitions.
In the past this may have meant site owners etc touching the original release file, such as english.php
As mentioned above, this has an impact on upgrading.


The new system uses a specific filesystem layout to mitigate this.

The base path for storing language definitions is

``app/resources/lang`` and under this directory are 2 futher directories, ``default`` and ``local``


The ``default`` directory represents `release` files and files within this directory should never be altered.

The ``local`` directory is where any customizations should occur.

Under both the ``default`` and ``local`` directories, definitions are then stored in a directory named after the language code.

e.g.

- app
    - resources
        - lang
            - default
                - en
                - de
            - local
                - en
                - de


So if we wanted to override ``header-title-logoff`` we would create a new file
in ``app/resources/lang/local/en/auth.php`` containing

::

    <?php
        return [
            'header-title-logoff', 'My Logoff String',
        ]


String Interpolation
--------------------

Legacy defines do not allow for direct variable interpolation.

For example

::

    define('TEXT_DISPLAY_NUMBER_OF_BANNERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> banners)');

would require the use of ``sprintf`` or similar to correctly output the define.

In v2 the Translator ``trans`` method (or the ``trans`` helper function) instead can take an array of substitution items.

e.g. if we define the language definition as

::

    'display-number-of-banners', 'Displaying <b>:start</b> to <b>:end</b> (of <b>:count</b> banners)'

we can properly display this with

::

    echo trans('display-number-of-banners', ['start' => 1, 'end' => 5, 'count' => 10]);

Now while this may feel slightly more verbose, the name hinting of interpolated strings makes it easier to see how the string will finally be displayed


Plugins
-------

See @todo plugins link

although as a quick hint plugins would access their defintions as


::

    echo trans('pluginname::auth.header-title-logoff');


Templates
---------

@todo template language override not supported at the moment as we are focused on admin
will be needed for store front support

