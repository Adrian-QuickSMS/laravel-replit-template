<?php

namespace App\Contracts;

/**
 * SmsGateway — interface for SMS/RCS message delivery providers.
 *
 * Implementations handle the actual HTTP REST API calls to upstream gateways.
 * Each gateway (supplier) has its own implementation.
 *
 * DeliveryService selects the correct gateway based on routing rules,
 * then calls sendMessage() to dispatch.
 */
interface SmsGateway
{
    /**
     * Get the unique identifier for this gateway.
     */
    public function getGatewayCode(): string;

    /**
     * Send a single message through the gateway.
     *
     * @param GatewayMessage $message The message to send
     * @return GatewayResponse The gateway's response
     * @throws GatewayException If the gateway call fails
     */
    public function sendMessage(GatewayMessage $message): GatewayResponse;

    /**
     * Check the gateway's health/connectivity.
     *
     * @return bool True if gateway is reachable and responding
     */
    public function healthCheck(): bool;
}

/**
 * DTO for an outbound message to be sent via a gateway.
 */
class GatewayMessage
{
    public function __construct(
        /** Recipient phone number in E.164 format */
        public readonly string $to,
        /** Sender ID (alphanumeric or shortcode) or RCS agent ID */
        public readonly string $from,
        /** Message body text (SMS/RCS Basic) */
        public readonly ?string $body,
        /** Message type: sms, rcs_basic, rcs_single */
        public readonly string $type,
        /** RCS rich content (cards, carousels, etc.) - for rcs_single */
        public readonly ?array $rcsContent,
        /** Campaign recipient ID for DLR correlation */
        public readonly string $recipientId,
        /** Campaign ID for DLR correlation */
        public readonly string $campaignId,
        /** Account ID for billing correlation */
        public readonly string $accountId,
        /** Optional metadata passed to gateway */
        public readonly array $metadata = [],
    ) {}
}

/**
 * DTO for a gateway's response to a send request.
 */
class GatewayResponse
{
    public function __construct(
        /** Whether the gateway accepted the message */
        public readonly bool $accepted,
        /** Gateway-assigned message ID for DLR correlation */
        public readonly ?string $messageId,
        /** Gateway-reported status (accepted, rejected, etc.) */
        public readonly string $status,
        /** Error message if rejected */
        public readonly ?string $errorMessage = null,
        /** Error code if rejected */
        public readonly ?string $errorCode = null,
        /** Raw gateway response for debugging */
        public readonly array $rawResponse = [],
    ) {}
}

/**
 * Exception for gateway communication failures.
 */
class GatewayException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $gatewayCode,
        public readonly ?string $errorCode = null,
        public readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
