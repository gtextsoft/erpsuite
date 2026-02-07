<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Closed = 'closed';

    /**
     * Get the display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Open => __('Open'),
            self::Accepted => __('Accepted'),
            self::Declined => __('Declined'),
            self::Closed => __('Closed'),
        };
    }

    /**
     * Get badge color class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary',
            self::Open => 'bg-info',
            self::Accepted => 'bg-success',
            self::Declined => 'bg-danger',
            self::Closed => 'bg-dark',
        };
    }
}
