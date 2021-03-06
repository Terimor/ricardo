<?php

namespace App\Constants;

/**
 * Class PaymentProviders
 * @package App\Constants
 */
class PaymentProviders
{
    const PAYPAL        = 'paypal';
    const EBANX         = 'ebanx';
    const CHECKOUTCOM   = 'checkoutcom';
    const BLUESNAP      = 'bluesnap';
    const NOVALNET      = 'novalnet';
    const MINTE         = 'minte';
    const PAYPAL_HK     = 'paypal_hk';
    const APPMAX        = 'appmax';
    const STRIPE        = 'stripe';
    /**
     * Payment providers
     * @var type
     */
    public static $list = [
        self::PAYPAL => [
            'name'      => 'PayPal',
            'is_active' => false,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 0,
                    'refuse_limit' => 60
                ],
                'affiliate' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 0,
                    'refuse_limit' => 80
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::INSTANT_TRANSFER => [
                        '-3ds' => ['*']
                    ]
                ],
                'fallback' => []
            ]
        ],
        self::PAYPAL_HK => [
            'name'      => 'PayPal HK',
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 0,
                    'refuse_limit' => 70
                ],
                'affiliate' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 0,
                    'refuse_limit' => 85
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::INSTANT_TRANSFER => [
                        '-3ds' => ['*']
                    ]
                ],
                'fallback' => []
            ]
        ],
        self::CHECKOUTCOM => [
            'name'      => 'Checkout.com',
            'is_active' => false,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 1,
                    'fallback_limit' => 65,
                    'refuse_limit' => 85
                ],
                'affiliate' => [
                    '3ds_limit' => 66,
                    'fallback_limit' => 85,
                    'refuse_limit' => 85
                ]
            ],
            'extra_fields'  => [
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AB', 'label' => 'Alberta'],
                            ['value' => 'BC', 'label' => 'British Columbia'],
                            ['value' => 'MB', 'label' => 'Manitoba'],
                            ['value' => 'NB', 'label' => 'New Brunswick'],
                            ['value' => 'NL', 'label' => 'Newfoundland and Labrador'],
                            ['value' => 'NS', 'label' => 'Nova Scotia'],
                            ['value' => 'NT', 'label' => 'Northwest Territories'],
                            ['value' => 'NU', 'label' => 'Nunavut'],
                            ['value' => 'ON', 'label' => 'Ontario'],
                            ['value' => 'PE', 'label' => 'Prince Edward Island'],
                            ['value' => 'QC', 'label' => 'Quebec'],
                            ['value' => 'SK', 'label' => 'Saskatchewan'],
                            ['value' => 'YT', 'label' => 'Yukon Territory']
                        ]
                    ]
                ],
                'us' => [
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
//                            ['value' => 'AK', 'label' => 'Alaska'],
                            ['value' => 'AL', 'label' => 'Alabama'],
                            ['value' => 'AP', 'label' => 'Armed Forces Pacific'],
                            ['value' => 'AR', 'label' => 'Arkansas'],
                            ['value' => 'AS', 'label' => 'American Samoa'],
                            ['value' => 'AZ', 'label' => 'Arizona'],
                            ['value' => 'CA', 'label' => 'California'],
                            ['value' => 'CO', 'label' => 'Colorado'],
                            ['value' => 'CT', 'label' => 'Connecticut'],
                            ['value' => 'DC', 'label' => 'District Of Columbia'],
                            ['value' => 'DE', 'label' => 'Delaware'],
                            ['value' => 'FL', 'label' => 'Florida'],
                            ['value' => 'GA', 'label' => 'Georgia'],
                            ['value' => 'GU', 'label' => 'Guam'],
//                            ['value' => 'HI', 'label' => 'Hawaii'],
                            ['value' => 'IA', 'label' => 'Iowa'],
                            ['value' => 'ID', 'label' => 'Idaho'],
                            ['value' => 'IL', 'label' => 'Illinois'],
                            ['value' => 'IN', 'label' => 'Indiana'],
                            ['value' => 'KS', 'label' => 'Kansas'],
                            ['value' => 'KY', 'label' => 'Kentucky'],
                            ['value' => 'LA', 'label' => 'Louisiana'],
                            ['value' => 'MA', 'label' => 'Massachusetts'],
                            ['value' => 'MD', 'label' => 'Maryland'],
                            ['value' => 'ME', 'label' => 'Maine'],
                            ['value' => 'MI', 'label' => 'Michigan'],
                            ['value' => 'MN', 'label' => 'Minnesota'],
                            ['value' => 'MO', 'label' => 'Missouri'],
                            ['value' => 'MS', 'label' => 'Mississippi'],
                            ['value' => 'MT', 'label' => 'Montana'],
                            ['value' => 'NC', 'label' => 'North Carolina'],
                            ['value' => 'ND', 'label' => 'North Dakota'],
                            ['value' => 'NE', 'label' => 'Nebraska'],
                            ['value' => 'NH', 'label' => 'New Hampshire'],
                            ['value' => 'NJ', 'label' => 'New Jersey'],
                            ['value' => 'NM', 'label' => 'New Mexico'],
                            ['value' => 'NV', 'label' => 'Nevada'],
                            ['value' => 'NY', 'label' => 'New York'],
                            ['value' => 'OH', 'label' => 'Ohio'],
                            ['value' => 'OK', 'label' => 'Oklahoma'],
                            ['value' => 'OR', 'label' => 'Oregon'],
                            ['value' => 'PA', 'label' => 'Pennsylvania'],
//                            ['value' => 'PR', 'label' => 'Puerto Rico'],
                            ['value' => 'RI', 'label' => 'Rhode Island'],
                            ['value' => 'SC', 'label' => 'South Carolina'],
                            ['value' => 'SD', 'label' => 'South Dakota'],
                            ['value' => 'TN', 'label' => 'Tennessee'],
                            ['value' => 'TX', 'label' => 'Texas'],
                            ['value' => 'UT', 'label' => 'Utah'],
                            ['value' => 'VA', 'label' => 'Virgina'],
                            ['value' => 'VI', 'label' => 'Virgin Islands'],
                            ['value' => 'VT', 'label' => 'Vermont'],
                            ['value' => 'WA', 'label' => 'Washington'],
                            ['value' => 'WI', 'label' => 'Wisconsin'],
                            ['value' => 'WV', 'label' => 'West Virginia'],
                            ['value' => 'WY', 'label' => 'Wyoming']
                        ]
                    ]
                ],
                'gb' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'au' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'de' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'be' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'nl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'dk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'tr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'id' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'in' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'kh' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'et' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pe' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cu' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'es' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bd' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ng' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jp' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'at' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ph' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'vn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::VISA => [
                        '+3ds' => [
                            'europe', 'as', 'at', 'au', 'by', 'ca', '??h', 'de', 'dk', 'fr', 'gb', 'gu', 'gy', 'jp', 'id', 'il', 'in', 'is',
                            'ko', 'kr', 'lk', 'mp', 'my', 'no', 'nz', 'pe', 'ph', 'pr', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'us', 'vi', 'vn'
                        ],
                        '-3ds' => ['*'],
                        'excl' => [
                            'ae', 'af', 'ag', 'al', 'ar', 'au', 'bg', 'br', 'bt', 'bz', 'ca', 'cf', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cz',
                            'dk', 'do', 'gb', 'gl', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'il', 'in', 'je', 'jo', 'jp', 'ki', 'kr', 'kz', 'li',
                            'ls', 'mx', 'my', 'na', 'nf', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'ro', 'ru', 'se', 'sg', 'sy', 'tk', 'tt',
                            'tr', 'tv', 'tw', 'za', 'us', 'uz'
                            // 'af', 'ag', 'al', 'ar', 'br', 'bz', 'ca', 'cf', 'co', 'do', 'gb', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'us', 'uz'
                        ]
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => [
                            'europe', 'as', 'at', 'au', 'by', 'ca', '??h', 'de', 'dk', 'fr', 'gb', 'gu', 'gy', 'jp', 'id', 'il', 'in', 'is',
                            'ko', 'kr', 'lk', 'mp', 'my', 'no', 'nz', 'pe', 'ph', 'pr', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'us', 'vi', 'vn'
                        ],
                        '-3ds' => ['*'],
                        'excl' => [
                            'ae', 'af', 'ag', 'al', 'ar', 'au', 'bg', 'br', 'bt', 'bz', 'ca', 'cf', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cz',
                            'dk', 'do', 'gb', 'gl', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'il', 'in', 'je', 'jo', 'jp', 'ki', 'kr', 'kz', 'li',
                            'ls', 'mx', 'my', 'na', 'nf', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'ro', 'ru', 'se', 'sg', 'sy', 'tk', 'tt',
                            'tr', 'tv', 'tw', 'za', 'us', 'uz'
                            // 'af', 'ag', 'al', 'ar', 'br', 'bz', 'ca', 'cf', 'co', 'do', 'gb', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'us', 'uz'
                        ]
                    ],
                    PaymentMethods::AMEX => [
                        '+3ds' => [
                            'europe', 'as', 'at', 'au', 'by', 'ca', '??h', 'de', 'dk', 'fr', 'gb', 'gu', 'gy', 'jp', 'id', 'il', 'in', 'is',
                            'ko', 'kr', 'lk', 'mp', 'my', 'no', 'nz', 'pe', 'ph', 'pr', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'us', 'vi', 'vn'
                        ],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DISCOVER => [
                        '+3ds' => [
                            'europe', 'as', 'at', 'au', 'by', 'ca', '??h', 'de', 'dk', 'fr', 'gb', 'gu', 'gy', 'jp', 'id', 'il', 'in', 'is',
                            'kr', 'lk', 'mp', 'my', 'no', 'pe', 'ph', 'pr', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'us', 'vi', 'vn'
                        ],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '+3ds' => [
                            'europe', 'as', 'at', 'au', 'by', 'ca', '??h', 'de', 'dk', 'fr', 'gb', 'gu', 'gy', 'jp', 'id', 'il', 'in', 'is',
                            'kr', 'lk', 'mp', 'my', 'no', 'pe', 'ph', 'pr', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'us', 'vi', 'vn'
                        ],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::JCB => [
                        '+3ds' => ['bd', 'ca', 'cn', 'hk', 'id', 'jp', 'kr', 'la', 'mm', 'mn', 'ph', 'se', 'th', 'tw', 'us', 'vn'],
                        '-3ds' => ['sg'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ]
                ],
                'fallback' => []
            ]
        ],
        self::EBANX => [
            'name'      => 'EBANX',
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => true,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ],
                'affiliate' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ]
            ],
            'extra_fields'  => [
                'ar' => [
                    'card_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'debit', 'phrase'   => 'checkout.payment_form.card_type.debit'],
                            ['value' => 'credit', 'phrase'  => 'checkout.payment_form.card_type.credit']
                        ],
                        'default'   => 'credit'
                    ],
                    'complement' => [
                        'type'      => 'text',
                        'pattern'   => '^.{0,100}$'
                    ],
                    'building' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,9}$'
                    ],
                    'document_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'CUIT', 'phrase' => 'document_type.ar.cuit'],
                            ['value' => 'CUIL', 'phrase' => 'document_type.ar.cuil'],
                            ['value' => 'CDI', 'phrase'  => 'document_type.ar.cdi'],
                            ['value' => 'DNI', 'phrase'  => 'document_type.ar.dni']
                        ],
                        'default'   => 'CUIT'
                    ],
                    'document_number'   => [
                        'type'  => 'text',
                        'pattern' => [
                            'DNI'   => '^\d{7,8}$',
                            'CDI'   => '^\d{2}-\d{8}-\d{1}$',
                            'CUIT'  => '^\d{2}-\d{8}-\d{1}$',
                            'CUIL'  => '^\d{2}-\d{8}-\d{1}$'
                        ],
                        'schema' => [
                            'DNI'   => ['\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d'],
                            'CDI'   => ['\d', '\d', '-', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '-', '\d'],
                            'CUIT'  => ['\d', '\d', '-', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '-', '\d'],
                            'CUIL'  => ['\d', '\d', '-', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '-', '\d']
                        ],
                        'placeholder' => [
                            'DNI'   => 'xxxxxxxx',
                            'CDI'   => 'xx-xxxxxxxx-x',
                            'CUIT'  => 'xx-xxxxxxxx-x',
                            'CUIL'  => 'xx-xxxxxxxx-x'
                        ]
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'   => 1,
                        'visibility' => ['card_type' => ['credit']]
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'Buenos Aires', 'label' => 'Buenos Aires'],
                            ['value' => 'C??rdoba', 'label' => 'C??rdoba'],
                            ['value' => 'Santa Fe', 'label' => 'Santa Fe'],
                            ['value' => 'Autonomous City of Buenos Aires', 'label' => 'Autonomous City of Buenos Aires'],
                            ['value' => 'Mendoza', 'label' => 'Mendoza'],
                            ['value' => 'Tucum??n', 'label' => 'Tucum??n'],
                            ['value' => 'Salta', 'label' => 'Salta'],
                            ['value' => 'Entre R??os', 'label' => 'Entre R??os'],
                            ['value' => 'Misiones', 'label' => 'Misiones'],
                            ['value' => 'Chaco', 'label' => 'Chaco'],
                            ['value' => 'Corrientes', 'label' => 'Corrientes'],
                            ['value' => 'Santiago del Estero', 'label' => 'Santiago del Estero'],
                            ['value' => 'San Juan', 'label' => 'San Juan'],
                            ['value' => 'Jujuy', 'label' => 'Jujuy'],
                            ['value' => 'R??o Negro', 'label' => 'R??o Negro'],
                            ['value' => 'Neuqu??n', 'label' => 'Neuqu??n'],
                            ['value' => 'Formosa', 'label' => 'Formosa'],
                            ['value' => 'Chubut', 'label' => 'Chubut'],
                            ['value' => 'San Luis', 'label' => 'San Luis'],
                            ['value' => 'Catamarca', 'label' => 'Catamarca'],
                            ['value' => 'La Rioja', 'label' => 'La Rioja'],
                            ['value' => 'La Pampa', 'label' => 'La Pampa'],
                            ['value' => 'Santa Cruz', 'label' => 'Santa Cruz'],
                            ['value' => 'Tierra del Fuego', 'label' => 'Tierra del Fuego']
                        ]
                    ]
                ],
                'br' => [
                    'complement' => [
                        'type'      => 'text',
                        'pattern'   => '^.{0,100}$'
                    ],
                    'building' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,9}$'
                    ],
                    'document_number'   => [
                        'type'      => 'text',
                        'pattern'   => '^\d{3}\.\d{3}\.\d{3}\-\d{2}$',
                        'schema'    => array('\d', '\d', '\d', '\.', '\d', '\d', '\d', '\.', '\d', '\d', '\d', '-', '\d', '\d'),
                        'placeholder' => 'xxx.xxx.xxx-xx'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default' => 3
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'DF', 'label' => 'Distrito Federal'],
                            ['value' => 'AC', 'label' => 'Acre'],
                            ['value' => 'AL', 'label' => 'Alagoas'],
                            ['value' => 'AP', 'label' => 'Amap??'],
                            ['value' => 'AM', 'label' => 'Amazonas'],
                            ['value' => 'BA', 'label' => 'Bahia'],
                            ['value' => 'CE', 'label' => 'Cear??'],
                            ['value' => 'ES', 'label' => 'Esp??rito Santo'],
                            ['value' => 'GO', 'label' => 'Goi??s'],
                            ['value' => 'MA', 'label' => 'Maranh??o'],
                            ['value' => 'MT', 'label' => 'Mato Grosso'],
                            ['value' => 'MS', 'label' => 'Mato Grosso do Sul'],
                            ['value' => 'MG', 'label' => 'Minas Gerais'],
                            ['value' => 'PA', 'label' => 'Par??'],
                            ['value' => 'PB', 'label' => 'Para??ba'],
                            ['value' => 'PR', 'label' => 'Paran??'],
                            ['value' => 'PE', 'label' => 'Pernambuco'],
                            ['value' => 'PI', 'label' => 'Piau??'],
                            ['value' => 'RJ', 'label' => 'Rio de Janeiro'],
                            ['value' => 'RN', 'label' => 'Rio Grande do Norte'],
                            ['value' => 'RS', 'label' => 'Rio Grande do Sul'],
                            ['value' => 'RO', 'label' => 'Rond??nia'],
                            ['value' => 'RR', 'label' => 'Roraima'],
                            ['value' => 'SC', 'label' => 'Santa Catarina'],
                            ['value' => 'SP', 'label' => 'S??o Paulo'],
                            ['value' => 'SE', 'label' => 'Sergipe'],
                            ['value' => 'TO', 'label' => 'Tocantins']
                        ]
                    ]
                ],
                'co' => [
                    'complement' => [
                        'type'      => 'text',
                        'pattern'   => '^.{0,100}$'
                    ],
                    'building' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,9}$'
                    ],
                    'document_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'NIT', 'phrase' => 'document_type.co.nit'],
                            ['value' => 'CC', 'phrase'  => 'document_type.co.cc'],
                            ['value' => 'CE', 'phrase'  => 'document_type.co.ce']
                        ],
                        'default'   => 'NIT'
                    ],
                    'document_number'   => [
                        'type'  => 'text',
                        'pattern' => [
                            'NIT' => '^\d{9,10}$',
                            'CC'  => '^\d{2,10}$',
                            'CE'  => '^\d{1,6}$'
                        ],
                        'schema' => [
                            'NIT' => array('\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d'),
                            'CC'  => array('\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\d'),
                            'CE'  => array('\d', '\d', '\d', '\d', '\d', '\d')
                        ],
                        'placeholder' => [
                            'NIT' => 'xxxxxxxxxx',
                            'CC'  => 'xxxxxxxxxx',
                            'CE'  => 'xxxxxx'
                        ]
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default' => 1
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'Capital District', 'label' => 'Capital District'],
                            ['value' => 'Amazonas', 'label' => 'Amazonas'],
                            ['value' => 'Antioquia', 'label' => 'Antioquia'],
                            ['value' => 'Arauca', 'label' => 'Arauca'],
                            ['value' => 'Atl??ntico', 'label' => 'Atl??ntico'],
                            ['value' => 'Bol??var', 'label' => 'Bol??var'],
                            ['value' => 'Boyac??', 'label' => 'Boyac??'],
                            ['value' => 'Caldas', 'label' => 'Caldas'],
                            ['value' => 'Caquet??', 'label' => 'Caquet??'],
                            ['value' => 'Casanare', 'label' => 'Casanare'],
                            ['value' => 'Cauca', 'label' => 'Cauca'],
                            ['value' => 'Cesar', 'label' => 'Cesar'],
                            ['value' => 'Choc??', 'label' => 'Choc??'],
                            ['value' => 'C??rdoba', 'label' => 'C??rdoba'],
                            ['value' => 'Cundinamarca', 'label' => 'Cundinamarca'],
                            ['value' => 'Guain??a', 'label' => 'Guain??a'],
                            ['value' => 'Guaviare', 'label' => 'Guaviare'],
                            ['value' => 'Huila', 'label' => 'Huila'],
                            ['value' => 'La Guajira', 'label' => 'La Guajira'],
                            ['value' => 'Magdalena', 'label' => 'Magdalena'],
                            ['value' => 'Meta', 'label' => 'Meta'],
                            ['value' => 'Nari??o', 'label' => 'Nari??o'],
                            ['value' => 'Norte de Santander', 'label' => 'Norte de Santander'],
                            ['value' => 'Putumayo', 'label' => 'Putumayo'],
                            ['value' => 'Quind??o', 'label' => 'Quind??o'],
                            ['value' => 'Risaralda', 'label' => 'Risaralda'],
                            ['value' => 'San Andr??s y Providencia', 'label' => 'San Andr??s y Providencia'],
                            ['value' => 'Santander', 'label' => 'Santander'],
                            ['value' => 'Sucre', 'label' => 'Sucre'],
                            ['value' => 'Tolima', 'label' => 'Tolima'],
                            ['value' => 'Valle del Cauca', 'label' => 'Valle del Cauca'],
                            ['value' => 'Vaup??s', 'label' => 'Vaup??s']
                        ]
                    ]
                ],
                'mx' => [
                    'card_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'debit', 'phrase'    => 'checkout.payment_form.card_type.debit'],
                            ['value' => 'credit', 'phrase'   => 'checkout.payment_form.card_type.credit']
                        ],
                        'default'   => 'credit'
                    ],
                    'complement' => [
                        'type'      => 'text',
                        'pattern'   => '^.{0,100}$'
                    ],
                    'building' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,9}$'
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' =>  'Aguascalientes', 'label' =>  'Aguascalientes'],
                            ['value' => 'Baja California', 'label' => 'Baja California'],
                            ['value' => 'Baja California Sur', 'label' => 'Baja California Sur'],
                            ['value' => 'Campeche', 'label' => 'Campeche'],
                            ['value' => 'Chiapas', 'label' => 'Chiapas'],
                            ['value' => 'Chihuahua', 'label' => 'Chihuahua'],
                            ['value' => 'Coahuila', 'label' => 'Coahuila'],
                            ['value' => 'Colima', 'label' => 'Colima'],
                            ['value' => 'Distrito Federal', 'label' => 'Distrito Federal'],
                            ['value' => 'Durango', 'label' => 'Durango'],
                            ['value' => 'Estado de M??xico', 'label' => 'Estado de M??xico'],
                            ['value' => 'Guanajuato', 'label' => 'Guanajuato'],
                            ['value' => 'Guerrero', 'label' => 'Guerrero'],
                            ['value' => 'Hidalgo', 'label' => 'Hidalgo'],
                            ['value' => 'Jalisco', 'label' => 'Jalisco'],
                            ['value' => 'Michoac??n', 'label' => 'Michoac??n'],
                            ['value' => 'Morelos', 'label' => 'Morelos'],
                            ['value' => 'Nayarit', 'label' => 'Nayarit'],
                            ['value' => 'Nuevo Le??n', 'label' => 'Nuevo Le??n'],
                            ['value' => 'Oaxaca', 'label' => 'Oaxaca'],
                            ['value' => 'Puebla', 'label' => 'Puebla'],
                            ['value' => 'Quer??taro', 'label' => 'Quer??taro'],
                            ['value' => 'Quintana Roo', 'label' => 'Quintana Roo'],
                            ['value' => 'San Luis Potos??', 'label' => 'San Luis Potos??'],
                            ['value' => 'Sinaloa', 'label' => 'Sinaloa'],
                            ['value' => 'Sonora', 'label' => 'Sonora'],
                            ['value' => 'Tabasco', 'label' => 'Tabasco'],
                            ['value' => 'Tamaulipas', 'label' => 'Tamaulipas'],
                            ['value' => 'Tlaxcala', 'label' => 'Tlaxcala'],
                            ['value' => 'Veracruz', 'label' => 'Veracruz'],
                            ['value' => 'Yucat??n', 'label' => 'Yucat??n'],
                            ['value' => 'Zacatecas', 'label' =>  'Zacatecas']
                        ]
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'    => 1,
                        'visibility' => ['card_type' => ['credit']],
                    ]
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['ar', /*'br',*/ 'mx', 'co']
                    ],
                    PaymentMethods::VISA => [
                        '-3ds' => ['ar', /*'br',*/ 'mx', 'co']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['ar', /*'br',*/ 'mx', 'co']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['ar', /*'br',*/ 'co']
                    ],
                    // PaymentMethods::HIPERCARD => [
                    //     '-3ds' => ['br']
                    // ],
                    // PaymentMethods::ELO => [
                    //     '-3ds' => ['br']
                    // ],
                    PaymentMethods::NARANJA => [
                        '-3ds' => ['ar']
                    ],
                    PaymentMethods::CARNET => [
                        '-3ds' => ['mx']
                    ],
                    PaymentMethods::CABAL => [
                        '-3ds' => ['ar']
                    ],
                    PaymentMethods::CREDIMAS => [
                        '-3ds' => ['ar']
                    ]
                ],
                'fallback' => [
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::VISA => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::HIPERCARD => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::ELO => [
                        '-3ds' => ['br']
                    ],
                ]
            ]
        ],
        self::BLUESNAP => [
            'name' => 'Bluesnap',
            'is_active' => true,
            'is_main' => true,
            'is_fallback' => true,
            'in_prod' => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 20,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ],
                'affiliate' => [
                    '3ds_limit' => 100,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ]
            ],
            'extra_fields'  => [
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AB', 'label' => 'Alberta'],
                            ['value' => 'BC', 'label' => 'British Columbia'],
                            ['value' => 'MB', 'label' => 'Manitoba'],
                            ['value' => 'NB', 'label' => 'New Brunswick'],
                            ['value' => 'NL', 'label' => 'Newfoundland and Labrador'],
                            ['value' => 'NS', 'label' => 'Nova Scotia'],
                            ['value' => 'NT', 'label' => 'Northwest Territories'],
                            ['value' => 'NU', 'label' => 'Nunavut'],
                            ['value' => 'ON', 'label' => 'Ontario'],
                            ['value' => 'PE', 'label' => 'Prince Edward Island'],
                            ['value' => 'QC', 'label' => 'Quebec'],
                            ['value' => 'SK', 'label' => 'Saskatchewan'],
                            ['value' => 'YT', 'label' => 'Yukon Territory']
                        ]
                    ]
                ],
                'us' => [
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
//                            ['value' => 'AK', 'label' => 'Alaska'],
                            ['value' => 'AL', 'label' => 'Alabama'],
                            ['value' => 'AP', 'label' => 'Armed Forces Pacific'],
                            ['value' => 'AR', 'label' => 'Arkansas'],
                            ['value' => 'AS', 'label' => 'American Samoa'],
                            ['value' => 'AZ', 'label' => 'Arizona'],
                            ['value' => 'CA', 'label' => 'California'],
                            ['value' => 'CO', 'label' => 'Colorado'],
                            ['value' => 'CT', 'label' => 'Connecticut'],
                            ['value' => 'DC', 'label' => 'District Of Columbia'],
                            ['value' => 'DE', 'label' => 'Delaware'],
                            ['value' => 'FL', 'label' => 'Florida'],
                            ['value' => 'GA', 'label' => 'Georgia'],
                            ['value' => 'GU', 'label' => 'Guam'],
//                            ['value' => 'HI', 'label' => 'Hawaii'],
                            ['value' => 'IA', 'label' => 'Iowa'],
                            ['value' => 'ID', 'label' => 'Idaho'],
                            ['value' => 'IL', 'label' => 'Illinois'],
                            ['value' => 'IN', 'label' => 'Indiana'],
                            ['value' => 'KS', 'label' => 'Kansas'],
                            ['value' => 'KY', 'label' => 'Kentucky'],
                            ['value' => 'LA', 'label' => 'Louisiana'],
                            ['value' => 'MA', 'label' => 'Massachusetts'],
                            ['value' => 'MD', 'label' => 'Maryland'],
                            ['value' => 'ME', 'label' => 'Maine'],
                            ['value' => 'MI', 'label' => 'Michigan'],
                            ['value' => 'MN', 'label' => 'Minnesota'],
                            ['value' => 'MO', 'label' => 'Missouri'],
                            ['value' => 'MS', 'label' => 'Mississippi'],
                            ['value' => 'MT', 'label' => 'Montana'],
                            ['value' => 'NC', 'label' => 'North Carolina'],
                            ['value' => 'ND', 'label' => 'North Dakota'],
                            ['value' => 'NE', 'label' => 'Nebraska'],
                            ['value' => 'NH', 'label' => 'New Hampshire'],
                            ['value' => 'NJ', 'label' => 'New Jersey'],
                            ['value' => 'NM', 'label' => 'New Mexico'],
                            ['value' => 'NV', 'label' => 'Nevada'],
                            ['value' => 'NY', 'label' => 'New York'],
                            ['value' => 'OH', 'label' => 'Ohio'],
                            ['value' => 'OK', 'label' => 'Oklahoma'],
                            ['value' => 'OR', 'label' => 'Oregon'],
                            ['value' => 'PA', 'label' => 'Pennsylvania'],
//                            ['value' => 'PR', 'label' => 'Puerto Rico'],
                            ['value' => 'RI', 'label' => 'Rhode Island'],
                            ['value' => 'SC', 'label' => 'South Carolina'],
                            ['value' => 'SD', 'label' => 'South Dakota'],
                            ['value' => 'TN', 'label' => 'Tennessee'],
                            ['value' => 'TX', 'label' => 'Texas'],
                            ['value' => 'UT', 'label' => 'Utah'],
                            ['value' => 'VA', 'label' => 'Virgina'],
                            ['value' => 'VI', 'label' => 'Virgin Islands'],
                            ['value' => 'VT', 'label' => 'Vermont'],
                            ['value' => 'WA', 'label' => 'Washington'],
                            ['value' => 'WI', 'label' => 'Wisconsin'],
                            ['value' => 'WV', 'label' => 'West Virginia'],
                            ['value' => 'WY', 'label' => 'Wyoming']
                        ]
                    ]
                ],
                'gb' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'au' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'de' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'be' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'nl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'dk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'tr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'id' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'in' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'kh' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'et' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pe' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cu' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'es' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bd' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ng' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jp' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'at' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ph' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'vn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ]
            ],
            'methods' => [
                'main' => [
                    PaymentMethods::VISA => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::AMEX => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DISCOVER => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::JCB => [
                        // '+3ds' => ['bd', 'ca', 'cn', 'hk', 'id', 'jp', 'kr', 'la', 'mm', 'mn', 'ph', 'se', 'th', 'tw', 'us', 'vn'],
                        '-3ds' => ['bd', 'ca', 'cn', 'hk', 'id', 'jp', 'kr', 'la', 'mm', 'mn', 'ph', 'se', 'sg', 'th', 'tw', 'us', 'vn'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ]
                ],
                'fallback' => [
                    PaymentMethods::VISA => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::AMEX => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DISCOVER => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::JCB => [
                        '-3ds' => ['bd', 'cn', 'hk', 'id', 'jp', 'kr', 'la', 'mm', 'mn', 'ph', 'sg', 'th', 'tw', 'vn'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ]
                ]
            ]
        ],
        self::NOVALNET => [
            'name'          => 'Novalnet',
            'is_active'     => false,
            'is_main'       => true,
            'is_fallback'   => false,
            'in_prod'       => false,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 20,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ],
                'affiliate' => [
                    '3ds_limit' => 100,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ]
            ],
            'extra_fields'  => [
                'at' => [
                    'state' => [
                        'type' => 'text',
                        'pattern' => '^.{1,30}$'
                    ]
                ],
                'de' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ],
                    'document_number' => [
                        'type' => 'text',
                        'pattern' => '[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}',
                        'schema' => ['\w', '\w', '\d', '\d', '\w', '\w', '\w', '\w', '\d', '\d', '\d', '\d', '\d', '\d', '\d', '\w+'],
                        'placeholder' => 'IBAN'
                    ]
                ],
                'nl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
            ],
            'methods' => [
                'main' => [
                    PaymentMethods::EPS => [
                        '-3ds' => ['at']
                    ],
                    PaymentMethods::P24 => [
                        '-3ds' => ['pl']
                    ],
                    PaymentMethods::IDEAL => [
                        '-3ds' => ['nl']
                    ],
//                    PaymentMethods::SEPA => [
//                        '-3ds' => ['de']
//                    ]
                ],
                'fallback' => []
            ]
        ],
        self::MINTE => [
            'name'      => 'Mint-e',
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => true,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 20,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ],
                'affiliate' => [
                    '3ds_limit' => 100,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ]
            ],
            'extra_fields'  => [
                'au' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'be' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'de' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'dk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'gb' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'nl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'tr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'id' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'in' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'kh' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'et' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pe' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cu' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bo' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'es' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'bd' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'pk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ng' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'jp' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'at' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ph' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'vn' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'cr' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AB', 'label' => 'Alberta'],
                            ['value' => 'BC', 'label' => 'British Columbia'],
                            ['value' => 'MB', 'label' => 'Manitoba'],
                            ['value' => 'NB', 'label' => 'New Brunswick'],
                            ['value' => 'NL', 'label' => 'Newfoundland and Labrador'],
                            ['value' => 'NS', 'label' => 'Nova Scotia'],
                            ['value' => 'NT', 'label' => 'Northwest Territories'],
                            ['value' => 'NU', 'label' => 'Nunavut'],
                            ['value' => 'ON', 'label' => 'Ontario'],
                            ['value' => 'PE', 'label' => 'Prince Edward Island'],
                            ['value' => 'QC', 'label' => 'Quebec'],
                            ['value' => 'SK', 'label' => 'Saskatchewan'],
                            ['value' => 'YT', 'label' => 'Yukon Territory']
                        ]
                    ]
                ],
                'us' => [
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
//                            ['value' => 'AK', 'label' => 'Alaska'],
                            ['value' => 'AL', 'label' => 'Alabama'],
                            ['value' => 'AP', 'label' => 'Armed Forces Pacific'],
                            ['value' => 'AR', 'label' => 'Arkansas'],
                            ['value' => 'AS', 'label' => 'American Samoa'],
                            ['value' => 'AZ', 'label' => 'Arizona'],
                            ['value' => 'CA', 'label' => 'California'],
                            ['value' => 'CO', 'label' => 'Colorado'],
                            ['value' => 'CT', 'label' => 'Connecticut'],
                            ['value' => 'DC', 'label' => 'District Of Columbia'],
                            ['value' => 'DE', 'label' => 'Delaware'],
                            ['value' => 'FL', 'label' => 'Florida'],
                            ['value' => 'GA', 'label' => 'Georgia'],
                            ['value' => 'GU', 'label' => 'Guam'],
//                            ['value' => 'HI', 'label' => 'Hawaii'],
                            ['value' => 'IA', 'label' => 'Iowa'],
                            ['value' => 'ID', 'label' => 'Idaho'],
                            ['value' => 'IL', 'label' => 'Illinois'],
                            ['value' => 'IN', 'label' => 'Indiana'],
                            ['value' => 'KS', 'label' => 'Kansas'],
                            ['value' => 'KY', 'label' => 'Kentucky'],
                            ['value' => 'LA', 'label' => 'Louisiana'],
                            ['value' => 'MA', 'label' => 'Massachusetts'],
                            ['value' => 'MD', 'label' => 'Maryland'],
                            ['value' => 'ME', 'label' => 'Maine'],
                            ['value' => 'MI', 'label' => 'Michigan'],
                            ['value' => 'MN', 'label' => 'Minnesota'],
                            ['value' => 'MO', 'label' => 'Missouri'],
                            ['value' => 'MS', 'label' => 'Mississippi'],
                            ['value' => 'MT', 'label' => 'Montana'],
                            ['value' => 'NC', 'label' => 'North Carolina'],
                            ['value' => 'ND', 'label' => 'North Dakota'],
                            ['value' => 'NE', 'label' => 'Nebraska'],
                            ['value' => 'NH', 'label' => 'New Hampshire'],
                            ['value' => 'NJ', 'label' => 'New Jersey'],
                            ['value' => 'NM', 'label' => 'New Mexico'],
                            ['value' => 'NV', 'label' => 'Nevada'],
                            ['value' => 'NY', 'label' => 'New York'],
                            ['value' => 'OH', 'label' => 'Ohio'],
                            ['value' => 'OK', 'label' => 'Oklahoma'],
                            ['value' => 'OR', 'label' => 'Oregon'],
                            ['value' => 'PA', 'label' => 'Pennsylvania'],
//                            ['value' => 'PR', 'label' => 'Puerto Rico'],
                            ['value' => 'RI', 'label' => 'Rhode Island'],
                            ['value' => 'SC', 'label' => 'South Carolina'],
                            ['value' => 'SD', 'label' => 'South Dakota'],
                            ['value' => 'TN', 'label' => 'Tennessee'],
                            ['value' => 'TX', 'label' => 'Texas'],
                            ['value' => 'UT', 'label' => 'Utah'],
                            ['value' => 'VA', 'label' => 'Virgina'],
                            ['value' => 'VI', 'label' => 'Virgin Islands'],
                            ['value' => 'VT', 'label' => 'Vermont'],
                            ['value' => 'WA', 'label' => 'Washington'],
                            ['value' => 'WI', 'label' => 'Wisconsin'],
                            ['value' => 'WV', 'label' => 'West Virginia'],
                            ['value' => 'WY', 'label' => 'Wyoming']
                        ]
                    ]
                ],
            ],
            'methods' => [
                'main' => [
                    PaymentMethods::VISA => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => [
                            'ae', 'ar', 'au', 'at', 'be', 'bg', 'bt', 'ca', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cy', 'cz', 'de', 'dk', 'ee',
                            'es', 'fi', 'fr', 'gb', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'ie', 'il', 'in', 'it', 'jp', 'ki', 'kr', 'li', 'ls',
                            'lt', 'lu', 'lv', 'mc', 'mt', 'mx', 'my', 'na', 'nf', 'nl', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'pt', 'ro',
                            'ru', /*'se',*/ 'sg', 'si', 'sk', 'sm', 'tk', 'tr', 'tv', 'tw', 'us', 'va', 'za'
                        ],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => [
                            'ae', 'ar', 'au', 'at', 'be', 'bg', 'bt', 'ca', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cy', 'cz', 'de', 'dk', 'ee',
                            'es', 'fi', 'fr', 'gb', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'ie', 'il', 'in', 'it', 'jp', 'ki', 'kr', 'li', 'ls',
                            'lt', 'lu', 'lv', 'mc', 'mt', 'mx', 'my', 'na', 'nf', 'nl', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'pt', 'ro',
                            'ru', /*'se',*/ 'sg', 'si', 'sk', 'sm', 'tk', 'tr', 'tv', 'tw', 'us', 'va', 'za'
                        ],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::EPS => [
                        '-3ds' => ['at']
                    ],
                    PaymentMethods::P24 => [
                        '-3ds' => ['pl']
                    ],
                    PaymentMethods::IDEAL => [
                        '-3ds' => ['nl']
                    ],
                    PaymentMethods::BANCONTACT => [
                        '-3ds' => ['be']
                    ]
                ],
                'fallback' => [
                    PaymentMethods::VISA => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => [
                            'ae', 'ar', 'au', 'at', 'be', 'bg', 'bt', 'ca', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cy', 'cz', 'de', 'dk', 'ee',
                            'es', 'fi', 'fr', 'gb', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'ie', 'id', 'il', 'in', 'it', 'jp', 'ki', 'kr', 'li',
                            'ls', 'lt', 'lu', 'lv', 'mc', 'mt', 'mx', 'my', 'na', 'nf', 'nl', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'pr',
                            'pt', 'ro', 'ru', 'sa', 'se', 'sg', 'si', 'sk', 'sm', 'th', 'tk', 'tr', 'tv', 'tw', 'us', 'uy', 'va', 'vn', 'za'
                        ]
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => ['at', 'bg', 'cy', 'cz', 'gr', 'hr', 'hu', 'mt', 'pl', 'ro', 'sk', 'sl'],
                        '-3ds' => [
                            'ae', 'ar', 'au', 'at', 'be', 'bg', 'bt', 'ca', 'cc', 'ch', 'ck', 'cl', 'co', 'cx', 'cy', 'cz', 'de', 'dk', 'ee',
                            'es', 'fi', 'fr', 'gb', 'gp', 'gr', 'hk', 'hm', 'hr', 'hu', 'ie', 'id', 'il', 'in', 'it', 'jp', 'ki', 'kr', 'li',
                            'ls', 'lt', 'lu', 'lv', 'mc', 'mt', 'mx', 'my', 'na', 'nf', 'nl', 'no', 'nr', 'nu', 'nz', 'ph', 'pl', 'pn', 'pr',
                            'pt', 'ro', 'ru', 'sa', 'se', 'sg', 'si', 'sk', 'sm', 'th', 'tk', 'tr', 'tv', 'tw', 'us', 'uy', 'va', 'vn', 'za'
                        ]
                    ]
                ],
            ]
        ],
        self::APPMAX => [
            'name'      => 'Appmax',
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ],
                'affiliate' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 99,
                    'refuse_limit' => 99
                ]
            ],
            'extra_fields'  => [
                'br' => [
                    'complement' => [
                        'type'      => 'text',
                        'pattern'   => '^.{0,255}$'
                    ],
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,255}$'
                    ],
                    'building' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,9}$'
                    ],
                    'document_number'   => [
                        'type'      => 'text',
                        'pattern'   => '^\d{3}\.\d{3}\.\d{3}\-\d{2}$',
                        'schema'    => ['\d', '\d', '\d', '\.', '\d', '\d', '\d', '\.', '\d', '\d', '\d', '-', '\d', '\d'],
                        'placeholder' => 'xxx.xxx.xxx-xx'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default' => 3
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'pattern'   => '^.{1,30}$',
                        'items' => [
                            ['value' => 'DF', 'label' => 'Distrito Federal'],
                            ['value' => 'AC', 'label' => 'Acre'],
                            ['value' => 'AL', 'label' => 'Alagoas'],
                            ['value' => 'AP', 'label' => 'Amap??'],
                            ['value' => 'AM', 'label' => 'Amazonas'],
                            ['value' => 'BA', 'label' => 'Bahia'],
                            ['value' => 'CE', 'label' => 'Cear??'],
                            ['value' => 'ES', 'label' => 'Esp??rito Santo'],
                            ['value' => 'GO', 'label' => 'Goi??s'],
                            ['value' => 'MA', 'label' => 'Maranh??o'],
                            ['value' => 'MT', 'label' => 'Mato Grosso'],
                            ['value' => 'MS', 'label' => 'Mato Grosso do Sul'],
                            ['value' => 'MG', 'label' => 'Minas Gerais'],
                            ['value' => 'PA', 'label' => 'Par??'],
                            ['value' => 'PB', 'label' => 'Para??ba'],
                            ['value' => 'PR', 'label' => 'Paran??'],
                            ['value' => 'PE', 'label' => 'Pernambuco'],
                            ['value' => 'PI', 'label' => 'Piau??'],
                            ['value' => 'RJ', 'label' => 'Rio de Janeiro'],
                            ['value' => 'RN', 'label' => 'Rio Grande do Norte'],
                            ['value' => 'RS', 'label' => 'Rio Grande do Sul'],
                            ['value' => 'RO', 'label' => 'Rond??nia'],
                            ['value' => 'RR', 'label' => 'Roraima'],
                            ['value' => 'SC', 'label' => 'Santa Catarina'],
                            ['value' => 'SP', 'label' => 'S??o Paulo'],
                            ['value' => 'SE', 'label' => 'Sergipe'],
                            ['value' => 'TO', 'label' => 'Tocantins']
                        ]
                    ]
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::VISA => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::HIPERCARD => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::ELO => [
                        '-3ds' => ['br']
                    ]
                ],
                'fallback' => []
            ]
        ],
        self::STRIPE => [
            'name'      => 'Stripe',
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => true,
            'in_prod'   => true,
            'fraud_setting' => [
                'common' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 101,
                    'refuse_limit' => 101
                ],
                'affiliate' => [
                    '3ds_limit' => 101,
                    'fallback_limit' => 101,
                    'refuse_limit' => 101
                ]
            ],
            'extra_fields'  => [
                'at' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'be' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'de' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'dk' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'es' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'gb' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ],
                'nl' => [
                    'state' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ]
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::VISA => [
                        '-3ds' => ['*'] //before it was 'europe' for every card type
                    ],
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::DISCOVER => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::JCB => [
                        '-3ds' => ['*']
                    ]
                ],
                'fallback' => [
                    PaymentMethods::VISA => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::DISCOVER => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['*']
                    ],
                    PaymentMethods::JCB => [
                        '-3ds' => ['*']
                    ]
                ]
            ]
        ]
    ];


    /**
     * Returns list of active providers
     * @return array
     */
    public static function getAllActive(): array
    {
        return array_keys(array_filter(self::$list, function(array $item) { return $item['is_active']; }));
    }

    /**
     * Checks is provider active
     * @param  string  $name
     * @param  boolean $is_main
     * @return bool
     */
    public static function isActive(string $name, bool $is_main = true): bool
    {
        $is_active_in_env = \App::environment() === 'production' ? self::$list[$name]['in_prod'] : true;
        if (self::$list[$name]['is_active'] && $is_active_in_env) {
            if (self::$list[$name]['is_main'] && $is_main) {
                return true;
            } elseif (self::$list[$name]['is_fallback'] && !$is_main) {
                return true;
            }
        }
        return false;
    }
}
