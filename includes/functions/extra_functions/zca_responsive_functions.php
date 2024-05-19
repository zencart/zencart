<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @author ZCAdditions.com, ZCA Responsive Template Default
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */

function layoutTypes()
{
    return ['default', 'mobile', 'tablet', 'full'];
}

function initLayoutType()
{
    // Safety check.
    if (!class_exists('MobileDetect')) { return 'default'; }

    $detect = new Detection\MobileDetect;
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
