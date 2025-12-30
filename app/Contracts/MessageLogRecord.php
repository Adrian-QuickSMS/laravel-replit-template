<?php

namespace App\Contracts;

/**
 * Interface defining the structure of a Message Log record.
 * 
 * This interface ensures consistent data structure across the application
 * for message log entries, whether retrieved from database, API, or mock data.
 */
interface MessageLogRecord
{
    /**
     * Get the unique message identifier.
     * Format: MSG-XXXXXXXXX (9-digit padded)
     */
    public function getId(): string;

    /**
     * Get the recipient mobile number.
     * Stored in E.164 format (e.g., +447712345678)
     */
    public function getMobileNumber(): string;

    /**
     * Get the masked mobile number for display.
     * Format: +44 77** ***XXX
     */
    public function getMaskedMobileNumber(): string;

    /**
     * Get the sender ID used for the message.
     * Alphanumeric (max 11 chars) or numeric (max 15 digits)
     */
    public function getSenderId(): string;

    /**
     * Get the message delivery status.
     * Values: delivered, pending, undeliverable, rejected
     */
    public function getStatus(): string;

    /**
     * Get the timestamp when the message was sent.
     */
    public function getSentTime(): ?\DateTimeInterface;

    /**
     * Get the timestamp when the message was delivered.
     * Null if not yet delivered or undeliverable.
     */
    public function getDeliveryTime(): ?\DateTimeInterface;

    /**
     * Get the timestamp when message processing completed.
     * Null if still pending.
     */
    public function getCompletedTime(): ?\DateTimeInterface;

    /**
     * Get the cost of the message in GBP.
     */
    public function getCost(): float;

    /**
     * Get the message type.
     * Values: sms, rcs_basic, rcs_rich
     */
    public function getType(): string;

    /**
     * Get the sub-account name that sent the message.
     */
    public function getSubAccount(): string;

    /**
     * Get the username of the user who sent the message.
     */
    public function getUser(): string;

    /**
     * Get the origin/source of the message.
     * Values: portal, api, email_to_sms, integration
     */
    public function getOrigin(): string;

    /**
     * Get the destination country code (ISO 3166-1 alpha-2).
     */
    public function getCountry(): string;

    /**
     * Get the number of message fragments/parts.
     * SMS: Based on character count and encoding
     * RCS: Always 1
     */
    public function getFragments(): int;

    /**
     * Get the character encoding used.
     * Values: gsm7, unicode
     */
    public function getEncoding(): string;

    /**
     * Get the message content.
     * Returns encrypted content for non-privileged users,
     * plaintext for Super Admin role.
     */
    public function getContent(): string;

    /**
     * Check if the message is billable.
     * False for rejected/failed messages that weren't charged.
     */
    public function isBillable(): bool;
}
