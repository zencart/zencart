<?php
/**
 * Controller for incoming subscribed PayPal Webhook notifications
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte June 2025 $
 *
 * Last updated: v1.2.0
 *
 * This webhook handler must listen on HTTPS port 443.
 * For webhook message deliveries to be successful, the handler must respond with an HTTP 2xx success status every time a webhook is posted.
 * Otherwise (if there is no response, or PayPal cannot connect, or if the server responds with a 404 or 500 error) PayPal will retry each delivery up to 25 times over 3 days.
 */

/**
 * STEPS:
 * 1. Confirm that this is a PayPal webhook
 * 2. Log the incoming call
 * 3. Do CRC check to ensure trustworthiness
 * 4. Acknowledge the incoming call with appropriate HTTP response
 * 5. Take action according to the nature of the webhook payload
 */

/**
 * Set supporting application_top parameters, and boot up
 */
$loaderPrefix = 'webhook';
require 'includes/application_top.php';
$current_page_base = 'ppr_webhook';
require DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/pprAutoload.php';

// call the controller class, which will dispatch as needed, if validation passes
$controller = new PayPalRestful\Webhooks\WebhookController();
$result = $controller();

// properly shut down the application
require 'includes/application_bottom.php';
