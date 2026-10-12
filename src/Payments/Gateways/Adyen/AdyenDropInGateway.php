<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Payments\Gateways\Adyen;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\CreatePaymentTransactionPayload as NezasaPayload;
use Nezasa\Checkout\Payments\Contracts\WidgetPaymentContract;
use Nezasa\Checkout\Payments\Dtos\AbortResult;
use Nezasa\Checkout\Payments\Dtos\AuthorizationResult;
use Nezasa\Checkout\Payments\Dtos\CaptureResult;
use Nezasa\Checkout\Payments\Dtos\PaymentAsset;
use Nezasa\Checkout\Payments\Dtos\PaymentInit;
use Nezasa\Checkout\Payments\Dtos\PaymentPrepareData;
use RuntimeException;

/**
 * Adyen payment gateway using the Sessions flow with Web Drop-in.
 *
 * Lifecycle, driven entirely by the checkout:
 *
 *   prepare()    creates an Adyen session with manual capture, so the funds are
 *                reserved rather than taken
 *   getAssets()  renders Adyen Drop-in on the payment page
 *   authorize()  confirms the outcome with Adyen server side, before any booking
 *   capture()    takes the money, only after Nezasa confirms the booking
 *   abort()      releases the authorisation when the booking fails
 *
 * Phase 1 has no webhooks, so the payment outcome is read from the session
 * result rather than received asynchronously. That requires Checkout API v70+.
 */
class AdyenDropInGateway implements WidgetPaymentContract
{
    private const string CONFIG_KEY = 'checkout.integrations.adyen';

    public static function isActive(): bool
    {
        return (bool) Config::get(self::CONFIG_KEY.'.active', false);
    }

    /**
     * Must stay unique across gateways: the checkout identifies the gateway by name
     * and Nezasa receives it as the payment method name.
     */
    public static function name(): string
    {
        return (string) Config::get(self::CONFIG_KEY.'.name', 'Adyen');
    }

    /**
     * False: we capture at Adyen ourselves and a payment transaction is created
     * in Nezasa. Tokenized gateways hand a token to Nezasa instead.
     */
    public static function isTokenized(): bool
    {
        return false;
    }

    public function prepare(PaymentPrepareData $data): PaymentInit
    {
        // Implemented in step 4: create the Adyen session.
        throw new RuntimeException('AdyenDropInGateway::prepare() is not implemented yet.');
    }

    public function getAssets(PaymentInit $paymentInit): PaymentAsset
    {
        // Implemented in step 5: load Adyen Web and mount Drop-in.
        throw new RuntimeException('AdyenDropInGateway::getAssets() is not implemented yet.');
    }

    /**
     * @param  array<string, mixed>  $persistentData
     */
    public function authorize(Request $request, array $persistentData): AuthorizationResult
    {
        // Implemented in step 6: verify the session result with Adyen.
        throw new RuntimeException('AdyenDropInGateway::authorize() is not implemented yet.');
    }

    /**
     * @param  array<string, mixed>  $persistentData
     * @param  array<string, mixed>  $resultData
     */
    public function capture(Request $request, array $persistentData, array $resultData): CaptureResult
    {
        // Implemented in step 7: capture the full authorised amount.
        throw new RuntimeException('AdyenDropInGateway::capture() is not implemented yet.');
    }

    /**
     * @param  array<string, mixed>  $persistentData
     * @param  array<string, mixed>  $resultData
     */
    public function abort(Request $request, array $persistentData, array $resultData): AbortResult
    {
        // Implemented in step 7: cancel the authorisation.
        throw new RuntimeException('AdyenDropInGateway::abort() is not implemented yet.');
    }

    public function makeNezasaTransactionPayload(Request $request, CaptureResult $captureResult): NezasaPayload
    {
        // Implemented in step 8: hand the Adyen reference back to the checkout.
        throw new RuntimeException('AdyenDropInGateway::makeNezasaTransactionPayload() is not implemented yet.');
    }

    /**
     * Resolve the merchant account for a currency.
     *
     * Royal Jordanian settles EUR and USD into separate accounts. The account
     * that authorises a payment must also capture or cancel it, so the resolved
     * value is stored with the transaction rather than looked up a second time.
     */
    public static function merchantAccountFor(string $currency): ?string
    {
        /** @var array<string, string> $accounts */
        $accounts = (array) Config::get(self::CONFIG_KEY.'.merchant_accounts', []);

        $account = $accounts[strtoupper($currency)] ?? null;

        return is_string($account) && $account !== '' && $account !== 'must_be_set_in_env'
            ? $account
            : null;
    }
}
