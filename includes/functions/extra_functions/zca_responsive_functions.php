<?php
/**
 * @author      Serban Ghita <serbanghita@gmail.com>
 * @license     MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
 *
 * @author ZCAdditions.com, ZCA Responsive Template Default
 */

function layoutTypes()
{
    return array('default', 'mobile', 'tablet', 'full');
}

function initLayoutType()
{
    // Safety check.
    if (!class_exists('Mobile_Detect')) { return 'default'; }

    $detect = new Mobile_Detect;
    $isMobile = $detect->isMobile();
    $isTablet = $detect->isTablet();

    $layoutTypes = layoutTypes();

    if ( isset($_GET['layoutType']) ) {
        $layoutType = $_GET['layoutType'];
    } else {
        if (empty($_SESSION['layoutType'])) {
            $layoutType = ($isMobile ? ($isTablet ? 'tablet' : 'mobile') : 'default');
        } else {
            $layoutType =  $_SESSION['layoutType'];
        }
    }

    if ( !in_array($layoutType, $layoutTypes) ) { 
        $layoutType = 'default'; 
    }

    $_SESSION['layoutType'] = $layoutType;

    return $layoutType;
}

$layoutType = initLayoutType();
