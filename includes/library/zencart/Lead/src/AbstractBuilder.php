<?php
/**
 * AdminLeadBuilder Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\Lead;

use \Closure;

abstract class AbstractBuilder extends \base
{

    /**
     * @var
     */
    protected $request;
    /**
     * @var
     */
    protected $outputLayout;
    /**
     * @var
     */
    protected $listingQuery;
    /**
     * @var array
     */
    protected $leadDefinition;

    /**
     * @param $listingBox
     * @param $request
     */
    public function __construct($listingBox, $request, $type = null)
    {
        $this->notify('NOTIFY_LEADBUILDER_CONSTRUCTOR_START');
        $this->request = $request;
        $this->outputLayout = $listingBox->getOutputLayout($type);
        $this->listingQuery = $listingBox->getListingQuery($type);
        $this->leadDefinition = array();
        $this->buildLeadDefinition();
        $this->notify('NOTIFY_LEADBUILDER_CONSTRUCTOR_END');
    }

    /**
     *
     */
    public function buildLeadDefinition()
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDLEADDEFINITION_START');
        $this->setActionFromRequest();
        $this->setLeadDefinitionDefaults();
        $this->mergeInOutputDefinitions();
        $this->buildFieldDefinitions();
        $this->notify('NOTIFY_LEADBUILDER_BUILDLEADDEFINITION_END');
    }

    /**
     *
     */
    protected function setActionFromRequest()
    {
        $this->notify('NOTIFY_LEADBUILDER_SETACTIONFROMREQUEST_START');
        $this->leadDefinition['action'] = 'list';
        $actionMap = array('edit', 'add', 'update');
        $requestAction = $this->request->readGet('action');
        $this->notify('NOTIFY_LEADBUILDER_SETACTIONFROMREQUEST_PRE_GUARD', array(), $requestAction, $actionMap);
        if (!isset($requestAction)) {
            return;
        }
        if (!in_array($requestAction, $actionMap)) {
            return;
        }
        $this->leadDefinition['action'] = $requestAction;
        $this->notify('NOTIFY_LEADBUILDER_SETACTIONFROMREQUEST_END');
    }

    /**
     *
     */
    protected function setLeadDefinitionDefaults()
    {
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONDEFAULTS_START');
        $this->processDefaults($this->getDefaultMap());
        $paginationSessKey = $this->leadDefinition['paginationSessKey'];
        $paginationQueryLimit = isset($_SESSION [$paginationSessKey]) ? $_SESSION [$paginationSessKey] : 20;
        $map = array(
            'paginationQueryLimit' => $paginationQueryLimit,
            'paginationLimitSelect' => $this->getPaginationLimitOptions(),
            'paginationLimitDefault' => $paginationQueryLimit,
            'columnCount' => count($this->outputLayout['listMap']) +2,
            'rowActions' => array(),
        );
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONDEFAULTS_SETMAP', array(), $paginationSessKey, $paginationQueryLimit, $map);
        $this->processDefaults($map);
        $this->setLeadDefinitionActionLinks();
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONDEFAULTS_END');
    }

    /**
     * @param $defaultMap
     */
    protected function  processDefaults($defaultMap)
    {
        $this->notify('NOTIFY_LEADBUILDER_PROCESSDEFAULTS_START', array(), $defaultMap);
        foreach ($defaultMap as $key => $value) {
            $setVal = $value;
            if (isset($this->outputLayout[$key])) {
                $setVal = $this->outputLayout[$key];
            }
            $this->leadDefinition[$key] = $setVal;
        }
        $this->mamageRowActions();
        $this->notify('NOTIFY_LEADBUILDER_PROCESSDEFAULTS_END');
    }

    /**
     * @return array
     */
    protected function getDefaultMap()
    {
        $defaultMap = array(
            'pageTitle' => HEADING_TITLE,
            'contentTemplate' => 'tplAdminLeadListContent.php',
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandler.php',
            'inputLabelTemplate' => 'leadInputTypes/tplInputLabel.php',
            'autoCompleteTemplate' => '/tplAutoComplete.php',
            'errorTemplate' => 'leadInputTypes/tplInputError.php',
            'listMap' => $this->outputLayout ['listMap'],
            'editMap' => $this->outputLayout ['editMap'],
            'multiEditMap' => array(),
            'headerTemplate' => false,
            'extraRowActions' => null,
            'mainTableFkeyField' => $this->listingQuery ['mainTable']['fkeyFieldLeft'],
            'hasImageUpload' => false,
            'allowEdit' => true,
            'allowDelete' => false,
            'allowMultiDelete' => true,
            'allowAdd' => true,
            'actionLinksList' => array(),
            'showActionLinkListList' => true,
            'relatedLinks' => array(),
            'paginationSessKey' => issetorArray($this->outputLayout, 'paginationSessionKey',
                $this->listingQuery['mainTable']['table'] . '_pql'),
            'enctype' => isset($this->outputLayout['hasImageUpload']) ? "enctype='multipart/form-data'" : ''
        );
        $this->notify('NOTIFY_LEADBUILDER_GETDEFAULTMAP_END', array(), $defaultMap);
        return $defaultMap;
    }

    protected function mamageRowActions()
    {
        $hasRowActions = false;
        if ($this->leadDefinition["allowEdit"]) {
            $hasRowActions = true;
        }
        if ($this->leadDefinition["allowEdit"]) {
            $hasRowActions = true;
        }
        if (isset($this->leadDefinition ['extraRowActions'])) {
            $hasRowActions = true;
        }
        $this->leadDefinition["hasRowActions"] = $hasRowActions;
    }
    /**
     *
     */
    protected function mergeInOutputDefinitions()
    {
        $this->notify('NOTIFY_LEADBUILDER_MERGEINOUTPUTDEFINITIONS_START');
        foreach ($this->outputLayout as $key => $value) {
            if ($key != 'actionLinksList') {
                $this->leadDefinition [$key] = $value;
            } else {
                $this->mergeInActionLinks($value);
            }
        }
        $this->buildRealActionLinks();
        if ($this->leadDefinition ['allowDelete'] == false) {
            $this->leadDefinition ['allowMultiDelete'] = false;
        }
        $this->leadDefinition ['showMultiActions'] = $this->leadDefinition ['multiEdit'] || $this->leadDefinition ['allowMultiDelete'];
        $this->notify('NOTIFY_LEADBUILDER_MERGEINOUTPUTDEFINITIONS_END');
    }

    /**
     *
     */
    protected function buildRealActionLinks()
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDREALACTIONLINKS_START');
        $this->leadDefinition ['actionLinks'] = array();
        foreach ($this->leadDefinition ['actionLinksList'] as $actionLink) {
            $this->processActionLinks($actionLink);
        }
        $this->notify('NOTIFY_LEADBUILDER_BUILDREALACTIONLINKS_END');
    }

    protected function processActionLinks($actionLink)
    {
        $this->notify('NOTIFY_LEADBUILDER_PROCESSACTIONLINKS_START');
        $linkParameters = '';
        if ($actionLink ['linkGetAllGetParams']) {
            $linkParameters = zen_get_all_get_params($actionLink ['linkGetAllGetParamsIgnore']);
        }
        if ($actionLink ['extraLinkActions']) {
            foreach ($actionLink ['extraLinkActions'] as $actionValue) {
                $linkParameters .= key($actionValue) . '=' . $actionValue [key($actionValue)] . '&';
            }
        }
        $link = zen_href_link($actionLink ['linkCmd'], $linkParameters);
        $this->leadDefinition ['actionLinks'] [] = array(
            'href' => $link,
            'text' => $actionLink ['linkTitle']
        );
        $this->notify('NOTIFY_LEADBUILDER_PROCESSACTIONLINKS_END');
    }

    /**
     * @param $value
     */
    protected function mergeinActionLinks($value)
    {
        $this->notify('NOTIFY_LEADBUILDER_MERGEINACTIONLINKS_START');
        foreach ($value as $key => $actionEntry) {
            foreach ($actionEntry as $actionKey => $actionValue) {
                $this->leadDefinition ['actionLinksList'] [$key] [$actionKey] = $actionValue;
            }
        }
        $this->notify('NOTIFY_LEADBUILDER_MERGEINACTIONLINKS_END');
    }

    /**
     *
     */
    protected function setLeadDefinitionActionLinks()
    {
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONACTIONLINKS_START');
        if ($this->leadDefinition ['showActionLinkListList']) {
            $this->leadDefinition ['actionLinksList'] ['listView'] = array(
                'linkTitle' => TEXT_LEAD_ACTION_LIST,
                'linkCmd' => $this->request->readGet('cmd'),
                'linkGetAllGetParams' => false,
                'linkGetAllGetParamsIgnore' => array(
                    'action'
                ),
                'extraLinkActions' => array()
            );
        }
        if ($this->leadDefinition ['allowAdd']) {
            $this->leadDefinition ['actionLinksList'] ['addView'] = array(
                'linkTitle' => TEXT_LEAD_ACTION_ADD_ENTRY,
                'linkCmd' => $this->request->readGet('cmd'),
                'linkGetAllGetParams' => false,
                'linkGetAllGetParamsIgnore' => array(
                    'action'
                ),
                'extraLinkActions' => array(
                    array(
                        'action' => 'add'
                    )
                )
            );
        }
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONACTIONLINKS_END');
    }

    /**
     *
     */
    protected function buildFieldDefinitions()
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDFIELDDEFINITIONS_START');
        foreach ($this->outputLayout ['fields'] as $field => $options) {
            $layout = $this->buildActualLayoutFromContext($options);
            if (isset($layout ['uploadOptions'] ['imageDirectorySelector']) && $layout ['uploadOptions'] ['imageDirectorySelector'] === true) {
                $selectList = $this->buildImageDirectorySelector();
                $layout ['uploadOptions'] ['imageDirectorySelectList'] = $selectList;
            }
            $validations = isset($options ['validations']) ? $options ['validations'] : array(
                'required' => true
            );
            $validations ['rules'] = isset($validations ['rules']) ? $validations ['rules'] : array();
            $validations ['required'] = isset($validations ['required']) ? $validations ['required'] : true;
            $validations ['pattern'] = isset($validations ['pattern']) ? $validations ['pattern'] : '';
            $validations ['errorText'] = isset($validations ['errorText']) ? $validations ['errorText'] : (defined('TEXT_FIELD_ERROR_' . strtoupper($field))) ? constant('TEXT_FIELD_ERROR_' . strtoupper($field)) : TEXT_FIELD_ERROR_GENERIC;
            $this->leadDefinition ['fields'] [$field] ['fieldType'] = isset($options ['fieldType']) ? $options ['fieldType'] : 'standard';
            $this->leadDefinition ['fields'] [$field] ['layout'] = $layout;
            $this->leadDefinition ['fields'] [$field] ['validations'] = $validations;
            $this->leadDefinition ['fields'] [$field] ['value'] = '';
            $this->leadDefinition ['fields'] [$field] ['field'] = 'entry_field_' . $field;
            $this->leadDefinition ['fields'] [$field] ['autocomplete'] = isset($options ['autocomplete']) ? $options ['autocomplete'] : false;
        }
        $this->notify('NOTIFY_LEADBUILDER_BUILDFIELDDEFINITIONS_END');
    }

    /**
     * @param $options
     * @return array
     */
    protected function buildActualLayoutFromContext($options)
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDACTIONLAYOUTFROMCONTEXT_START', array(), $options);
        $defaultLayout = isset($options ['layout'] ['common']) ? $options ['layout'] ['common'] : array();
        $actualLayout = isset($options ['layout'] [$this->leadDefinition['action']]) ? $options ['layout'] [$this->leadDefinition ['action']] : array();
        $layout = array_merge($defaultLayout, $actualLayout);
        $this->notify('NOTIFY_LEADBUILDER_BUILDACTIONLAYOUTFROMCONTEXT_END', array(), $layout);
        return $layout;
    }

    /**
     * Build a list of subdirectories found in the specified $baseDirectory
     * Returns formatted array in 'id/text' pairs for use in SELECT pulldowns
     *
     * @param string $baseDirectory
     * @return array
     */
    protected function buildImageDirectorySelector($baseDirectory = DIR_FS_CATALOG_IMAGES)
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDIMAGEDIRECTORYSELECTOR_START', array(), $baseDirectory);
        $selectList = array();
        $selectList [] = array(
            'id' => '',
            'text' => TEXT_SELECT_MAIN_DIRECTORY
        );
        $dir = @dir($baseDirectory);
        while ($file = $dir->read()) {
            if (is_dir($baseDirectory . $file) && $file != "." && $file != "..") {
                $selectList [] = array(
                    'id' => $file . '/',
                    'text' => $file
                );
            }
        }
        $dir->close();
        unset($dir);
        sort($selectList);
        $this->notify('NOTIFY_LEADBUILDER_BUILDIMAGEDIRECTORYSELECTOR_END', array(), $selectList);
        return $selectList;
    }

    /**
     * @return array
     */
    protected function getPaginationLimitOptions()
    {
        $paginationLimitOptions = array(
            array(
                'id' => 5,
                'text' => 5
            ),
            array(
                'id' => 10,
                'text' => 10
            ),
            array(
                'id' => 15,
                'text' => 15
            ),
            array(
                'id' => 20,
                'text' => 20
            ),
            array(
                'id' => 30,
                'text' => 30
            ),
            array(
                'id' => 50,
                'text' => 50
            )
        );
        $this->notify('NOTIFY_LEADBUILDER_PAGINATIONLIMITOPTIONS_END', array(), $paginationLimitOptions);
        return $paginationLimitOptions;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getOutputLayout()
    {
        return $this->outputLayout;
    }

    /**
     * @param mixed $outputLayout
     */
    public function setOutputLayout($outputLayout)
    {
        $this->outputLayout = $outputLayout;
    }

    /**
     * @return mixed
     */
    public function getListingQuery()
    {
        return $this->listingQuery;
    }

    /**
     * @param mixed $listingQuery
     */
    public function setListingQuery($listingQuery)
    {
        $this->listingQuery = $listingQuery;
    }

    /**
     * @return array
     */
    public function getLeadDefinition()
    {
        return $this->leadDefinition;
    }

    /**
     * @param array $leadDefinition
     */
    public function setLeadDefinition($leadDefinition)
    {
        $this->leadDefinition = $leadDefinition;
    }
}
