<?php

class zcObserverTableControllerTest
{
    use \Zencart\Traits\ObserverManager;

    public function __construct()
    {
        $this->attach($this, ['NOTIFY_DATASOURCE_CONSTRUCTOR_END', 'NOTIFY_TABLEVIEW_PROCESSREQUEST']);
    }

    public function updateNotifyDatasourceConstructorEnd(&$class, $eventID)
    {
        $testClosure = function ($tableRow, $colName, $columnInfo) {
            return (string)rand(1, 100);
        };
        $td = $class->getTableDefinition();
        $td->setParameter('maxRowCount', 4)->addColumnBefore('version', 'test', ['title' => 'Test', 'derivedItem' => [
            'type' => 'closure',
            'method' => $testClosure
        ]
        ]);
        $td->addRowAction(['action' => 'foo', 'icon' => 'fa-info', 'linkParams' => [['source' => 'tableRow', 'field' => 'status', 'param' => 'status']]]);
        $td->addButtonAction(['action' => 'new', 'title' => 'New', 'buttonClass' => 'btn-primary', 'blacklist' => ['new']]);

        $class->setTableDefinition($td);
    }

    public function updateNotifyTableviewProcessrequest(&$class, $eventId, $param1, $method)
    {
        if ($method == 'processActionFoo') {
            $class->setBoxHeader('FOO');
            $class->setBoxContent('BAR');
        }
        if ($method == 'processActionNew') {
            $class->setBoxHeader('NEW');
            $class->setBoxContent('BAR');
        }
    }
}
