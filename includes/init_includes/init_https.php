<?php
/** 
 * Do automatic redirect of http URIs to https, if https is given in HTTP_SERVER in configure.php
 *
 */

if ((IS_ADMIN_FLAG || HTTPS_SERVER == HTTP_SERVER) && (strpos(HTTP_SERVER, 'https:') !== false)) {
        if ($request_type !== 'SSL') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }
}
