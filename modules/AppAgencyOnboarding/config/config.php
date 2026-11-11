<?php

return [
    /*
    | When true, onboarding plan step applies the selected plan immediately (no checkout).
    | Set AGENCY_ONBOARDING_SKIP_PAYMENT=false to send paid tiers through payment again.
    */
    'skip_payment_during_onboarding' => filter_var(
        env('AGENCY_ONBOARDING_SKIP_PAYMENT', true),
        FILTER_VALIDATE_BOOL,
    ),

    /*
    | During setup, all agent tiles are selectable; enabled_agents on the project
    | drives which dashboards appear in the sidebar after launch.
    */
    'offer_all_agents_in_setup' => filter_var(
        env('AGENCY_ONBOARDING_ALL_AGENTS_SETUP', true),
        FILTER_VALIDATE_BOOL,
    ),

    'industry_options' => [
        'ecommerce' => 'E-commerce',
        'saas' => 'SaaS',
        'healthcare' => 'Healthcare',
        'finance' => 'Finance',
        'education' => 'Education',
        'agency' => 'Marketing agency',
        'other' => 'Other',
    ],
    'budget_options' => [
        'under_1k' => 'Under $1k',
        '1k_5k' => '$1k – $5k',
        '5k_15k' => '$5k – $15k',
        '15k_plus' => '$15k+',
    ],
    'brand_tone_options' => [
        'professional' => 'Professional',
        'friendly' => 'Friendly',
        'bold' => 'Bold',
        'luxury' => 'Luxury',
        'playful' => 'Playful',
    ],
];
