<?php

declare(strict_types=1);

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Command\Guzzle\Description;
use Composer\InstalledVersions;

/**
 * Victoriabank MIA API service description
 *
 * @link https://test-ipspj.victoriabank.md
 * @link https://test-ipspj-demopay.victoriabank.md/swagger/
 */
class VictoriabankMiaDescription extends Description
{
    private const PACKAGE_NAME = 'alexminza/victoriabank-mia-sdk';
    private const DEFAULT_VERSION = 'dev';

    private static function detectVersion(): string
    {
        if (!class_exists(InstalledVersions::class)) {
            return self::DEFAULT_VERSION;
        }

        if (!InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return self::DEFAULT_VERSION;
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
            ?? self::DEFAULT_VERSION;
    }

    public function __construct(array $options = [])
    {
        $version = self::detectVersion();
        $userAgent = "victoriabank-mia-sdk-php/$version";

        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'description' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $models = [
            #region Generic Models
            'getResponse' => [
                'type' => 'object',
                'additionalProperties' => [
                    'location' => 'json'
                ]
            ],
            'getRawResponse' => [
                'type' => 'object',
                'properties' => [
                    'body' => [
                        'type' => 'string',
                        'location' => 'body',
                        'filters' => ['strval']
                    ]
                ]
            ],
            #endregion

            #region Schema-based Models
            'AuthTokenDto' => [
                'type' => 'object',
                'properties' => [
                    'grant_type' => ['type' => 'string'],
                    'username' => ['type' => 'string'],
                    'password' => ['type' => 'string'],
                    'refresh_token' => ['type' => 'string'],
                ],
            ],
            'CreatePayeeQrResponse' => [
                'type' => 'object',
                'properties' => [
                    'qrHeaderUUID' => ['type' => ['string', 'null']],
                    'qrExtensionUUID' => ['type' => ['string', 'null']],
                    'qrAsText' => ['type' => ['string', 'null']],
                    'qrAsImage' => ['type' => ['string', 'null'], 'format' => 'byte'],
                ],
            ],
            'MoneyDto' => [
                'type' => 'object',
                'properties' => [
                    'sum' => ['type' => 'number'],
                    'currency' => ['type' => ['string', 'null']],
                ],
            ],
            'PayeeAccountDto' => [
                'type' => 'object',
                'properties' => [
                    'iban' => ['type' => ['string', 'null']],
                ],
            ],
            'PayeeQrExtensionStatusDto' => [
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => ['string', 'null']],
                    'isLast' => ['type' => 'boolean'],
                    'status' => ['type' => 'string', 'enum' => ['None', 'Active', 'Paid', 'Expired', 'Cancelled', 'Replaced', 'Inactive']],
                    'statusDtTm' => ['type' => 'string', 'format' => 'date-time'],
                    'isHeaderLocked' => ['type' => 'boolean'],
                    'ttl' => ['$ref' => 'TtlDto'],
                    'payments' => ['type' => ['array', 'null'], 'items' => ['$ref' => 'PaymentOutDto']],
                ],
            ],
            'PayeeQrStatusDto' => [
                'type' => 'object',
                'properties' => [
                    'uuid' => ['type' => ['string', 'null']],
                    'status' => ['type' => 'string', 'enum' => ['None', 'Active', 'Paid', 'Expired', 'Cancelled', 'Replaced', 'Inactive']],
                    'statusDtTm' => ['type' => 'string', 'format' => 'date-time'],
                    'lockTtl' => ['$ref' => 'TtlDto'],
                    'extensions' => ['type' => ['array', 'null'], 'items' => ['$ref' => 'PayeeQrExtensionStatusDto']],
                ],
            ],
            'PaymentOutDto' => [
                'type' => 'object',
                'properties' => [
                    'system' => ['type' => ['string', 'null']],
                    'reference' => ['type' => ['string', 'null']],
                    'amount' => ['$ref' => 'MoneyDto'],
                ],
            ],
            'SignalDto' => [
                'type' => 'object',
                'properties' => [
                    'signalCode' => ['type' => ['string', 'null']],
                    'signalDtTm' => ['type' => 'string', 'format' => 'date-time'],
                    'qrHeaderUUID' => ['type' => ['string', 'null']],
                    'qrExtensionUUID' => ['type' => ['string', 'null']],
                    'payment' => ['$ref' => 'PaymentOutDto'],
                ],
            ],
            'TransactionInfo' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => ['string', 'null']],
                    'date' => ['type' => ['string', 'null']],
                    'time' => ['type' => ['string', 'null']],
                    'payerName' => ['type' => ['string', 'null']],
                    'payerIdnp' => ['type' => ['string', 'null']],
                    'beneficiaryIdnp' => ['type' => ['string', 'null']],
                    'transactionType' => ['type' => ['string', 'null']],
                    'transactionAmount' => ['type' => 'number'],
                    'transactionStatus' => ['type' => ['string', 'null']],
                    'destinationBankName' => ['type' => ['string', 'null']],
                    'transactionMessage' => ['type' => ['string', 'null']],
                    'paymentType' => ['type' => ['string', 'null']],
                    'miaId' => ['type' => ['string', 'null']],
                    'creditorRef' => ['type' => ['string', 'null']],
                ],
            ],
            'TransactionListDto' => [
                'type' => 'object',
                'properties' => [
                    'transactionsInfo' => ['type' => ['array', 'null'], 'items' => ['$ref' => 'TransactionInfo']],
                ],
            ],
            'TtlDto' => [
                'type' => 'object',
                'properties' => [
                    'length' => ['type' => 'integer'],
                    'units' => ['type' => ['string', 'null'], 'pattern' => '^(ss|mm)$'],
                ],
            ],
            'VbPayeeQrDto' => [
                'type' => 'object',
                'properties' => [
                    'header' => ['$ref' => 'VbPayeeQrHeaderDto'],
                    'extension' => ['$ref' => 'VbPayeeQrExtensionDto'],
                ],
            ],
            'VbPayeeQrExtensionDto' => [
                'type' => 'object',
                'properties' => [
                    'creditorAccount' => ['$ref' => 'PayeeAccountDto'],
                    'amount' => ['$ref' => 'MoneyDto'],
                    'amountMin' => ['$ref' => 'MoneyDto'],
                    'amountMax' => ['$ref' => 'MoneyDto'],
                    'dba' => ['type' => ['string', 'null']],
                    'remittanceInfo4Payer' => ['type' => ['string', 'null']],
                    'creditorRef' => ['type' => ['string', 'null']],
                    'ttl' => ['$ref' => 'TtlDto'],
                ],
            ],
            'VbPayeeQrHeaderDto' => [
                'type' => 'object',
                'properties' => [
                    'qrType' => ['type' => 'string', 'enum' => ['DYNM', 'STAT', 'HYBR']],
                    'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free']],
                    'pmtContext' => ['type' => ['string', 'null']],
                ],
            ],
            'DemoPayDto' => [
                'type' => 'object',
                'properties' => [
                    'qrHeaderUUID' => ['type' => 'string', 'required' => true],
                ],
            ],
            'ReconciliationTransactionsDto' => [
                'type' => 'object',
                'properties' => [
                    'dateFrom' => ['type' => 'string', 'format' => 'date-time'],
                    'dateTo' => ['type' => 'string', 'format' => 'date-time'],
                    'messageId' => ['type' => 'string'],
                ],
            ],
            #endregion
        ];

        $description = [
            'name' => 'Victoriabank MIA API',
            'apiVersion' => 'v1',

            'operations' => [
                'baseOp' => [
                    'parameters' => [
                        'User-Agent' => [
                            'location' => 'header',
                            'default'  => $userAgent,
                        ],
                    ],
                ],

                #region Health Operations
                'getHealthStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/health/status',
                    'summary' => 'Health Status',
                    'responseModel' => 'getResponse',
                ],
                #endregion

                #region Token Operations
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/identity/token',
                    'summary' => 'Get tokens',
                    'responseModel' => 'getResponse',
                    'parameters' => self::getProperties($models, 'AuthTokenDto', 'formParam'),
                ],
                #endregion

                #region QR Operations
                'createPayeeQr' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/api/v1/qr',
                    'summary' => 'CreatePayeeQr - Register new payee-presented QR code',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'width' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image width (Default: 300)'],
                        'height' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image height (Default: 300)'],
                    ], self::getProperties($models, 'VbPayeeQrDto')),
                ],
                'createPayeeQrExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/extentions',
                    'summary' => 'CreatePayeeQrExtention - Register new extension for HYBR or STAT payee-presented QR code',
                    'responseModel' => 'getRawResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ], self::getProperties($models, 'VbPayeeQrExtensionDto')),
                ],
                'cancelPayeeQr' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}',
                    'summary' => 'CancelPayeeQr-Cancel payee-resented QR code',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'cancelHybrExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/active-extension',
                    'summary' => 'Cancel active extension of hybrid payee-presented QR code',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'getPayeeQrStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/status',
                    'summary' => 'Get status of payee-presented QR code header',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfExt' => ['type' => 'integer', 'location' => 'query'],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                ],
                'getQrExtensionStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr-extensions/{qrExtensionUUID}/status',
                    'summary' => 'Get status of QR code extension',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                ],
                #endregion

                #region Reconciliation Operations
                'getReconciliationTransactions' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/reconciliation/transactions',
                    'summary' => 'Transaction list for reconciliation',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'ReconciliationTransactionsDto', 'query')),
                ],
                #endregion

                #region Signal Operations
                'getSignal' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/signal/{qrExtensionUUID}',
                    'responseModel' => 'getResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                #endregion

                #region Transaction Operations
                'reverseTransaction' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/transaction/{id}',
                    'summary' => 'Reverse already processed transaction',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                #endregion

                #region Demo Payment Operations
                'demoPay' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/api/pay',
                    'summary' => 'Demo Pay (Test)',
                    'responseModel' => 'getResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'DemoPayDto')),
                ],
                #endregion
            ],

            'models' => $models
        ];

        parent::__construct($description, $options);
    }

    /**
     * Get property definitions from a model and inject a specific location.
     */
    private static function getProperties(array $models, string $modelName, string $location = 'json'): array
    {
        $props = $models[$modelName]['properties'] ?? [];
        foreach ($props as &$prop) {
            $prop['location'] = $location;
        }
        return $props;
    }
}
