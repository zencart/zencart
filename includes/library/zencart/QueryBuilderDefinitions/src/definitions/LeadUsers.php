<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadUsers
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadUsers extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $profileName = function($resultItem)
        {
            return $resultItem['profile_name'];
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_ADMIN,
                'alias' => 'a',
                'fkeyFieldLeft' => 'admin_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_ADMINS,
                    'pagingVarSrc' => 'post'
                )
            ),
            'joinTables' => array(
                'TABLE_ADMIN_PROFILES' => array(
                    'table' => TABLE_ADMIN_PROFILES,
                    'alias' => 'ap',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'admin_profile',
                    'fkeyFieldRight' => 'profile_id',
                    'selectColumns' => array('profile_name')
                ),
            ),
            'derivedItems' => array(
                array(
                    'context' => 'list',
                    'field' => 'admin_profile',
                    'handler' => $profileName
                ),
            ),
        );

        $this->outputLayout = array(
            'allowDelete' => false,
            'relatedLinks' => array(
                array(
                    'text' => BOX_ADMIN_ACCESS_PROFILES,
                    'href' => zen_href_link(FILENAME_PROFILES)
                ),
                array(
                    'text' => BOX_ADMIN_ACCESS_PAGE_REGISTRATION,
                    'href' => zen_href_link(FILENAME_ADMIN_PAGE_REGISTRATION),
                ),
                array(
                    'text' => BOX_ADMIN_ACCESS_LOGS,
                    'href' => zen_href_link(FILENAME_ADMIN_ACTIVITY),
                ),
            ),
            'listMap' => array(
                'admin_id',
                'admin_name',
                'admin_email',
                'mobile_phone',
                'admin_profile',
            ),
            'editMap' => array(
                'admin_name',
                'admin_email',
                'mobile_phone',
                'admin_profile',
//                'password',
            ),
            'autoMap' => array(
                'add' => array(
                    array(
                        'field' => 'last_modified',
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
                'admin_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'admin_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ADMIN_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '10'
                        )
                    ),
                    'validations' => array(
                        'rules' => array(
                            array('type' => 'lengthBetween', 'params'=>array(4,12))
                        )
                    )
                ),
                'admin_email' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ADMIN_EMAIL,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '20'
                        )
                    ),
                    'validations' => array(
                        'rules' => array(
                            array('type' => 'email')
                        )
                    )
                ),
                'admin_profile' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ADMIN_PROFILE,
                            'align' => 'right',
                            'type' => 'select',
                            'size' => '5'
                        ),
                        'list' => array(
                            'options' => \getProfilesList()
                        ),
                        'edit' => array(
                            'options' => \getProfilesList(false)
                        ),
                        'update' => array(
                            'options' => \getProfilesList(false)
                        ),
                        'add' => array(
                            'options' => \getProfilesList(false)
                        ),
                    ),
                ),
                'mobile_phone' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_MOBILE_PHONE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '20'
                        )
                    ),
                    'validations' => array(
                        'required' => false
                    )

                ),
                'password' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_PASSWORD_MAIN,
                            'title_confirm' => TEXT_ENTRY_PASSWORD_CONFIRM,
                            'align' => 'right',
                            'type' => 'password',
                            'size' => '40'
                        )
                    ),
                    'validations' => array(
                        'required' => false
                    )

                ),
                'profile_name' => array(
                    'bindVarsType' => 'integer',
                ),
            ),
            'formatter' => array('class' => 'AdminLead')
        );
    }

}
