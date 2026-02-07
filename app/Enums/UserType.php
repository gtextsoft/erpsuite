<?php

namespace App\Enums;

enum UserType: string
{
    case SuperAdmin = 'super admin';
    case Company = 'company';
    case Client = 'client';
    case Vendor = 'vendor';
    case Staff = 'staff';
    case Driver = 'driver';
    case SalesAgent = 'salesagent';
    case Hr = 'hr';
    case Doctor = 'doctor';
    case Patient = 'patient';
    case GymMember = 'gym member';
    case GymTrainer = 'gym trainer';
    case Advocate = 'advocate';
    case CaseInitiator = 'case initiator';
    case ParentUser = 'parent';

    /**
     * Get all user types that cannot be edited.
     */
    public static function nonEditableRoles(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get user types that are not employee types.
     */
    public static function nonEmployeeTypes(): array
    {
        return [
            self::SuperAdmin->value,
            self::Company->value,
            self::Client->value,
            self::Vendor->value,
            self::Driver->value,
            self::SalesAgent->value,
            self::Hr->value,
            self::GymMember->value,
            self::GymTrainer->value,
            self::Advocate->value,
            self::CaseInitiator->value,
            self::ParentUser->value,
        ];
    }

    /**
     * Check if this type is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Check if this type is a company.
     */
    public function isCompany(): bool
    {
        return $this === self::Company;
    }
}
