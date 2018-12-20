Setting Type Handlers
=====================

Setting type handlers can be defined in ``app/library/zencart/ConfigSettings/src/settingTypes``


Some settings types may not need a handler, in which case the DefaultType handler will be used.
Any custom handlers must extend the `DefaultType`.

The `DefaulType` currently provides 2 methods which may be overriden.

- getValueFromRequest

returns the value from the form, if present, otherwise returns ``null``

- transformSettingsFromDefinition

In the `DefaultType` this performs no actions, simply returning it's input as is.


As examples of why we might need to override these methods, let's first consider the `boolean` type.

The `boolean` type uses a checkbox, however checkboxes do not return anything from a form if the checkbox is not checked.

To work around this, and to return a value even when no checked we can override the
``getValueFromRequest`` method.

e.g.

::

    class BooleanType extends DefaultType
    {
        public function getValueFromRequest($request, $setting)
        {
            return $request->readPost($setting['setting_key'], 'off');
        }
    }


Another example is the `selectFromArray` type. This can store language define keys for the select dropdown options.
However as these are stored as strings in the database, we need to resolve them at runtime to their language definitions.

To do this we can use the ``transformSettingsFromDefinition`` method.

e.g.

::

    public function transformSettingsFromDefinition(array $setting)
    {
        $definition = json_decode($setting['setting_definition'], true);
        $options = $definition['options'];
        $newOptions = [];
        foreach ($options as $option) {
            $id = $option['id'];
            $text = $option['text'];
            if (defined($text)) {
                $text = constant($text);
            }
            $newOptions[] = ['id' => $id, 'text' => $text];
        }
        $definition['options'] = $newOptions;
        $definition = json_encode($definition, true);
        $setting['setting_definition'] = $definition;
        return $setting;
    }


