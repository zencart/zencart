<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 18695 2011-05-04 05:24:19Z drbyte $
 */

/*
 * Database Upgrade script
 * 1. Checks to be sure that the configure.php exists and can be read
 * 2. Uses info from configure.php to connect to database
 * 3. Queries database to determine whether settings unique to each upgrade level exist or not
 * 4. Presents a list of upgrade steps to be completed (checkboxes)
 * 5. If can connect to database, but cannot find the "configuration" table, only allows option to rename table prefixes
 * 6. Requires admin password in order to do upgrade steps
 * 7. Cycles through processing each upgrade SQL file in sequence, as selected.
 *    Won't process upgrades if prerequisites for prior step aren't already validated.
 *
 * @todo  Optimize routine to check for database permissions at the MySQL "user" level.
 *           Needs: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP
 *        NEEDS TO WORK RELIABLY FOR BOTH well-configured and poorly-configured hosting configurations
 */

/////////////////////////////////////////////////////////////////////
//this is the latest database-version-level that this script knows how to inspect and upgrade to.
//it is used to determine whether to stay on the upgrade page when done, or continue to the finished page
$latest_version = '1.5.0';

///////////////////////////////////
$is_upgrade = true; //that's what this page is all about!
$failed_entries=0;

$configure_files_array = array('../includes/configure.php','../admin/includes/configure.php');
$database_tablenames_array=array('../includes/database_tables.php', '../includes/extra_datafiles/music_type_database_names.php');

define('DIR_WS_INCLUDES', '../includes/');
$zc_install->test_store_configure(ERROR_TEXT_STORE_CONFIGURE,ERROR_CODE_STORE_CONFIGURE);
if (ZC_UPG_DEBUG==true && $zc_install->fatal_error) echo 'FATAL ERROR-CONFIGURE FILE';

if (!$zc_install->fatal_error) {
  if (ZC_UPG_DEBUG==true) echo 'configure.php file exists<br />';
  @require_once(DIR_WS_INCLUDES . 'configure.php');

  require(DIR_WS_INCLUDES . 'classes/db/' . DB_TYPE . '/query_factory.php');

  //open database connection to run queries against it
  $db_test = new queryFactory;
  $db_test->Connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE) or die("Unable to connect to database");

  //check to see if a database_table_prefix has been defined.  If not, set it to blank.
  if (!defined('DB_PREFIX') || DB_PREFIX == 'DB_PREFIX' || "'".DB_PREFIX."'" == 'DB_PREFIX') {
    define('DB_PREFIX','');
  }

  // Now check the database for what version it's at, if found
  require('includes/classes/class.installer_version_manager.php');
  $dbinfo = new versionManager;

  $privs_array =  explode('|||',zen_check_database_privs('','',true));
  $db_priv_ok = $privs_array[0];
  $zdb_privs_list =  $privs_array[1];
  $privs_found_text='';
  if (ZC_UPG_DEBUG==true) echo 'privs_list_to_parse='.$db_priv_ok.'|||'.$zdb_privs_list;
  foreach(array('ALL PRIVILEGES','SELECT','INSERT','UPDATE','DELETE','CREATE','ALTER','INDEX','DROP') as $value) {
    if (in_array($value,explode(', ',$zdb_privs_list))) {
      $privs_found_text .= $value .', ';
    }
  }
  $zdb_privs=str_replace(',  ',' ',$privs_found_text.' ');
  if (!zen_not_null($zdb_privs)) $zdb_privs=$zdb_privs_list;
  if ($zdb_privs_list == 'Not Checked') $zdb_privs = $zdb_privs_list;

// Finished querying database for configuration info
  $db_test->Close();


// *** NOW DETERMINE REQUIRED UPDATES BASED ON TEST RESULTS
$sniffer_text = '';

