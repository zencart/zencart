<?php
/**
 * Redirect stub for the search page
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */

// NOTE: For search page preparation, see the search page module.
// This file is just a redirect for backward compatibility.
//
// If you are looking at this file in order to merge an older plugin,
// consider making those changes in the "search" directory, not "advanced_search".

header('HTTP/1.1 301 Moved Permanently');
zen_redirect(zen_href_link(FILENAME_SEARCH, zen_get_all_get_params(), 'SSL', true, false));
