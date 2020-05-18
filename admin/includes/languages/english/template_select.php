<?php
/**
 * @package admin
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.7 $
 */

define('HEADING_TITLE', 'Template Selection');
define('TEXT_TEMPLATE_SELECT_INFO', '<p>A different template may be assigned to each of the installed languages.<br>The default template is used when there is no template assigned to that language.</p><p>It is possible to view a template in a <em>private</em> session, for example when testing a new template:</p>
<ol><li>Assign the current/active template to <strong>all</strong> of the installed languages.</li><li>Assign the new template as the Default.</li><li>Add your (admin) IP address to Configuration->Website Maintenance->Down For Maintenance (exclude this IP-Address).</li><li>When viewing the shop front, to override the template display, add "&amp;t=new_template_directory_name" to the page url. This override will persist for the duration of the session. Use private browsing/incognito tabs to create separate sessions/use different templates.</li> <li>Add "&amp;t=off" to the page url to cancel the session override.</li> </ol></ul>');
define('TABLE_HEADING_LANGUAGE', 'Template Language');
define('TABLE_HEADING_NAME', 'Template Name');
define('TABLE_HEADING_DIRECTORY', 'Template Directory');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_HEADING_LANGUAGE', 'Language');
define('TEXT_INFO_DEFAULT_LANGUAGE', 'Default (any)');
define('TEXT_INFO_DEFAULT_TEMPLATE', 'This template is used by default / when an installed language does not have an assigned template.');
define('TEXT_INFO_HEADING_EDIT_TEMPLATE', 'Edit Template Settings');
define('TEXT_INFO_HEADING_DELETE_TEMPLATE', 'Delete Template association');
define('TEXT_INFO_EDIT_INTRO', 'Change the Template');
define('TEXT_INFO_DELETE_INTRO', 'Delete this template-language association');
define('TEXT_INFO_TEMPLATE_NAME', 'Template Name');
define('TEXT_INFO_LANGUAGE_NAME', 'Language Name');
define('TEXT_INFO_TEMPLATE_VERSION', 'Template Version: ');
define('TEXT_INFO_TEMPLATE_AUTHOR', 'Template Author: ');
define('TEXT_INFO_TEMPLATE_DESCRIPTION', 'Template Description:');
define('TEXT_INFO_TEMPLATE_INSTALLED', 'Templates Available');
define('TEXT_INFO_HEADING_NEW_TEMPLATE', 'Associate Template with a language');
define('TEXT_INFO_INSERT_INTRO', 'Choose below to associate a template with a language');
define('IMAGE_NEW_TEMPLATE', 'Create a new template/language association');