//display options based on what was found -- THESE SHOULD BE PROCESSED IN REVERSE ORDER, NEWEST VERSION FIRST... !
//that way only the "earliest-required" upgrade is suggested first.
    $needs_v1_5_0=false;
    if (!$dbinfo->version150) {
      $sniffer_text =  ' upgrade v1.3.9 to v1.5.0';
      $needs_v1_5_0=true;
    }
    $needs_v1_3_9=false;
    if (!$dbinfo->version139) {
      $sniffer_text =  ' upgrade v1.3.8 to v1.3.9';
      $needs_v1_3_9=true;
    }
    $needs_v1_3_8=false;
    if (!$dbinfo->version138) {
      $sniffer_text =  ' upgrade v1.3.7 to v1.3.8';
      $needs_v1_3_8=true;
    }
    $needs_v1_3_7=false;
    if (!$dbinfo->version137) {
      $sniffer_text =  ' upgrade v1.3.6 to v1.3.7';
      $needs_v1_3_7=true;
    }
    $needs_v1_3_6=false;
    if (!$dbinfo->version136) {
      $sniffer_text =  ' upgrade v1.3.5 to v1.3.6';
      $needs_v1_3_6=true;
    }
    $needs_v1_3_5=false;
    if (!$dbinfo->version135) {
      $sniffer_text =  ' upgrade v1.3.0.2 to v1.3.5';
      $needs_v1_3_5=true;
    }
    $needs_v1_3_0_2=false;
    if (!$dbinfo->version1302) {
      $sniffer_text =  ' upgrade v1.3.0.1 to v1.3.0.2';
      $needs_v1_3_0_2=true;
    }
    $needs_v1_3_0_1=false;
    if (!$dbinfo->version1301) {
      $sniffer_text =  ' upgrade v1.3.0 to v1.3.0.1';
      $needs_v1_3_0_1=true;
    }
    $needs_v1_3_0=false;
    if (!$dbinfo->version130) {
      $sniffer_text =  ' upgrade v1.2.7 to v1.3.0';
      $needs_v1_3_0=true;
    }
    $needs_v1_2_7=false;
    if (!$dbinfo->version127) {
      $sniffer_text =  ' upgrade v1.2.6 to v1.2.7';
      $needs_v1_2_7=true;
    }
    $needs_v1_2_6=false;
    if (!$dbinfo->version126) {
      $sniffer_text =  ' upgrade v1.2.5 to v1.2.6';
      $needs_v1_2_6=true;
    }
    $needs_v1_2_5=false;
    if (!$dbinfo->version125) {
      $sniffer_text =  ' upgrade v1.2.4 to v1.2.5';
      $needs_v1_2_5=true;
    }
    $needs_v1_2_4=false;
    if (!$dbinfo->version124) {
      $sniffer_text =  ' upgrade v1.2.3 to v1.2.4';
      $needs_v1_2_4=true;
    }
    $needs_v1_2_3=false;
    if (!$dbinfo->version123) {
      $sniffer_text =  ' upgrade v1.2.2 to v1.2.3';
      $needs_v1_2_3=true;
    }
    $needs_v1_2_2=false;
    if (!$dbinfo->version122) {
      $sniffer_text =  ' upgrade v1.2.1 to v1.2.2';
      $needs_v1_2_2=true;
    }
    $needs_v1_2_1=false;
    if (!$dbinfo->version121) {
      $sniffer_text =  ' upgrade v1.2.0 to v1.2.1';
      $needs_v1_2_1=true;
    }
    $needs_v1_2_0=false;
    if (!$dbinfo->version120) {
      $sniffer_text =  ' upgrade v1.1.4 to v1.2.0';
      $needs_v1_2_0=true;
    }
    $needs_v1_1_4_patch1=false;
    if (!$dbinfo->version1141) {
      $sniffer_text =  ' upgrade v1.1.4 to v1.1.4_patch1';
      $needs_v1_1_4_patch1=true;
    }
    $needs_v1_1_4=false;
    if (!$dbinfo->version114) {
      $sniffer_text =  ' upgrade v1.1.2 or v1.1.3 to v1.1.4';
      $needs_v1_1_4=true;
    }
    $needs_v1_1_2=false;
    if (!$dbinfo->version112) {
      $sniffer_text =  ' upgrade v1.1.1 to v1.1.2';
      $needs_v1_1_2=true;
    }
    $needs_v1_1_1=false;
    if (!$dbinfo->version111) {
      $sniffer_text =  ' upgrade v1.1.0 to v1.1.1';
      $needs_v1_1_1=true;
    }
    $needs_v1_1_0=false;
    if (!$dbinfo->version110) {
      $sniffer_text =  ' upgrade v1.04 to v.1.1.1';
      $needs_v1_1_0=true;
//    $needs_v1_1_1=false; // exclude the 1.1.0-to-1.1.1 update since it's included in this step if selected
    }

    if (!isset($sniffer_text) || $sniffer_text == '') {
      $sniffer_text = ' &nbsp;*** No upgrade required ***';
      $sniffer_version = '';
    }

} // end if zc_install_error == false ....... and database schema checks

