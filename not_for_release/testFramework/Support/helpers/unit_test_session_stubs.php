<?php

if (!function_exists('zen_session_name')) {
    function zen_session_name($name = ''): string
    {
        return 'zenid';
    }
}

if (!function_exists('zen_session_id')) {
    function zen_session_id($sessid = ''): string
    {
        return '1234567890';
    }
}
