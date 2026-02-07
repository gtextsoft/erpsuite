<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partialy paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    /**
     * Get the display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Sent => __('Sent'),
            self::Unpaid => __('Unpaid'),
            self::PartiallyPaid => __('Partially Paid'),
            self::Paid => __('Paid'),
            self::Cancelled => __('Cancelled'),
        };
    }

    /**
     * Get badge color class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary',
            self::Sent => 'bg-info',
            self::Unpaid => 'bg-danger',
            self::PartiallyPaid => 'bg-warning',
            self::Paid => 'bg-success',
            self::Cancelled => 'bg-dark',
        };
    }
}
