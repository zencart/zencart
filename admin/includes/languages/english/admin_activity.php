<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_activity.php drbyte Modified in v1.5.5 $
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
define('TEXT_NO_RECORDS_FOUND', 'No Records Found using the filter you selected.');

define('TEXT_EXPORTFORMAT0', 'Export as HTML (ideal for on-screen viewing)');
define('TEXT_EXPORTFORMAT1', 'Export to CSV (ideal for importing to spreadsheets)');
define('TEXT_EXPORTFORMAT2', 'Export to TXT');
define('TEXT_EXPORTFORMAT3', 'Export to XML');

define('TEXT_ACTIVITY_EXPORT_FILTER', 'Which log data do you want to see?');
define('TEXT_EXPORTFILTER0', 'All logged data, regardless of severity level.');
define('TEXT_EXPORTFILTER1', 'INFO - General logged information');
define('TEXT_EXPORTFILTER2', 'NOTICE - Notable info, which should be reviewed periodically');
define('TEXT_EXPORTFILTER3', 'WARNING - Activity which should be reviewed daily');
define('TEXT_EXPORTFILTER4', 'Both NOTICE and WARNING (common combination for review).');

define('TEXT_INTERPRETING_LOG_DATA', '<p><strong>Interpretation of the log data</strong><ul>
<li><strong>Severity</strong> - The standards for logging generally describe severities as follows:
  <ul>
  <li><strong>INFO</strong> refers to general activity. This may or may not contain remarkable details.</li>
  <li><strong>NOTICE</strong> refers to activity which indicates higher privilege was used, and may include things like creating new admin users or adding new payment modules. It also highlights when any data submitted on the web page includes potentially risky content such as script tags or embedded iframes, where malicious content is being added to your products/categories/pages by unhappy employees or an intruder on your site. These should be reviewed regularly for any anomalies such as unauthorized activity.</li>
  <li><strong>WARNING</strong> is assigned to CRITICAL things such as removal of payment modules or deletion of admin users. These are activities which might suggest pending trouble if not caught quickly. These should be reviewed very frequently; recommended daily.</li>
  </ul>
</li>
<li><strong>admin_user</strong> - This will show the admin user ID number followed by their admin username. If not logged in, it will show 0.</li>
<li><strong>page_accessed</strong> - This will indicate the name of the page visited, thus giving hints to the kind of activity taking place.</li>
<li><strong>parameters</strong> - This is the rest of the URI of the page visited, and gives further indication of the kind of activity being attempted by the visitor.</li>
<li><strong>flagged</strong> - If this is set to 1, that indicates that you should inspect the content recorded in the "postdata" field for unauthorized entry of script or iframe or other potentially dangerous content. An explanation of suspicious content will be listed in the "attention" field.</li>
<li><strong>attention</strong> - This will contain suggestions related to the kind of suspicious activity which should be reviewed in the "postdata" field if flagged. </li>
<li><strong>logmessage</strong> - This contains any messages recorded by the system about the activity taking place, such as installation of a certain module.</li>
<li><strong>postdata</strong> - This contains the raw POST data (with some sensitive information scrubbed) for easy review in case malicious activity is suspected.</li>
</ul></p>');