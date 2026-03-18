<?php

namespace App\Services;

/**
 * Value object for anti-flood check results.
 */
class AntiFloodResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly bool $isDuplicate,
        public readonly bool $monitored,
        public readonly ?string $reason = null,
    ) {}

    public static function allowed(): self
    {
        return new self(allowed: true, isDuplicate: false, monitored: false);
    }

    public static function blocked(string $reason): self
    {
        return new self(allowed: false, isDuplicate: true, monitored: false, reason: $reason);
    }

    public static function monitored(): self
    {
        return new self(allowed: true, isDuplicate: true, monitored: true);
    }
}
