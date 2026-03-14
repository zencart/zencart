<?php
/**
 * PayPal REST API Webhook Contract
 * This abstract class is the base for all configured webhook handler classes.
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte June 2025 $
 *
 * Last updated: v2.0.0
 */

namespace PayPalRestful\Webhooks;

use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Common\Logger;

abstract class WebhookHandlerContract
{
    protected array $eventsHandled = [];

    protected WebhookObject $webhook;
    protected array $data;
    protected string $eventType;

    protected Logger $log;

    protected PayPalRestfulApi $ppr;
    protected $paymentModule; // TODO: we can probably declare this as an instance of \paypalr

    public function __construct(WebhookObject $webhook)
    {
        $this->webhook = $webhook;
        $this->data = $this->webhook->getJsonBody();
        $this->eventType = $this->data['event_type'];

        $this->log = new Logger();
    }

    abstract public function action(): void;

    /**
     * Instantiate paypalr payment module, including its language string dependencies.
     */
    protected function loadCorePaymentModuleAndLanguageStrings(): void
    {
        require DIR_WS_CLASSES . 'payment.php';
        $payment_modules = new \payment ('paypalr');
        $this->paymentModule = $GLOBALS[$payment_modules->selected_module];
    }

    /**
     * Call this before making API calls if needed by the webhook
     * It will grab the active merchant credentials and instantiate the API class object.
     */
    protected function getApiAndCredentials(): bool
    {
        if (!empty($this->ppr)) {
            return true;
        }

        [$client_id, $secret] = \paypalr::getEnvironmentInfo();
        if ($client_id !== '' && $secret !== '') {
            $this->ppr = new PayPalRestfulApi(MODULE_PAYMENT_PAYPALR_SERVER, $client_id, $secret);
            return true;
        }

        return false;
    }

    /**
     * Determine whether the selected class should respond.
     * In the case of PayPal webhooks, we currently just check whether the selected class
     * has the EventType registered as a property (it should, because filename is based on event name).
     * Other checks could be added by overriding this function.
     */
    public function eventTypeIsSupported(): bool
    {
        if (!empty($this->eventsHandled) && \in_array($this->eventType, $this->eventsHandled, true)) {
            return true;
        }

        $this->log->write('WARNING: ' . __CLASS__ . ' does not support requested action: [' . $this->eventType . ']');
        return false;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

}
