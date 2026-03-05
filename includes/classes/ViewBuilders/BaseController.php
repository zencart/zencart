<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
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

    protected array $infoBox = [];

    public function __construct(protected Request $request, protected $messageStack, protected TableViewDefinition $tableDefinition, protected $formatter)
    {
        $this->infoBox = ['header' => [], 'content' => []];
    }

    /**
     * @since ZC v1.5.8
     */
    public function processRequest(): void
    {
        $action = $this->getAction();
        $method = ($action === '') ? 'processDefaultAction' : 'processAction' . ucfirst($action);
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
    public function setBoxHeader(string $content, array $params = []): void
    {
        $this->infoBox['header'][] = ['text' => $content, 'params' => $params];
    }

    /**
     * @since ZC v1.5.8
     */
    public function setBoxForm(string $content): void
    {
        $this->infoBox['content']['form'] = $content;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getBoxHeader(): mixed
    {
        return $this->infoBox['header'];
    }

    /**
     * @since ZC v1.5.8
     */
    public function setBoxContent(string $content, array $params = []): void
    {
        $this->infoBox['content'][] = ['text' => $content, 'params' => $params];
    }

    /**
     * @since ZC v1.5.8
     */
    public function getBoxContent(): array
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
    public function currentFieldValue($field): string|array|int|float|null
    {
        $currentRow = $this->formatter->currentRowFromRequest();
        if (is_null($currentRow)) {
            return null;
        }
        return $currentRow->$field;
    }

    /**
     * @since ZC v1.5.8
     */
    public function outputMessageList($errorList, $errorType): void
    {
        if (!count($errorList)) {
            return;
        }
        foreach ($errorList as $error) {
            $this->messageStack->add_session($error, $errorType);
        }
    }

}
