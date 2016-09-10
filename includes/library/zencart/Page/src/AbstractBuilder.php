<?php
/**
 * AdminLeadBuilder Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\Page;

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
    protected $pageDefinition;

    /**
     * @param $listingBox
     * @param $request
     */
    public function __construct($listingBox, $request)
    {
        $this->notify('NOTIFY_LEADBUILDER_CONSTRUCTOR_START');
        $this->request = $request;
        $this->outputLayout = $listingBox->getOutputLayout();
        $this->listingQuery = $listingBox->getListingQuery();
        $this->pageDefinition = array();
        $this->buildPageDefinition();
        $this->notify('NOTIFY_LEADBUILDER_CONSTRUCTOR_END');
    }

    /**
     *
     */
    public function buildPageDefinition()
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDLEADDEFINITION_START');
        $this->setActionFromRequest();
        $this->setPageDefinitionDefaults();
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
        $this->pageDefinition['action'] = 'list';
        $actionMap = array('edit', 'add', 'update');
        $requestAction = $this->request->readGet('action');
        $this->notify('NOTIFY_LEADBUILDER_SETACTIONFROMREQUEST_PRE_GUARD', array(), $requestAction, $actionMap);
        if (!isset($requestAction)) {
            return;
        }
        if (!in_array($requestAction, $actionMap)) {
            return;
        }
        $this->pageDefinition['action'] = $requestAction;
        $this->notify('NOTIFY_LEADBUILDER_SETACTIONFROMREQUEST_END');
    }

    /**
     *
     */
    protected function setPageDefinitionDefaults()
    {
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONDEFAULTS_START');
        $this->processDefaults($this->getDefaultMap());
        $paginationSessKey = $this->pageDefinition['paginationSessKey'];
        $paginationQueryLimit = isset($_SESSION [$paginationSessKey]) ? $_SESSION [$paginationSessKey] : 20;
        $map = array(
            'paginationQueryLimit' => $paginationQueryLimit,
            'paginationLimitSelect' => $this->getPaginationLimitOptions(),
            'paginationLimitDefault' => $paginationQueryLimit,
            'columnCount' => count($this->outputLayout['listMap']) + 2,
            'rowActions' => array(),
        );
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONDEFAULTS_SETMAP', array(), $paginationSessKey, $paginationQueryLimit,
            $map);
        $this->processDefaults($map);
        $this->setPageDefinitionActionLinks();
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
            $this->pageDefinition[$key] = $setVal;
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
            'select2DriverTemplate' => '/tplSelect2Driver.php',
            'errorTemplate' => 'leadInputTypes/tplInputError.php',
            'listMap' => $this->outputLayout ['listMap'],
            'editMap' => $this->outputLayout ['editMap'],
            'multiEditMap' => array(),
            'headerTemplate' => false,
            'extraRowActions' => null,
            'mainTableFkeyField' => $this->listingQuery ['mainTable']['fkeyFieldLeft'],
            'hasMediaUpload' => false,
            'allowEdit' => true,
            'allowDelete' => false,
            'allowMultiDelete' => true,
            'allowAdd' => true,
            'actionLinksList' => array(),
            'showActionLinkListList' => true,
            'relatedLinks' => array(),
            'paginationSessKey' => issetorArray($this->outputLayout, 'paginationSessionKey',
                $this->listingQuery['mainTable']['table'] . '_pql'),
            'enctype' => isset($this->outputLayout['hasMediaUpload']) ? "enctype='multipart/form-data'" : '',
            'extraHandlerTemplates' => isset($this->outputLayout['extraHandlerTemplates']) ? $this->outputLayout['extraHandlerTemplates'] : array(),
        );
        $this->notify('NOTIFY_LEADBUILDER_GETDEFAULTMAP_END', array(), $defaultMap);
        return $defaultMap;
    }

    protected function mamageRowActions()
    {
        $hasRowActions = false;
        if ($this->pageDefinition["allowEdit"]) {
            $hasRowActions = true;
        }
        if ($this->pageDefinition["allowDelete"]) {
            $hasRowActions = true;
        }
        if (isset($this->pageDefinition ['extraRowActions'])) {
            $hasRowActions = true;
        }
        $this->pageDefinition["hasRowActions"] = $hasRowActions;
    }

    /**
     *
     */
    protected function mergeInOutputDefinitions()
    {
        $this->notify('NOTIFY_LEADBUILDER_MERGEINOUTPUTDEFINITIONS_START');
        foreach ($this->outputLayout as $key => $value) {
            if ($key != 'actionLinksList') {
                $this->pageDefinition [$key] = $value;
            } else {
                $this->mergeInActionLinks($value);
            }
        }
        $this->buildRealActionLinks();
        if ($this->pageDefinition ['allowDelete'] == false) {
            $this->pageDefinition ['allowMultiDelete'] = false;
        }
        $this->pageDefinition ['showMultiActions'] = $this->pageDefinition ['multiEdit'] || $this->pageDefinition ['allowMultiDelete'];
        $this->notify('NOTIFY_LEADBUILDER_MERGEINOUTPUTDEFINITIONS_END');
    }

    /**
     *
     */
    protected function buildRealActionLinks()
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDREALACTIONLINKS_START');
        $this->pageDefinition ['actionLinks'] = array();
        foreach ($this->pageDefinition ['actionLinksList'] as $actionLink) {
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
        $this->pageDefinition ['actionLinks'] [] = array(
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
                $this->pageDefinition ['actionLinksList'] [$key] [$actionKey] = $actionValue;
            }
        }
        $this->notify('NOTIFY_LEADBUILDER_MERGEINACTIONLINKS_END');
    }

    /**
     *
     */
    protected function setPageDefinitionActionLinks()
    {
        $this->notify('NOTIFY_LEADBUILDER_SETLEADDEFINITIONACTIONLINKS_START');
        if ($this->pageDefinition ['showActionLinkListList']) {
            $this->pageDefinition ['actionLinksList'] ['listView'] = array(
                'linkTitle' => TEXT_LEAD_ACTION_LIST,
                'linkCmd' => $this->request->readGet('cmd'),
                'linkGetAllGetParams' => false,
                'linkGetAllGetParamsIgnore' => array(
                    'action'
                ),
                'extraLinkActions' => array()
            );
        }
        if ($this->pageDefinition ['allowAdd']) {
            $this->pageDefinition ['actionLinksList'] ['addView'] = array(
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
            $layout['uploadOptions'] = $this->buildUploadFileOptions($layout);
            $validations = $this->getValidationsforField($field);
            $this->pageDefinition ['fields'] [$field] ['fieldType'] = isset($options ['fieldType']) ? $options ['fieldType'] : 'standard';
            $this->pageDefinition ['fields'] [$field] ['layout'] = $layout;
            $this->pageDefinition ['fields'] [$field] ['validations'] = $validations;
            $this->pageDefinition ['fields'] [$field] ['value'] = '';
            $this->pageDefinition ['fields'] [$field] ['field'] = 'entry_field_' . $field;
            $this->pageDefinition ['fields'] [$field] ['fillByLookup'] = isset($options ['fillByLookup']) ? $options ['fillByLookup'] : false;
        }

        $this->notify('NOTIFY_LEADBUILDER_BUILDFIELDDEFINITIONS_END');
    }

    protected function getValidationsforField($field)
    {
        $validations = array();
        $validations ['required'] = isset($this->outputLayout['validations'][$field]['required']) ? $this->outputLayout['validations'][$field]['required'] : true;
        $validations ['rules'] = isset($this->outputLayout['validations'][$field]['rules']) ? $this->outputLayout['validations'][$field]['rules'] : array();
        $validations ['pattern'] = isset($this->outputLayout['validations'][$field]['pattern']) ? $this->outputLayout['validations'][$field]['pattern'] : '';
        $validations ['errorText'] = isset($this->outputLayout['validations'][$field]['errorText']) ? $this->outputLayout['validations'][$field]['errorText'] : '';
        return $validations;
    }

    protected function buildUploadFileOptions($layout)
    {
        $uploadOptions = array(
            'mediaDirectorySelector' => false,
            'mediaDirectoryServer' => false,
            'baseUploadDirectory' => DIR_FS_CATALOG_IMAGES,
            'textMainUploadDirectiry' => TEXT_SELECT_MAIN_DIRECTORY,
            'textMissingMedia' => TEXT_NONEXISTENT_IMAGE,
            'mediaPreviewTemplate' => 'partials/tplUploadPreview.php'
        );

        if (isset($layout['uploadOptions'])) {
            $uploadOptions = array_merge($uploadOptions, $layout['uploadOptions']);
            }

        if ( $uploadOptions ['mediaDirectorySelector'] === true) {
            $selectList = $this->buildMediaDirectorySelector($uploadOptions);
            $uploadOptions ['imageDirectorySelectList'] = $selectList;
        }

        return $uploadOptions;

    }

    /**
     * @param $options
     * @return array
     */
    protected function buildActualLayoutFromContext($options)
    {
        $this->notify('NOTIFY_LEADBUILDER_BUILDACTIONLAYOUTFROMCONTEXT_START', array(), $options);
        $defaultLayout = isset($options ['layout'] ['common']) ? $options ['layout'] ['common'] : array();
        $actualLayout = isset($options ['layout'] [$this->pageDefinition['action']]) ? $options ['layout'] [$this->pageDefinition ['action']] : array();
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
    protected function buildMediaDirectorySelector($uploadOptions)
    {
        $baseDirectory = $uploadOptions['baseUploadDirectory'];
        $this->notify('NOTIFY_LEADBUILDER_BUILDMEDIADIRECTORYSELECTOR_START', array(), $baseDirectory);
        $selectList = array();
        $selectList [] = array(
            'id' => '',
            'text' => $uploadOptions['textMainUploadDirectiry']
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
        $this->notify('NOTIFY_LEADBUILDER_BUILDMEDIADIRECTORYSELECTOR_END', array(), $selectList);
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
    public function getPageDefinition()
    {
        return $this->pageDefinition;
    }

    /**
     * @param array $pageDefinition
     */
    public function setPageDefinition($pageDefinition)
    {
        $this->pageDefinition = $pageDefinition;
    }
}
