<?php
/**
 * Sidebar navigation config per dashboard area.
 * Returns [ ['label'=>..,'page'=>..,'icon'=>..], ... ].
 */
function dashboard_nav_items(string $area): array
{
    return match ($area) {
        'customer' => [
            ['label' => 'Dashboard', 'page' => 'dashboard'],
            ['label' => 'Business Profile & Verification', 'page' => 'onboarding'],
            ['label' => 'Payment History', 'page' => 'transactions'],
            ['label' => 'Profile', 'page' => 'profile'],
        ],
        'partner' => [
            ['label' => 'Dashboard', 'page' => 'dashboard'],
            ['label' => 'Onboarding Status', 'page' => 'onboarding'],
            ['group' => 'Customer Management', 'label' => 'Customers', 'page' => 'customers'],
            ['group' => 'Customer Management', 'label' => 'Enroll Customer', 'page' => 'enroll-customer'],
            ['group' => 'Solutions', 'label' => 'Solution Catalog', 'page' => 'products'],
            ['group' => 'Solutions', 'label' => 'Payment Links', 'page' => 'payment-links'],
            ['group' => 'Finance', 'label' => 'Transactions', 'page' => 'transactions'],
            ['group' => 'Finance', 'label' => 'Settlements', 'page' => 'settlements'],
            ['group' => 'Finance', 'label' => 'Commissions', 'page' => 'commissions'],
            ['group' => 'Growth', 'label' => 'Performance', 'page' => 'performance'],
            ['group' => 'Growth', 'label' => 'Proposals', 'page' => 'proposals'],
            ['group' => 'Growth', 'label' => 'Marketing Hub', 'page' => 'marketing'],
            ['group' => 'Support', 'label' => 'Support Tickets', 'page' => 'support'],
            ['group' => 'Support', 'label' => 'Knowledge Center', 'page' => 'resources'],
            ['group' => 'Account', 'label' => 'Team', 'page' => 'team'],
            ['group' => 'Account', 'label' => 'Profile', 'page' => 'profile'],
        ],
        'employee' => [
            ['label' => 'Dashboard', 'page' => 'dashboard'],
            ['label' => 'My Tasks', 'page' => 'tasks'],
            ['label' => 'Profile & Security', 'page' => 'profile'],
        ],
        'hrms' => [
            ['label' => 'Dashboard', 'page' => 'dashboard'],
            ['label' => 'Employees', 'page' => 'employees'],
            ['label' => 'Recruitment', 'page' => 'recruitment'],
            ['label' => 'Attendance', 'page' => 'attendance'],
        ],
        // The admin and super-admin areas use the enterprise admin shell; their
        // navigation comes only from includes/admin/registry.php.
        default => [],
    };
}

function dashboard_area_label(string $area): string
{
    return match ($area) {
        'customer' => 'Customer Portal',
        'partner' => 'Partner Hub',
        'employee' => 'Employee Portal',
        'hrms' => 'HRMS',
        'admin' => 'Admin Panel',
        'super-admin' => 'Super Admin',
        default => 'Dashboard',
    };
}
