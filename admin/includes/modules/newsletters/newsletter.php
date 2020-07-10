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

class newsletter {

  var $show_choose_audience, $title, $content, $content_html, $queryname;

  function __construct($title, $content, $content_html, $queryname = '') {
    $this->show_choose_audience = true;
//      $this->show_choose_audience = (count(get_audiences_list('newsletters')) > 1 );    //if only 1 list of newsletters, don't offer selection
    $this->title = $title;
    $this->content = $content;
    $this->content_html = $content_html;
    $this->query_name = $queryname;
  }

  function choose_audience() {
    global $_GET;

    $choose_audience_string = zen_draw_form('audience', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm', 'post', 'onsubmit="return check_form(audience);" class="form-horizontal"') . PHP_EOL;
    $choose_audience_string .= '<div class="form-group">' . PHP_EOL;
    $choose_audience_string .= zen_draw_label(TEXT_PLEASE_SELECT_AUDIENCE, 'audience_selected', 'class="control-label col-sm-3"') . PHP_EOL;
    $choose_audience_string .= '<div class="col-sm-9 col-md-6">' . PHP_EOL;
    $choose_audience_string .= zen_draw_pull_down_menu('audience_selected', get_audiences_list('newsletters'), $this->query_name, 'class="form-control"') . PHP_EOL;
    $choose_audience_string .= '</div>' . PHP_EOL;
    $choose_audience_string .= '</div>' . PHP_EOL;
    $choose_audience_string .= '<div class="row text-right">' . PHP_EOL;
    $choose_audience_string .= '<button type="submit" class="btn btn-primary">' . IMAGE_SELECT . '</button>' . PHP_EOL;
    $choose_audience_string .= '</div>' . PHP_EOL;
    $choose_audience_string .= '</form>' . PHP_EOL;

    return $choose_audience_string;
  }

  function confirm() {
    global $db;

    if ($_POST['audience_selected']) {
      $this->query_name = $_POST['audience_selected'];
      if (is_array($_POST['audience_selected'])) {
        $this->query_name = $_POST['audience_selected']['text'];
      }
    }

    $query_array = get_audience_sql_query($this->query_name, 'newsletters');
    $mail = $db->Execute($query_array['query_string']);
    $confirm_string = '<div class="row">' . PHP_EOL;
    $confirm_string .= '<div class="col-sm-12"><span class="text-danger"><strong>' . sprintf(TEXT_COUNT_CUSTOMERS, $mail->RecordCount()) . '</strong></span></div>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '  <div class="row">' . PHP_EOL;
    $confirm_string .= zen_draw_separator() . PHP_EOL;
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
    $confirm_string .= zen_draw_form('ready_to_send', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm_send');
    $confirm_string .= zen_draw_hidden_field('audience_selected', $this->query_name) . PHP_EOL;
    $confirm_string .= '<div class="row text-right">' . PHP_EOL;
    $confirm_string .= '<button type="submit" class="btn btn-primary">' . IMAGE_SEND_EMAIL . '</button> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>' . PHP_EOL;
    $confirm_string .= '</div>' . PHP_EOL;
    $confirm_string .= '</form>' . PHP_EOL;

    return $confirm_string;
  }

  function send($newsletter_id) {
    global $db;
    $audience_select = get_audience_sql_query($this->query_name, 'newsletters');
    $audience = $db->Execute($audience_select['query_string']);
    $records = $audience->RecordCount();
    if ($records == 0) {
      return 0;
    }
    $i = 0;

    foreach ($audience as $item) {
      $i++;
      $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
      $html_msg['EMAIL_FIRST_NAME'] = $item['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME'] = $item['customers_lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = $this->content_html;
      zen_mail($item['customers_firstname'] . ' ' . $item['customers_lastname'], $item['customers_email_address'], $this->title, $this->content, STORE_NAME, EMAIL_FROM, $html_msg, 'newsletters');
      echo zen_image(DIR_WS_ICONS . 'tick.gif', $item['customers_email_address']);

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
    return $records;  //return number of records processed whether successful or not
  }

}
