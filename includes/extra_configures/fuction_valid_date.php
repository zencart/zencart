<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Piloujp 2024 Mar 8 Modified in v2.0.0-beta1 $
 */

function validDate(string $date, string $format = DATE_FORMAT)
{
	//Validate date using DATE_FORMAT by default as reference format. Accepts separators / or - or none.
	$format0 = str_replace('-', '/', $format);
	$format1 = str_replace('/', '-', $format);
	$format2 = str_replace(array('/','-'), '', $format);
    $d0 = DateTime::createFromFormat('!' . $format0, $date);
    $d1 = DateTime::createFromFormat('!' . $format1, $date);
    $d2 = DateTime::createFromFormat('!' . $format2, $date);
    return ($d0 && $d0->format($format0) == $date) || ($d1 && $d1->format($format1) == $date) || ($d2 && $d2->format($format2) == $date);
}
