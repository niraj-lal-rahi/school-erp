<?php

namespace App\Enums\Finance;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Upi = 'upi';
    case Card = 'card';
    case OnlineGateway = 'online_gateway';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
