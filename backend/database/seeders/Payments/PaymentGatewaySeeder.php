<?php

namespace Database\Seeders\Payments;

use App\Models\Payments\PaymentGateway;
use App\Models\School;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $razorpay = PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'razorpay-test',
            ],
            [
                'name' => 'Razorpay Test',
                'provider' => 'razorpay',
                'mode' => 'test',
                'config' => [
                    'merchant_name' => 'Greenwood School',
                    'upi_vpa' => 'fees.greenwood@oksbi',
                ],
                'supports_upi' => true,
                'supports_card' => true,
                'supports_netbanking' => true,
                'supports_wallet' => true,
                'status' => 'active',
            ]
        );

        $razorpay->credentials()->updateOrCreate(
            ['key_name' => 'key_id'],
            [
                'school_id' => $school->id,
                'key_value' => 'rzp_test_key_id',
                'is_encrypted' => true,
            ]
        );
        $razorpay->credentials()->updateOrCreate(
            ['key_name' => 'key_secret'],
            [
                'school_id' => $school->id,
                'key_value' => 'rzp_test_secret',
                'is_encrypted' => true,
            ]
        );

        $stripe = PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'stripe-test',
            ],
            [
                'name' => 'Stripe Test',
                'provider' => 'stripe',
                'mode' => 'test',
                'config' => [
                    'merchant_name' => 'Greenwood School',
                ],
                'supports_upi' => false,
                'supports_card' => true,
                'supports_netbanking' => false,
                'supports_wallet' => true,
                'status' => 'active',
            ]
        );

        $stripe->credentials()->updateOrCreate(
            ['key_name' => 'publishable_key'],
            [
                'school_id' => $school->id,
                'key_value' => 'pk_test_greenwood',
                'is_encrypted' => true,
            ]
        );
        $stripe->credentials()->updateOrCreate(
            ['key_name' => 'secret_key'],
            [
                'school_id' => $school->id,
                'key_value' => 'sk_test_greenwood',
                'is_encrypted' => true,
            ]
        );

        PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'manual-upi',
            ],
            [
                'name' => 'Manual UPI',
                'provider' => 'upi_manual',
                'mode' => 'live',
                'config' => [
                    'upi_vpa' => 'fees.greenwood@oksbi',
                    'payee_name' => 'Greenwood School',
                ],
                'supports_upi' => true,
                'supports_card' => false,
                'supports_netbanking' => false,
                'supports_wallet' => false,
                'status' => 'active',
            ]
        );

        PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'offline-payment',
            ],
            [
                'name' => 'Offline Payment',
                'provider' => 'offline',
                'mode' => 'live',
                'config' => [
                    'allow_methods' => ['cash', 'bank_transfer', 'cheque'],
                ],
                'supports_upi' => false,
                'supports_card' => false,
                'supports_netbanking' => false,
                'supports_wallet' => false,
                'status' => 'active',
            ]
        );
    }
}
