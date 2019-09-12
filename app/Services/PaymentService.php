<?php

namespace App\Services;

use App\Services\UtilsService;

/**
 * Payment Service class
 */
class PaymentService
{

    const PROVIDER_PAYPAL      = 'paypal';
    const PROVIDER_EBANX       = 'ebanx';
    const PROVIDER_CHECKOUTCOM = 'checkoutcom';
    const PROVIDER_BLUESNAP    = 'bluesnap';
    const PROVIDER_NOVALNET    = 'novalnet';
    const METHOD_INSTANT_TRANSFER = 'instant_transfer';
    const METHOD_CREDITCARD       = 'creditcard';
    const METHOD_MASTERCARD       = 'mastercard';
    const METHOD_VISA             = 'visa';
    const METHOD_AMEX             = 'amex';
    const METHOD_DISCOVER         = 'discover';
    const METHOD_DINERSCLUB       = 'dinersclub';
    const METHOD_JCB              = 'jcb';
    const METHOD_HIPERCARD        = 'hipercard';
    const METHOD_AURA             = 'aura';
    const METHOD_ELO              = 'elo';
    const METHOD_PREZELEWY24      = 'prezelewy24';
    const METHOD_IDEAL            = 'ideal';
    const METHOD_EPS              = 'eps';

    /**
     * Payment providers
     * +3ds — 3DS is required
     * -3ds — 3DS is optional
     * excl — excluded countries
     * @var type
     */
    public static $providers = [
        self::PROVIDER_PAYPAL      => [
            'name'      => 'PayPal',
            'is_active' => true,
            'methods'   => [
                self::METHOD_INSTANT_TRANSFER => [
                    '-3ds' => ['*']
                ]
            ]
        ],
        self::PROVIDER_CHECKOUTCOM => [
            'name'      => 'Checkout.com',
            'is_active' => true,
            'methods'   => [
                self::METHOD_CREDITCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_VISA       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_MASTERCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_AMEX       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_DISCOVER   => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru'],
                    '-3ds' => ['us']
                ],
                self::METHOD_DINERSCLUB => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru'],
                    '-3ds' => ['us', 'ko']
                ],
                self::METHOD_JCB        => [
                    '+3ds' => ['europe', 'il', 'ko', 'id'],
                    '-3ds' => ['sg', 'jp', 'tw', 'hk', 'mo', 'th', 'vn', 'kh', 'my', 'mm']
                ],
            ]
        ],
        self::PROVIDER_EBANX       => [
            'name'      => 'EBANX',
            'is_active' => true,
            'methods'   => [
                self::METHOD_CREDITCARD => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_MASTERCARD => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_VISA       => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_AMEX       => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_DISCOVER   => [
                    '-3ds' => ['br']
                ],
                self::METHOD_DINERSCLUB => [
                    '-3ds' => ['br', 'co']
                ],
                self::METHOD_HIPERCARD  => [
                    '-3ds' => ['br']
                ],
                self::METHOD_AURA       => [
                    '-3ds' => ['br']
                ],
                self::METHOD_ELO        => [
                    '-3ds' => ['br']
                ]
            ]
        ],
        self::PROVIDER_NOVALNET    => [
            'name'      => 'EBANX',
            'is_active' => true,
            'methods'   => [
                self::METHOD_PREZELEWY24 => [
                    '-3ds' => ['pl']
                ],
                self::METHOD_IDEAL       => [
                    '-3ds' => ['nl']
                ],
                self::METHOD_EPS         => [
                    '-3ds' => ['at']
                ],
            ]
        ]
    ];

    /**
     * Payment methods
     * @var type
     */
    public static $methods = [
        self::METHOD_INSTANT_TRANSFER => [
            'name'      => 'Instant transfer',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/paypal-curved-128px.png',
            'is_active' => true
        ],
        self::METHOD_MASTERCARD       => [
            'name'      => 'MasterCard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/mastercard-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_CREDITCARD       => [
            'name'      => 'Credit card',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/othercard.png',
            'is_active' => true,
        ],
        self::METHOD_VISA             => [
            'name'      => 'VISA',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_AMEX             => [
            'name'      => 'AmEx',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/american-express-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_IDEAL            => [
            'name'      => 'iDeal',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/ideal-curved.png',
            'is_active' => true,
        ],
        self::METHOD_EPS              => [
            'name'      => 'EPS',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/eps-curved.png',
            'is_active' => true,
        ],
        self::METHOD_PREZELEWY24      => [
            'name'      => 'Prezelewy 24',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/prezelewy24-curved.png',
            'is_active' => true,
        ],
        self::METHOD_JCB              => [
            'name'      => 'JCB',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/jcb-curved.png',
            'is_active' => true,
        ],
        self::METHOD_AURA             => [
            'name'      => 'Aura',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/aura-curved.png',
            'is_active' => true,
        ],
        self::METHOD_ELO              => [
            'name'      => 'Elo',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/elo-curved.png',
            'is_active' => true,
        ],
        self::METHOD_HIPERCARD        => [
            'name'      => 'Hipercard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/hipercard-curved.png',
            'is_active' => true,
        ],
        self::METHOD_DISCOVER         => [
            'name'      => 'Discover',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/discover-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_DINERSCLUB       => [
            'name'      => 'Diners Club',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/diners-curved.png',
            'is_active' => true,
        ],
    ];


    public static $installments = [
        'mx' => [
            
        ]
    ];

    /**
     * Returns payment methods array by country
     * Results example:
     * $result = [
      self::PROVIDER_CHECKOUTCOM => [
      self::METHOD_VISA => [
      'name' => 'VISA',
      'logo' => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
      '3ds' => true
      ]
      ]
      ];
     * @param string $country
     * @return boolean
     */
    public static function getPaymentMethodsByCountry(string $country)
    {
        $result = [];
        foreach (static::$providers as $providerId => $provider)
        {
            if ($provider['is_active'])
            {
                $result[$providerId] = [];

                //check every method of provider
                foreach ($provider['methods'] as $methodId => $method)
                {
                    if (static::$methods[$methodId]['is_active'])
                    {
                        //check 3DS settings
                        if (!empty($method['+3ds']) && static::checkIfMethodInCountries($country, $method['+3ds']))
                        {
                            $result[$providerId][$methodId] = ['3ds' => true];
                        } elseif (!empty($method['-3ds']) && static::checkIfMethodInCountries($country, $method['-3ds']))
                        {
                            $result[$providerId][$methodId] = ['3ds' => false];
                        }

                        //check if country is excluded
                        if (!empty($method['excl']) && static::checkIfMethodInCountries($country, $method['excl']))
                        {
                            unset($result[$providerId][$methodId]);
                        }
                    }
                }
                if ($result[$providerId])
                {
                    foreach ($result[$providerId] as $methodId => &$methodData)
                    {
                        $method             = static::$methods[$methodId];
                        $methodData['name'] = $method['name'];
                        $methodData['logo'] = $method['logo'];
                    }
                } else
                {
                    //no suitable methods found for this provider
                    unset($result[$providerId]);
                }
            }
        }
        return $result;
    }

    /**
     * Checks if method exists for specified country
     * @param string $country
     * @param array $methodCountries
     * @return bool
     */
    private static function checkIfMethodInCountries(string $country, array $methodCountries): bool
    {
        $result = false;

        $orCountry = '*';
        if (UtilsService::isEUCountry($country))
        {
            $orCountry = 'europe';
        }

        if (
                in_array($country, $methodCountries) ||
                in_array($orCountry, $methodCountries) ||
                in_array('*', $methodCountries)
        )
        {
            $result = true;
        }
        return $result;
    }

}