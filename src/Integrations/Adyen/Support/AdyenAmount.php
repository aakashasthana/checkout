<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Adyen\Support;

use InvalidArgumentException;

/**
 * Converts decimal amounts into the minor units Adyen expects.
 *
 * Adyen never accepts decimals: an amount is always a whole number in the
 * smallest unit of the currency. The number of decimals differs per currency,
 * so EUR 100.50 is 10050 while JOD 100.50 is 100500.
 *
 * Do not use Price::toCent() for Adyen: it multiplies by 100 for every
 * currency and casts, which both truncates and breaks three-decimal currencies.
 */
final class AdyenAmount
{
    /**
     * Number of decimals Adyen expects per currency.
     *
     * Phase 1 for Royal Jordanian is EUR and USD. The remaining entries are
     * included so that adding a currency is a configuration decision rather
     * than a code change, and so the three-decimal cases are already correct.
     *
     * @var array<string, int>
     */
    private const array EXPONENTS = [
        'AED' => 2,
        'BHD' => 3,
        'CHF' => 2,
        'EUR' => 2,
        'GBP' => 2,
        'ILS' => 2,
        'IQD' => 3,
        'JOD' => 3,
        'JPY' => 0,
        'KWD' => 3,
        'OMR' => 3,
        'SAR' => 2,
        'USD' => 2,
    ];

    /**
     * Convert a decimal amount to Adyen minor units.
     *
     * @throws InvalidArgumentException when the currency has no known exponent
     */
    public static function toMinorUnits(float|int|string $amount, string $currency): int
    {
        $exponent = self::exponentFor($currency);

        // round() rather than a cast: (int) (19.99 * 100) yields 1998.
        return (int) round(((float) $amount) * (10 ** $exponent));
    }

    /**
     * Convert Adyen minor units back to a decimal amount, for comparisons and display.
     */
    public static function toDecimal(int $minorUnits, string $currency): float
    {
        return $minorUnits / (10 ** self::exponentFor($currency));
    }

    /**
     * Whether we can safely send amounts in this currency to Adyen.
     */
    public static function supports(string $currency): bool
    {
        return array_key_exists(strtoupper($currency), self::EXPONENTS);
    }

    /**
     * @throws InvalidArgumentException when the currency has no known exponent
     */
    private static function exponentFor(string $currency): int
    {
        $currency = strtoupper($currency);

        // Never guess. A silent default of 2 would undercharge a three-decimal
        // currency by a factor of ten, which is not recoverable quietly.
        if (! array_key_exists($currency, self::EXPONENTS)) {
            throw new InvalidArgumentException(
                "Unsupported Adyen currency [{$currency}]: no minor unit exponent configured."
            );
        }

        return self::EXPONENTS[$currency];
    }
}
