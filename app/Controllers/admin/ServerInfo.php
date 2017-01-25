<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers;

use ZenCart\Services\IndexRoute;
use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;

/**
 * Class ServerInfo
 * @package App\Controllers
 */
class ServerInfo extends AbstractInfoController
{

    /**
     *
     */
    public function mainExecute()
    {
        $this->view->getTplVarManager()->set('contentTemplate','tplServerInfo.php');
        $this->view->getTplVarManager()->set('hasPHPInfo',false);
        $this->buildPHPInfoSection();
        $this->buildSystemInfo();
        $this->buildVersionInfo();
        $this->buildDatabaseInfoSection();

    }

    private function buildPHPInfoSection()
    {
        if (!function_exists('ob_start')) {
            return;
        }
        $this->view->getTplVarManager()->set('hasPHPInfo',true);
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
        $this->view->getTplVarManager()->set('cachedPHPInfo',$phpinfo);
    }

    private function buildSystemInfo()
    {
        $system = zen_get_system_information();
        $systemInfo = [];
        $systemInfo['left'][] = array('title' => TITLE_SERVER_HOST, 'content' => $system['host'] . ' (' . $system['ip'] . ')');
        $systemInfo['left'][] = array('title' => TITLE_SERVER_OS, 'content' => $system['system'] . ' ' . $system['kernel']);
        $systemInfo['left'][] = array('title' => TITLE_SERVER_DATE, 'content' => $system['date']);
        $systemInfo['left'][] = array('title' => TITLE_SERVER_UP_TIME, 'content' => $system['uptime']);
        $systemInfo['left'][] = array('title' => TITLE_WEBSERVER, 'content' => $system['webserver']);
        $systemInfo['left'][] = array('title' => TITLE_PHP_VERSION, 'content' => $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')');
        $systemInfo['left'][] = array('title' => TITLE_PHP_FILE_UPLOADS, 'content' => ($system['php_file_uploads'] != '' && $system['php_file_uploads'] != 'off' && $system['php_file_uploads'] != '0') ? 'On' : 'Off');
        $systemInfo['left'][] = array('title' => TITLE_PHP_UPLOAD_MAX, 'content' => $system['php_uploadmaxsize']);
        $systemInfo['left'][] = array('title' => TITLE_PHP_MEMORY_LIMIT, 'content' => $system['php_memlimit']);
        $systemInfo['left'][] = array('title' => TITLE_PHP_POST_MAX_SIZE, 'content' => $system['php_postmaxsize']);

        $systemInfo['right'][] = array('title' => TITLE_DATABASE, 'content' => $system['db_version'] . ($system['mysql_strict_mode'] == true ? '<em> ' . TITLE_MYSQL_STRICT_MODE . '</em>' : ''));
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_HOST, 'content' => $system['db_server'] . ' (' . $system['db_ip'] . ')');
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_DATE, 'content' => $system['db_date']);
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_DATA_SIZE, 'content' => number_format(($system['database_size']/1024),0));
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_INDEX_SIZE, 'content' => number_format(($system['index_size']/1024),0));
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_MYSQL_SLOW_LOG_STATUS, 'content' => $system['mysql_slow_query_log_status'] != '0' ? 'On' : 'Off');
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_MYSQL_SLOW_LOG_FILE, 'content' => zen_output_string_protected($system['mysql_slow_query_log_file']));
        $systemInfo['right'][] = array('title' => TITLE_DATABASE_MYSQL_MODE, 'content' => $system['mysql_mode'] == '' ? '(None set)' : zen_output_string_protected(str_replace(',', ', ', $system['mysql_mode'])));
        $this->view->getTplVarManager()->set('systemInfo', $systemInfo);
    }
    private function buildVersionInfo()
    {
        $versionInfo = [];
        $versionInfo[] = '<a href="http://www.zen-cart.com"><img border="0" src="images/small_zen_logo.gif" alt="Zen Cart"></a>';
        $versionInfo[] = PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR ;
        if (PROJECT_VERSION_PATCH1 != '') {
            $versionInfo[] = 'Patch: ' . PROJECT_VERSION_PATCH1 . '::' .  PROJECT_VERSION_PATCH1_SOURCE ;
        }
        if (PROJECT_VERSION_PATCH2 != '') {
            $versionInfo[] = 'Patch: ' . PROJECT_VERSION_PATCH2 . '::' .  PROJECT_VERSION_PATCH2_SOURCE ;
        }
        $versionInfo[] = PROJECT_DATABASE_LABEL . ' ' . PROJECT_DB_VERSION_MAJOR . '.' . PROJECT_DB_VERSION_MINOR;
        if (PROJECT_DB_VERSION_PATCH1 != '') {
            $versionInfo[] = 'Patch: ' . PROJECT_DB_VERSION_PATCH1 . '::' .  PROJECT_DB_VERSION_PATCH1_SOURCE ;
        }
        if (PROJECT_DB_VERSION_PATCH2 != '') {
            $versionInfo[] = 'Patch: ' . PROJECT_DB_VERSION_PATCH2 . '::' .  PROJECT_DB_VERSION_PATCH2_SOURCE ;
        }

        $query = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC";
        $result = $this->dbConn->Execute($query);
        $sInfo = 'v' . $result->fields['project_version_major'] . '.' . $result->fields['project_version_minor'];
        if (zen_not_null($result->fields['project_version_patch'])) $sInfo .= '&nbsp;&nbsp;Patch: ' . $result->fields['project_version_patch'];
        if (zen_not_null($result->fields['project_version_date_applied'])) $sInfo .= ' &nbsp;&nbsp;[' . $result->fields['project_version_date_applied'] . '] ';
        if (zen_not_null($result->fields['project_version_comment'])) $sInfo .= ' &nbsp;&nbsp;(' . $result->fields['project_version_comment'] . ')';
        $versionInfo[] = $sInfo;

        $query = "SELECT * from " . TABLE_PROJECT_VERSION_HISTORY . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC, project_version_patch DESC";
        $results = $this->dbConn->Execute($query);
        foreach ($results as $result) {
            $sInfo = 'v' . $result['project_version_major'] . '.' . $result['project_version_minor'];
            if (zen_not_null($result['project_version_patch'])) $sInfo .= '&nbsp;&nbsp;Patch: ' . $result['project_version_patch'];
            if (zen_not_null($result['project_version_date_applied'])) $sInfo .= ' &nbsp;&nbsp;[' . $result['project_version_date_applied'] . '] ';
            if (zen_not_null($result['project_version_comment'])) $sInfo .= ' &nbsp;&nbsp;(' . $result['project_version_comment'] . ')';
            $versionInfo[] =  $sInfo;
        }
        $this->view->getTplVarManager()->set('versionInfo', $versionInfo);
    }
    
    private function buildDatabaseInfoSection ()
    {
        $databaseInfo = [];
        $system = zen_get_system_information();
        $databaseInfo['heading'] = TITLE_DATABASE_VARIABLES . $system['db_version'] . ($system['mysql_strict_mode'] == true ? '<em> ' . TITLE_MYSQL_STRICT_MODE . '</em>' : '');
        
        $databaseInfo['fields'] = [];
        $show_variables = $this->dbConn->Execute ("SHOW VARIABLES");
        while (!$show_variables->EOF) {
            $databaseInfo['fields'][] = array (
                'name' => $show_variables->fields['Variable_name'],
                'value' => zen_not_null ($show_variables->fields['Value']) ? $show_variables->fields['Value'] : '&nbsp;'
            );
            $show_variables->MoveNext ();
        }
        
        $this->view->getTplVarManager()->set('databaseInfo', $databaseInfo);
    }
}
