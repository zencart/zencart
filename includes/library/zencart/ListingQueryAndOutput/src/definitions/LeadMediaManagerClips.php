<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\ListingQueryAndOutput\definitions;

/**
 * Class LeadMediaManagerClips
 * @package ZenCart\ListingQueryAndOutput\definitions
 */
class LeadMediaManagerClips extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndOutput()
    {


        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_MEDIA_CLIPS,
                'alias' => 'mtp',
                'fkeyFieldLeft' => 'clip_id',
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_MEDIA_CLIPS,
                    'field' => 'media_id',
                    'value' => ':media_id:'
                )
            ),
            'bindVars' => array(
                array(
                    ':media_id:',
                    $this->request->readGet('media_id'),
                    'integer'
                )
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TABLE_HEADING_MEDIA_CLIP_NAME,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'pageTitle' => $this->getTitle(),
            //            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerMusicGenre.php',
            'allowDelete' => true,
            'extraDeleteParameters' => '&media_id=' . $this->request->readGet('media_id'),
            'allowEdit' => false,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_RECORD_ARTISTS,
                    'href' => zen_href_link(FILENAME_RECORD_ARTISTS)
                ),
                array(
                    'text' => BOX_CATALOG_RECORD_COMPANY,
                    'href' => zen_href_link(FILENAME_RECORD_COMPANY)
                ),
                array(
                    'text' => BOX_CATALOG_MUSIC_GENRE,
                    'href' => zen_href_link(FILENAME_MUSIC_GENRE)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_TYPES,
                    'href' => zen_href_link(FILENAME_MEDIA_TYPES)
                )
            ),
            'actionLinksList' => array(
                'listView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'clip_id'
                    )
                ),
                'addView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'clip_id'
                    )
                ),
                'parentView' => array(
                    'linkTitle' => TEXT_PARENT_COLLECTION,
                    'linkCmd' => FILENAME_MEDIA_MANAGER,
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'clip_id'
                    )
                )
            ),
            'hasMediaUpload' => true,
            'listMap' => array(
                'clip_filename',
            ),
            'editMap' => array(
                'media_id',
                'clip_filename',
            ),
            'autoMap' => array(
                'add' => array(
                    array(
                        'field' => 'date_added',
                        'value' => 'now()',
                        'bindVarsType' => 'passthru'
                    )
                ),
                'edit' => array(
                    array(
                        'field' => 'last_modified',
                        'value' => 'now()',
                        'bindVarsType' => 'passthru'
                    )
                )
            ),
            'fields' => array(
                'clip_filename' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_MEDIA_CLIP_NAME,
                            ),

                        'add' => array(
                            'title' => TABLE_HEADING_MEDIA_CLIP_NAME,
                            'size' => '30',
                            'type' => 'file',
                            'uploadOptions' => array(
                                'mediaDirectorySelector' => true,
                                'mediaDirectoryServer' => false,
                                'baseUploadDirectory' => DIR_FS_CATALOG_MEDIA,
                                'textMainUploadDirectiry' => TEXT_SELECT_MAIN_MEDIA_DIRECTORY,
                                'mediaPreviewTemplate' => 'partials/tplUploadMediaManagerPreview.php'
                            ),
                        )
                    )
                ),
                'media_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_MEDIA,
                            'size' => '30'
                        ),
                        'add' => array(
                            'type' => 'hidden',
                        )
                    )
                ),
                'clip_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_SELECT_PRODUCT,
                            'size' => '30',
                        ),
                    )
                ),
            ),
        );
    }

    protected function getTitle()
    {
        $sql = "SELECT media_name FROM " . TABLE_MEDIA_MANAGER . " WHERE media_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $this->request->readGet('media_id'), 'integer');
        $result = $this->dbConn->execute($sql);
        return $result->fields['media_name'];
    }

}
