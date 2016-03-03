<?php
/**
 * upload Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Thu Apr 2 14:27:45 2015 -0400 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * upload Class.
 * This class is used to manage file uploads
 *
 * @package classes
 */
class upload extends base {
  var $file, $filename, $destination, $permissions, $extensions, $tmp_filename, $message_location;

  function __construct($file = '', $destination = '', $permissions = '644', $extensions = array() ) {
    $this->set_file($file);
    $this->set_destination($destination);
    $this->set_permissions($permissions);

    if (!zen_not_null($extensions)) {
      if (!defined(UPLOAD_FILENAME_EXTENSIONS)) define ('UPLOAD_FILENAME_EXTENSIONS','jpg,jpeg,gif,png,eps,cdr,ai,pdf,tif,tiff,bmp,zip');
      $extensions=explode(" ",preg_replace('/[.,;\s]+/',' ',UPLOAD_FILENAME_EXTENSIONS));
    }
    $this->set_extensions($extensions);

    $this->set_output_messages('direct');

    if (zen_not_null($this->file) && zen_not_null($this->destination)) {
      $this->set_output_messages('session');

      if ( ($this->parse() == true) && ($this->save() == true) ) {
        return true;
      } else {
        // self destruct
        while(list($key,) = each($this)) {
          $this->$key = null;
        }

        return false;
      }
    }
  }

  // iii Added: $key to differentiate between different files uploaded
  function parse($key = '') {
    global $messageStack;

    if (isset($_FILES[$this->file])) {
      if (zen_not_null($key)) {
        $file = array('name' => $_FILES[$this->file]['name'][$key],
        'type' => $_FILES[$this->file]['type'][$key],
        'size' => $_FILES[$this->file]['size'][$key],
        'tmp_name' => $_FILES[$this->file]['tmp_name'][$key]);
      } else {
        $file = array('name' => $_FILES[$this->file]['name'],
        'type' => $_FILES[$this->file]['type'],
        'size' => $_FILES[$this->file]['size'],
        'tmp_name' => $_FILES[$this->file]['tmp_name']);
      }
    } elseif (isset($GLOBALS['HTTP_POST_FILES'][$this->file])) {
      global $HTTP_POST_FILES;

      $file = array('name' => $HTTP_POST_FILES[$this->file]['name'],
      'type' => $HTTP_POST_FILES[$this->file]['type'],
      'size' => $HTTP_POST_FILES[$this->file]['size'],
      'tmp_name' => $HTTP_POST_FILES[$this->file]['tmp_name']);
    } else {
      $file = array('name' => (isset($GLOBALS[$this->file . '_name']) ? $GLOBALS[$this->file . '_name'] : ''),
      'type' => (isset($GLOBALS[$this->file . '_type']) ? $GLOBALS[$this->file . '_type'] : ''),
      'size' => (isset($GLOBALS[$this->file . '_size']) ? $GLOBALS[$this->file . '_size'] : ''),
      'tmp_name' => (isset($GLOBALS[$this->file]) ? $GLOBALS[$this->file] : ''));
    }
    //if (!zen_not_null($file['tmp_name'])) return false;
    //if ($file['tmp_name'] == 'none') return false;
    //if (!is_uploaded_file($file['tmp_name'])) return false;

// not working at this time to test for server limit error
    if (!is_uploaded_file($file['tmp_name'])) {
      if ($this->message_location == 'direct') {
        $messageStack->add_session('header', WARNING_NO_FILE_UPLOADED, 'warning');
      } else {
        $messageStack->add_session('upload', WARNING_NO_FILE_UPLOADED, 'warning');
      }
      return false;
    }

    if ( zen_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
      if (zen_not_null($file['size']) and ($file['size'] > MAX_FILE_UPLOAD_SIZE)) {
        if ($this->message_location == 'direct') {
          $messageStack->add_session('header', ERROR_FILE_TOO_BIG, 'error');
        } else {
          $messageStack->add_session('upload', ERROR_FILE_TOO_BIG, 'error');
        }
        return false;
      }
      if (substr($file['name'], -9) == '.htaccess' || (sizeof($this->extensions) > 0 && !in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions))) {
          if ($this->message_location == 'direct') {
            $messageStack->add_session('header', ERROR_FILETYPE_NOT_ALLOWED . ' ' . UPLOAD_FILENAME_EXTENSIONS, 'error');
          } else {
            $messageStack->add_session('upload', ERROR_FILETYPE_NOT_ALLOWED . ' - ' . UPLOAD_FILENAME_EXTENSIONS, 'error');
          }
          return false;
      }

      $this->set_file($file);
      $this->set_filename($file['name']);
      $this->set_tmp_filename($file['tmp_name']);

      return $this->check_destination();
    } else {
      if ($this->message_location == 'direct') {
        $messageStack->add_session('header', WARNING_NO_FILE_UPLOADED, 'warning');
      } else {
        $messageStack->add_session('upload', WARNING_NO_FILE_UPLOADED, 'warning');
      }
      return false;
    }
  }

  function save() {
    global $messageStack;

    if (substr($this->destination, -1) != '/') $this->destination .= '/';

    if (@move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
      chmod($this->destination . $this->filename, $this->permissions);

      if ($this->message_location == 'direct') {
        $messageStack->add_session('header', SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
      } else {
        $messageStack->add_session('upload', SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
      }

      return true;
    } else {
      if ($this->message_location == 'direct') {
        $messageStack->add_session('header', ERROR_FILE_NOT_SAVED, 'error');
      } else {
        $messageStack->add_session('upload', ERROR_FILE_NOT_SAVED, 'error');
      }

      return false;
    }
  }

  function set_file($file) {
    $this->file = $file;
  }

  function set_destination($destination) {
    $this->destination = $destination;
  }

  function set_permissions($permissions) {
    $this->permissions = octdec($permissions);
  }

  function set_filename($filename) {
    $this->filename = $filename;
  }

  function set_tmp_filename($filename) {
    $this->tmp_filename = $filename;
  }

  function set_extensions($extensions) {
    if (zen_not_null($extensions)) {
      if (is_array($extensions)) {
        $this->extensions = $extensions;
      } else {
        $this->extensions = array($extensions);
      }
    } else {
      $this->extensions = array();
    }
  }

  function check_destination() {
    global $messageStack;

    if (!is_writeable($this->destination)) {
      if (is_dir($this->destination)) {
        if ($this->message_location == 'direct') {
          $messageStack->add_session('header', sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
        } else {
          $messageStack->add_session('upload', sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
        }
      } else {
        if ($this->message_location == 'direct') {
          $messageStack->add_session('header', sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
        } else {
          $messageStack->add_session('upload', sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
        }
      }

      return false;
    } else {
      return true;
    }
  }

  function set_output_messages($location) {
    switch ($location) {
      case 'session':
      $this->message_location = 'session';
      break;
      case 'direct':
      default:
      $this->message_location = 'direct';
      break;
    }
  }
}
