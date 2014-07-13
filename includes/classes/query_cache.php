<?php
/**
 * Temporary cache for sql
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Created by Data-Diggers.com http://www.data-diggers.com/
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Mar 2 16:20:12 2014 -0500 Modified in v1.5.3 $
 *
 */
/**
 * QueryCache
 *
 */
 class QueryCache {

    function QueryCache() {
        $this->queries = array();
    }

    // cache queries if and only if query is 'SELECT' statement
    // returns:
    //	TRUE - if and only if query has been stored in cache
    //	FALSE - otherwise
    function cache($query, $result) {
        if ($this->isSelectStatement($query) === TRUE) $this->queries[$query] = $result;
        else return(FALSE);
        return(TRUE);
    }

    function getFromCache($query) {
        $ret = $this->queries[$query];
        mysqli_data_seek($ret, 0);
        return($ret);
    }

    function inCache($query) {
        return(isset($this->queries[$query]));
    }

    function isSelectStatement($q) {
        if(($q[0] == 's' || $q[0] == 'S')
                && ($q[1] == 'e' || $q[1] == 'E')
                && ($q[2] == 'l' || $q[2] == 'L'))
            return(true);
        return(false);
    }

    function reset($query) {
      if ('ALL' == $query) {
        $this->queries = array();
        return FALSE;
      }
      unset ($this->queries[$query]);
    }
}

