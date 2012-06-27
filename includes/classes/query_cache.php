<?php

/**
 * QueryCache ver 1.9
 * Created by Data-Diggers.com
 * Website:             http://www.data-diggers.com/
 * Copyright notice: This is free software. GNU license.
 * This code is provided as is. We don't take any responsibility for damage
 * done by use of this software.
 * Feel free to change and redistribute this code, but leave reference to our
 * website.
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
        mysql_data_seek($ret, 0);
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

}

?>