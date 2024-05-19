<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Apr 07 Modified in v2.0.1 $
 */

use TypeError;

class MeasurementUnits
{
    /**
     * Convert weight from one unit to another
     */
    public static function convertWeight(float|int $incoming, string $from_unit, string $to_unit, ?int $precision = null): float|int
    {
        if (!in_array($from_unit, ['lbs', 'kgs', 'oz', 'g'])) {
            throw new TypeError('Invalid weight unit for $from_unit: ' . $from_unit);
        }
        if (!in_array($to_unit, ['lbs', 'kgs', 'oz', 'g'])) {
            throw new TypeError('Invalid weight unit for $to_unit: ' . $to_unit);
        }

        $ratio = [];
        $ratio['lbs']['oz'] = 16;
        $ratio['lbs']['kgs'] = 0.45359237;
        $ratio['lbs']['g'] = 453.59237;
        $ratio['oz']['lbs'] = 0.0625;
        $ratio['oz']['kgs'] = 0.02834952; //0.0625 * 0.45359237;
        $ratio['oz']['g'] = 28.34952; //0.0625 * 0.45359237 * 1000;
        $ratio['kgs']['oz'] = 35.2739619; //2.20462262 * 16;
        $ratio['kgs']['lbs'] = 2.20462262;
        $ratio['kgs']['g'] = 1000;
        $ratio['g']['oz'] = 0.0352739619; //0.001 * 2.20462262 * 16;
        $ratio['g']['kgs'] = 0.001;
        $ratio['g']['lbs'] = .00220462262;

        if ($from_unit !== $to_unit) {
            $result = $incoming * $ratio[$from_unit][$to_unit];
        } else {
            // if converting "to" same as "from", then use original value and carry on to rounding
            $result = $incoming;
        }

        if (($precision === null) && in_array($to_unit, ['lbs', 'kgs'])) {
            $precision = 2;
        }
        if ($precision !== null) {
            return round($result, $precision);
        }
        return $result;
    }

    /**
     * Convert lengths from one unit to another
     */
    public static function convertLength(float|int $incoming, string $from_unit, string $to_unit, ?int $precision = null): float
    {
        if (!in_array($from_unit, ['in', 'cm'])) {
            throw new TypeError('Invalid length unit for $from_unit: ' . $from_unit);
        }
        if (!in_array($to_unit, ['in', 'cm'])) {
            throw new TypeError('Invalid length unit for $to_unit: ' . $to_unit);
        }

        $ratio = [];
        $ratio['in']['cm'] = 2.54;
        $ratio['cm']['in'] = 0.393700787;

        if ($from_unit !== $to_unit) {
            $result = $incoming * $ratio[$from_unit][$to_unit];
        } else {
            // if converting "to" same as "from", then use original value and carry on to rounding
            $result = $incoming;
        }

        if ($precision !== null) {
            return round($result, $precision);
        }
        return $result;
    }
}
