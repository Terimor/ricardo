<?php
namespace App\Constants;
use App\Models\Txn;

class PaymentMethods
{
    const INSTANT_TRANSFER   = 'instant_transfer';
    const CREDITCARD    = 'creditcard';
    const MASTERCARD    = 'mastercard';
    const VISA          = 'visa';
    const AMEX          = 'amex';
    const DISCOVER      = 'discover';
    const DINERSCLUB    = 'dinersclub';
    const JCB           = 'jcb';
    const HIPERCARD     = 'hipercard';
    const AURA          = 'aura';
    const ELO           = 'elo';
    const CARNET        = 'carnet';
    const NARANJA       = 'naranja';
    const CABAL         = 'cabal';
    const CREDIMAS      = 'credimas';
    // APM
    const BANCONTACT    = 'bancontact';
    const MULTIBANCO    = 'multibanco';
    const RECHNUNG      = 'rechnung';
    const SEPA          = 'sepa';
    const EPS           = 'eps';
    const IDEAL         = 'ideal';
    const P24           = 'p24';


    /**
     * Payment methods
     * @var type
     */
    public static $list = [
        self::INSTANT_TRANSFER => [
            'name'      => 'PayPal',
            'logo'      => '/static/images/payPal.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::MASTERCARD => [
            'name'      => 'MasterCard',
            'logo'      => '/static/images/mastercard-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::CREDITCARD => [
            'name'      => 'Credit card',
            'logo'      => '/static/images/othercard.png',
            'is_active' => false,
            'is_apm'    => false
        ],
        self::VISA => [
            'name'      => 'VISA',
            'logo'      => '/static/images/visa-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::AMEX => [
            'name'      => 'AmEx',
            'logo'      => '/static/images/american-express-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::JCB => [
            'name'      => 'JCB',
            'logo'      => '/static/images/jcb-curved.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::AURA => [
            'name'      => 'Aura',
            'logo'      => '/static/images/aura-curved.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::ELO => [
            'name'      => 'Elo',
            'logo'      => '/static/images/elo-curved.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::HIPERCARD => [
            'name'      => 'Hipercard',
            'logo'      => '/static/images/hipercard-curved.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::DISCOVER  => [
            'name'      => 'Discover',
            'logo'      => '/static/images/discover-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::DINERSCLUB => [
            'name'      => 'Diners Club',
            'logo'      => '/static/images/diners-curved.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::NARANJA => [
            'name'      => 'Naranja',
            'logo'      => '/static/images/naranja-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::CARNET => [
            'name'      => 'Carnet',
            'logo'      => '/static/images/carnet-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::CABAL => [
            'name'      => 'Cabal',
            'logo'      => '/static/images/cabal-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::CREDIMAS => [
            'name'      => 'Credimas',
            'logo'      => '/static/images/credimas-curved-128px.png',
            'is_active' => true,
            'is_apm'    => false
        ],
        self::IDEAL => [
            'name'      => 'iDeal',
            'logo'      => '/static/images/ideal-curved.png',
            'is_active' => true,
            'is_apm'    => true
        ],
        self::EPS => [
            'name'      => 'EPS',
            'logo'      => '/static/images/eps-curved.png',
            'is_active' => true,
            'is_apm'    => true
        ],
        self::P24 => [
            'name'      => 'Prezelewy 24',
            'logo'      => '/static/images/p24-curved.png',
            'is_active' => true,
            'is_apm'    => true
        ],
        self::BANCONTACT => [
            'name'      => 'Bancontact',
            'logo'      => '/static/images/bancontact-curved.png',
            'is_active' => true,
            'is_apm'    => true
        ],
        self::MULTIBANCO => [
            'name'      => 'MultiBanco',
            'logo'      => '/static/images/multibanco-curved.png',
            'is_active' => false,
            'is_apm'    => true
        ],
        self::SEPA => [
            'name'      => 'SEPA',
            'logo'      => '/static/images/sepa-curved.png',
            'is_active' => false,
            'is_apm'    => true
        ],
        self::RECHNUNG => [
            'name'      => 'Rechnung',
            'logo'      => '/static/images/rechnung-curved.png',
            'is_active' => false,
            'is_apm'    => true
        ]
    ];
}
