<?php
/**
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
*/
$define = [
    'HEADING_TITLE' => 'Customer Groups',
    'TABLE_HEADING_GROUP_NAME' => 'Group Name',
    'TABLE_HEADING_GROUP_CUSTOMER_COUNT' => '# of Customers',
    'TABLE_HEADING_GROUP_COMMENTS' => 'Comment/Description',
    'TEXT_HEADING_ADD_GROUP' => 'Add Group',
    'TEXT_HEADING_EDIT_GROUP' => 'Edit Group',
    'TEXT_HEADING_DELETE_GROUP' => 'Delete Group',
    'TEXT_INFORMATION' => 'You can create customer groups which can be used elsewhere in your store for various special permissions. Some payment modules also allow being enabled for only a certain customer group. Customers may belong to multiple groups; however you should review assignments regularly and prune them for optimal performance.',
    'TEXT_NO_GROUPS_FOUND' => 'No groups found. Click INSERT to create one.',
    'TEXT_NEW_INTRO' => 'Please describe the new group',
    'TEXT_DELETE_INTRO' => 'Are you sure you want to delete this group?',
    'TEXT_DELETE_EVEN_IF_CUSTOMERS_ASSIGNED' => 'Delete even though group has customers assigned',
    'TEXT_DELETE_WARNING_GROUP_MEMBERS_EXIST' => '<b>WARNING:</b> There are %s customers still linked to this group!',
    'TEXT_GROUP_NAME' => 'Group Name:',
    'TEXT_GROUP_COMMENT' => 'Comment/Description:',
    'TEXT_CUSTOMERS_IN_GROUP' => 'Customers in Group:',
    'ERROR_GROUP_STILL_HAS_CUSTOMERS' => 'ERROR: Customers are assigned to this group. Please confirm that you wish to remove all members from the group and delete it.',
];

return $define;
