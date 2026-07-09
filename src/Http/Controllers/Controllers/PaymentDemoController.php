<?php

namespace Sagartimilsina\NepalPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationRequest;
use Sagartimilsina\NepalPayment\Enums\Gateway;
use Sagartimilsina\NepalPayment\Http\Requests\StartPaymentRequest;
use Sagartimilsina\NepalPayment\NepalPaymentManager;
use Throwable;

/**
 * Bundled demo. Deliberately thin -- validation lives in
 * StartPaymentRequest, all gateway logic lives in the Gateway classes.
 * This class only wires the pieces together and shapes the HTTP
 * responses. Treat it as a reference implementation to copy into your
 * own app's controllers, not as production code to depend on directly.
 */
class PaymentDemoController extends Controller
{
    public function __construct(private readonly NepalPaymentManager $manager)
    {
    }

    public function index()
    {
        return view('nepal-payment::demo');
    }

    public function pay(StartPaymentRequest $request)
    {
        $orderId = (string) Str::uuid();

        $initiationRequest = new PaymentInitiationRequest(
            amount: $request->amount(),
            orderId: $orderId,
            orderName: $request->orderName(),
            successUrl: route('nepal-payment.demo.callback', ['gateway' => $request->gateway()]),
            failureUrl: route('nepal-payment.demo.index', ['error' => 'payment_failed']),
        );

        try {
            $result = $this->manager->driver($request->gateway())->initiate($initiationRequest);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        // Stashed so the callback route can verify the payment without
        // needing a database in this demo. A real app would persist
        // this on the order record instead of the session.
        session([
            'nepal_payment_pending' => [
                'gateway' => $request->gateway(),
                'order_id' => $orderId,
                'amount' => $initiationRequest->totalAmount(),
            ],
        ]);

        if ($result->requiresFormPost()) {
            // eSewa: render a self-submitting form pointed at eSewa's endpoint.
            return view('nepal-payment::redirect-form', [
                'formAction' => $result->formAction,
                'formFields' => $result->formFields,
            ]);
        }

        // Khalti: we already have a ready-made URL, just send the browser there.
        return redirect()->away($result->redirectUrl);
    }

    public function callback(Request $request, string $gateway)
    {
        $pending = session('nepal_payment_pending');

        if (! is_array($pending) || $pending['gateway'] !== $gateway) {
            return redirect()
                ->route('nepal-payment.demo.index')
                ->with('error', 'No matching pending payment found for this callback.');
        }

        $reference = $gateway === Gateway::ESEWA->value
            ? $pending['order_id']
            : (string) $request->query('pidx');

        try {
            $result = $this->manager->driver($gateway)->verify($reference, [
                'amount' => $pending['amount'],
            ]);
        } catch (Throwable $e) {
            return redirect()
                ->route('nepal-payment.demo.index')
                ->with('error', $e->getMessage());
        }

        session()->forget('nepal_payment_pending');

        return redirect()
            ->route('nepal-payment.demo.index')
            ->with('result', $result);
    }
}
