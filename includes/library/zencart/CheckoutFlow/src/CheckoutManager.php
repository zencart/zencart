<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\CheckoutFlow;

use ZenCart\Request\Request as Request;
use ZenCart\View\View as View;

/**
 * Class CheckoutManager
 * @package Zencart\CheckoutFlow
 */
class CheckoutManager extends \base
{
    /**
     * @var FlowFactoryInterface
     */
    protected $flowFactory;
    /**
     * @var FlowStepFactoryInterface
     */
    protected $flowStepFactory;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var mixed
     */
    protected $checkoutFlow;
    /**
     * @var mixed
     */
    protected $checkoutFlowStep;

    /**
     * @param Request $request
     * @param \queryFactory $dbConn
     * @param FlowFactoryInterface $flowFactory
     * @param FlowStepFactoryInterface $flowStepFactory
     */
    public function __construct(
        Request $request,
        View $view,
        \queryFactory $dbConn,
        FlowFactoryInterface $flowFactory,
        FlowStepFactoryInterface $flowStepFactory
    ) {
        $this->flowFactory = $flowFactory;
        $this->flowStepFactory = $flowStepFactory;
        $this->request = $request;
        $this->checkoutFlow = $this->getFlowFromFactory($this->determineInitialFlow(), $view);
        $flowStepName = $this->getFlowStepFromFlow($this->request->readGet('step'));
        $this->checkoutFlowStep = $this->flowStepFactory($flowStepName, $dbConn, $view);
        $this->checkoutFlow->setCheckoutStepsTemplate();
        $this->requireStepLanguageFile();
        $this->checkoutFlow->processFlow();
        $view->getTplVarManager()->globalize();
        $this->notify('NOTIFY_CHECKOUT_MANAGER_CONSTRUCT_END');
    }

    /**
     * @return string
     */
    protected function determineInitialFlow()
    {
        $flow = null;
        $this->notify('NOTIFY_CHECKOUT_MANAGER_DETERMINE_INITIAL_FLOW', array(), $flow);
        if (!isset($flow)) {
            $flow = 'standard';
        }
        return $flow;
    }

    /**
     * @param $step
     * @return mixed
     */
    protected function getFlowStepFromFlow($step)
    {
        if (!isset($step)) {
            $step = $this->checkoutFlow->getInitialStep();
        }
        return $step;
    }


    /**
     * @param $step
     * @param $dbConn
     * @return mixed
     */
    protected function flowStepFactory($step, $dbConn, $view)
    {
        $flowStep = $this->flowStepFactory->getFlowStep($step, $this, $this->request, $dbConn, $view);
        return $flowStep;
    }

    /**
     * @param $flow
     * @return mixed
     */
    protected function getFlowFromFactory($flow, $view)
    {
        $flow = $this->flowFactory->getFlow($flow, $this, $this->request, $view);
        return $flow;
    }


    /**
     *
     */
    public function requireStepLanguageFile()
    {
        global $template, $template_dir, $language_page_directory;
        $body_code = $this->checkoutFlowStep->getTemplateName();
        $current_page_base = $body_code . '_flow';
        include(DIR_WS_MODULES . 'require_languages.php');
    }

    /**
     * @param $currentStep
     * @return mixed
     */
    public function getNextFlowStep($currentStep)
    {
        $stepList = $this->checkoutFlow->getStepsList();
        $key = array_search($currentStep, $stepList);
        return $stepList[$key + 1];
    }

    /**
     * @return mixed
     */
    public function getRedirectNeeded()
    {
        return $this->checkoutFlow->getRedirectNeeded();
    }

    /**
     * @return mixed
     */
    public function getRedirectDestination()
    {
        return $this->checkoutFlow->getRedirectDestination();
    }

    /**
     * @return mixed
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return mixed
     */
    public function getViewStep()
    {
        return $this->checkoutFlowStep->getViewStep();
    }

    /**
     * @param mixed $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @return mixed
     */
    public function getCheckoutFlowStep()
    {
        return $this->checkoutFlowStep;
    }

    /**
     * @param $checkoutFlowStep
     */
    public function setCheckoutFlowStep($checkoutFlowStep)
    {
        $this->checkoutFlowStep = $checkoutFlowStep;
    }

    /**
     * @return mixed
     */
    public function getCheckoutFlow()
    {
        return $this->checkoutFlow;
    }

    /**
     * @param mixed $checkoutFlow
     */
    public function setCheckoutFlow($checkoutFlow)
    {
        $this->checkoutFlow = $checkoutFlow;
    }
}
