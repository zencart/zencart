<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\Services\IndexRoute;
use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;

/**
 * Class Index
 * @package ZenCart\Controllers
 */
class ServerInfo extends AbstractInfoController
{

    /**
     *
     */
    public function mainExecute()
    {
        $this->tplVars['contentTemplate'] = 'tplServerInfo.php';
        $this->tplVars['hasPHPInfo'] = false;
        $this->buildPHPInfoSection();
        $this->buildSystemInfo();
        $this->buildVersionInfo();

    }

    private function buildPHPInfoSection()
    {
        if (!function_exists('ob_start')) {
            return;
        }
        $this->tplVars['hasPHPInfo'] = true;
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        $phpinfo = preg_replace('#^.*<body>(.*)</body>.*$#s', '$1', $phpinfo);
        $phpinfo = str_replace('<font', '<span', $phpinfo);
        $phpinfo = str_replace('</font>', '</span>', $phpinfo);
        $phpinfo = str_replace('<table>',
            '<table class="table table-bordered table-striped" style="table-layout: fixed;word-wrap: break-word;">', $phpinfo);
        $phpinfo = str_replace('<tr class="h"><th>', '<thead><tr><th>', $phpinfo);
        $phpinfo = str_replace('</th></tr>', '</th></tr></thead><tbody>', $phpinfo);
        $phpinfo = str_replace('</table>', '</tbody></table>', $phpinfo);
        $phpinfo = preg_replace('#>(on|enabled|active)#i', '><span class="text-success">$1</span>', $phpinfo);
        $phpinfo = preg_replace('#>(off|disabled)#i', '><span class="text-error">$1</span>', $phpinfo);
        $this->tplVars['cachedPHPInfo'] = $phpinfo;
    }

    private function buildSystemInfo()
    {
        $system = zen_get_system_information();
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_SERVER_HOST, 'content' => $system['host'] . ' (' . $system['ip'] . ')');
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_SERVER_OS, 'content' => $system['system'] . ' ' . $system['kernel']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_SERVER_DATE, 'content' => $system['date']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_SERVER_UP_TIME, 'content' => $system['uptime']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_HTTP_SERVER, 'content' => $system['http_server']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_PHP_VERSION, 'content' => $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')');
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_PHP_FILE_UPLOADS, 'content' => ($system['php_file_uploads'] != '' && $system['php_file_uploads'] != 'off' && $system['php_file_uploads'] != '0') ? 'On' : 'Off');
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_PHP_UPLOAD_MAX, 'content' => $system['php_uploadmaxsize']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_PHP_MEMORY_LIMIT, 'content' => $system['php_memlimit']);
        $this->tplVars['systemInfo']['left'][] = array('title' => TITLE_PHP_POST_MAX_SIZE, 'content' => $system['php_postmaxsize']);


        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE, 'content' => $system['db_version'] . ($system['mysql_strict_mode'] == true ? '<em> ' . TITLE_MYSQL_STRICT_MODE . '</em>' : ''));
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_HOST, 'content' => $system['db_server'] . ' (' . $system['db_ip'] . ')');
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_DATE, 'content' => $system['db_date']);
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_DATA_SIZE, 'content' => number_format(($system['database_size']/1024),0));
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_INDEX_SIZE, 'content' => number_format(($system['index_size']/1024),0));
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_MYSQL_SLOW_LOG_STATUS, 'content' => $system['mysql_slow_query_log_status'] != '0' ? 'On' : 'Off');
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_MYSQL_SLOW_LOG_FILE, 'content' => zen_output_string_protected($system['mysql_slow_query_log_file']));
        $this->tplVars['systemInfo']['right'][] = array('title' => TITLE_DATABASE_MYSQL_MODE, 'content' => $system['mysql_mode'] == '' ? '(None set)' : zen_output_string_protected(str_replace(',', ', ', $system['mysql_mode'])));
    }
    private function buildVersionInfo()
    {
        $this->tplVars['versionInfo'][] = '<a href="http://www.zen-cart.com"><img border="0" src="images/small_zen_logo.gif" alt="Zen Cart"></a>';
        $this->tplVars['versionInfo'][] = PROJECT_VERSION_NAME . PROJECT_VERSION_MAJOR . PROJECT_VERSION_MINOR ;
        if (PROJECT_VERSION_PATCH1 != '') {
            $this->tplVars['versionInfo'][] = 'Patch: ' . PROJECT_VERSION_PATCH1 . '::' .  PROJECT_VERSION_PATCH1_SOURCE ;
        }
        if (PROJECT_VERSION_PATCH2 != '') {
            $this->tplVars['versionInfo'][] = 'Patch: ' . PROJECT_VERSION_PATCH2 . '::' .  PROJECT_VERSION_PATCH2_SOURCE ;
        }
        $this->tplVars['versionInfo'][] = PROJECT_DATABASE_LABEL . ' ' . PROJECT_DB_VERSION_MAJOR . '.' . PROJECT_DB_VERSION_MINOR;
        if (PROJECT_DB_VERSION_PATCH1 != '') {
            $this->tplVars['versionInfo'][] = 'Patch: ' . PROJECT_DB_VERSION_PATCH1 . '::' .  PROJECT_DB_VERSION_PATCH1_SOURCE ;
        }
        if (PROJECT_DB_VERSION_PATCH2 != '') {
            $this->tplVars['versionInfo'][] = 'Patch: ' . PROJECT_DB_VERSION_PATCH2 . '::' .  PROJECT_DB_VERSION_PATCH2_SOURCE ;
        }

        $query = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC";
        $result = $this->dbConn->Execute($query);
        $sInfo = 'v' . $result->fields['project_version_major'] . '.' . $result->fields['project_version_minor'];
        if (zen_not_null($result->fields['project_version_patch'])) $sInfo .= '&nbsp;&nbsp;Patch: ' . $result->fields['project_version_patch'];
        if (zen_not_null($result->fields['project_version_date_applied'])) $sInfo .= ' &nbsp;&nbsp;[' . $result->fields['project_version_date_applied'] . '] ';
        if (zen_not_null($result->fields['project_version_comment'])) $sInfo .= ' &nbsp;&nbsp;(' . $result->fields['project_version_comment'] . ')';
        $this->tplVars['versionInfo'][] = $sInfo;

        $query = "SELECT * from " . TABLE_PROJECT_VERSION_HISTORY . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC, project_version_patch DESC";
        $results = $this->dbConn->Execute($query);
        foreach ($results as $result) {
            $sInfo = 'v' . $result['project_version_major'] . '.' . $result['project_version_minor'];
            if (zen_not_null($result['project_version_patch'])) $sInfo .= '&nbsp;&nbsp;Patch: ' . $result['project_version_patch'];
            if (zen_not_null($result['project_version_date_applied'])) $sInfo .= ' &nbsp;&nbsp;[' . $result['project_version_date_applied'] . '] ';
            if (zen_not_null($result['project_version_comment'])) $sInfo .= ' &nbsp;&nbsp;(' . $result['project_version_comment'] . ')';
            $this->tplVars['versionInfo'][] =  $sInfo;

        }
    }
}