if (ZC_UPG_DEBUG2==true) {
  echo '<br>110='.$dbinfo->version110;
  echo '<br>111='.$dbinfo->version111;
  echo '<br>112='.$dbinfo->version112;
  echo '<br>114='.$dbinfo->version114;
  echo '<br>1_1_4_patch1='.$dbinfo->version1141;
  echo '<br>120='.$dbinfo->version120;
  echo '<br>121='.$dbinfo->version121;
  echo '<br>122='.$dbinfo->version122;
  echo '<br>123='.$dbinfo->version123;
  echo '<br>124='.$dbinfo->version124;
  echo '<br>125='.$dbinfo->version125;
  echo '<br>126='.$dbinfo->version126;
  echo '<br>127='.$dbinfo->version127;
  echo '<br>130='.$dbinfo->version130;
  echo '<br>1301='.$dbinfo->version1301;
  echo '<br>1302='.$dbinfo->version1302;
  echo '<br>135='.$dbinfo->version135;
  echo '<br>136='.$dbinfo->version136;
  echo '<br>137='.$dbinfo->version137;
  echo '<br>138='.$dbinfo->version138;
  echo '<br>139='.$dbinfo->version139;
  echo '<br>150='.$dbinfo->version150;
  echo '<br>';
  }

// IF FORM WAS SUBMITTED, CHECK SELECTIONS AND PERFORM THEM
  if (isset($_POST['submit'])) {
    $sniffer_text =  '';
    $sniffer_version = '';
    $nothing_to_process = false;
    if (is_array($_POST['version'])) {
      if (ZC_UPG_DEBUG2==true) foreach($_POST['version'] as $value) { echo 'Selected: ' . htmlspecialchars($value).'<br />';}
      reset($_POST['version']);
      if (sizeof($_POST['version'])) $zc_install->updateAdminIpList();
      while (list(, $value) = each($_POST['version'])) {
        $sniffer_file = '';
        switch ($value) {
          case '1.0.4':  // upgrading from v1.0.4 to 1.1.1
            if ($dbinfo->version111) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_104_to_111.sql';
            if (ZC_UPG_DEBUG2==true) echo '<br>'.$sniffer_file.'<br>';
            $got_v1_1_1 = true;
            $db_upgraded_to_version='1.1.1';
            break;
          case '1.1.0':  // upgrading from v1.1.0 to 1.1.1
            if (!$dbinfo->version110 || $dbinfo->version111) continue; // if don't have prerequisite, or if already done this step
            $sniffer_file = '_upgrade_zencart_110_to_111.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_1_1 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.1.1';
            break;
          case '1.1.1':  // upgrading from v1.1.1 to 1.1.2
            if (!$dbinfo->version111 || $dbinfo->version112) continue;
            $sniffer_file = '_upgrade_zencart_110_to_112.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_1_2 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.1.2';
            break;
          case '1.1.2-or-1.1.3':  // upgrading from v1.1.2 or v.1.13  TO   1.1.4
            if (!$dbinfo->version112 || $dbinfo->version114) continue;
            $sniffer_file = '_upgrade_zencart_112_to_114.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_1_4 = true;
            $got_v1_1_4_patch1 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.1.4-1';
            break;
          case '1.1.4':  // upgrading from v1.1.4 to 1.1.4 patch1
            if (!$dbinfo->version114 || $dbinfo->version1141) continue;
            $sniffer_file = '_upgrade_zencart_114_patch1.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_1_4_patch1 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.1.4-1';
            break;
          case '1.1.4u':  // upgrading from v1.1.4 TO v1.2.0  ('u' implies "upgrade", rather than just the patch1)
            if (!$dbinfo->version114 || $dbinfo->version120) continue;
            $sniffer_file = '_upgrade_zencart_114_to_120.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_0 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.0';
            break;
          case '1.2.0':  // upgrading from v1.2.0 TO v1.2.1
            if (!$dbinfo->version120 || $dbinfo->version121) continue;   // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_120_to_121.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_1 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.1';
            break;
          case '1.2.1':  // upgrading from v1.2.1 TO v1.2.2
//          if (!$dbinfo->version121 || $dbinfo->version122) continue;   // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_121_to_122.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_2 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.2';
            break;
          case '1.2.2':  // upgrading from v1.2.2 TO v1.2.3
//          if (!$dbinfo->version122 || $dbinfo->version123) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_122_to_123.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_3 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.3';
            break;
          case '1.2.3':  // upgrading from v1.2.3 TO v1.2.4
//          if (!$dbinfo->version123 || $dbinfo->version124) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_123_to_124.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_4 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.4';
            break;
          case '1.2.4':  // upgrading from v1.2.4 TO v1.2.5
//          if (!$dbinfo->version124 || $dbinfo->version125) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_124_to_125.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_5 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.5';
            break;
          case '1.2.5':  // upgrading from v1.2.5 TO v1.2.6
//          if (!$dbinfo->version125 || $dbinfo->version126) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_125_to_126.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_6 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.6';
            break;
          case '1.2.6':  // upgrading from v1.2.6 TO v1.2.7
//          if (!$dbinfo->version126 || $dbinfo->version127) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_126_to_127.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_2_7 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.2.7';
            break;
          case '1.2.7':  // upgrading from v1.2.7 TO v1.3.0
//          if (!$dbinfo->version127 || $dbinfo->version130) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_127_to_130.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_0 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.0';
            break;
          case '1.3.0':  // upgrading from v1.3.0 TO 1.3.0.1
//          if (!$dbinfo->version130 || $dbinfo->version1301) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_130_to_1301.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_0_1 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.0.1';
            break;
          case '1.3.0.1':  // upgrading from v1.3.0.1 TO 1.3.0.2
//          if (!$dbinfo->version1301 || $dbinfo->version1302) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_1301_to_1302.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_0_2 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.0.2';
            break;
          case '1.3.0.2':  // upgrading from v1.3.0.2 TO 1.3.5
//          if (!$dbinfo->version1302 || $dbinfo->version135) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_1302_to_135.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_5 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.5';
            break;
          case '1.3.5':  // upgrading from v1.3.5 TO 1.3.6
//          if (!$dbinfo->version135 || $dbinfo->version136) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_135_to_136.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_6 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.6';
            break;
          case '1.3.6':  // upgrading from v1.3.6 TO 1.3.7
//          if (!$dbinfo->version135 || $dbinfo->version137) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_136_to_137.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_7 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.7';
            break;
          case '1.3.7':  // upgrading from v1.3.7 TO 1.3.8
//          if (!$dbinfo->version137 || $dbinfo->version138) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_137_to_138.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_8 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.8';
            break;
          case '1.3.8':  // upgrading from v1.3.8 TO 1.3.9
//          if (!$dbinfo->version138 || $dbinfo->version139) continue;  // if prerequisite not completed, or already done, skip
            $sniffer_file = '_upgrade_zencart_138_to_139.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_3_9 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.3.9';
            break;
       case '1.3.9':  // upgrading from v1.3.9 TO 1.5.0
            $sniffer_file = '_upgrade_zencart_139_to_150.sql';
            if (ZC_UPG_DEBUG2==true) echo $sniffer_file.'<br>';
            $got_v1_5_0 = true; //after processing this step, this will be the new version-level
            $db_upgraded_to_version='1.5.0';
            break;
          default:
            $nothing_to_process=true;
        } // end switch
        if (file_exists(DIR_WS_INCLUDES . '../extras/curltest.php')) @unlink(DIR_WS_INCLUDES . '../extras/curltest.php');
        if (file_exists(DIR_WS_INCLUDES . 'modules/payment/paypal/ipn_application_top.php')) @unlink(DIR_WS_INCLUDES . 'modules/payment/paypal/ipn_application_top.php');

        //check for errors
        $zc_install->test_store_configure(ERROR_TEXT_STORE_CONFIGURE,ERROR_CODE_STORE_CONFIGURE);
        if (!$zc_install->fatal_error && isset($_POST['adminid']) && isset($_POST['adminpwd'])) {
          $zc_install->fileExists('sql/' . DB_TYPE . $sniffer_file, DB_TYPE . $sniffer_file . ' ' . ERROR_TEXT_DB_SQL_NOTEXIST, ERROR_CODE_DB_SQL_NOTEXIST);
          $zc_install->functionExists(DB_TYPE, ERROR_TEXT_DB_NOTSUPPORTED, ERROR_CODE_DB_NOTSUPPORTED);
          $zc_install->dbConnect(DB_TYPE, DB_SERVER, DB_DATABASE, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, ERROR_TEXT_DB_CONNECTION_FAILED, ERROR_CODE_DB_CONNECTION_FAILED,ERROR_TEXT_DB_NOTEXIST, ERROR_CODE_DB_NOTEXIST);
          $zc_install->verifyAdminCredentials($_POST['adminid'], $_POST['adminpwd']);
        } //end if !fatal_error

        if (ZC_UPG_DEBUG2==true) echo 'Processing ['.$sniffer_file.']...<br />';
        if ($zc_install->error == false && $nothing_to_process==false) {
          //open database connection to run queries against it
          $db = new queryFactory;
          $db->Connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE) or die("Unable to connect to database");

          // load the upgrade.sql file(s) relative to the required step(s)
          $query_results = executeSql('sql/'. DB_TYPE . $sniffer_file, DB_DATABASE, DB_PREFIX);
           if ($query_results['queries'] > 0 && $query_results['queries'] != $query_results['ignored']) {
             $messageStack->add('upgrade',$query_results['queries'].' statements processed.', 'success');
           } else {
             $messageStack->add('upgrade','Failed: '.$query_results['queries'], 'error');
           }
           if (zen_not_null($query_results['errors'])) {
             foreach ($query_results['errors'] as $value) {
               $messageStack->add('upgrade-error-details','SKIPPED: '.$value, 'error');
             }
           }
           if ($query_results['ignored'] != 0) {
             $messageStack->add('upgrade','Note: '.$query_results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'caution');
           }
/*           if (zen_not_null($query_results['output'])) {
           foreach ($query_results['output'] as $value) {
echo 'CAUTION: '.$value.'<br />';
             if (zen_not_null($value)) $messageStack->add('INFO: '.$value, 'caution');
           }
         }
*/
        $failed_entries += $query_results['ignored'];

        if ($db_upgraded_to_version == '1.5.0') {
          $zc_install->addSuperUser();
        }

        $db->Close();
      } // end if "no error"

    } // end while - version loop
    if ($failed_entries !=0 ) {
      $zc_install->setError('<span class="errors">NOTE: Skipped upgrade statements: '.$failed_entries.'<br />See details at bottom of page for your inspection.<br />(Details also logged in the "upgrade_exceptions" table.)</span><br />Note: In most cases, these failed statements can be ignored, <br />as they are indications that certain settings may have already been set on your site. <br />If all the suggested upgrade steps have been completed (no recommendations left), <br />you may proceed to Skip Upgrades and continue configuring your site.','85', false);
    }
    if (ZC_UPG_DEBUG2==true) echo '<span class="errors">NOTE: Skipped upgrade statements: '.$failed_entries.'<br />See details at bottom of page for your inspection.<br />(Details also logged in the "upgrade_exceptions" table.)</span>';
  } // end if-is-array-POST['version']



    // PREFIX-RENAME ROUTINE:
    // if database table-prefix 'change' has been requested, process it here:
    if (isset($_POST['newprefix'])) {
      $zc_install->checkPrefix($_POST['newprefix'], ERROR_TEXT_DB_PREFIX_NODOTS, ERROR_CODE_DB_PREFIX_NODOTS);
      $newprefix = $_POST['newprefix'];
      if (isset($_POST['db_prefix'])) { //use specified "old" prefix if entered
        $db_prefix_rename_from = $_POST['db_prefix'];
      } else {
        $db_prefix_rename_from = DB_PREFIX;
      }
      if ($newprefix != $db_prefix_rename_from) { // don't process prefix changes if same prefix selected
        $zc_install->doPrefixRename($newprefix, $db_prefix_rename_from);
      } //endif newprefix != DB_PREFIX
  } //endif prefix POST'd

