<?php

namespace App\Enum;

enum PaymentMethodEnum: string
{
    case Cash = 'cash';
    case Pix = 'pix';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Transfer = 'transfer';
    case Other = 'other';
}
