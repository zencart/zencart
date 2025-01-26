<?php
/**
 * URL functions
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Leonard 2024 Oct 02 Modified in v2.1.0 $
 */


/**
 * Redirect to another page or site
 * @param $url
 * @param int $httpResponseCode
 */
function zen_redirect($url, $httpResponseCode = null)
{
    // @TODO - rework admin so this exclusion isn't necessary
    if (IS_ADMIN_FLAG !== true) {
        $url = zen_get_site_url_for_request($url);
    }

    $url = zen_cleanup_url_params($url, $for_redirect = true);

    zen_set_redirect_http_headers($url, $httpResponseCode);

    exit();
}

/**
 * Normalize URL ampersand parameters to prevent duplicates and re-encodings
 * @param string $url
 * @param bool $for_redirect
 * @return string
 */
function zen_cleanup_url_params($url, $for_redirect = false)
{
    // clean up URL before executing it
    $url = preg_replace('/&{2,}/', '&', $url);
    $url = preg_replace('/(&amp;)+/', '&amp;', $url);

    if ($for_redirect) {
        // header Location URLs should not have the &amp; in the address (it breaks things)
        $url = preg_replace('/(&amp;)+/', '&', $url);
    }

    return $url;
}

/**
 * Close session and set headers for page-redirect
 *
 * @param string $url
 * @param int $httpResponseCode
 */
function zen_set_redirect_http_headers($url, $httpResponseCode = null)
{
    session_write_close();
    if (empty($httpResponseCode)) {
        header('Location: ' . $url);
    } else {
        header('Location: ' . $url, true, (int)$httpResponseCode);
    }
}

/**
 * Get appropriate HTTPS_SERVER vs HTTP_SERVER and subdirs based on $request_type of current page
 * Typically used within zen_redirect function
 *
 * @TODO - rework catalog and admin so this can be simplified ... perhaps offering https only?
 *
 * @param string $url
 * @return string
 */
function zen_get_site_url_for_request($url)
{
    global $request_type;
    // Are we loading an SSL page?
    if ((ENABLE_SSL == 'true') && ($request_type == 'SSL')) {
        // yes, but a NONSSL url was supplied
        if (substr($url, 0, strlen(HTTP_SERVER . DIR_WS_CATALOG)) == HTTP_SERVER . DIR_WS_CATALOG) {
            // So, change it to SSL, based on site's configuration for SSL
            $url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . substr($url, strlen(HTTP_SERVER . DIR_WS_CATALOG));
        }
    }

    return $url;
}


function zen_get_top_level_domain(string $url) {
    if (strpos($url, '://')) {
        $url = parse_url($url);
        $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = count($domain_array);
    if ($domain_size > 1) {
        if (SESSION_USE_FQDN == 'True') return $url;
        if (is_numeric($domain_array[$domain_size-2]) && is_numeric($domain_array[$domain_size-1])) {
            return false;
        }

        $tld = "";
        foreach ($domain_array as $dPart)
        {
            if ($dPart != "www") $tld = $tld . "." . $dPart;
        }
        return substr($tld, 1);
    }

    return false;
}


/**
  * Generate A HREF link for an HTML-based "Back" button, determined from user's session browsing history
  */
function zen_back_link(bool $link_only = false, string $parameters = ''): string
{
    if (count($_SESSION['navigation']->path) - 2 >= 0) {
        $back = count($_SESSION['navigation']->path) - 2;
        $link = zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']);
    } else {
        if (isset($_SERVER['HTTP_REFERER']) && preg_match("~^" . HTTP_SERVER . "~i", $_SERVER['HTTP_REFERER'])) {
            //if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], str_replace(array('http://', 'https://'), '', HTTP_SERVER) ) ) {
            $link = $_SERVER['HTTP_REFERER'];
        } else {
            $link = zen_href_link(FILENAME_DEFAULT);
        }
        $_SESSION['navigation'] = new navigationHistory;
    }

    if ($link_only) {
        return $link;
    } else {
        return '<a href="' . $link . '"' . $parameters . '>';
    }
}
