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
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/paypal-curved-128px.png',
            'is_active' => true
        ],
        self::MASTERCARD => [
            'name'      => 'MasterCard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/mastercard-curved-128px.png',
            'is_active' => true,
        ],
        self::CREDITCARD => [
            'name'      => 'Credit card',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/othercard.png',
            'is_active' => false,
        ],
        self::VISA => [
            'name'      => 'VISA',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
            'is_active' => true,
        ],
        self::AMEX => [
            'name'      => 'AmEx',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/american-express-curved-128px.png',
            'is_active' => true,
        ],
        self::IDEAL => [
            'name'      => 'iDeal',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/ideal-curved.png',
            'is_active' => true,
        ],
        self::EPS => [
            'name'      => 'EPS',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/eps-curved.png',
            'is_active' => true,
        ],
        self::PREZELEWY24 => [
            'name'      => 'Prezelewy 24',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/prezelewy24-curved.png',
            'is_active' => true,
        ],
        self::JCB => [
            'name'      => 'JCB',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/jcb-curved.png',
            'is_active' => true,
        ],
        self::AURA => [
            'name'      => 'Aura',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/aura-curved.png',
            'is_active' => true,
        ],
        self::ELO => [
            'name'      => 'Elo',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/elo-curved.png',
            'is_active' => true,
        ],
        self::HIPERCARD => [
            'name'      => 'Hipercard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/hipercard-curved.png',
            'is_active' => true,
        ],
        self::DISCOVER  => [
            'name'      => 'Discover',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/discover-curved-128px.png',
            'is_active' => true,
        ],
        self::DINERSCLUB => [
            'name'      => 'Diners Club',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/diners-curved.png',
            'is_active' => true,
        ],
        self::NARANJA => [
            'name'      => 'Naranja',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/naranja-curved.png',
            'is_active' => true,
        ],
        self::CARNET => [
            'name'      => 'Carnet',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/carnet-curved.png',
            'is_active' => true,
        ],
        self::CABAL => [
            'name'      => 'Cabal',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/cabal-curved.png',
            'is_active' => true,
        ],
        self::CREDIMAS => [
            'name'      => 'Credimas',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/credimas-curved.png',
            'is_active' => true,
        ]
    ];
}
