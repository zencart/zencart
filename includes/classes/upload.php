<?php
/**
 * upload Class.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * upload Class.
 * This class is used to manage file uploads
 *
 * @since ZC v1.0.3
 */
 //
// This is the old UPLOAD_FILENAME_EXTENSIONS which was in the database
zen_define_default('UPLOAD_FILENAME_EXTENSIONS_LIST', 'jpg,jpeg,gif,png,eps,cdr,ai,pdf,tif,tiff,bmp,zip');

class upload
{
    protected string $fileVarName;
    protected string $destination;
    protected array $extensions;
    public string $filename = '';
    protected string $message_location;
    protected int $permissions;
    protected string $tmp_filename;

    public function __construct(string $fileVarName = '', string $destination = '', string $permissions = '644', array $extensions = [])
    {
        $this->set_file($fileVarName);
        $this->set_destination($destination);
        $this->set_permissions($permissions);

        if (empty($extensions)) {
            $extensions = explode(' ', preg_replace('/[.,;\s]+/', ' ', UPLOAD_FILENAME_EXTENSIONS_LIST));
        }
        $this->set_extensions($extensions);

        $this->set_output_messages('direct');

        if (!empty($this->fileVarName) && !empty($this->destination)) {
            $this->set_output_messages('session');

            if ($this->parse() === true && $this->save() === true) {
                return;
            }

            // self destruct
            foreach ($this as $key => $val) {
                $this->$key = null;
            }
        }
    }

    /**
     * @since ZC v1.0.3
     */
    public function parse(): bool
    {
        if (empty($_FILES[$this->fileVarName])) {
            return false;
        }

        if ($this->check_destination() === false) {
            return false;
        }

        $file = $_FILES[$this->fileVarName];
        if ($this->fileError($file)) {
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->message_stack(WARNING_NO_FILE_UPLOADED, 'warning');
            return false;
        }

        $this->set_filename($file['name']);
        $this->set_tmp_filename($file['tmp_name']);

        if (!empty($file['size']) && $file['size'] > MAX_FILE_UPLOAD_SIZE) {
            $this->message_stack(ERROR_FILE_TOO_BIG, 'error');
            return false;
        }

        if (str_ends_with($file['name'], '.htaccess') || (count($this->extensions) !== 0 && !in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), $this->extensions))) {
            $this->message_stack(ERROR_FILETYPE_NOT_ALLOWED . ' .' . implode(', .', $this->extensions), 'error');
            return false;
        }

        return true;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function fileError(array $file): bool
    {
        if ((int)$file['error'] === 0) {
            return false;
        }
        switch ((int)$file['error']) {  //- See for details: https://www.php.net/manual/en/filesystem.constants.php#constant.upload-err-form-size
            case UPLOAD_ERR_INI_SIZE:   //- 1
                if (IS_ADMIN_FLAG === true) {
                    $this->message_stack(sprintf(ERROR_FILE_TOO_BIG_INI, ini_get('upload_max_filesize')), 'error'); //- TODO: Check post_max_size, too
                } else {
                    $this->message_stack(ERROR_FILE_TOO_BIG);
                }
                break;

            case UPLOAD_ERR_FORM_SIZE:  //- 2
                if (IS_ADMIN_FLAG === true) {
                    $this->message_stack(sprintf(ERROR_FILE_TOO_BIG_MAXSIZE, $_POST['MAX_FILE_SIZE']), 'error');
                } else {
                    $this->message_stack(ERROR_FILE_TOO_BIG);
                }
                break;

            case UPLOAD_ERR_NO_FILE:    //- 4
                $this->message_stack(WARNING_NO_FILE_UPLOADED, 'warning');
                break;

            default:
                $this->message_stack(sprintf(ERROR_FILE_NOT_SAVED, (int)$file['error']));
                break;
        }
        return true;
    }

    /**
     * @param bool $overwrite
     * @return bool
     * @since ZC v1.0.3
     */
    public function save(bool $overwrite = true): bool
    {
        if (!$overwrite && is_file($this->destination . $this->filename)) {
            $this->message_stack(TEXT_IMAGE_OVERWRITE_WARNING . $this->filename, 'caution');
            return true;
        }

        if (!str_ends_with($this->destination, '/')) {
            $this->destination .= '/';
        }

        if (move_uploaded_file($this->tmp_filename, $this->destination . $this->filename)) {
            chmod($this->destination . $this->filename, $this->permissions);

            $this->message_stack(sprintf(SUCCESS_FILE_SAVED_SUCCESSFULLY, $this->filename), 'success');

            if (function_exists('zen_record_admin_activity')) {
                zen_record_admin_activity(sprintf(SUCCESS_FILE_SAVED_SUCCESSFULLY, $this->filename), 'notice');
            }

            return true;
        }

        $this->message_stack(ERROR_FILE_NOT_SAVED, 'error');

        return false;
    }

    /**
     * @param string $file
     * @since ZC v1.0.3
     */
    public function set_file(string $file): void
    {
        $this->fileVarName = $file;
    }

    /**
     * @param string $destination
     * @since ZC v1.0.3
     */
    public function set_destination(string $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @param string $permissions
     * @since ZC v1.0.3
     */
    public function set_permissions(string $permissions): void
    {
        $this->permissions = (int)octdec($permissions);
    }

    /**
     * @param string $filename
     * @since ZC v1.0.3
     */
    public function set_filename(string $filename): void
    {
        $this->filename = $this->sanitizeFileName($filename);
    }

    /**
     * @param string $filename
     * @since ZC v1.0.3
     */
    public function set_tmp_filename(string $filename): void
    {
        $this->tmp_filename = $filename;
    }

    /**
     * @param array $extensions
     * @since ZC v1.0.3
     */
    function set_extensions(array|string $extensions): void
    {
        if (!empty($extensions)) {
            if (is_array($extensions)) {
                $this->extensions = $extensions;
            } else {
                $this->extensions = [$extensions];
            }
        } else {
            $this->extensions = [];
        }
    }

    /**
     * @since ZC v1.0.3
     */
    public function check_destination(): bool
    {
        if (!is_writeable($this->destination)) {
            if (is_dir($this->destination)) {
                $this->message_stack(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
            } else {
                $this->message_stack(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $location
     * @since ZC v1.0.3
     */
    public function set_output_messages(string $location): void
    {
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

    /**
     * @since ZC v1.5.5e
     */
    protected function message_stack(string $msg = '', string $type = ''): void
    {
        global $messageStack;
        if (!isset($messageStack) || !is_object($messageStack)) {
            return;
        }
        if (IS_ADMIN_FLAG === true) {
            $messageStack->add_session($msg, $type);
            $messageStack->add($msg, $type);
        } elseif ($this->message_location === 'direct') {
            $messageStack->add_session('header', $msg, $type);
        } else {
            $messageStack->add_session('upload', $msg, $type);
        }
    }

    /**
     * @since ZC v2.2.0
     */
    protected function sanitizeFileName(string $filename): string
    {
        // Convert file-extension to lowercase
        $file_pieces = pathinfo($filename);
        $filename = $file_pieces['filename'] . '.' . strtolower($file_pieces['extension']);

        // Replace spaces with hyphens
        $filename = str_replace(' ', '-', $filename);

        // Remove special characters (keep alphanumerics, dashes, underscores, and dots)
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

        // Replace multiple dots with a single dot
        $filename = preg_replace('/\.+/', '.', $filename);

        return $filename;
    }
}
