<?php

namespace VoxDev\Core\Helpers;

class NumberFormatHelper
{
    /**
     * Format a number into a abbreviated version with K, M, B, T suffixes
     *
     * @param  float|int  $number  The number to format
     * @param  int  $precision  Number of decimal places to show
     * @param  string  $currency  Currency symbol (e.g., 'Rp ')
     * @param  bool  $separate  Use thousand separator
     * @return string
     */
    public static function formatMoney($number, $precision = 1, $currency = 'Rp ', $separate = true)
    {
        // Handle zero or empty values
        if ($number == 0 || $number == '') {
            return $currency . '0';
        }

        // Make sure the number is positive for calculations (we'll add the minus sign later if needed)
        $sign = ($number < 0) ? '-' : '';
        $number = abs($number);

        // Format based on size
        if ($number < 1000) {
            // Numbers less than 1,000
            return $sign . $currency . self::formatNumber($number, 0, $separate);
        } elseif ($number < 1000000) {
            // Numbers between 1,000 and 1,000,000 (K)
            return $sign . $currency . self::formatNumber($number / 1000, $precision, $separate) . 'K';
        } elseif ($number < 1000000000) {
            // Numbers between 1,000,000 and 1,000,000,000 (M)
            return $sign . $currency . self::formatNumber($number / 1000000, $precision, $separate) . 'M';
        } elseif ($number < 1000000000000) {
            // Numbers between 1,000,000,000 and 1,000,000,000,000 (B)
            return $sign . $currency . self::formatNumber($number / 1000000000, $precision, $separate) . 'B';
        } else {
            // Numbers over 1,000,000,000,000 (T)
            return $sign . $currency . self::formatNumber($number / 1000000000000, $precision, $separate) . 'T';
        }
    }

    /**
     * Format a number with specified precision and optionally with thousand separator
     *
     * @param  float|int  $number  The number to format
     * @param  int  $precision  Number of decimal places
     * @param  bool  $separate  Use thousand separator
     * @return string
     */
    private static function formatNumber($number, $precision, $separate)
    {
        // Remove trailing zeros after decimal if precision is greater than 0
        if ($precision > 0) {
            $formatted = rtrim(number_format($number, $precision, ',', $separate ? '.' : ''), '0');

            // Remove trailing comma if all decimal places are zeros
            return rtrim($formatted, ',');
        }

        // Format without decimal places
        return number_format($number, 0, ',', $separate ? '.' : '');
    }
}
