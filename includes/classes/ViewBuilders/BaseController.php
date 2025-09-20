<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2023 Jul 01 Modified in v2.0.0-alpha1 $
 */

namespace Zencart\ViewBuilders;

use Zencart\Request\Request;
use Zencart\Traits\NotifierManager;

/**
 * @since ZC v1.5.8
 */
class BaseController
{
    use NotifierManager;

    protected $request;
    protected $messageStack;
    protected $tableDefinition;
    protected $infoBox = [];
    protected $formatter;

    public function __construct(Request $request, $messageStack, TableViewDefinition $tableDefinition, $formatter)
    {
        $this->request = $request;
        $this->messageStack = $messageStack;
        $this->tableDefinition = $tableDefinition;
        $this->infoBox = ['header' => [], 'content' => []];
        $this->formatter = $formatter;
    }

    /**
     * @since ZC v1.5.8
     */
    public function processRequest()
    {
        $action = $this->getAction();
        $method = ($action == '') ? 'processDefaultAction' : 'processAction' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->$method();
        }
        $this->notify('NOTIFY_TABLEVIEW_PROCESSREQUEST', [], $method);
    }

    /**
     * @since ZC v1.5.8
     */
    protected function getAction() : string
    {
        $action = $this->request->input('action', '');
        return $action;
    }

    /**
     * @since ZC v1.5.8
     */
    public function setBoxHeader(string $content, array $params = [])
    {
        $this->infoBox['header'][] = ['text' => $content, 'params' => $params];
    }

    /**
     * @since ZC v1.5.8
     */
    public function setBoxForm(string $content)
    {
        $this->infoBox['content']['form'] = $content;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getBoxHeader()
    {
        return $this->infoBox['header'];
    }

    /**
     * @since ZC v1.5.8
     */
    public function setBoxContent(string $content, array $params = [])
    {
        $this->infoBox['content'][] = ['text' => $content, 'params' => $params];
    }

    /**
     * @since ZC v1.5.8
     */
    public function getBoxContent()
    {
        return $this->infoBox['content'];
    }

    /**
     * @since ZC v1.5.8
     */
    public function pageLink(): string
    {
        $page = $this->request->input($this->tableDefinition->getParameter('pagerVariable'), 1);
        return $this->tableDefinition->getParameter('pagerVariable') . '=' . $page;
    }

    /**
     * @since ZC v1.5.8
     */
    public function colKeyLink() : string
    {
        return $this->tableDefinition->colKeyName() . '=' . $this->currentFieldValue($this->tableDefinition->getParameter('colKey'));
    }

    /**
     * @since ZC v1.5.8
     */
    public function currentFieldValue($field)
    {
        $currentRow = $this->formatter->currentRowFromRequest();
        return $currentRow->$field;
    }

    /**
     * @since ZC v1.5.8
     */
    public function outputMessageList($errorList, $errorType)
    {
        if (!count($errorList)) {
            return;
        }
        foreach ($errorList as $error) {
            $this->messageStack->add_session($error, $errorType);
        }
    }

}
