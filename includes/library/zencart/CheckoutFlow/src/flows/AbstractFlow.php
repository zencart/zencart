<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\CheckoutFlow\flows;

use ZenCart\CheckoutFlow\CheckoutRedirectException;
use ZenCart\CheckoutFlow\CheckoutManager as CheckoutManager;
use ZenCart\Request\Request as Request;
use ZenCart\View\View as View;

/**
 * Class AbstractFlow
 * @package Zencart\CheckoutFlow\flows
 */
abstract class AbstractFlow extends \base
{
    /**
     * @var
     */
    protected $manager;
    /**
     * @var
     */
    protected $request;
    /**
     * @var
     */
    protected $session;
    /**
     * @var
     */
    protected $stepsList;
    /**
     * @var
     */
    protected $redirectNeeded;
    /**
     * @var
     */
    protected $redirectDestination;
    /**
     * @var
     */
    protected $tplVarManager;

    /**
     * @param CheckoutManager $manager
     * @param Request $request
     * @param View $view
     */
    public function __construct(CheckoutManager $manager, Request $request, View $view)
    {
        $this->tplVarManager = $view->getTplVarManager();
        $this->manager = $manager;
        $this->request = $request;
        $this->session = $request->getSession();
        $this->setInitialStepsList();
    }

    /**
     *
     */
    public function setCheckoutStepsTemplate()
    {
        $checkoutStepsTemplate = 'tpl_modules_checkout_steps_standard.php';
        $this->notify('NOTIFY_CHECKOUTFLOW_DEFAULT_SET_CHECKOUT_STEPS_TEMPLATE', array(), $checkoutStepsTemplate);
        $checkoutCurrentStep = $this->getViewStepName();
        $this->notify('NOTIFY_CHECKOUTFLOW_DEFAULT_SET_CURRENT_STEP', array(), $checkoutCurrentStep);
        $this->tplVarManager->set('checkoutStepsTemplate', $checkoutStepsTemplate);
        $this->tplVarManager->set('checkoutStepsList', $this->getDisplayStepsList());
        $this->tplVarManager->set('checkoutCurrentStep', $checkoutCurrentStep);
    }

    /**
     * @return array
     */
    protected function getDisplayStepsList()
    {
        $displayStepsList = array();
        foreach ($this->stepsList as $step) {
            $displayStepsList = $this->addDisplayStepList($step, $displayStepsList);
        }
        return $displayStepsList;
    }

    /**
     * @param $step
     * @param $displayStepsList
     * @return array
     */
    protected function addDisplayStepList($step, $displayStepsList)
    {
        if (!is_array($step) || (is_array($step) && isset($step['type']) && $step['type'] != 'hidden')) {
            $displayStepsList[] = $step;
        }
        return $displayStepsList;
    }

    /**
     *
     */
    public function processFlow()
    {
        $this->redirectNeeded = false;
        try {
            $this->doUserValidate();
            $this->setLayoutOptions();
            $this->manager->getCheckoutFlowStep()->processStep();
        } catch (CheckoutRedirectException $e) {
            $this->redirectNeeded = true;
            $message = $e->getRedirectDestination();
            $this->redirectDestination = $message;
        }
    }

    /**
     * @return mixed
     */
    protected function getViewStepName()
    {
        $currentStep = $this->manager->getViewStep();
        $actualStep = $this->request->readGet('step', $currentStep);
        return $actualStep;
    }

    /**
     *
     */
    public function doUserValidate()
    {
        $skipCoreValidate = false;
        $redirectLink = zen_href_link(FILENAME_LOGIN);
        $this->notify('NOTIFY_CHECKOUTFLOW_DOUSERVALIDATE_START', array(), $skipCoreValidate, $redirectLink);
        if (!$skipCoreValidate) {
            $this->validateUser($redirectLink);
        }
        $this->notify('NOTIFY_CHECKOUTFLOW_DOUSERVALIDATE_END');
    }

    /**
     *
     */
    public function validateUser($redirectLink)
    {
        if ($this->session->get('customer_id', false) === false) {
            $this->session->get('navigation')->set_snapshot();
            $this->redirectNeeded = true;
            $this->redirectDestination = array('redirect' => $redirectLink);

            return;
        }
        if (zen_get_customer_validate_session($this->session->get('customer_id')) == false) {
            $this->session->get('navigation')->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_FLOW));
            $this->redirectNeeded = true;
            $this->redirectDestination = array('redirect' => $redirectLink);
        }
    }

    /**
     * @return array
     */
    public function getStepsList()
    {
        return $this->stepsList;
    }

    /**
     * @param $stepsList
     */
    public function setStepsList($stepsList)
    {
        $this->stepsList = $stepsList;
    }

    /**
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     *
     */
    public function setLayoutOptions()
    {
        $this->tplVarManager->set('flag_disable_left', true);
        $this->tplVarManager->set('flag_disable_right', true);
    }

    /**
     * @return mixed
     */
    public function getRedirectNeeded()
    {
        return $this->redirectNeeded;
    }

    /**
     * @return mixed
     */
    public function getRedirectDestination()
    {
        return $this->redirectDestination;
    }
}
