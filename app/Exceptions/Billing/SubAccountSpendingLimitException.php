<?php

namespace App\Exceptions\Billing;

use RuntimeException;

class SubAccountSpendingLimitException extends RuntimeException
{
    public function __construct(
        public readonly string $subAccountId,
        public readonly string $required,
        public readonly string $limit,
    ) {
        parent::__construct(
            "Sub-account {$subAccountId} spending limit exceeded: required={$required}, limit={$limit}"
        );
    }
}
