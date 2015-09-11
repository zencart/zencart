<?php
/**
 * Version Manager Class
 *
 * This class is used during the installation and upgrade processes
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Sep 10 2015 Modified in v1.5.5 $
 */



/*////////////////////////////////////////////////////////////////////////////////////////////////////////////
////  HOW TO UPDATE FOR NEW RELEASES:
////  a. in function zen_database() below, set the $this->latest_version appropriately
////  b. in function check_check_all_versions(), update:
////     i)  add a line to call a new check_versionXXXX() function
////     ii) add another IF statement to set the displayed version text ($retVal)
////  c. add a new check_versionXXXX() function to the end of the class (BEFORE the closing } in the file)
////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

  class versionManager extends base{
    var $latest_version, $found_version, $zdb_configuration_table_found;

    function versionManager() {
      /**
       * The version that this edition of the installer is designed to support
       */
      $this->latest_version = '1.5.5';

      /**
       * Check to see if the configuration table can be found...thus validating the installation, in part.
       */
      $this->zdb_configuration_table_found = $this->check_configuration_table();
      /**
       * Check to see which versions are successfully detected
       */
      $this->found_version = $this->check_check_all_versions();
    }

    function check_configuration_table() {
      global $db_test;
// Check to see if any Zen Cart tables exist
      $tables = $db_test->Execute("SHOW TABLES like '".DB_PREFIX."configuration'");
       if (ZC_UPG_DEBUG==true) echo 'ZEN-Configuration (should be 1) = '. $tables->RecordCount() .'<br>';
       if ($tables->RecordCount() > 0) {
         return true;
       }
    }

    function check_check_all_versions() {
      if (!$this->zdb_configuration_table_found) return false;
      $this->version110 = $this->check_version_110();
      $this->version111 = $this->check_version_111();
      $this->version112 = $this->check_version_112();  // no way to determine
      $this->version113 = $this->check_version_113();  // there were no db changes for 1.1.3
      $this->version114 = $this->check_version_114();
      $this->version1141 = $this->check_version_1141();
      $this->version120 = $this->check_version_120();
      $this->version121 = $this->check_version_121();
      $this->version122 = $this->check_version_122();
      $this->version123 = $this->check_version_123();
      $this->version124 = $this->check_version_124();
      $this->delete_bad_1_2_4_index_key();
      $this->version125 = $this->check_version_125();
      $this->version126 = $this->check_version_126();
      $this->version127 = $this->check_version_127();
      $this->version130 = $this->check_version_130();
      $this->version1301 = $this->check_version_1301();
      $this->version1302 = $this->check_version_1302();
      $this->version135 = $this->check_version_135();
      $this->version136 = $this->check_version_136();
      $this->version137 = $this->check_version_137();
      $this->version138 = $this->check_version_138();
      $this->version139 = $this->check_version_139();
      $this->version150 = $this->check_version_150();
      $this->version151 = $this->check_version_151();
      $this->version152 = $this->check_version_152();
      $this->version153 = $this->check_version_153();
      $this->version154 = $this->check_version_154();
      $this->version155 = $this->check_version_155();

      if ($this->version110 == true)  $retVal = '1.1.0';
      if ($this->version111 == true)  $retVal = '1.1.1';
      if ($this->version112 == true)  $retVal = '1.1.2 or 1.1.3';
      if ($this->version114 == true)  $retVal = '1.1.4';
      if ($this->version1141 == true) $retVal = '1.1.4-patch1';
      if ($this->version120 == true)  $retVal = '1.2.0';
      if ($this->version121 == true)  $retVal = '1.2.1';
      if ($this->version122 == true)  $retVal = '1.2.2';
      if ($this->version123 == true)  $retVal = '1.2.3';
      if ($this->version124 == true)  $retVal = '1.2.4';
      if ($this->version125 == true)  $retVal = '1.2.5';
      if ($this->version126 == true)  $retVal = '1.2.6';
      if ($this->version127 == true)  $retVal = '1.2.7';
      if ($this->version130 == true)  $retVal = '1.3.0';
      if ($this->version1301 == true) $retVal = '1.3.0.1';
      if ($this->version1302 == true) $retVal = '1.3.0.2';
      if ($this->version135 == true) $retVal = '1.3.5';
      if ($this->version136 == true) $retVal = '1.3.6';
      if ($this->version137 == true) $retVal = '1.3.7';
      if ($this->version138 == true) $retVal = '1.3.8';
      if ($this->version139 == true) $retVal = '1.3.9';
      if ($this->version150 == true) $retVal = '1.5.0';
      if ($this->version151 == true) $retVal = '1.5.1';
      if ($this->version152 == true) $retVal = '1.5.2';
      if ($this->version153 == true) $retVal = '1.5.3';
      if ($this->version154 == true) $retVal = '1.5.4';
      if ($this->version155 == true) $retVal = '1.5.5';

      return $retVal;
    }

    function check_version_110() {
      global $db_test;
      // first test to see if they have run the 1.1 upgrade script   (v1.0.4 to v.1.1.1)
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "files_uploaded'");
       if (ZC_UPG_DEBUG==true) echo '104-Table (should be 1) = '. $tables->RecordCount() .'<br>';
       if ($tables->RecordCount() > 0) {
          return true;
      }
    } //end of 1.1.0 check

    function check_version_111() {
      global $db_test;
      // test to see if they have run the 1.1 -> 1.1.1 bugfix update
      $got_v1_1_1 = false;
      $sql = "SELECT count(*) as count FROM " . DB_PREFIX . "configuration WHERE configuration_key = 'CATEGORIES_COUNT_ZERO'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo 'v111-count (should be 1) =' . $result->fields['count'] .'<br>';
      if ($result->fields['count'] > '0') {
        $got_v1_1_1 = true;
      }
      return $got_v1_1_1;
    } //end of 1.1.1 check

    function check_version_112() {
      global $db_test;
      if (ZC_UPG_DEBUG==true) echo '112-unknown-no way to determine<br>';
      return true;
    } //end of 1.1.2 check

    function check_version_113(){
    // there were no critical SQL changes from v1.1.2 to v1.1.3 -- just to change a default, but such change shouldn't
    // be necessary if the installed shop/store is already functional, unless can't get free-shipping for 0-weight to work
      if (ZC_UPG_DEBUG==true) echo '113-unknown-no way to determine<br>';
      return true;
    }

    function check_version_114(){
      global $db_test;
      // test to see if they have run the 1.1.2 -> 1.1.4 update
      $sql = "show fields from " . DB_PREFIX . "customers_basket_attributes";   // could we use "describe" as well ?
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
      if (ZC_UPG_DEBUG==true) echo "114-fields (need products_options_sort_order) =" . $result->fields['Field'] . '<br>';
       if  ($result->fields['Field'] == 'products_options_sort_order') {
         if ($result->fields['Type'] == 'text')  {
           $got_v1_1_4 = true;
         }
       }
        $result->MoveNext();
      }
      return $got_v1_1_4;
    } //end of 1.1.4 check

    function check_version_1141() {
      global $db_test;
      // test to see if they have run the 1.1.4 -> PATCH1 update
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='SHIPPING_BOX_WEIGHT'";   // could use "describe" as well ?
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
      if (ZC_UPG_DEBUG==true) echo "114patch-fields=" . $result->fields['configuration_title'] . '<br>';
        if  ($result->fields['configuration_title'] == 'Package Tare Small to Medium - added percentage:weight') {
           $got_v1_1_4_patch1 = true;
        }
        $result->MoveNext();
      }
      return $got_v1_1_4_patch1;
    } // end 1.1.4-patch1 check

    function check_version_120() {
      global $db_test;
      // test to see if the v1.1.4->v1.2.0 upgrade has been completed
      //1st check for v1.20
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='TUTORIAL_STATUS'";
      $result = $db_test->Execute($sql);
      $got_v1_2_0a = true;  // set true -- if value found (but should be deleted), then set to false.
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "120a-configtitle=" . $result->fields['configuration_title'] . '<br>';
          if  ($result->fields['configuration_title'] != '') {
            $got_v1_2_0a = false;
          }
          $result->MoveNext();
        }
      //2nd check for v1.20
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "product_type_layout'");
      if (ZC_UPG_DEBUG==true) echo '120b-Table= '. $tables->RecordCount() .'<br>';
      if ($tables->RecordCount() > 0) {
        $got_v1_2_0b = true;
      }
      //3rd check for v1.20
      $sql = "select configuration_group_title, configuration_group_description from " . DB_PREFIX . "configuration_group WHERE configuration_group_id = '13'";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "120c-cfggroup13=attrb ==" . $result->fields['configuration_group_title'] . '<br>';
        if ($result->fields['configuration_group_title'] == 'Attribute Settings') {
          $got_v1_2_0c = true;
        }
        $result->MoveNext();
      }
     //4th check for v1.20
     $sql = "show fields from " . DB_PREFIX . "categories";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "120d-fields=" . $result->fields['Field'] . '<br>';
        if ($result->fields['Field'] == 'categories_status') {
          if ($result->fields['Type'] == 'tinyint(1)')  {
            $got_v1_2_0d = true;
          }
        }
        $result->MoveNext();
      }
      //5th check for v1.20
      $sql = "show fields from " . DB_PREFIX . "customers";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "120e-fields=" . $result->fields['Field'] . '<br>';
        if  ($result->fields['Field'] == 'customers_nick' || $result->fields['Field'] == 'customers_group_pricing' || $result->fields['Field'] == 'customers_email_format') {
          $got_v1_2_0e = true;
        }
        $result->MoveNext();
      }
      //6th check for v1.20
      $sql = "show fields from " . DB_PREFIX . "products";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
      if (ZC_UPG_DEBUG==true) echo "120f-fields=" . $result->fields['Field'] . '<br>';
        if  ($result->fields['Field'] == 'master_categories_id') {
          $got_v1_2_0f = true;
        }
        $result->MoveNext();
      }

      //7th check for v1.2.0
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "project_version'");
      if ($tables->RecordCount() > 0) {
      $sql = "SELECT project_version_major, project_version_minor from " . DB_PREFIX . "project_version WHERE project_version_key = 'Zen-Cart Main'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "120g-project_version=" . $result->fields['project_version_major'] . '.' . $result->fields['project_version_minor'] . '<br>';
      if ($result->fields['project_version_major']=='1' && $result->fields['project_version_minor']>='2') $got_v1_2_0g = true;
      } //end project_version

      // evaluate all 6 checks
      if ($got_v1_2_0a && $got_v1_2_0b && $got_v1_2_0c && $got_v1_2_0d && $got_v1_2_0e && $got_v1_2_0f) {
        $got_v1_2_0 = true;
        if (ZC_UPG_DEBUG==true) echo 'Got 1.2.0<br>';
      }
      return $got_v1_2_0;
    } // end 1.2.0 check


    function check_version_121() {
      global $db_test;
      $got_v1_2_1a = false;
      $got_v1_2_1b = false;

      // test to see if the v1.2.0->v1.2.1 upgrade has been completed
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "project_version'");
      if ($tables->RecordCount() > 0) {
        //1st check for v1.2.1
        $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='DISPLAY_PRICE_WITH_TAX_ADMIN'";
        $result = $db_test->Execute($sql);
        if (ZC_UPG_DEBUG==true) echo "121a-configkey_check=" . $result->fields['configuration_title'] . '<br>';
        if ($result->RecordCount()>0) $got_v1_2_1a = true;
      }

      //2nd check for v1.2.1
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "products_discount_quantity'");
      if ($tables->RecordCount() > 0) {
        $sql = "show fields from " . DB_PREFIX . "products_discount_quantity";
        $result = $db_test->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "121b-fields-'discount_qty'->FLOAT=" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
          if  ($result->fields['Field'] == 'discount_qty') {
            if (strtoupper($result->fields['Type']) == 'FLOAT')  {
              $got_v1_2_1b = true;
            }
          }
          $result->MoveNext();
        }
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.2.1a='.$got_v1_2_1a.'<br>';
        echo '1.2.1b='.$got_v1_2_1b.'<br>';
      }
      // evaluate all 3 checks
      if ($got_v1_2_1a && $got_v1_2_1b) {
        $got_v1_2_1 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.1<br>';
      }
      return $got_v1_2_1;
    } //end of 1.2.1 check


    function check_version_122() {
      global $db_test;
      // 1.2.2 checks
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='SEND_EXTRA_ORDER_EMAILS_TO'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "122a-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Send Copy of Order Confirmation Emails To') {
        $got_v1_2_2a = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.2<br>';
      }
      return $got_v1_2_2a;
    } //end of 1.2.2 check

    function check_version_123() {
      global $db_test;
      //1st check for v1.2.3
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "123a-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Default Order Status For Zero Balance Orders') {
        $got_v1_2_3 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.3<br>';
      }
      return $got_v1_2_3;
    } //end of 1.2.3 check

    function check_version_124() {
      global $db_test;
      //1st check for v1.2.4
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='PRODUCTS_MANUFACTURERS_STATUS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "124a-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Manufacturers List - Verify Product Exist') {
        $got_v1_2_4 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.4<br>';
      }
      return $got_v1_2_4;
    } //end of 1.2.4 check

    function delete_bad_1_2_4_index_key() {
      global $db_test;
      // THE FOLLOWING SIMPLY CHECKS FOR THE EXTRA INDEX KEY AND REMOVES IT:
      $tables = $db_test->Execute("SHOW TABLES like '".DB_PREFIX."project_version_history'");
      if ($tables->RecordCount() > 0) {
        $sql = "show index from " . DB_PREFIX . "project_version_history";
        $result = $db_test->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "INDEX TEST-'project_version_history'=" . $result->fields['Key_name'] . ' (vs project_version_key)' . '<br>';
          if  ($result->fields['Key_name'] == 'project_version_key') {
            if (ZC_UPG_DEBUG==true) echo 'Index on project_version_key found. Deleting.<br>';
            $db_test->Execute("drop index project_version_key on " . DB_PREFIX . "project_version_history");
            return true;
          }
          $result->MoveNext();
        }
      }
    }

    function check_version_125() {
      global $db_test;
      //1st check for v1.2.5
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='SESSION_IP_TO_HOST_ADDRESS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "125a-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'IP to Host Conversion Status') {
        $got_v1_2_5a = true;
      }
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "125b-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Maximum Display Columns Products to Multiple Categories Manager') {
        $got_v1_2_5b = true;
      }
      //3rd check for v1.2.5
        $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "meta_tags_products_description'");
         if (ZC_UPG_DEBUG==true) echo '125c-Table= '. $tables->RecordCount() .'<br>';
         if ($tables->RecordCount() > 0) {
           $got_v1_2_5c = true;
         }
      //4th check for v1.2.5
      $sql = "show fields from " . DB_PREFIX . "whos_online";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "125d-fields-'user_agent'->VARCHAR=" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
        if  ($result->fields['Field'] == 'user_agent') {
          if (strstr(strtoupper($result->fields['Type']),'VARCHAR'))  {
            $got_v1_2_5d = true;
          }
        }
      $result->MoveNext();
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.2.5a='.$got_v1_2_5a.'<br>';
        echo '1.2.5b='.$got_v1_2_5b.'<br>';
        echo '1.2.5c='.$got_v1_2_5c.'<br>';
        echo '1.2.5d='.$got_v1_2_5d.'<br>';
      }
      // evaluate all 4 1.2.5 checks
      if ($got_v1_2_5a && $got_v1_2_5b && $got_v1_2_5c && $got_v1_2_5d) {
        $got_v1_2_5 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.5<br>';
      }
      return $got_v1_2_5;
    } // end 1.2.5 check


    function check_version_126() {
      global $db_test;
      //check for v1.2.6
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='CATEGORIES_TABS_STATUS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "126-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Categories-Tabs Menu ON/OFF') {
        $got_v1_2_6 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.6<br>';
      }
      return $got_v1_2_6;
    }  // end 1.2.6 check


    function check_version_127() {
      global $db_test;
      //check for v1.2.7
      $sql = "select configuration_description from " . DB_PREFIX . "configuration where configuration_key='SHOW_VERSION_UPDATE_IN_HEADER'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "127a-configkey_check=" . $result->fields['configuration_description'] . '<br>';
      if  ($result->fields['configuration_description'] == 'Automatically check to see if a new version of Zen Cart is available. Enabling this can sometimes slow down the loading of Admin pages. (Displayed on main Index page after login, and Server Info page.)') {
        $got_v1_2_7a = true;
      }
      //check #2
      $sql = "select configuration_description from " . DB_PREFIX . "configuration where configuration_key='SHOW_SHOPPING_CART_BOX_STATUS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "127b-configkey_check=" . $result->fields['configuration_description'] . '<br>';
      if  ($result->fields['configuration_description'] == 'Shopping Cart Shows<br />0= Always<br />1= Only when full<br />2= Only when full but not when viewing the Shopping Cart') {
        $got_v1_2_7b = true;
      }
      if (ZC_UPG_DEBUG==true) {
        echo '1.2.7a='.$got_v1_2_7a.'<br>';
        echo '1.2.7b='.$got_v1_2_7b.'<br>';
      }
      // evaluate all 1.2.7 checks
      if ($got_v1_2_7a && $got_v1_2_7b) {
        $got_v1_2_7 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.2.7<br>';
      }
      return $got_v1_2_7;
    }  // end 1.2.7 check


    function check_version_130() {
      global $db_test;
      //1st check for v1.3.0
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='DEFINE_BREADCRUMB_STATUS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "130a-configkey_check DEFINE_BREADCRUMB_STATUS =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Define Breadcrumb Status') {
        $got_v1_3_0a = true;
      }
      //2nd check for v1.3.0
      $tables = $db_test->Execute("SHOW TABLES like '".DB_PREFIX."ezpages'");
      if ($tables->RecordCount() > 0) {
        $sql = "show index from " . DB_PREFIX . "ezpages";
        $result = $db_test->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "1.3.0b-Index check on ezpages=" . $result->fields['Key_name'] . '->' . $result->fields['Column_name'] . '<br>';
          if  ($result->fields['Column_name'] == 'pages_id') {
            $got_v1_3_0b = true;
          }
          $result->MoveNext();
        }
      }
      //3rd check for v1.3.0
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='EZPAGES_STATUS_HEADER'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "130c-configkey_check=" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'EZ-Pages Display Status - HeaderBar') {
        $got_v1_3_0c = true;
      }
      //4th check for 1.3.0
      $tables = $db_test->Execute("SHOW TABLES like '".DB_PREFIX."meta_tags_categories_description'");
      if ($tables->RecordCount() > 0) {
        $sql = "show index from " . DB_PREFIX . "meta_tags_categories_description";
        $result = $db_test->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "1.3.0d-Index check on meta_tags_categories_description=" . $result->fields['Key_name'] . '->' . $result->fields['Column_name'] . '<br>';
          if  ($result->fields['Column_name'] == 'categories_id') {
            $got_v1_3_0d = true;
          }
          $result->MoveNext();
        }
      }
      //5th check for v1.3.0
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='EMAIL_SEND_MUST_BE_STORE'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "130e-configkey_check EMAIL_SEND_MUST_BE_STORE =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Emails must send from known domain?') {
        $got_v1_3_0e = true;
      }
      //6th check for v1.3.0
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='IMAGE_USE_CSS_BUTTONS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "130f-configkey_check IMAGE_USE_CSS_BUTTONS =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'CSS Buttons') {
        $got_v1_3_0f = true;
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.0a='.$got_v1_3_0a.'<br>';
        echo '1.3.0b='.$got_v1_3_0b.'<br>';
        echo '1.3.0c='.$got_v1_3_0c.'<br>';
        echo '1.3.0d='.$got_v1_3_0d.'<br>';
        echo '1.3.0e='.$got_v1_3_0e.'<br>';
        echo '1.3.0f='.$got_v1_3_0f.'<br>';
      }
      // evaluate all 1.3.0 checks
      if ($got_v1_3_0a && $got_v1_3_0b && $got_v1_3_0c && $got_v1_3_0d && $got_v1_3_0e && $got_v1_3_0f  ) {
        $got_v1_3_0 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.0<br>';
      }
      return $got_v1_3_0;
    } //end of 1.3.0 check


    function check_version_1301() {
      global $db_test;
      //1st check for v1.3.0.1
      $sql = "select configuration_group_id from " . DB_PREFIX . "configuration where configuration_key='SHOW_ACCOUNT_LINKS_ON_SITE_MAP'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "1301-configkey_check SHOW_ACCOUNT_LINKS_ON_SITE_MAP =" . $result->fields['configuration_group_id'] . '<br>';
      if  ($result->fields['configuration_group_id'] == '19') {
        $got_v1_3_0_1 = true;
        if (ZC_UPG_DEBUG==true) {
          echo '<br>Got 1.3.0.1<br>';
        }
      }
      return $got_v1_3_0_1;
    } //end of 1.3.0.1 check


    function check_version_1302() {
      global $db_test;
      //1st check for v1.3.0.2
      $sql = "select sort_order from " . DB_PREFIX . "configuration where configuration_key='SHOW_ACCOUNT_LINKS_ON_SITE_MAP'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "1302-configkey_check SHOW_ACCOUNT_LINKS_ON_SITE_MAP =" . $result->fields['sort_order'] . '<br>';
      if  ($result->fields['sort_order'] == 115) {
        $got_v1_3_0_2 = true;
        if (ZC_UPG_DEBUG==true) {
          echo '<br>Got 1.3.0.2<br>';
        }
      }
      return $got_v1_3_0_2;
    } //end of 1.3.0.2 check

    function check_version_135() {
      global $db_test;
      $got_v1_3_5 = false;
      $got_v1_3_5a = false;
      $got_v1_3_5b = false;
      //1st check for v1.3.5
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='PRODUCT_LIST_PRICE_BUY_NOW'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "135a-configkey_check PRODUCT_LIST_PRICE_BUY_NOW =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Display Product Add to Cart Button (0=off; 1=on; 2=on with Qty Box per Product)') {
        $got_v1_3_5a = true;
      }
      //2nd check for v1.3.5
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='PRODUCT_LIST_ALPHA_SORTER'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "135b-configkey_check PRODUCT_LIST_ALPHA_SORTER =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Include Product Listing Alpha Sorter Dropdown') {
        $got_v1_3_5b = true;
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.5a='.$got_v1_3_5a.'<br>';
        echo '1.3.5b='.$got_v1_3_5b.'<br>';
      }
      // evaluate all 1.3.5 checks
      if ($got_v1_3_5a && $got_v1_3_5b ) {
        $got_v1_3_5 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.5<br>';
      }
      return $got_v1_3_5;
    } //end of 1.3.5 check

    function check_version_136() {
      global $db_test;
      $got_v1_3_6 = false;
      $got_v1_3_6a = false;
      $got_v1_3_6b = false;
      //1st check for v1.3.6
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='META_TAG_INCLUDE_MODEL'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "136a-configkey_check META_TAG_INCLUDE_MODEL =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Meta Tags - Include Product Model in Title') {
        $got_v1_3_6a = true;
      }
      //2nd check for v1.3.6
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='SHOW_SHOPPING_CART_EMPTY_UPCOMING'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "136b-configkey_check SHOW_SHOPPING_CART_EMPTY_UPCOMING =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Show Upcoming Products on empty Shopping Cart Page') {
        $got_v1_3_6b = true;
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.6a='.$got_v1_3_6a.'<br>';
        echo '1.3.6b='.$got_v1_3_6b.'<br>';
      }
      // evaluate all 1.3.6 checks
      if ($got_v1_3_6a && $got_v1_3_6b ) {
        $got_v1_3_6 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.6<br>';
      }
      return $got_v1_3_6;
    } //end of 1.3.6 check



    function check_version_137() {
      global $db_test;
      $got_v1_3_7 = false;
      $got_v1_3_7a = false;
      $got_v1_3_7b = false;
      //1st check for v1.3.7
      $sql = "select configuration_description from " . DB_PREFIX . "configuration where configuration_key='DEFINE_BREADCRUMB_STATUS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "137a-configdesc_check DEFINE_BREADCRUMB_STATUS =" . $result->fields['configuration_description'] . '<br>';
      if  ($result->fields['configuration_description'] == 'Enable the Breadcrumb Trail Links?<br />0= OFF<br />1= ON<br />2= Off for Home Page Only') {
        $got_v1_3_7a = true;
      }
      //2nd check for v1.3.7
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='USE_SPLIT_LOGIN_MODE'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "137b-configkey_check USE_SPLIT_LOGIN_MODE =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Use split-login page') {
        $got_v1_3_7b = true;
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.7a='.$got_v1_3_7a.'<br>';
        echo '1.3.7b='.$got_v1_3_7b.'<br>';
      }
      // evaluate all 1.3.7 checks
      if ($got_v1_3_7a && $got_v1_3_7b ) {
        $got_v1_3_7 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.7<br>';
      }
      return $got_v1_3_7;
    } //end of 1.3.7 check



    function check_version_138() {
      global $db_test;
      $got_v1_3_8 = false;
      $got_v1_3_8a = false;
      $got_v1_3_8b = false;
      //1st check for v1.3.8
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'SHOW_SHOPPING_CART_COMBINED'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "138a-configtitle_check SHOW_SHOPPING_CART_COMBINED =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Show Notice of Combining Shopping Cart on Login') {
        $got_v1_3_8a = true;
      }
      //2nd check for v1.3.8
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'MAX_RANDOM_SELECT_FEATURED_PRODUCTS'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "138b-configkey_check MAX_RANDOM_SELECT_FEATURED_PRODUCTS =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Random Featured Products for SideBox') {
        $got_v1_3_8b = true;
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.8a='.$got_v1_3_8a.'<br>';
        echo '1.3.8b='.$got_v1_3_8b.'<br>';
      }
      // evaluate all 1.3.8 checks
      if ($got_v1_3_8a && $got_v1_3_8b ) {
        $got_v1_3_8 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.8<br>';
      }
      return $got_v1_3_8;
    } //end of 1.3.8 check



    function check_version_139() {
      global $db_test;
      $got_v1_3_9 = false;
      $got_v1_3_9a = false;
      $got_v1_3_9b = false;
      //1st check for v1.3.9
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'SHOW_SPLIT_TAX_CHECKOUT'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "139a-configtitle_check SHOW_SPLIT_TAX_CHECKOUT =" . $result->fields['configuration_title'] . '<br>';
      if  ($result->fields['configuration_title'] == 'Show Split Tax Lines') {
        $got_v1_3_9a = true;
      }
      //2nd check for v1.3.9
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . "authorizenet'");
      if ($tables->RecordCount() > 0) {
        $sql = "show fields from " . DB_PREFIX . "authorizenet";
        $result = $db_test->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG==true) echo "139b-fields-'transaction_id'->bigint=" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
          if  ($result->fields['Field'] == 'transaction_id') {
            if (strstr(strtoupper($result->fields['Type']),'BIGINT'))  {
              $got_v1_3_9b = true;
            }
          }
        $result->MoveNext();
        }
      }

      if (ZC_UPG_DEBUG==true) {
        echo '1.3.9a='.$got_v1_3_9a.'<br>';
        echo '1.3.9b='.$got_v1_3_9b.'<br>';
      }
      // evaluate all 1.3.9 checks
      if ($got_v1_3_9a && $got_v1_3_9b ) {
        $got_v1_3_9 = true;
        if (ZC_UPG_DEBUG==true) echo '<br>Got 1.3.9<br><br>';
      }
      return $got_v1_3_9;
    } //end of 1.3.9 check




    function check_version_150() {
      global $db_test;
      $got_v1_5_0 = false;
      $got_v1_5_0a = false;
      $got_v1_5_0b = false;
      //1st check for v1.5.0
      $sql = "show fields from " . DB_PREFIX . "admin";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "150-fields-'reset_token'" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
        if  ($result->fields['Field'] == 'reset_token') {
          $got_v1_5_0a = true;
        }
        $result->MoveNext();
      }
      //2nd check for v1.5.0
      $sql = "show fields from " . DB_PREFIX . "admin";
      $result = $db_test->Execute($sql);
      while (!$result->EOF) {
        if (ZC_UPG_DEBUG==true) echo "150-fields-'last_failed_ip'" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
        if  ($result->fields['Field'] == 'last_failed_ip') {
          $got_v1_5_0b = true;
        }
        $result->MoveNext();
      }
      if (ZC_UPG_DEBUG==true) {
        echo '1.5.0a='.$got_v1_5_0a.'<br>';
        echo '1.5.0b='.$got_v1_5_0b.'<br>';
      }
      // evaluate all 1.5.0 checks
      if ($got_v1_5_0a && $got_v1_5_0b ) {
        $got_v1_5_0 = true;
        if (ZC_UPG_DEBUG==true) echo 'Got 1.5.0<br><br>';
      }
      return $got_v1_5_0;
    } //end of 1.5.0 check


    function check_version_151() {
      global $db_test;
      $got_v1_5_1 = false;
      $sql = "show fields from " . DB_PREFIX . "admin_activity_log";
      $result = $db_test->Execute($sql);
      while (!$result->EOF && !$got_v1_5_1) {
        if (ZC_UPG_DEBUG==true) echo "151-fields-'ip_address TEST: '" . $result->fields['Field'] . '->' . $result->fields['Type'] . '<br>';
        if  ($result->fields['Field'] == 'ip_address' && strtoupper($result->fields['Type']) == 'VARCHAR(45)') {
          $got_v1_5_1 = true;
          if (ZC_UPG_DEBUG==true) echo 'Got 1.5.1<br><br>';
        }
        $result->MoveNext();
      }
      return $got_v1_5_1;
    } //end of 1.5.1 check


    function check_version_152() {
      global $db_test;
      $got_v1_5_2a = $got_v1_5_2b = false;
      $sql = "show fields from " . DB_PREFIX . "sessions";
      $result = $db_test->Execute($sql);
      while (!$result->EOF && !$got_v1_5_2a) {
        if (ZC_UPG_DEBUG==true && $result->fields['Field'] == 'sesskey') echo "152a-fields-'sesskey TEST: '" . $result->fields['Field'] . '->' . $result->fields['Type'] . ' (expecting VARCHAR(255))<br>';
        if  ($result->fields['Field'] == 'sesskey' && strtoupper($result->fields['Type']) == 'VARCHAR(255)') {
          $got_v1_5_2a = true;
          if (ZC_UPG_DEBUG==true) echo 'OKAY 1.5.2a<br><br>';
        }
        $result->MoveNext();
      }
      $sql = "select configuration_description from " . DB_PREFIX . "configuration where configuration_key = 'SESSION_WRITE_DIRECTORY'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "152b-configdesc_check SESSION_WRITE_DIRECTORY =" . $result->fields['configuration_description'] . '<br>';
      if  ($result->fields['configuration_description'] == 'This should point to the folder specified in your DIR_FS_SQL_CACHE setting in your configure.php files.') {
        $got_v1_5_2b = true;
        if (ZC_UPG_DEBUG==true) echo 'OKAY: 1.5.2b<br><br>';
      }
      if (ZC_UPG_DEBUG==true && !$got_v1_5_2b) echo 'BAD: 1.5.2b<br><br>';

      return ($got_v1_5_2a && $got_v1_5_2b);
    } //end of 1.5.2 check


    function check_version_153() {
      global $db_test;
      $got_v1_5_3a = false;
      $sql = "show fields from " . DB_PREFIX . "customers";
      $result = $db_test->Execute($sql);
      while (!$result->EOF && !$got_v1_5_3a) {
        if (ZC_UPG_DEBUG==true && $result->fields['Field'] == 'customers_password') echo "153a-fields-'customers_password TEST: '" . $result->fields['Field'] . '->' . $result->fields['Type'] . ' (expecting VARCHAR(255))<br>';
        if  ($result->fields['Field'] == 'customers_password' && strtoupper($result->fields['Type']) == 'VARCHAR(255)') {
          $got_v1_5_3a = true;
          if (ZC_UPG_DEBUG==true) echo 'OKAY 1.5.3a<br><br>';
        }
        $result->MoveNext();
      }
      if (ZC_UPG_DEBUG==true && !$got_v1_5_3a) echo 'BAD: 1.5.3a<br><br>';

      return $got_v1_5_3a;
    } //end of 1.5.3 check


    function check_version_154() {
      global $db_test;
      $got_v1_5_4a = false;
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'PADSS_AJAX_CHECKOUT'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "154a-configtitle_check PADSS_AJAX_CHECKOUT =" . $result->fields['configuration_title'] . '<br>';
      if (!$result->EOF && $result->fields['configuration_title'] == 'PA-DSS Ajax Checkout?') {
        $got_v1_5_4a = true;
        if (ZC_UPG_DEBUG==true) echo 'OKAY 1.5.4<br><br>';
      }
      if (ZC_UPG_DEBUG==true && !$got_v1_5_4a) echo 'BAD: 1.5.4a<br><br>';

      return $got_v1_5_4a;
    } //end of 1.5.4 check


    function check_version_155() {
      global $db_test;
      $got_v1_5_5a = false;
      $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key = 'CC_ENABLED_DEBIT'";
      $result = $db_test->Execute($sql);
      if (ZC_UPG_DEBUG==true) echo "155a-configtitle_check CC_ENABLED_DEBIT =" . ($result->RecordCount() ? $result->fields['configuration_title'] : ' NOT FOUND ') . '<br>';
      if (!$result->EOF && $result->fields['configuration_title'] == 'Credit Card Enable Status - Debit') {
        $got_v1_5_5a = true;
        if (ZC_UPG_DEBUG==true) echo 'OKAY 1.5.5<br><br>';

      }
      if (ZC_UPG_DEBUG==true && !$got_v1_5_5a) echo 'BAD: 1.5.5a<br><br>';

      return $got_v1_5_5a;
    } //end of 1.5.5 check


  } // end class

