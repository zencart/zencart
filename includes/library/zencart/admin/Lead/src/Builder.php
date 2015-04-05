<?php
/**
 * AdminLeadBuilder Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\Admin\Lead;

use \Closure;

class Builder extends \base
{

    /**
     * @param $listingBox
     * @param $request
     */
    public function __construct($listingBox, $request)
    {
        $this->request = $request;
        $this->outputLayout = $listingBox->getOutputLayout();
        $this->listingQuery = $listingBox->getListingQuery();
        $this->leadDefinition = array();
        $this->buildLeadDefinition();
    }

    /**
     *
     */
    public function buildLeadDefinition()
    {
        $this->setActionFromRequest();
        $this->setLeadDefinitionDefaults();
        $this->mergeInOutputDefinitions();
        $this->buildFieldDefinitions();
    }

    /**
     *
     */
    protected function setActionFromRequest()
    {
        $this->leadDefinition['action'] = 'list';
        $actionMap = array('edit', 'add');
        $requestAction = $this->request->readGet('action');
        if (!isset($requestAction)) {
            return;
        }
        if (!in_array($requestAction, $actionMap)) {
            return;
        }
        $this->leadDefinition['action'] = $requestAction;
    }

    /**
     *
     */
    protected function setLeadDefinitionDefaults()
    {
        $this->processDefaults($this->getDefaultMap());
        $paginationSessKey = $this->leadDefinition['paginationSessKey'];
        $paginationQueryLimit = isset($_SESSION [$paginationSessKey]) ? $_SESSION [$paginationSessKey] : 20;
        $map = array(
            'paginationQueryLimit' => $paginationQueryLimit,
            'paginationLimitSelect' => $this->getPaginationLimitOptions(),
            'paginationLimitDefault' => $paginationQueryLimit,
            'columnCount' => count($this->outputLayout['listMap']) +2,
            'rowActions' => array(),
            ''
        );
        $this->processDefaults($map);
        $this->setLeadDefinitionActionLinks();
    }

    /**
     * @param $defaultMap
     */
    protected function  processDefaults($defaultMap)
    {
        foreach ($defaultMap as $key => $value) {
            $setVal = $value;
            if (isset($this->outputLayout[$key])) {
                $setVal = $this->outputLayout[$key];
            }
            $this->leadDefinition[$key] = $setVal;
        }
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
            'relatedLinks' => array(),
            'paginationSessKey' => issetorArray($this->outputLayout, 'paginationSessionKey',
                $this->listingQuery['mainTable']['table'] . '_pql'),
            'enctype' => isset($this->outputLayout['hasImageUpload']) ? "enctype='multipart/form-data'" : ''
        );

        return $defaultMap;
    }

    /**
     *
     */
    protected function mergeInOutputDefinitions()
    {
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
    }

    /**
     *
     */
    protected function buildRealActionLinks()
    {
        $this->leadDefinition ['actionLinks'] = array();
        foreach ($this->leadDefinition ['actionLinksList'] as $actionLink) {
            $this->processActionLinks($actionLink);
        }
    }

    protected function processActionLinks($actionLink)
    {
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

    }

    /**
     * @param $value
     */
    protected function mergeinActionLinks($value)
    {
        foreach ($value as $key => $actionEntry) {
            foreach ($actionEntry as $actionKey => $actionValue) {
                $this->leadDefinition ['actionLinksList'] [$key] [$actionKey] = $actionValue;
            }
        }
    }

    /**
     *
     */
    protected function setLeadDefinitionActionLinks()
    {
        $this->leadDefinition ['actionLinksList'] ['listView'] = array(
            'linkTitle' => TEXT_LEAD_ACTION_LIST,
            'linkCmd' => $this->request->readGet('cmd'),
            'linkGetAllGetParams' => false,
            'linkGetAllGetParamsIgnore' => array(
                'action'
            ),
            'extraLinkActions' => array()
        );
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

    /**
     *
     */
    protected function buildFieldDefinitions()
    {
        foreach ($this->outputLayout ['fields'] as $field => $options) {
            $layout = $this->buildActualLayoutFromContext($options);
            if (isset($layout ['uploadOptions'] ['imageDirectorySelector']) && $layout ['uploadOptions'] ['imageDirectorySelector'] === true) {
                $selectList = $this->buildImageDirectorySelector();
                $layout ['uploadOptions'] ['imageDirectorySelectList'] = $selectList;
            }
            $validations = isset($options ['validations']) ? $options ['validations'] : array(
                'required' => true
            );
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
    }

    /**
     * @param $options
     * @return array
     */
    protected function buildActualLayoutFromContext($options)
    {
        $defaultLayout = isset($options ['layout'] ['common']) ? $options ['layout'] ['common'] : array();
        $actualLayout = isset($options ['layout'] [$this->leadDefinition['action']]) ? $options ['layout'] [$this->leadDefinition ['action']] : array();
        $layout = array_merge($defaultLayout, $actualLayout);

        return $layout;
    }

    /**
     * @param string $baseDirectory
     * @return array
     */
    protected function buildImageDirectorySelector($baseDirectory = DIR_FS_CATALOG_IMAGES)
    {
        $selectList = array();
        $selectList [] = array(
            'id' => '',
            'text' => TEXT_SELECT_MAIN_DIRECTORY
        );
        $dir = @dir($baseDirectory);
        while ($file = $dir->read()) {
            if (is_dir($baseDirectory . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
                $selectList [] = array(
                    'id' => $file . '/',
                    'text' => $file
                );
            }
        }
        $dir->close();
        unset($dir);

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
        $this->notify('NOTIFY_LEADBUILDER_PAGINATIONLIMITOPTIONS_END', $paginationLimitOptions);
        return $paginationLimitOptions;
    }

    /**
     * @return array
     */
    public function getleadDefinition()
    {
        return $this->leadDefinition;
    }

    /**
     * @return mixed
     */
    public function getTableRows()
    {
        return $this->tableRows;
    }

    /**
     * @return mixed
     */
    public function getresultItems()
    {
        return $this->resultItems;
    }

    /**
     * @return mixed
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
