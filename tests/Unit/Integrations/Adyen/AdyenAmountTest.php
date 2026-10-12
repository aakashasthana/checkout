<?php

declare(strict_types=1);

use Nezasa\Checkout\Integrations\Adyen\Support\AdyenAmount;

it('converts two decimal currencies to minor units', function (): void {
    expect(AdyenAmount::toMinorUnits(100.50, 'EUR'))->toBe(10050)
        ->and(AdyenAmount::toMinorUnits(100.50, 'USD'))->toBe(10050)
        ->and(AdyenAmount::toMinorUnits(1, 'EUR'))->toBe(100)
        ->and(AdyenAmount::toMinorUnits(0, 'EUR'))->toBe(0);
});

it('converts three decimal currencies to minor units', function (): void {
    expect(AdyenAmount::toMinorUnits(100.50, 'JOD'))->toBe(100500)
        ->and(AdyenAmount::toMinorUnits(1, 'KWD'))->toBe(1000)
        ->and(AdyenAmount::toMinorUnits(12.345, 'BHD'))->toBe(12345);
});

it('converts zero decimal currencies to minor units', function (): void {
    expect(AdyenAmount::toMinorUnits(1200, 'JPY'))->toBe(1200);
});

it('rounds rather than truncates', function (): void {
    // (int) (19.99 * 100) is 1998 in float arithmetic: one cent short, every time.
    expect(AdyenAmount::toMinorUnits(19.99, 'EUR'))->toBe(1999)
        ->and(AdyenAmount::toMinorUnits(0.07, 'EUR'))->toBe(7)
        ->and(AdyenAmount::toMinorUnits(1.005, 'EUR'))->toBe(101);
});

it('accepts amounts given as integers or strings', function (): void {
    expect(AdyenAmount::toMinorUnits(100, 'EUR'))->toBe(10000)
        ->and(AdyenAmount::toMinorUnits('100.50', 'EUR'))->toBe(10050);
});

it('ignores currency casing', function (): void {
    expect(AdyenAmount::toMinorUnits(100.50, 'eur'))->toBe(10050);
});

it('rejects an unsupported currency instead of guessing', function (): void {
    AdyenAmount::toMinorUnits(100.50, 'XYZ');
})->throws(InvalidArgumentException::class, 'Unsupported Adyen currency [XYZ]');

it('reports which currencies are supported', function (): void {
    expect(AdyenAmount::supports('EUR'))->toBeTrue()
        ->and(AdyenAmount::supports('usd'))->toBeTrue()
        ->and(AdyenAmount::supports('XYZ'))->toBeFalse();
});

it('converts minor units back to a decimal amount', function (): void {
    expect(AdyenAmount::toDecimal(10050, 'EUR'))->toBe(100.50)
        ->and(AdyenAmount::toDecimal(100500, 'JOD'))->toBe(100.50);
});
