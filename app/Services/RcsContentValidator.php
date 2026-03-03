<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\RcsAsset;

/**
 * Validates RCS content structure for campaigns and templates.
 *
 * Enforces limits matching the frontend wizard:
 * - Max 10 cards per carousel
 * - Max 4 buttons per card
 * - Button label max 25 chars
 * - Card text body max 2000 chars
 * - Valid button types and action fields
 * - Valid orientations
 * - Asset finalization checks (for send validation)
 */
class RcsContentValidator
{
    const MAX_CARDS_CAROUSEL = 10;
    const MIN_CARDS_CAROUSEL = 2;
    const MAX_BUTTONS_PER_CARD = 4;
    const MAX_BUTTON_LABEL_LENGTH = 25;
    const MAX_TEXT_BODY_LENGTH = 2000;
    const MAX_CALLBACK_DATA_LENGTH = 64;

    const VALID_BUTTON_TYPES = ['url', 'phone', 'calendar', 'postback'];
    const VALID_ORIENTATIONS = ['vertical_short', 'vertical_medium', 'vertical_tall', 'horizontal'];
    const VALID_CAROUSEL_WIDTHS = ['small', 'medium'];

    /**
     * Validate RCS content structure on campaign save.
     * Returns array of error messages (empty = valid).
     */
    public function validateStructure(array $rcsContent, string $campaignType): array
    {
        $errors = [];

        $contentType = $rcsContent['type'] ?? null;

        if ($campaignType === Campaign::TYPE_RCS_SINGLE) {
            if ($contentType !== null && $contentType !== 'single') {
                $errors[] = 'RCS content type must be "single" for single rich card campaigns.';
            }
            $errors = array_merge($errors, $this->validateSingleCard($rcsContent));
        } elseif ($campaignType === Campaign::TYPE_RCS_CAROUSEL) {
            if ($contentType !== null && $contentType !== 'carousel') {
                $errors[] = 'RCS content type must be "carousel" for carousel campaigns.';
            }
            $errors = array_merge($errors, $this->validateCarousel($rcsContent));
        }

        return $errors;
    }

    /**
     * Validate RCS content for send readiness (stricter — checks asset finalization).
     */
    public function validateForSend(array $rcsContent, string $campaignType): array
    {
        $errors = $this->validateStructure($rcsContent, $campaignType);

        $cards = $rcsContent['cards'] ?? [];
        foreach ($cards as $index => $card) {
            $cardNum = $index + 1;
            $errors = array_merge($errors, $this->validateCardAssetFinalized($card, $cardNum));
        }

        return $errors;
    }

    /**
     * Validate a single rich card structure.
     */
    private function validateSingleCard(array $rcsContent): array
    {
        $errors = [];
        $cards = $rcsContent['cards'] ?? [];

        if (empty($cards)) {
            $errors[] = 'RCS single card requires exactly one card.';
            return $errors;
        }

        if (count($cards) !== 1) {
            $errors[] = 'RCS single card must have exactly one card, got ' . count($cards) . '.';
        }

        foreach ($cards as $index => $card) {
            $errors = array_merge($errors, $this->validateCard($card, $index + 1));
        }

        return $errors;
    }

    /**
     * Validate carousel structure with card count constraints.
     */
    private function validateCarousel(array $rcsContent): array
    {
        $errors = [];
        $cards = $rcsContent['cards'] ?? [];

        $cardCount = count($cards);
        if ($cardCount < self::MIN_CARDS_CAROUSEL) {
            $errors[] = 'Carousel requires at least ' . self::MIN_CARDS_CAROUSEL . ' cards.';
        }
        if ($cardCount > self::MAX_CARDS_CAROUSEL) {
            $errors[] = 'Carousel cannot exceed ' . self::MAX_CARDS_CAROUSEL . ' cards. Got ' . $cardCount . '.';
        }

        foreach ($cards as $index => $card) {
            $errors = array_merge($errors, $this->validateCard($card, $index + 1));
        }

        // All carousel cards must share the same orientation
        $orientations = [];
        foreach ($cards as $card) {
            $orientation = $card['media']['orientation'] ?? null;
            if ($orientation) {
                $orientations[] = $orientation;
            }
        }
        $uniqueOrientations = array_unique($orientations);
        if (count($uniqueOrientations) > 1) {
            $errors[] = 'All carousel cards must use the same image orientation. Found: ' . implode(', ', $uniqueOrientations) . '.';
        }

        return $errors;
    }

