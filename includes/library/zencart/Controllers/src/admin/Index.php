<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;
use ZenCart\Services\IndexRoute;
use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;
use ZenCart\View\ViewFactory as View;
use ZenCart\DashboardWidget\WidgetManager;

/**
 * Class Index
 * @package ZenCart\Controllers
 */
class Index extends AbstractAdminController
{
    /**
     * @var
     */
    protected $service;

    /**
     * Index constructor.
     * @param Request $request
     * @param $db
     * @param User $user
     * @param View $view
     */
    public function __construct(Request $request, $db, User $user, View $view)
    {
        parent::__construct($request, $db, $user, $view);
        $this->service = new IndexRoute($this, $request, $db);
    }

    /**
     *
     */
    public function prepareDefaultCSS()
    {
        parent::prepareDefaultCSS();
        $this->tplVars['cssList'] [] = array(
            'href' => 'includes/template/css/index.css',
            'id' => 'indexCSS'
        );
    }

    /**
     *
     */
    public function mainExecute()
    {
        $this->displayHomePage();
    }

    /**
     *
     */
    public function setupWizardExecute()
    {
        $result = $this->service->setupWizardExecute();
        if ($result) {
            zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
    }

    /**
     *
     */
    public function displayHomePage()
    {
        if (STORE_NAME == '' || STORE_OWNER == '') {
            $this->doStartWizardDisplay();
        } else {
            $this->doWidgetsDisplay();
        }

    }

    /**
     *
     */
    public function doWidgetsDisplay()
    {
        $widgetInfoList = WidgetManager::getWidgetInfoForUser($this->request->getSession()->get('admin_id'), $this->request->getSession()->get('languages_id'));
        $widgetList = widgetManager::loadWidgetClasses($widgetInfoList);
        $this->setTplVar('widgetList', $widgetList);
        $this->setTplVar('widgets', WidgetManager::prepareTemplateVariables($widgetList));
        $this->setTplVar('widgetInfoList', $widgetInfoList);

        // Update $widgetInfoList with $widgetList changes
        foreach ($widgetInfoList as &$widgets) {
            foreach ($widgets as &$widget) {
                if ($widgetList[$widget['widget_key']]->widgetInfoChanged) {
                    $widget = $widgetList[$widget['widget_key']]->widgetInfo;
                }
            }
        }

        $this->setTplVar('widgetInfoList', $widgetInfoList);
    }

    /**
     *
     */
    public function doStartWizardDisplay()
    {
        $this->view->setMainTemplate('tplIndexStartWizard.php');
        $storeAddress = $this->request->readPost('store_address', ((STORE_NAME_ADDRESS != '') ? STORE_NAME_ADDRESS : ''));
        $storeName = $this->request->readPost('store_name', ((STORE_NAME != '') ? STORE_NAME : ''));
        $storeOwner = $this->request->readPost('store_owner', ((STORE_OWNER != '') ? STORE_OWNER : ''));
        $storeOwnerEmail = $this->request->readPost('store_owner_email', ((STORE_OWNER_EMAIL_ADDRESS != '') ? STORE_OWNER_EMAIL_ADDRESS : ''));
        $storeCountry = $this->request->readPost('store_country', ((STORE_COUNTRY != '') ? STORE_COUNTRY : ''));
        $storeZone = $this->request->readPost('store_zone', ((STORE_ZONE != '') ? STORE_ZONE : ''));
        $country_string = zen_draw_pull_down_menu('store_country', zen_get_countries_for_pulldown(), $storeCountry, 'id="store_country" tabindex="4"');
        $zone_string = zen_draw_pull_down_menu('store_zone', zen_get_country_zones($storeCountry), $storeZone, 'id="store_zone" tabindex="5"');
        $this->setTplVar('storeName', $storeName);
        $this->setTplVar('storeAddress', $storeAddress);
        $this->setTplVar('storeOwner', $storeOwner);
        $this->setTplVar('storeOwnerEmail', $storeOwnerEmail);
        $this->setTplVar('countryString', $country_string);
        $this->setTplVar('zoneString', $zone_string);
    }


    /**
     *
     */
    public function getZonesExecute()
    {
        $this->response = array('html'=>'');
        if ($this->request->readPost('id'))  {
            $options = zen_get_country_zones((int)$this->request->readPost('id'));
            if (count($options) == 0) {
                array_unshift($options, array('id' => 0, 'text' => TEXT_NONE));
            }
            $html = zen_draw_pull_down_menu('store_zone', $options, -1, 'id="store_zone" tabindex="5"'); // tabindex is here so it gets reinserted when ajax redraws this input field
            $this->response = array('html'=>$html);
        }
    }
}
