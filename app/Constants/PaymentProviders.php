<?php

namespace App\Constants;

class PaymentProviders {
    const PAYPAL           = 'paypal';
    const EBANX            = 'ebanx';
    const CHECKOUTCOM      = 'checkoutcom';
    const BLUESNAP         = 'bluesnap';
    const NOVALNET         = 'novalnet';

    /**
     * Payment providers
     * @var type
     */
    public static $list = [
        self::PAYPAL => [
            'name'      => 'PayPal',
            'is_active' => true,
            'on_prod'   => true,
            'methods'   => [
                PaymentMethods::INSTANT_TRANSFER => [
                    '-3ds' => ['*']
                ]
            ]
        ],
        self::CHECKOUTCOM => [
            'name'      => 'Checkout.com',
            'is_active' => true,
            'on_prod'   => true,
            'methods'   => [
                PaymentMethods::CREDITCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::VISA       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::MASTERCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::AMEX       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::DISCOVER   => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'co', 'mx']
                    // '-3ds' => ['us']
                ],
                PaymentMethods::DINERSCLUB => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'co', 'mx']
                    // '-3ds' => ['us', 'ko']
                ],
                PaymentMethods::JCB        => [
                    '+3ds' => ['europe', 'il', 'ko', 'id', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['ar', 'br', 'co', 'mx']
                    // '-3ds' => ['sg', 'jp', 'tw', 'hk', 'mo', 'th', 'vn', 'kh', 'my', 'mm']
                ],
            ]
        ],
        self::EBANX       => [
            'name'      => 'EBANX',
            'is_active' => true,
            'on_prod'   => false,
            'extra_fields'  => [
                'ar' => [
                    'card_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'debit', 'phrase'    => 'checkout.payment_form.card_type.debit'],
                            ['value' => 'credit', 'phrase'   => 'checkout.payment_form.card_type.credit']
                        ],
                        'default'   => 'credit',
                        'phrase'     => 'checkout.payment_form.card_type.label'
                    ],
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$',
                        'phrase'     => 'checkout.payment_form.district.label'
                    ],
                    'document_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'CUIT', 'phrase' => 'checkout.payment_form.document_type.cuit'],
                            ['value' => 'CUIL', 'phrase' => 'checkout.payment_form.document_type.cuil'],
                            ['value' => 'CDI', 'phrase'  => 'checkout.payment_form.document_type.cdi'],
                            ['value' => 'DNI', 'phrase'  => 'checkout.payment_form.document_type.dni']
                        ],
                        'default'   => 'CUIT',
                        'phrase'     => 'checkout.payment_form.document_type.label'
                    ],
                    'document_number'   => [
                        'type'  => 'text',
                        'pattern' => [
                            'DNI'   => '^\d{7,8}$',
                            'CDI'   => '^\d{2}-\d{8}-\d{1}$',
                            'CUIT'  => '^\d{2}-\d{8}-\d{1}$',
                            'CUIL'  => '^\d{2}-\d{8}-\d{1}$'
                        ],
                        'placeholder' => [
                            'DNI'   => 'xxxxxxx(x)',
                            'CDI'   => 'xx-xxxxxxxx-x',
                            'CUIT'  => 'xx-xxxxxxxx-x',
                            'CUIL'  => 'xx-xxxxxxxx-x'
                        ],
                        'phrase' => 'checkout.payment_form.document_number.label'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'   => 1,
                        'visibility' => ['card_type' => ['credit']],
                        'phrase'   => 'checkout.payment_form.installments.label'
                    ],
                    'state' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'Buenos Aires', 'label' => 'Buenos Aires'],
                            ['value' => 'La Rioja', 'label' => 'Buenos Aires'],
                        ],
                        'default' => 'Buenos Aires',
                        'phrase'   => 'checkout.payment_form.state.label'
                    ]
                ],
                'br' => [
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$',
                        'phrase'     => 'checkout.payment_form.district.label'
                    ],
                    'document_number'   => [
                        'type'      => 'text',
                        'pattern'   => '^\d{3}\.\d{3}\.\d{3}\-\d{2}$',
                        'placeholder' => 'xxx-xxx-xxx-xx',
                        'phrase' => 'checkout.payment_form.document_number.label'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'   => 3,
                        'phrase'   => 'checkout.payment_form.installments.label'
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
                        'default' => 'DF',
                        'phrase'   => 'checkout.payment_form.state.label'
                    ]
                ],
                'co' => [
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$',
                        'phrase'    => 'checkout.payment_form.district.label'
                    ],
                    'document_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'NIT', 'phrase' => 'checkout.payment_form.document_type.nit'],
                            ['value' => 'CC', 'phrase'  => 'checkout.payment_form.document_type.cc'],
                            ['value' => 'CE', 'phrase'  => 'checkout.payment_form.document_type.ce']
                        ],
                        'default'   => 'NIT',
                        'phrase'    => 'checkout.payment_form.document_type.label'
                    ],
                    'document_number'   => [
                        'type'  => 'text',
                        'pattern' => [
                            'NIT' => '^\d{9,10}$',
                            'CC'  => '^\d{2,10}$',
                            'CE'  => '^\d{1,6}$'
                        ],
                        'placeholder' => [
                            'NIT' => 'xxxxxxxxx(x)',
                            'CC'  => 'xx(xxxxxxxx)',
                            'CE'  => 'x(xxxxx)'
                        ],
                        'phrase' => 'checkout.payment_form.document_number.label'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'   => 1,
                        'phrase'   => 'checkout.payment_form.installments.label'
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
                            ['value' => 'Vaupés', 'lable' => 'Vaupés']
                        ],
                        'default' => 'Capital District',
                        'phrase'   => 'checkout.payment_form.state.label'
                    ]
                ],
                'mx' => [
                    'card_type' => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 'debit', 'phrase'    => 'checkout.payment_form.card_type.debit'],
                            ['value' => 'credit', 'phrase'   => 'checkout.payment_form.card_type.credit']
                        ],
                        'default'   => 'credit',
                        'phrase'     => 'checkout.payment_form.card_type.label'
                    ],
                    'district' => [
                        'type'      => 'text',
                        'pattern'   => '^.{1,30}$',
                        'phrase'    => 'checkout.payment_form.district.label'
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
                        'default' => 'Distrito Federal',
                        'phrase'  => 'checkout.payment_form.state.label'
                    ],
                    'installments'  => [
                        'type'  => 'dropdown',
                        'items' => [
                            ['value' => 1, 'phrase' => 'checkout.payment_form.installments.full_amount'],
                            ['value' => 3, 'phrase' => 'checkout.payment_form.installments.pay_3'],
                            ['value' => 6, 'phrase' => 'checkout.payment_form.installments.pay_6'],
                        ],
                        'default'    => 1,
                        'phrase'     => 'checkout.payment_form.installments.label',
                        'visibility' => ['card_type' => ['credit']],
                    ]
                ]
            ],
            'methods'   => [
                PaymentMethods::CREDITCARD => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                PaymentMethods::MASTERCARD => [
                    '-3ds' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::VISA       => [
                    '-3ds' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::AMEX       => [
                    '-3ds' => ['ar', 'br', 'mx', 'co']
                ],
                PaymentMethods::DINERSCLUB => [
                    '-3ds' => ['ar', 'br', 'co']
                ],
                PaymentMethods::HIPERCARD  => [
                    '-3ds' => ['br']
                ],
                PaymentMethods::ELO        => [
                    '-3ds' => ['br']
                ],
                PaymentMethods::NARANJA   => [
                    '-3ds' => ['ar']
                ],
                PaymentMethods::CARNET     => [
                    '-3ds' => ['mx']
                ],
                PaymentMethods::CABAL      => [
                    '-3ds' => ['ar']
                ],
                PaymentMethods::CREDIMAS   => [
                    '-3ds' => ['ar']
                ]
            ]
        ],
        self::NOVALNET    => [
            'name'      => 'Novalnet',
            'is_active' => false,
            'on_prod'   => false,
            'methods'   => [
                PaymentMethods::PREZELEWY24 => [
                    '-3ds' => ['pl']
                ],
                PaymentMethods::IDEAL       => [
                    '-3ds' => ['nl']
                ],
                PaymentMethods::EPS         => [
                    '-3ds' => ['at']
                ],
            ]
        ]
    ];
}
