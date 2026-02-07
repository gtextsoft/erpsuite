<?php

namespace App\Enums;

enum BillingDuration: string
{
    case Month = 'Month';
    case Year = 'Year';
    case Trial = 'Trial';

    /**
     * Get the label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Month => __('Monthly'),
            self::Year => __('Yearly'),
            self::Trial => __('Trial'),
        };
    }

    /**
     * Check if this is a trial period.
     */
    public function isTrial(): bool
    {
        return $this === self::Trial;
    }
}
