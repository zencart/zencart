<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @author ZCAdditions.com, ZCA Responsive Template Default
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

/**
 * @since ZC v1.5.5
 */
function layoutTypes()
{
    return ['default', 'mobile', 'tablet', 'full'];
}

/**
 * @since ZC v1.5.5
 */
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
