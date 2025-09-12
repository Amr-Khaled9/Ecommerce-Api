<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\Payment;
use Stripe\PaymentIntent;
use App\Enum\PaymentStatus;
use Illuminate\Http\Request;
use App\Enum\PaymentProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));   // هتعملو في config
    }

    public function createPayment(Request $request, Order $order)
    {
        $request->validate([
            'provider' => 'required|string|in:' . implode(',', PaymentProvider::values()), // dynamic
        ]);

        // Check User 
        if ($request->user()->id !== $order->user_id) {
            return response()->json([
                'message' => 'Unauthorized. This payment does not belong to you.'
            ], 403);
        }

        if (!$order->canAcceptPayment()) {
            return response()->json(['message' => 'This order cannot be paid at this time'], 400);
        }
        // check correct payment provider
        if (PaymentProvider::from($request->input('provider')) === PaymentProvider::STRIPE) {
            return $this->createStripePayment($order);
        } else {
            return response()->json([
                'message' => 'Payment provider not implemented yet.'
            ], 501);
        }
    }

    protected function createStripePayment(Order $order)
    {
        try {

            // create Payment 
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => PaymentProvider::STRIPE,
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => PaymentStatus::PENDING,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'created_at' => now()->toIso8601ZuluString(),
                ],
            ]);

            // create a payment intent 
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($order->total * 100),
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->order_id,
                    'payment_id' => $payment->id,
                ],
                'description' => 'Payment for Order #' . $order->order_number,
            ]);

            $payment->update([
                'payment_intent_id' => $paymentIntent->id,
                'metadata' => array_merge($order->metadata, [
                    'client_secret' => $paymentIntent->client_secret
                ])
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
                'publishable_key' => config('services.stripe.key'),
            ]);
        } catch (ApiErrorException  $e) {
            Log::error('stripe payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function confirmPayment(Request $request, $paymentId)
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found.'
            ], 404);
        }

        // check if the payment belongs to the user
        if ($payment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. This payment does not belong to you.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully.',
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }

    //  Stripe payments WebHooks
 public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook.secret');
        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return $this->handleSuccessfulPayment($event->data->object);
                case 'payment_intent.payment_failed':
                    return $this->handleFailedPayment($event->data->object);
                default:
                    Log::warning('Unhandled Stripe event type: ' . $event->type);
                    return response()->json(['status' => 'ignored']);
            }
        } catch (UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }

    protected function handleSuccessfulPayment($paymentIntent)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        if (!$payment) {
            Log::error('Payment not found for payment intent: ' . $paymentIntent->id);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }
        if (!$payment->isFinal()) {
            $payment->markAsCompleted($paymentIntent->id, [
                'strpe_data' => [
                    'amount' => $paymentIntent->amount / 100, // Convert cents to dollars
                    'currency' => $paymentIntent->currency,
                    'status' => $paymentIntent->status,
                    'description' => $paymentIntent->description,
                    'completed_at' => now()->toIso8601String(),
                ]
            ]);
        }


        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.',
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }
    protected function handleFailedPayment($paymentIntent)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        if (!$payment) {
            Log::error('Payment not found for payment intent: ' . $paymentIntent->id);
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }
        if (!$payment->isFinal()) {
            $payment->markAsFailed([
                'stripe_data' => [
                    'error' => $paymentIntent->last_payment_error ? $paymentIntent->last_payment_error->message : 'Unknown error',
                    'status' => $paymentIntent->status,
                    'failed_at' => now()->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment failed.',
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }
}
