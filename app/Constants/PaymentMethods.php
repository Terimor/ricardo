<?php
namespace App\Constants;

class PaymentMethods
{
    const INSTANT_TRANSFER   = 'instant_transfer';
    const CREDITCARD         = 'creditcard';
    const MASTERCARD         = 'mastercard';
    const VISA               = 'visa';
    const AMEX               = 'amex';
    const DISCOVER           = 'discover';
    const DINERSCLUB         = 'dinersclub';
    const JCB                = 'jcb';
    const HIPERCARD          = 'hipercard';
    const AURA               = 'aura';
    const ELO                = 'elo';
    const PREZELEWY24        = 'prezelewy24';
    const IDEAL              = 'ideal';
    const EPS                = 'eps';
    const CARNET             = 'carnet';
    const NARANJA            = 'naranja';
    const CABAL              = 'cabal';
    const CREDIMAS           = 'credimas';

    /**
     * Payment methods
     * @var type
     */
    public static $list = [
        self::INSTANT_TRANSFER => [
            'name'      => 'PayPal',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/payPal.png',
            'is_active' => true
        ],
        self::MASTERCARD => [
            'name'      => 'MasterCard',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/mastercard-curved-128px.png',
            'is_active' => true,
        ],
        self::CREDITCARD => [
            'name'      => 'Credit card',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/othercard.png',
            'is_active' => false,
        ],
        self::VISA => [
            'name'      => 'VISA',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/visa-curved-128px.png',
            'is_active' => true,
        ],
        self::AMEX => [
            'name'      => 'AmEx',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/american-express-curved-128px.png',
            'is_active' => true,
        ],
        self::IDEAL => [
            'name'      => 'iDeal',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/ideal-curved.png',
            'is_active' => true,
        ],
        self::EPS => [
            'name'      => 'EPS',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/eps-curved.png',
            'is_active' => true,
        ],
        self::PREZELEWY24 => [
            'name'      => 'Prezelewy 24',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/prezelewy24-curved.png',
            'is_active' => true,
        ],
        self::JCB => [
            'name'      => 'JCB',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/jcb-curved.png',
            'is_active' => true,
        ],
        self::AURA => [
            'name'      => 'Aura',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/aura-curved.png',
            'is_active' => true,
        ],
        self::ELO => [
            'name'      => 'Elo',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/elo-curved.png',
            'is_active' => true,
        ],
        self::HIPERCARD => [
            'name'      => 'Hipercard',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/hipercard-curved.png',
            'is_active' => true,
        ],
        self::DISCOVER  => [
            'name'      => 'Discover',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/discover-curved-128px.png',
            'is_active' => true,
        ],
        self::DINERSCLUB => [
            'name'      => 'Diners Club',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/diners-curved.png',
            'is_active' => true,
        ],
        self::NARANJA => [
            'name'      => 'Naranja',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/naranja-curved-128px.png',
            'is_active' => true,
        ],
        self::CARNET => [
            'name'      => 'Carnet',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/carnet-curved-128px.png',
            'is_active' => true,
        ],
        self::CABAL => [
            'name'      => 'Cabal',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/cabal-curved-128px.png',
            'is_active' => true,
        ],
        self::CREDIMAS => [
            'name'      => 'Credimas',
            'logo'      => 'https://cdn.backenddomainsecure.com/static/images/credimas-curved-128px.png',
            'is_active' => true,
        ]
    ];
}
