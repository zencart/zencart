<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_activity.php 19537 2011-09-20 17:14:44Z drbyte $
 */

define('HEADING_TITLE', 'Admin Activity Log Manager');
define('HEADING_SUB1', 'Review or Export Logs');
define('HEADING_SUB2', 'Purge Log History');
define('TEXT_ACTIVITY_EXPORT_FORMAT', 'Export File Format:');
define('TEXT_ACTIVITY_EXPORT_FILENAME', 'Export Filename:');
define('TEXT_ACTIVITY_EXPORT_SAVETOFILE','Save to file on server? (otherwise will stream for download directly from this window)');
define('TEXT_ACTIVITY_EXPORT_DEST','Destination: ');
define('TEXT_PROCESSED', ' Processed.');
define('SUCCESS_EXPORT_ADMIN_ACTIVITY_LOG', 'Export complete. ');
define('FAILURE_EXPORT_ADMIN_ACTIVITY_LOG', 'ALERT: Export failed. Could not successfully write to file ');

define('TEXT_INSTRUCTIONS','<u>INSTRUCTIONS</u><br />You can use this page to export your Zen Cart&reg; Admin User Access Activity to a CSV file for archiving.<br />You should save this data for use in fraud investigations in case your site is compromised. This is a requirement for PCI Compliance.<br />
<ol><li>Choose whether to display or export to a file.<li>Enter a filename.<li>Click Save to proceed.<li>Choose whether to save or open the file, depending on what your browser offers.</ol>');

define('TEXT_INFO_ADMIN_ACTIVITY_LOG', '<strong>Empty Admin Activity Log table from the database<br />WARNING: BE SURE TO BACKUP YOUR DATABASE before running this update!</strong><br />The Admin Activity Log is a tracking method that records activity in the Admin. <br />Due to its nature it can become very large, very quickly and does need to be cleaned out from time to time.<br />Warnings are given at 50,000 records or 60 days, which ever happens first.<br /><span class="alert">NOTE: For PCI Compliance, you are required to retain admin activity log history for 12 months.<br />It is best to archive your logs by choosing EXPORT TO CSV and clicking Save, above, *BEFORE* purging log data.</span>');
define('TEXT_ADMIN_LOG_PLEASE_CONFIRM_ERASE', '<strong><span class="alert">WARNING!: You are about to DELETE *important* audit trail records from your database.</span></strong><br />You should FIRST confirm that you have a reliable BACKUP of your database before proceeding.<br />By proceeding you accept that this information will be deleted and understand your legal responsibilities regarding this data.<br /><br />I understand my responsibilities, and wish to proceed with the deletion by clicking Reset:<br />');
define('SUCCESS_CLEAN_ADMIN_ACTIVITY_LOG', '<strong>Completed</strong> erasure of the Admin Activity log');

