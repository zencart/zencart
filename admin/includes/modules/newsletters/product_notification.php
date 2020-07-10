<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 Oct 02 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

class product_notification {

  var $show_choose_audience, $title, $content, $content_html;

  function __construct($title, $content, $content_html, $queryname = '') {
    $this->show_choose_audience = true;
    $this->title = $title;
    $this->content = $content;
    $this->content_html = $content_html;
  }

  function choose_audience() {
    global $db;

    $products_array = array();
    $products = $db->Execute("SELECT pd.products_id, pd.products_name
                              FROM " . TABLE_PRODUCTS . " p,
                                   " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              WHERE pd.language_id = " . (int)$_SESSION['languages_id'] . "
                              AND pd.products_id = p.products_id
                              AND p.products_status = 1
                              ORDER BY pd.products_name");

    foreach ($products as $product) {
      $products_array[] = array(
        'id' => $product['products_id'],
        'text' => $product['products_name']);
    }

    $choose_audience_string = '<script>
function mover(move) {
  if (move == \'remove\') {
    for (x=0; x<(document.notifications.products.length); x++) {
      if (document.notifications.products.options[x].selected) {
        with(document.notifications.elements[\'chosen[]\']) {
          options[options.length] = new Option(document.notifications.products.options[x].text,document.notifications.products.options[x].value);
        }
        document.notifications.products.options[x] = null;
        x = -1;
      }
    }
  }
  if (move == \'add\') {
    for (x=0; x<(document.notifications.elements[\'chosen[]\'].length); x++) {
      if (document.notifications.elements[\'chosen[]\'].options[x].selected) {
        with(document.notifications.products) {
          options[options.length] = new Option(document.notifications.elements[\'chosen[]\'].options[x].text,document.notifications.elements[\'chosen[]\'].options[x].value);
        }
        document.notifications.elements[\'chosen[]\'].options[x] = null;
        x = -1;
      }
    }
  }
  return true;
}

function selectAll(FormName, SelectBox) {
  temp = "document." + FormName + ".elements[\'" + SelectBox + "\']";
  Source = eval(temp);

  for (x=0; x<(Source.length); x++) {
    Source.options[x].selected = "true";
  }

  if (x<1) {
    alert("' . JS_PLEASE_SELECT_PRODUCTS . '");
    return false;
  } else {
    return true;
  }
}
</script>';

    $global_button = '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm&global=true') . '" class="btn btn-default" role="button">' . BUTTON_GLOBAL . '</a>' . PHP_EOL;

    $cancel_button = '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '" class="btn btn-default" role="button">' . BUTTON_CANCEL . '</a>' . PHP_EOL;

    $choose_audience_string .= zen_draw_form('notifications' ,FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm', 'post', 'onSubmit="return selectAll(\'notifications\', \'chosen[]\')"') . PHP_EOL;;
    $choose_audience_string .= '<div class="row">' . PHP_EOL;
    $choose_audience_string .= '<div class="col-sm-4"><b>' . TEXT_PRODUCTS . '</b><br />' . zen_draw_pull_down_menu('products', $products_array, '', 'size="20" class="form-control" multiple') . '</div>' . PHP_EOL;
    $choose_audience_string .= '<div class="col-sm-4 text-center"><div class="btn-group-vertical">' . $global_button . '<input type="button" value="' . BUTTON_SELECT . '" onClick="mover(\'remove\');" class="btn btn-default"><input type="button" value="' . BUTTON_UNSELECT . '" onClick="mover(\'add\');" class="btn btn-default"><input type="submit" value="' . BUTTON_SUBMIT . '" class="btn btn-default">' . $cancel_button . '</div></div>' . PHP_EOL;
    $choose_audience_string .= '<div class="col-sm-4"><b>' . TEXT_SELECTED_PRODUCTS . '</b><br />' . zen_draw_pull_down_menu('chosen[]', array(), '', 'size="20" class="form-control" multiple') . '</div>' . PHP_EOL;
    $choose_audience_string .= '</div>' . PHP_EOL;
    $choose_audience_string .= '</form>' . PHP_EOL;

    return $choose_audience_string;
  }

  function confirm() {
    global $db;

    $audience = array();

    if (isset($_GET['global']) && ($_GET['global'] == 'true')) {
      $products = $db->Execute("SELECT DISTINCT customers_id
                                FROM " . TABLE_PRODUCTS_NOTIFICATIONS);

      foreach ($products as $product) {
        $audience[$product['customers_id']] = '1';
      }

      $customers = $db->Execute("SELECT customers_info_id
                                 FROM " . TABLE_CUSTOMERS_INFO . "
                                 WHERE global_product_notifications = 1");

      foreach ($customers as $customer) {
        $audience[$customer['customers_info_id']] = '1';
      }
    } else {
      $chosen = $_POST['chosen'];

      $ids = zen_db_input(implode(',', $chosen));

      $products = $db->Execute("SELECT DISTINCT customers_id
                                FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                                WHERE products_id in (" . $ids . ")");

      foreach ($products as $product) {
        $audience[$product['customers_id']] = '1';
      }

      $customers = $db->Execute("SELECT customers_info_id
                                 FROM " . TABLE_CUSTOMERS_INFO . "
                                 WHERE global_product_notifications = 1");

      foreach ($customers as $customer) {
        $audience[$customer['customers_info_id']] = '1';
      }
    }

    $confirm_string = '<div class="row">' . PHP_EOL;
    $confirm_string .= '<div class="col-sm-12"><span class="text-danger"><strong>' . sprintf(TEXT_COUNT_CUSTOMERS, sizeof($audience)) . '</strong></span></div>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= zen_draw_separator() . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= '<div class="col-sm-12"><strong>' . $this->title . '</strong></div>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= zen_draw_separator() . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= '<div class="col-sm-12">' . nl2br($this->content_html) . '</div>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= zen_draw_separator() . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= '<div class="col-sm-12"><tt>' . nl2br($this->content) . '</tt></div>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '<div class="row">' . PHP_EOL;
    $confirm_string .= zen_draw_separator() . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= zen_draw_form('confirm', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm_send') . PHP_EOL;
    $confirm_string .= '<div class="row text-right">' . PHP_EOL;
    if (sizeof($audience) > 0) {
      if (isset($_GET['global']) && ($_GET['global'] == 'true')) {
        $confirm_string .= zen_draw_hidden_field('global', 'true') . PHP_EOL;
      } else {
        for ($i = 0, $n = sizeof($chosen); $i < $n; $i++) {
          $confirm_string .= zen_draw_hidden_field('chosen[]', $chosen[$i]) . PHP_EOL;
        }
      }
      $confirm_string .= '<button type="submit" class="btn btn-primary">' . IMAGE_SEND . '</button> ';
    }
    $confirm_string .= '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=send') . '" class="btn btn-default" role="button">' . IMAGE_BACK . '</a> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>' . PHP_EOL;
     $confirm_string .= '  </div>' . PHP_EOL;

    return $confirm_string;
  }

  function send($newsletter_id) {
    global $db;

    $audience = array();

    if (isset($_POST['global']) && ($_POST['global'] == 'true')) {
      $products = $db->Execute("SELECT DISTINCT pn.customers_id, c.customers_firstname,
                                                c.customers_lastname, c.customers_email_address
                                FROM " . TABLE_CUSTOMERS . " c,
                                     " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                                WHERE c.customers_id = pn.customers_id");

      foreach ($products as $product) {
        $audience[$product['customers_id']] = array(
          'firstname' => $product['customers_firstname'],
          'lastname' => $product['customers_lastname'],
          'email_address' => $product['customers_email_address']);
      }

      $customers = $db->Execute("SELECT c.customers_id, c.customers_firstname, c.customers_lastname,
                                        c.customers_email_address
                                 FROM " . TABLE_CUSTOMERS . " c,
                                      " . TABLE_CUSTOMERS_INFO . " ci
                                 WHERE c.customers_id = ci.customers_info_id
                                 AND ci.global_product_notifications = 1");

      foreach ($customers as $customer) {
        $audience[$customer['customers_id']] = array(
          'firstname' => $customer['customers_firstname'],
          'lastname' => $customer['customers_lastname'],
          'email_address' => $customer['customers_email_address']);
      }
    } else {  //not global==true; instead, process all selected products
      $chosen = $_POST['chosen'];

      $ids = zen_db_input(implode(',', $chosen));

      $products = $db->Execute("SELECT DISTINCT pn.customers_id, c.customers_firstname,
                                                c.customers_lastname, c.customers_email_address
                                FROM " . TABLE_CUSTOMERS . " c,
                                     " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                                WHERE c.customers_id = pn.customers_id
                                AND pn.products_id IN (" . $ids . ")");

      foreach ($products as $product) {
        $audience[$product['customers_id']] = array(
          'firstname' => $product['customers_firstname'],
          'lastname' => $product['customers_lastname'],
          'email_address' => $product['customers_email_address']);
      }

      $customers = $db->Execute("SELECT c.customers_id, c.customers_firstname, c.customers_lastname,
                                        c.customers_email_address
                                 FROM " . TABLE_CUSTOMERS . " c,
                                      " . TABLE_CUSTOMERS_INFO . " ci
                                 WHERE c.customers_id = ci.customers_info_id
                                 AND ci.global_product_notifications = 1");

      foreach ($customers as $customer) {
        $audience[$customer['customers_id']] = array(
          'firstname' => $customer['customers_firstname'],
          'lastname' => $customer['customers_lastname'],
          'email_address' => $customer['customers_email_address']);
      }
    }


//send emails
    $i = 0;
    foreach ($audience as $key => $value) {
      $i++;
      $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
      $html_msg['EMAIL_FIRST_NAME'] = $value['firstname'];
      $html_msg['EMAIL_LAST_NAME'] = $value['lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = $this->content_html;
      zen_mail($value['firstname'] . ' ' . $value['lastname'], $value['email_address'], $this->title, $this->content, STORE_NAME, EMAIL_FROM, $html_msg, 'product_notification', '');
      echo zen_image(DIR_WS_ICONS . 'tick.gif', $value['email_address']);

      //force output to the screen to show status indicator each time a message is sent...
      if (function_exists('ob_flush')) {
        @ob_flush();
      }
      @flush();
    }

    $newsletter_id = zen_db_prepare_input($newsletter_id);
    $db->Execute("UPDATE " . TABLE_NEWSLETTERS . "
                  SET date_sent = now(),
                      status = 1
                  WHERE newsletters_id = " . zen_db_input($newsletter_id));
    return $i;  //return number of records processed whether successful or not
  }

}
