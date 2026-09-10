<?php

return [
    'domain' => env('PARTNER_DOMAIN'),
    'enabled' => false,
    'registration_enabled' => false,
    'payouts_enabled' => false,
    'active_countries' => ['TG'],
    'country_catalog' => [
        'TG' => ['name' => 'Togo', 'dial_code' => '+228', 'phone_min_length' => 8, 'phone_max_length' => 8],
        'BJ' => ['name' => 'Bénin', 'dial_code' => '+229', 'phone_min_length' => 8, 'phone_max_length' => 8],
        'BF' => ['name' => 'Burkina Faso', 'dial_code' => '+226', 'phone_min_length' => 8, 'phone_max_length' => 8],
        'CI' => ['name' => 'Côte d’Ivoire', 'dial_code' => '+225', 'phone_min_length' => 10, 'phone_max_length' => 10],
        'CM' => ['name' => 'Cameroun', 'dial_code' => '+237', 'phone_min_length' => 9, 'phone_max_length' => 9],
        'GA' => ['name' => 'Gabon', 'dial_code' => '+241', 'phone_min_length' => 7, 'phone_max_length' => 8],
        'GN' => ['name' => 'Guinée', 'dial_code' => '+224', 'phone_min_length' => 9, 'phone_max_length' => 9],
        'ML' => ['name' => 'Mali', 'dial_code' => '+223', 'phone_min_length' => 8, 'phone_max_length' => 8],
        'NE' => ['name' => 'Niger', 'dial_code' => '+227', 'phone_min_length' => 8, 'phone_max_length' => 8],
        'SN' => ['name' => 'Sénégal', 'dial_code' => '+221', 'phone_min_length' => 9, 'phone_max_length' => 9],
    ],
    'payout_gateways' => [
        'TG' => ['MOOV-MONEY-TG'],
    ],
    'payout_gateway_catalog' => [
        'TG' => [
            'MOOV-MONEY-TG' => ['label' => 'Moov Money (Flooz)', 'prefixes' => ['76', '77', '78', '79', '96', '97', '98', '99']],
            'MIXX-YAS-TG' => ['label' => 'Mixx by Yas (TMoney)', 'prefixes' => ['70', '71', '72', '73', '90', '91', '92', '93']],
        ],
    ],
    'first_discount_bps' => 1000,
    'commission_hold_days' => 7,
    'payout_min_xof' => 5000,
    'payout_min_qualified_clients' => 3,
    'code_change_cooldown_days' => 30,
    'reserved_codes' => [
        'ADMIN', 'ADMINS', 'API', 'CONTACT', 'DISCOUNT', 'HELP', 'MAXANOU', 'PARTENAIRE',
        'PARTNERS', 'PROMO', 'SUPPORT', 'TEST', 'WWW', 'BRONZE', 'SILVER', 'GOLD',
        'PREMIUM', 'STARTER', 'PRO', 'ENTERPRISE',
    ],
];
