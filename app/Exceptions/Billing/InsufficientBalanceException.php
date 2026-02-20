<?php

namespace App\Exceptions\Billing;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $required,
        public readonly string $available,
    ) {
        parent::__construct(
            "Insufficient balance for account {$accountId}: required={$required}, available={$available}"
        );
    }
}