// ?
  if (isset($_POST['upgrade'])) {
      header('location: index.php?main_page=system_setup' . zcInstallAddSID() );
    exit;
  }


 if ($db_upgraded_to_version==$latest_version && $zc_install->error == false && $failed_entries==0) {
  // if all db upgrades have been applied, go to the 'finished' page.
  header('location: index.php?main_page=finished' . zcInstallAddSID() );
  exit;
  } else { //return for more upgrades
    if (!$zc_install->fatal_error && !$zc_install->error && $failed_entries==0 ) {
      header('location: index.php?main_page=database_upgrade' . zcInstallAddSID() );
      exit;
    }
  }//endif
 } // end if POST==submit

 if (isset($_POST['skip'])) {
  header('location: index.php?main_page=finished' . zcInstallAddSID() );
  exit;
 }
 if (isset($_POST['refresh'])) {
   header('location: index.php?main_page=database_upgrade' . zcInstallAddSID() );
   exit;
 }

  // quick sanitization
  foreach($_POST as $key=>$val) {
    if(is_array($val)){
      foreach($val as $key2 => $val2){
        $_POST[$key][$key2] = htmlspecialchars($val2);
      }
    } else {
      $_POST[$key] = htmlspecialchars($val);
    }
  }

  $adminName = (isset($_POST['adminid'])) ? $_POST['adminid'] : '';
