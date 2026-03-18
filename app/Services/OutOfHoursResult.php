<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Value object for out-of-hours check results.
 */
class OutOfHoursResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly ?string $action,
        public readonly ?Carbon $releaseAfter,
        public readonly ?string $reason,
    ) {}

    public static function allowed(): self
    {
        return new self(allowed: true, action: null, releaseAfter: null, reason: null);
    }

    public static function restricted(string $action, Carbon $releaseAfter, string $reason): self
    {
        return new self(allowed: false, action: $action, releaseAfter: $releaseAfter, reason: $reason);
    }
}
