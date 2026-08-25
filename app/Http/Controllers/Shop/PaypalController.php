<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    public function process(Request $request)
    {
        $amount = (float) $request->input('amount', 5);

        if ($amount <= 0) {
            return redirect()->route('shop.index')->with('error', 'Invalid amount specified.');
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.successful-transaction'),
                "cancel_url" => route('paypal.cancelled-transaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => number_format($amount, 2, '.', '')
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->route('shop.index')->with('error', 'Something went wrong with PayPal transaction.');
    }

    public function successful(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0;

            if ($amount > 0 && auth()->check()) {
                DB::table('users')->where('id', auth()->id())->increment('website_balance', $amount);
            }

            return redirect()->route('shop.index')->with('success', 'Transaction complete! Balance added.');
        }

        return redirect()->route('shop.index')->with('error', 'Payment failed or was cancelled.');
    }

    public function cancelled()
    {
        return redirect()->route('shop.index')->with('error', 'Payment cancelled.');
    }
}