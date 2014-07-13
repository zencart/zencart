<?php
/**
 * zcActionAdminBase Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
use Zencart\DashboardWidgets\zcWidgetManager;
if (! defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcActionAdminBase Class
 *
 * @package classes
 */
abstract class zcActionAdminBase extends base
{
  public $templateVariables;
  protected $controllerCommand;
  protected $controllerAction;
  protected $useView = true;
  protected $useFoundation = false;
  public function __construct($controllerCommand)
  {
    $this->controllerCommand = $controllerCommand;
    $this->templateVariables = array();
    $this->response = array(
        'data' => null
    );
    $this->prepareDefaultCss();
    $this->prepareCommonTemplateVariables();
    $this->preCheck();
    $this->initDefinitions();
  }
  public function prepareCommonTemplateVariables()
  {
    global $extraCss, $PHP_SELF, $messageStack, $new_gv_queue_cnt, $goto_gv, $new_version;
    global $hide_languages, $languages, $languages_array, $languages_selected;
    if ($this->useView) {
      if (isset($extraCss))
        $this->templateVariables ['extraCss'] = $extraCss;
      if (isset($messageStack))
        $this->templateVariables ['messageStack'] = $messageStack;
      if (isset($PHP_SELF))
        $this->templateVariables ['PHP_SELF'] = $PHP_SELF;
      if (isset($new_gv_queue_cnt))
        $this->templateVariables ['new_gv_queue_cnt'] = $new_gv_queue_cnt;
      if (isset($goto_gv))
        $this->templateVariables ['goto_gv'] = $goto_gv;
      if (isset($new_version))
        $this->templateVariables ['new_version'] = $new_version;
      if (isset($hide_languages))
        $this->templateVariables ['hide_languages'] = $hide_languages;
      if (isset($languages))
        $this->templateVariables ['languages'] = $languages;
      if (isset($languages_array))
        $this->templateVariables ['languages_array'] = $languages_array;
      if (isset($languages_selected))
        $this->templateVariables ['languages_selected'] = $languages_selected;
      $this->templateVariables ['useFoundation'] = $this->useFoundation;
    }
  }
  public function prepareDefaultCSS()
  {
    if ($this->useView) {
      $cssList [] = array(
          'href' => 'includes/template/css/normalize.css',
          'id' => 'normalizeCSS'
      );
      if ($this->useFoundation) {
        $cssList [] = array(
            'href' => 'includes/template/css/foundation.min.css',
            'id' => 'foundationCSS'
        );
      }
      $cssList [] = array(
          'href' => 'includes/template/css/stylesheet.css',
          'id' => 'stylesheetCSS'
      );
//       $cssList [] = array(
//           'href' => 'includes/template/javascript/select2-master/select2.css',
//           'id' => 'stylesheetCSS'
//       );
      $cssList [] = array(
          'href' => 'includes/template/css/stylesheet_print.css',
          'media' => 'print',
          'id' => 'printCSS'
      );
      if ($this->useFoundation) {
        $cssList [] = array(
            'href' => 'includes/template/css/zen-foundation-reset.css',
            'id' => 'zenFoundationResetCSS'
        );
      }
    }
    $this->templateVariables ['cssList'] = $cssList;
  }
  public function invoke()
  {
    $this->controllerAction = 'main';
    $tmp = zcRequest::get('action', zcRequest::get('action', 'main', 'post'), 'get');
    if ($tmp = preg_replace('/[^a-zA-Z0-9_-]/', '', $tmp)) {
      $this->controllerAction = $tmp;
    }
    $this->controllerAction .= 'Execute';
    $this->controllerAction = (method_exists($this, $this->controllerAction)) ? $this->controllerAction : 'mainExecute';
    $this->{$this->controllerAction}();
    $this->doOutput();
  }
  public function doOutput()
  {
    if (! $this->useView) {
      $this->doNonViewOutput();
    } else {
      $this->doViewOutput();
    }
  }
  public function doViewOutput()
  {
    $tplVars = $this->templateVariables;
    require ('includes/template/common/tplAdminHtmlHead.php');
    echo "\n" . "</head>";
    echo "\n" . "<body>";
    require_once ('includes/template/common/tplHeader.php');
    $useTemplate = $this->getMainTemplate();
    if (isset($useTemplate)) {
      require ($useTemplate);
    }
    require ('includes/template/common/tplFooter.php');
  }
  public function getMainTemplate()
  {
    if (isset($this->mainTemplate)) {
      return ('includes/template/templates/' . $this->mainTemplate);
    }
    $tryTemplate = 'tpl' . ucfirst($this->controllerCommand) . '.php';
    if (file_exists('includes/template/templates/' . $tryTemplate)) {
      return ('includes/template/templates/' . $tryTemplate);
    }
    return null;
  }
  public function doNonViewOutput()
  {
    echo json_encode($this->response);
  }
  public function loadTemplateAsString($template, $tplVars)
  {
    ob_start();
    require_once ($template);
    $result = ob_get_clean();
    ob_flush();
    return $result;
  }
  public function preCheck()
  {
  }
  public function initDefinitions()
  {
  }
}