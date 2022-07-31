<?php
/**
 * Stubbed Function
 *
 * @return bool
 *
 */
function is_product_valid()
{
    return true;
}

/**
 * Stubbed Function
 * @return bool
 */
function is_coupon_valid_for_sales()
{
    return true;
}

/**
 * Stubbed Function
 *
 * @param $value
 * @param $precision
 * @return float
 */
function zen_round($value, $precision)
{
    $value = round($value * pow(10, $precision), 0);
    $value = $value / pow(10, $precision);

    return $value;
}
