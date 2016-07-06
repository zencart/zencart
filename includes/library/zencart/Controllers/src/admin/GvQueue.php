<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

/**
 * Class GvQueue
 * @package ZenCart\Controllers
 */
class GvQueue extends AbstractLeadController
{

    public function releaseConfirmExecute()
    {
        $result = $this->tryReleaseGV();
        if ($result === true) {
            $this->filterExecute();
            return;
        }
        header("Status: 403 Forbidden", true, 403);  //@todo REFACTOR  handle header output in main controller
        $this->response = $result;
    }

    private function tryReleaseGV()
    {
        if (!$this->request->has('id', 'post')) {
            return false;
        }
        $result = $this->releaseFromQueueSql($this->request->readPost('id'));
        if (!$result) {
            return false;
        }
        $this->updateGVBalances($this->request->readPost('id'));
        $this->sendReleaseEmail();
        return true;
    }

    private function releaseFromQueueSql($id)
    {
        $sql = "UPDATE " . TABLE_COUPON_GV_QUEUE . " set release_flag = 'Y' WHERE unique_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $id, 'integer');
        $result = $this->dbConn->execute($sql);
        return $result;
    }

    private function updateGVBalances($id)
    {
        $sql = "SELECT customer_id, amount, order_id
                                  FROM " . TABLE_COUPON_GV_QUEUE . "
                                  WHERE unique_id=" . (int)$id;
        $result = $this->dbConn->Execute($sql);
        $this->orderId = $result->fields['order_id'];
        $this->gvAmount = $result->fields['amount'];
        $this->customerId = $result->fields['customer_id'];
        $sql = "SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id='" . $this->customerId . "'";
        $result = $this->dbConn->execute($sql);
        $total_gv_amount = 0;
        $customer_gv = false;
        if ($result->RecordCount() > 0) {
            $total_gv_amount = $result->fields['amount'];
            $customer_gv = true;
        }
        $total_gv_amount = $total_gv_amount + $this->gvAmount;
        if ($customer_gv) {
            $this->dbConn->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                      set amount='" . $total_gv_amount . "'
                      where customer_id='" . $this->customerId . "'");
        } else {
            $this->dbConn->Execute("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . "
                    (customer_id, amount)
                    VALUES ('" . $this->customerId . "', '" . $total_gv_amount . "')");
        }
    }
    private function sendReleaseEmail()
    {
        $currencies = new \currencies();

        $mail = $this->dbConn->Execute("select customers_firstname, customers_lastname, customers_email_address
                           from " . TABLE_CUSTOMERS . "
                           where customers_id = '" . $this->customerId . "'");

        $message  = TEXT_REDEEM_GV_MESSAGE_HEADER . "\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n\n" . TEXT_REDEEM_GV_MESSAGE_RELEASED;
        $message .= sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, $currencies->format($this->gvAmount)) . "\n\n";
        $message .= TEXT_REDEEM_GV_MESSAGE_THANKS . "\n" . STORE_OWNER . "\n\n" . HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
        $message .= TEXT_REDEEM_GV_MESSAGE_BODY;
        $message .= TEXT_REDEEM_GV_MESSAGE_FOOTER;
        $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

        $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
        $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];
        $html_msg['GV_NOTICE_HEADER']  = TEXT_REDEEM_GV_MESSAGE_HEADER;
        $html_msg['GV_NOTICE_RELEASED']  = TEXT_REDEEM_GV_MESSAGE_RELEASED;
        $html_msg['GV_NOTICE_AMOUNT_REDEEM'] = sprintf(TEXT_REDEEM_GV_MESSAGE_AMOUNT, '<strong>' . $currencies->format($this->gvAmount) . '</strong>');
        $html_msg['GV_NOTICE_VALUE'] = $currencies->format($this->gvAmount);
        $html_msg['GV_NOTICE_THANKS'] = TEXT_REDEEM_GV_MESSAGE_THANKS;
        $html_msg['TEXT_REDEEM_GV_MESSAGE_BODY'] = TEXT_REDEEM_GV_MESSAGE_BODY;
        $html_msg['TEXT_REDEEM_GV_MESSAGE_FOOTER'] = TEXT_REDEEM_GV_MESSAGE_FOOTER;

//send the message
        zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $this->orderId , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'gv_queue');
        // send copy to Admin if enabled
        if (SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO != '') {
            zen_mail('', SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO, SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_SUBJECT . ' ' . TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $this->orderId , $message, STORE_NAME, EMAIL_FROM, $html_msg, 'gv_queue');
        }

        zen_record_admin_activity('GV Queue entry released in the amount of ' . $gv_amount . ' for ' . $mail->fields['customers_email_address'], 'info');
        $this->notify('ADMIN_GV_QUEUE_RELEASE', $mail->fields['customers_email_address'], $this->gvAmount);
    }
}
