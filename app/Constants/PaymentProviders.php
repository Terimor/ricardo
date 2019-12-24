<?php

namespace App\Constants;

class PaymentProviders
{
    const PAYPAL           = 'paypal';
    const EBANX            = 'ebanx';
    const CHECKOUTCOM      = 'checkoutcom';
    const BLUESNAP         = 'bluesnap';
    const NOVALNET         = 'novalnet';
    const MINTE            = 'minte';
    const PAYPAL_HK        = 'paypal_hk';
    const APPMAX           = 'appmax';

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
                '3ds_limit' => 85,
                'fallback_limit' => 0,
                'refuse_limit' => 60
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
                '3ds_limit' => 85,
                'fallback_limit' => 0,
                'refuse_limit' => 60
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
            'is_active' => true,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                '3ds_limit' => 65,
                'fallback_limit' => 65,
                'refuse_limit' => 85
            ],
            'extra_fields'  => [
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'default' => 'ON',
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
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
                            ['value' => 'AK', 'label' => 'Alaska'],
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
                            ['value' => 'HI', 'label' => 'Hawaii'],
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
                            ['value' => 'PR', 'label' => 'Puerto Rico'],
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
                        ],
                        'default' => 'DC'
                    ]
                ]
            ],
            'methods'   => [
                'main' => [
                    PaymentMethods::VISA => [
                        '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'ko', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'ca', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'us', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'ko', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'ca', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'us', 'uz']
                    ],
                    PaymentMethods::AMEX => [
                        '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'ko', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DISCOVER => [
                        '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::JCB => [
                        '+3ds' => ['bd', 'cn', 'hk', 'id', 'jp', 'kr', 'la', 'mm', 'mn', 'ph', 'th', 'tw', 'vn'],
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
            'is_fallback' => false,
            'in_prod'   => true,
            'fraud_setting' => [
                '3ds_limit' => 101,
                'fallback_limit' => 99,
                'refuse_limit' => 101
            ],
            'extra_fields'  => [
                'ar' => [
                    'card_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'debit', 'phrase'    => 'checkout.payment_form.card_type.debit'],
                            ['value' => 'credit', 'phrase'   => 'checkout.payment_form.card_type.credit']
                        ],
                        'default'   => 'credit'
                    ],
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
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
                        'items' => [
                            ['value' => 'Buenos Aires', 'label' => 'Buenos Aires'],
                            ['value' => 'Córdoba', 'label' => 'Córdoba'],
                            ['value' => 'Santa Fe', 'label' => 'Santa Fe'],
                            ['value' => 'Autonomous City of Buenos Aires', 'label' => 'Autonomous City of Buenos Aires'],
                            ['value' => 'Mendoza', 'label' => 'Mendoza'],
                            ['value' => 'Tucumán', 'label' => 'Tucumán'],
                            ['value' => 'Salta', 'label' => 'Salta'],
                            ['value' => 'Entre Ríos', 'label' => 'Entre Ríos'],
                            ['value' => 'Misiones', 'label' => 'Misiones'],
                            ['value' => 'Chaco', 'label' => 'Chaco'],
                            ['value' => 'Corrientes', 'label' => 'Corrientes'],
                            ['value' => 'Santiago del Estero', 'label' => 'Santiago del Estero'],
                            ['value' => 'San Juan', 'label' => 'San Juan'],
                            ['value' => 'Jujuy', 'label' => 'Jujuy'],
                            ['value' => 'Río Negro', 'label' => 'Río Negro'],
                            ['value' => 'Neuquén', 'label' => 'Neuquén'],
                            ['value' => 'Formosa', 'label' => 'Formosa'],
                            ['value' => 'Chubut', 'label' => 'Chubut'],
                            ['value' => 'San Luis', 'label' => 'San Luis'],
                            ['value' => 'Catamarca', 'label' => 'Catamarca'],
                            ['value' => 'La Rioja', 'label' => 'La Rioja'],
                            ['value' => 'La Pampa', 'label' => 'La Pampa'],
                            ['value' => 'Santa Cruz', 'label' => 'Santa Cruz'],
                            ['value' => 'Tierra del Fuego', 'label' => 'Tierra del Fuego']
                        ],
                        'default' => 'Buenos Aires'
                    ]
                ],
                'br' => [
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
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
                        'items' => [
                            ['value' => 'DF', 'label' => 'Distrito Federal'],
                            ['value' => 'AC', 'label' => 'Acre'],
                            ['value' => 'AL', 'label' => 'Alagoas'],
                            ['value' => 'AP', 'label' => 'Amapá'],
                            ['value' => 'AM', 'label' => 'Amazonas'],
                            ['value' => 'BA', 'label' => 'Bahia'],
                            ['value' => 'CE', 'label' => 'Ceará'],
                            ['value' => 'ES', 'label' => 'Espírito Santo'],
                            ['value' => 'GO', 'label' => 'Goiás'],
                            ['value' => 'MA', 'label' => 'Maranhão'],
                            ['value' => 'MT', 'label' => 'Mato Grosso'],
                            ['value' => 'MS', 'label' => 'Mato Grosso do Sul'],
                            ['value' => 'MG', 'label' => 'Minas Gerais'],
                            ['value' => 'PA', 'label' => 'Pará'],
                            ['value' => 'PB', 'label' => 'Paraíba'],
                            ['value' => 'PR', 'label' => 'Paraná'],
                            ['value' => 'PE', 'label' => 'Pernambuco'],
                            ['value' => 'PI', 'label' => 'Piauí'],
                            ['value' => 'RJ', 'label' => 'Rio de Janeiro'],
                            ['value' => 'RN', 'label' => 'Rio Grande do Norte'],
                            ['value' => 'RS', 'label' => 'Rio Grande do Sul'],
                            ['value' => 'RO', 'label' => 'Rondônia'],
                            ['value' => 'RR', 'label' => 'Roraima'],
                            ['value' => 'SC', 'label' => 'Santa Catarina'],
                            ['value' => 'SP', 'label' => 'São Paulo'],
                            ['value' => 'SE', 'label' => 'Sergipe'],
                            ['value' => 'TO', 'label' => 'Tocantins']
                        ],
                        'default' => 'DF'
                    ]
                ],
                'co' => [
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
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
                        'items' => [
                            ['value' => 'Capital District', 'label' => 'Capital District'],
                            ['value' => 'Amazonas', 'label' => 'Amazonas'],
                            ['value' => 'Antioquia', 'label' => 'Antioquia'],
                            ['value' => 'Arauca', 'label' => 'Arauca'],
                            ['value' => 'Atlántico', 'label' => 'Atlántico'],
                            ['value' => 'Bolívar', 'label' => 'Bolívar'],
                            ['value' => 'Boyacá', 'label' => 'Boyacá'],
                            ['value' => 'Caldas', 'label' => 'Caldas'],
                            ['value' => 'Caquetá', 'label' => 'Caquetá'],
                            ['value' => 'Casanare', 'label' => 'Casanare'],
                            ['value' => 'Cauca', 'label' => 'Cauca'],
                            ['value' => 'Cesar', 'label' => 'Cesar'],
                            ['value' => 'Chocó', 'label' => 'Chocó'],
                            ['value' => 'Córdoba', 'label' => 'Córdoba'],
                            ['value' => 'Cundinamarca', 'label' => 'Cundinamarca'],
                            ['value' => 'Guainía', 'label' => 'Guainía'],
                            ['value' => 'Guaviare', 'label' => 'Guaviare'],
                            ['value' => 'Huila', 'label' => 'Huila'],
                            ['value' => 'La Guajira', 'label' => 'La Guajira'],
                            ['value' => 'Magdalena', 'label' => 'Magdalena'],
                            ['value' => 'Meta', 'label' => 'Meta'],
                            ['value' => 'Nariño', 'label' => 'Nariño'],
                            ['value' => 'Norte de Santander', 'label' => 'Norte de Santander'],
                            ['value' => 'Putumayo', 'label' => 'Putumayo'],
                            ['value' => 'Quindío', 'label' => 'Quindío'],
                            ['value' => 'Risaralda', 'label' => 'Risaralda'],
                            ['value' => 'San Andrés y Providencia', 'label' => 'San Andrés y Providencia'],
                            ['value' => 'Santander', 'label' => 'Santander'],
                            ['value' => 'Sucre', 'label' => 'Sucre'],
                            ['value' => 'Tolima', 'label' => 'Tolima'],
                            ['value' => 'Valle del Cauca', 'label' => 'Valle del Cauca'],
                            ['value' => 'Vaupés', 'label' => 'Vaupés']
                        ],
                        'default' => 'Capital District'
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
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
                    ],
                    'state' => [
                        'type'  => 'dropdown',
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
                            ['value' => 'Estado de México', 'label' => 'Estado de México'],
                            ['value' => 'Guanajuato', 'label' => 'Guanajuato'],
                            ['value' => 'Guerrero', 'label' => 'Guerrero'],
                            ['value' => 'Hidalgo', 'label' => 'Hidalgo'],
                            ['value' => 'Jalisco', 'label' => 'Jalisco'],
                            ['value' => 'Michoacán', 'label' => 'Michoacán'],
                            ['value' => 'Morelos', 'label' => 'Morelos'],
                            ['value' => 'Nayarit', 'label' => 'Nayarit'],
                            ['value' => 'Nuevo León', 'label' => 'Nuevo León'],
                            ['value' => 'Oaxaca', 'label' => 'Oaxaca'],
                            ['value' => 'Puebla', 'label' => 'Puebla'],
                            ['value' => 'Querétaro', 'label' => 'Querétaro'],
                            ['value' => 'Quintana Roo', 'label' => 'Quintana Roo'],
                            ['value' => 'San Luis Potosã', 'label' => 'San Luis Potosã'],
                            ['value' => 'Sinaloa', 'label' => 'Sinaloa'],
                            ['value' => 'Sonora', 'label' => 'Sonora'],
                            ['value' => 'Tabasco', 'label' => 'Tabasco'],
                            ['value' => 'Tamaulipas', 'label' => 'Tamaulipas'],
                            ['value' => 'Tlaxcala', 'label' => 'Tlaxcala'],
                            ['value' => 'Veracruz', 'label' => 'Veracruz'],
                            ['value' => 'Yucatán', 'label' => 'Yucatán'],
                            ['value' => 'Zacatecas', 'label' =>  'Zacatecas']
                        ],
                        'default' => 'Distrito Federal'
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
                        '-3ds' => ['ar', 'br', 'mx', 'co']
                    ],
                    PaymentMethods::VISA => [
                        '-3ds' => ['ar', 'br', 'mx', 'co']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['ar', 'br', 'mx', 'co']
                    ],
                    PaymentMethods::DINERSCLUB => [
                        '-3ds' => ['ar', 'br', 'co']
                    ],
                    PaymentMethods::HIPERCARD => [
                        '-3ds' => ['br']
                    ],
                    PaymentMethods::ELO => [
                        '-3ds' => ['br']
                    ],
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
                'fallback' => []
            ]
        ],
        self::BLUESNAP  => [
            'name'      => 'Bluesnap',
            'is_active' => true,
            'is_main'   => false,
            'is_fallback' => true,
            'in_prod'   => true,
            'fraud_setting' => [
                '3ds_limit' => 101,
                'fallback_limit' => 0,
                'refuse_limit' => 99
            ],
            'extra_fields'  => [
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'default' => 'ON',
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
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
                            ['value' => 'AK', 'label' => 'Alaska'],
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
                            ['value' => 'HI', 'label' => 'Hawaii'],
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
                            ['value' => 'PR', 'label' => 'Puerto Rico'],
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
                        ],
                        'default' => 'DC'
                    ]
                ]
            ],
            'methods' => [
                'main' => [],
                'fallback' => [
                    PaymentMethods::VISA => [
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::AMEX => [
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DISCOVER => [
                        '-3ds' => ['*'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::DINERSCLUB => [
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
        self::NOVALNET  => [
            'name'          => 'Novalnet',
            'is_active'     => false,
            'is_main'       => false,
            'is_fallback'   => false,
            'in_prod'       => false,
            'fraud_setting' => [
                '3ds_limit' => 101,
                'fallback_limit' => 0,
                'refuse_limit' => 101
            ],
            'methods' => [
                'main' =>[
                    PaymentMethods::PREZELEWY24 => [
                        '-3ds' => ['pl']
                    ],
                    PaymentMethods::IDEAL => [
                        '-3ds' => ['nl']
                    ],
                    PaymentMethods::EPS => [
                        '-3ds' => ['at']
                    ]
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
                '3ds_limit' => 1,
                'fallback_limit' => 70,
                'refuse_limit' => 99
            ],
            'extra_fields'  => [
                'ca' => [
                    'state' => [
                        'type' => 'dropdown',
                        'default' => 'ON',
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
                        'items' => [
                            ['value' => 'AE', 'label' => 'Armed Forces (AE)'],
                            ['value' => 'AK', 'label' => 'Alaska'],
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
                            ['value' => 'HI', 'label' => 'Hawaii'],
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
                            ['value' => 'PR', 'label' => 'Puerto Rico'],
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
                        ],
                        'default' => 'DC'
                    ]
                ]
            ],
            'methods' => [
                'main' => [
                    PaymentMethods::VISA => [
                        // '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'ko', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        // '-3ds' => ['*'],
                        '-3ds' => ['ca', 'us'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ],
                    PaymentMethods::MASTERCARD => [
                        // '+3ds' => ['europe', 'by', 'gb', 'gy', 'id', 'il', 'in', 'is', 'ko', 'kr', 'lk', 'ro', 'ru', 'sa', 'se', 'tr', 'um', 'vi', 'my', 'jp'],
                        // '-3ds' => ['*'],
                        '-3ds' => ['ca', 'us'],
                        'excl' => ['af', 'ag', 'al', 'ar', 'br', 'bz', 'cf', 'co', 'do', 'gl', 'je', 'jo', 'kz', 'mx', 'sy', 'tt', 'uz']
                    ]
                ],
                'fallback' => [
                    PaymentMethods::VISA => [
                        '-3ds' => ['ar', 'co', 'mx']
                    ],
                    PaymentMethods::MASTERCARD => [
                        '-3ds' => ['ar', 'co', 'mx']
                    ]
                ],
            ]
        ],
        self::APPMAX => [
            'name'      => 'Appmax',
            'is_active' => false,
            'is_main'   => true,
            'is_fallback' => false,
            'in_prod'   => false,
            'fraud_setting' => [
                '3ds_limit' => 101,
                'fallback_limit' => 0,
                'refuse_limit' => 99
            ],
            'extra_fields'  => [
                'br' => [
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$'
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
                        'items' => [
                            ['value' => 'DF', 'label' => 'Distrito Federal'],
                            ['value' => 'AC', 'label' => 'Acre'],
                            ['value' => 'AL', 'label' => 'Alagoas'],
                            ['value' => 'AP', 'label' => 'Amapá'],
                            ['value' => 'AM', 'label' => 'Amazonas'],
                            ['value' => 'BA', 'label' => 'Bahia'],
                            ['value' => 'CE', 'label' => 'Ceará'],
                            ['value' => 'ES', 'label' => 'Espírito Santo'],
                            ['value' => 'GO', 'label' => 'Goiás'],
                            ['value' => 'MA', 'label' => 'Maranhão'],
                            ['value' => 'MT', 'label' => 'Mato Grosso'],
                            ['value' => 'MS', 'label' => 'Mato Grosso do Sul'],
                            ['value' => 'MG', 'label' => 'Minas Gerais'],
                            ['value' => 'PA', 'label' => 'Pará'],
                            ['value' => 'PB', 'label' => 'Paraíba'],
                            ['value' => 'PR', 'label' => 'Paraná'],
                            ['value' => 'PE', 'label' => 'Pernambuco'],
                            ['value' => 'PI', 'label' => 'Piauí'],
                            ['value' => 'RJ', 'label' => 'Rio de Janeiro'],
                            ['value' => 'RN', 'label' => 'Rio Grande do Norte'],
                            ['value' => 'RS', 'label' => 'Rio Grande do Sul'],
                            ['value' => 'RO', 'label' => 'Rondônia'],
                            ['value' => 'RR', 'label' => 'Roraima'],
                            ['value' => 'SC', 'label' => 'Santa Catarina'],
                            ['value' => 'SP', 'label' => 'São Paulo'],
                            ['value' => 'SE', 'label' => 'Sergipe'],
                            ['value' => 'TO', 'label' => 'Tocantins']
                        ],
                        'default' => 'DF'
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
        ]
    ];

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
