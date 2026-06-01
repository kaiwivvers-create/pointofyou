<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettings extends Model
{
    protected $fillable = [
        'qr_code_image',
        'qr_code_instructions',
        'bank_name',
        'account_number',
        'account_name',
        'bank_address',
        'swift_code',
        'card_instructions',
        'transfer_instructions',
        'cash_instructions',
    ];

    public static function getSettings()
    {
        return self::first() ?? self::createDefault();
    }

    public static function createDefault()
    {
        return self::create([
            'qr_code_instructions' => 'Scan the QR code to pay',
            'bank_name' => 'Your Bank Name',
            'account_number' => '1234567890',
            'account_name' => 'Your Business Name',
            'bank_address' => '123 Bank Street',
            'swift_code' => 'SWIFT123',
            'card_instructions' => 'Insert or tap card',
            'transfer_instructions' => 'Transfer to the account details below',
            'cash_instructions' => 'Accept cash payments',
        ]);
    }
}
