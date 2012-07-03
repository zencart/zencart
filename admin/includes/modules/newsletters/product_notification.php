<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product_notification.php 18695 2011-05-04 05:24:19Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
  class product_notification {
    var $show_choose_audience, $title, $content, $content_html;

    function product_notification($title, $content, $content_html, $queryname='') {
      $this->show_choose_audience = true;
      $this->title = $title;
      $this->content = $content;
      $this->content_html = $content_html;
    }

    function choose_audience() {
      global $_GET, $db;

      $products_array = array();
      $products = $db->Execute("select pd.products_id, pd.products_name
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                where pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                and pd.products_id = p.products_id
                                and p.products_status = '1'
                                order by pd.products_name");

      while (!$products->EOF) {
        $products_array[] = array('id' => $products->fields['products_id'],
                                  'text' => $products->fields['products_name']);
        $products->MoveNext();
      }

$choose_audience_string = '<script language="javascript"><!--
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
    alert(\'' . JS_PLEASE_SELECT_PRODUCTS . '\');
    return false;
  } else {
    return true;
  }
}
//--></script>';

      $global_button = '<script language="javascript"><!--' . "\n" .
                       'document.write(\'<input type="button" value="' . BUTTON_GLOBAL . '" style="width: 8em;" onclick="document.location=\\\'' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm&global=true') . '\\\'">\');' . "\n" .
                       '//--></script><noscript><a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm&global=true') . '">[ ' . BUTTON_GLOBAL . ' ]</a></noscript>';

      $cancel_button = '<script language="javascript"><!--' . "\n" .
                       'document.write(\'<input type="button" value="' . BUTTON_CANCEL . '" style="width: 8em;" onclick="document.location=\\\'' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '\\\'">\');' . "\n" .
                       '//--></script><noscript><a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">[ ' . BUTTON_CANCEL . ' ]</a></noscript>';

      $choose_audience_string .= '<form name="notifications" action="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm') . '" method="post" onSubmit="return selectAll(\'notifications\', \'chosen[]\')"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" . '<input type="hidden" name="securityToken" value="' . $_SESSION['securityToken'] . '" />' . 
                                 '  <tr>' . "\n" .
                                 '    <td align="center" class="main"><b>' . TEXT_PRODUCTS . '</b><br />' . zen_draw_pull_down_menu('products', $products_array, '', 'size="20" style="width: 20em;" multiple') . '</td>' . "\n" .
                                 '    <td align="center" class="main">&nbsp;<br />' . $global_button . '<br /><br /><br /><input type="button" value="' . BUTTON_SELECT . '" style="width: 8em;" onClick="mover(\'remove\');"><br /><br /><input type="button" value="' . BUTTON_UNSELECT . '" style="width: 8em;" onClick="mover(\'add\');"><br /><br /><br /><input type="submit" value="' . BUTTON_SUBMIT . '" style="width: 8em;"><br /><br />' . $cancel_button . '</td>' . "\n" .
                                 '    <td align="center" class="main"><b>' . TEXT_SELECTED_PRODUCTS . '</b><br />' . zen_draw_pull_down_menu('chosen[]', array(), '', 'size="20" style="width: 20em;" multiple') . '</td>' . "\n" .
                                 '  </tr>' . "\n" .
                                 '</table></form>';

      return $choose_audience_string;
    }

    function confirm() {
      global $_GET, $_POST, $db;

      $audience = array();

      if (isset($_GET['global']) && ($_GET['global'] == 'true')) {
        $products = $db->Execute("select distinct customers_id
                                  from " . TABLE_PRODUCTS_NOTIFICATIONS);

        while (!$products->EOF) {
          $audience[$products->fields['customers_id']] = '1';
          $products->MoveNext();
        }

        $customers = $db->Execute("select customers_info_id
                                   from " . TABLE_CUSTOMERS_INFO . "
                                   where global_product_notifications = '1'");

        while (!$customers->EOF) {
          $audience[$customers->fields['customers_info_id']] = '1';
          $customers->MoveNext();
        }
      } else {
        $chosen = $_POST['chosen'];

        $ids = zen_db_input(implode(',', $chosen));

        $products = $db->Execute("select distinct customers_id
                                  from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                                  where products_id in (" . $ids . ")");

        while (!$products->EOF) {
          $audience[$products->fields['customers_id']] = '1';
          $products->MoveNext();
        }

        $customers = $db->Execute("select customers_info_id
                                   from " . TABLE_CUSTOMERS_INFO . "
                                   where global_product_notifications = '1'");

        while (!$customers->EOF) {
          $audience[$customers->fields['customers_info_id']] = '1';
          $customers->MoveNext();
        }
      }

      $confirm_string = '<table border="0" cellspacing="0" cellpadding="2">' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><font color="#ff0000"><b>' . sprintf(TEXT_COUNT_CUSTOMERS, sizeof($audience)) . '</b></font></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><b>' . $this->title . '</b></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '<hr /></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main">' . nl2br($this->content_html) . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td><hr>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><tt>' . nl2br($this->content) . '</tt><hr /></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . zen_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . zen_draw_form('confirm', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm_send') . "\n" .
                        '    <td align="right">';
      if (sizeof($audience) > 0) {
        if (isset($_GET['global']) && ($_GET['global'] == 'true')) {
          $confirm_string .= zen_draw_hidden_field('global', 'true');
        } else {
          for ($i = 0, $n = sizeof($chosen); $i < $n; $i++) {
            $confirm_string .= zen_draw_hidden_field('chosen[]', $chosen[$i]);
          }
        }
        $confirm_string .= zen_image_submit('button_send.gif', IMAGE_SEND) . ' ';
      }
      $confirm_string .= '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=send') . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a></td>' . "\n" .
                         '  </tr>' . "\n" .
                         '</table>';

      return $confirm_string;
    }

    function send($newsletter_id) {
      global $_POST, $db;

      $audience = array();

      if (isset($_POST['global']) && ($_POST['global'] == 'true')) {
        $products = $db->Execute("select distinct pn.customers_id, c.customers_firstname,
                                                  c.customers_lastname, c.customers_email_address
                                  from " . TABLE_CUSTOMERS . " c, " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                                  where c.customers_id = pn.customers_id");

        while (!$products->EOF) {
          $audience[$products->fields['customers_id']] = array('firstname' => $products->fields['customers_firstname'],
                                                       'lastname' => $products->fields['customers_lastname'],
                                                       'email_address' => $products->fields['customers_email_address']);
          $products->MoveNext();
        }

        $customers = $db->Execute("select c.customers_id, c.customers_firstname, c.customers_lastname,
                                          c.customers_email_address
                                   from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci
                                   where c.customers_id = ci.customers_info_id
                                   and ci.global_product_notifications = '1'");

        while (!$customers->EOF) {
          $audience[$customers->fields['customers_id']] = array('firstname' => $customers->fields['customers_firstname'],
                                                        'lastname' => $customers->fields['customers_lastname'],
                                                        'email_address' => $customers->fields['customers_email_address']);
          $customers->MoveNext();
        }
      } else {  //not global==true; instead, process all selected products
        $chosen = $_POST['chosen'];

        $ids = zen_db_input(implode(',', $chosen));

        $products = $db->Execute("select distinct pn.customers_id, c.customers_firstname,
                                                  c.customers_lastname, c.customers_email_address
                                  from " . TABLE_CUSTOMERS . " c, " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                                  where c.customers_id = pn.customers_id
                                  and pn.products_id in (" . $ids . ")");

        while (!$products->EOF) {
          $audience[$products->fields['customers_id']] = array('firstname' => $products->fields['customers_firstname'],
                                                       'lastname' => $products->fields['customers_lastname'],
                                                       'email_address' => $products->fields['customers_email_address']);
          $products->MoveNext();
        }

        $customers = $db->Execute("select c.customers_id, c.customers_firstname, c.customers_lastname,
                                          c.customers_email_address
                                   from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci
                                   where c.customers_id = ci.customers_info_id
                                   and ci.global_product_notifications = '1'");

        while (!$customers->EOF) {
          $audience[$customers->fields['customers_id']] = array('firstname' => $customers->fields['customers_firstname'],
                                                        'lastname' => $customers->fields['customers_lastname'],
                                                        'email_address' => $customers->fields['customers_email_address']);
          $customers->MoveNext();
        }
      }


//send emails
      reset($audience);
    $i=0;
      while (list($key, $value) = each ($audience)) {
    $i++;
      $html_msg['EMAIL_FIRST_NAME'] = $value['firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $value['lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = $this->content_html;
      zen_mail($value['firstname'] . ' ' . $value['lastname'], $value['email_address'], $this->title, $this->content, STORE_NAME, EMAIL_FROM, $html_msg, 'product_notification','');
      echo zen_image(DIR_WS_ICONS . 'tick.gif', $value['email_address']);

      //force output to the screen to show status indicator each time a message is sent...
      if (function_exists('ob_flush')) @ob_flush();
      @flush();
      }

      $newsletter_id = zen_db_prepare_input($newsletter_id);
      $db->Execute("update " . TABLE_NEWSLETTERS . "
                    set date_sent = now(), status = '1'
                    where newsletters_id = '" . zen_db_input($newsletter_id) . "'");
     return $i;  //return number of records processed whether successful or not
    }
  }
