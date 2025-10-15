<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->data() as $data) {
            \App\Models\Tenants\PaymentMethod::create($data);
        }
    }

    private function data(): array
    {
        return [
            [
                'name' => __('Cash'),
                'is_cash' => true,
                'is_debit' => false,
                'is_credit' => false,
                'is_wallet' => false,
                'icon' => 'assets/images/payment-methods/cash.png',
            ],
            [
                'name' => __('Transfer'),
                'is_cash' => false,
                'is_debit' => true,
                'is_credit' => false,
                'is_wallet' => false,
                'icon' => 'assets/images/payment-methods/cash.png',
            ],
            [
                'name' => 'Utang',
                'is_cash' => false,
                'is_debit' => false,
                'is_credit' => true,
                'is_wallet' => false,
                'icon' => 'assets/images/payment-methods/cash.png',
            ],
        ];
    }
}
