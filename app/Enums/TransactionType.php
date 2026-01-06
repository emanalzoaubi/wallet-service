<?php

namespace App\Enums;

enum TransactionType: string {
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case TRANSFER_DEBIT = 'transfer_debit';
    case TRANSFER_CREDIT = 'transfer_credit';
}