    /**
     * Validate a single card's content.
     */
    private function validateCard(array $card, int $cardNum): array
    {
        $errors = [];

        // Text body length
        $textBody = $card['textBody'] ?? '';
        if (mb_strlen($textBody) > self::MAX_TEXT_BODY_LENGTH) {
            $errors[] = "Card {$cardNum}: Text body exceeds " . self::MAX_TEXT_BODY_LENGTH . " character limit.";
        }

        // Media validation
        $media = $card['media'] ?? null;
        if ($media) {
            $orientation = $media['orientation'] ?? null;
            if ($orientation && !in_array($orientation, self::VALID_ORIENTATIONS)) {
                $errors[] = "Card {$cardNum}: Invalid orientation '{$orientation}'.";
            }
        }

        // Button validation
        $buttons = $card['buttons'] ?? [];
        if (count($buttons) > self::MAX_BUTTONS_PER_CARD) {
            $errors[] = "Card {$cardNum}: Maximum " . self::MAX_BUTTONS_PER_CARD . " buttons allowed per card.";
        }

        foreach ($buttons as $btnIndex => $button) {
            $btnNum = $btnIndex + 1;
            $errors = array_merge($errors, $this->validateButton($button, $cardNum, $btnNum));
        }

        return $errors;
    }

    /**
     * Validate a button's structure.
     */
    private function validateButton(array $button, int $cardNum, int $btnNum): array
    {
        $errors = [];

        // Label required and length check
        $label = $button['label'] ?? '';
        if (empty($label)) {
            $errors[] = "Card {$cardNum}, Button {$btnNum}: Label is required.";
        } elseif (mb_strlen($label) > self::MAX_BUTTON_LABEL_LENGTH) {
            $errors[] = "Card {$cardNum}, Button {$btnNum}: Label exceeds " . self::MAX_BUTTON_LABEL_LENGTH . " character limit.";
        }

        // Type validation
        $type = $button['type'] ?? null;
        if (!$type || !in_array($type, self::VALID_BUTTON_TYPES)) {
            $errors[] = "Card {$cardNum}, Button {$btnNum}: Invalid button type '{$type}'.";
            return $errors;
        }

        // Action field validation per type
        $action = $button['action'] ?? [];
        switch ($type) {
            case 'url':
                if (empty($action['url'])) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: URL is required for URL buttons.";
                }
                break;
            case 'phone':
                if (empty($action['phoneNumber'])) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: Phone number is required for phone buttons.";
                }
                break;
            case 'calendar':
                if (empty($action['title'])) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: Event title is required for calendar buttons.";
                }
                if (empty($action['startTime'])) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: Start time is required for calendar buttons.";
                }
                if (empty($action['endTime'])) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: End time is required for calendar buttons.";
                }
                break;
            case 'postback':
                // Postback callback_data is optional but has length limit
                $callbackData = $button['tracking']['callback_data'] ?? '';
                if (mb_strlen($callbackData) > self::MAX_CALLBACK_DATA_LENGTH) {
                    $errors[] = "Card {$cardNum}, Button {$btnNum}: Callback data exceeds " . self::MAX_CALLBACK_DATA_LENGTH . " character limit.";
                }
                break;
        }

        return $errors;
    }

    /**
     * Validate that a card's media asset has been finalized (not still a draft).
     */
    private function validateCardAssetFinalized(array $card, int $cardNum): array
    {
        $errors = [];
        $media = $card['media'] ?? null;

        if (!$media) {
            return $errors;
        }

        $assetUuid = $media['assetUuid'] ?? null;
        if (!$assetUuid) {
            return $errors;
        }

        $asset = RcsAsset::withoutGlobalScope('tenant')
            ->where('uuid', $assetUuid)
            ->first();

        if (!$asset) {
            $errors[] = "Card {$cardNum}: Media asset not found (UUID: {$assetUuid}).";
        } elseif ($asset->is_draft) {
            $errors[] = "Card {$cardNum}: Media asset has not been finalized.";
        }

        return $errors;
    }
}
