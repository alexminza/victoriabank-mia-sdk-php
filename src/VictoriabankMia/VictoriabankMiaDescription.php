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
    private const PACKAGE_NAME    = 'alexminza/victoriabank-mia-sdk';
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
        $version   = self::detectVersion();
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
                'additionalProperties' => ['location' => 'formParam'],
                'properties' => [
                    'grant_type' => ['type' => 'string'],
                    'username' => ['type' => 'string'],
                    'password' => ['type' => 'string'],
                    'refresh_token' => ['type' => 'string'],
                ],
            ],
            'CreatePayeeQrResponse' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'qrHeaderUUID' => ['type' => 'string', 'location' => 'json'],
                    'qrExtensionUUID' => ['type' => 'string', 'location' => 'json'],
                    'qrAsText' => ['type' => 'string', 'location' => 'json'],
                    'qrAsImage' => ['type' => 'string', 'format' => 'byte', 'location' => 'json'],
                ],
            ],
            'MoneyDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'sum' => ['type' => 'number', 'required' => true, 'location' => 'json'],
                    'currency' => ['type' => 'string', 'required' => true, 'location' => 'json'],
                ],
            ],
            'PayeeAccountDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'iban' => ['type' => 'string', 'location' => 'json'],
                ],
            ],
            'PayeeQrExtensionStatusDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'uuid' => ['type' => 'string', 'location' => 'json'],
                    'isLast' => ['type' => 'boolean', 'location' => 'json'],
                    'status' => ['type' => 'string', 'enum' => ['None', 'Active', 'Paid', 'Expired', 'Cancelled', 'Replaced', 'Inactive'], 'location' => 'json'],
                    'statusDtTm' => ['type' => 'string', 'format' => 'date-time', 'location' => 'json'],
                    'isHeaderLocked' => ['type' => 'boolean', 'location' => 'json'],
                    'ttl' => ['$ref' => 'TtlDto', 'location' => 'json'],
                    'payments' => ['type' => 'array', 'items' => ['$ref' => 'PaymentOutDto'], 'location' => 'json'],
                ],
            ],
            'PayeeQrStatusDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'uuid' => ['type' => 'string', 'location' => 'json'],
                    'status' => ['type' => 'string', 'enum' => ['None', 'Active', 'Paid', 'Expired', 'Cancelled', 'Replaced', 'Inactive'], 'location' => 'json'],
                    'statusDtTm' => ['type' => 'string', 'format' => 'date-time', 'location' => 'json'],
                    'lockTtl' => ['$ref' => 'TtlDto', 'location' => 'json'],
                    'extensions' => ['type' => 'array', 'items' => ['$ref' => 'PayeeQrExtensionStatusDto'], 'location' => 'json'],
                ],
            ],
            'PaymentOutDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'system' => ['type' => 'string', 'location' => 'json'],
                    'reference' => ['type' => 'string', 'location' => 'json'],
                    'amount' => ['$ref' => 'MoneyDto', 'location' => 'json'],
                ],
            ],
            'SignalDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'signalCode' => ['type' => 'string', 'location' => 'json'],
                    'signalDtTm' => ['type' => 'string', 'format' => 'date-time', 'location' => 'json'],
                    'qrHeaderUUID' => ['type' => 'string', 'location' => 'json'],
                    'qrExtensionUUID' => ['type' => 'string', 'location' => 'json'],
                    'payment' => ['$ref' => 'PaymentOutDto', 'location' => 'json'],
                ],
            ],
            'TransactionInfo' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'id' => ['type' => 'string', 'location' => 'json'],
                    'date' => ['type' => 'string', 'location' => 'json'],
                    'time' => ['type' => 'string', 'location' => 'json'],
                    'payerName' => ['type' => 'string', 'location' => 'json'],
                    'payerIdnp' => ['type' => 'string', 'location' => 'json'],
                    'beneficiaryIdnp' => ['type' => 'string', 'location' => 'json'],
                    'transactionType' => ['type' => 'string', 'location' => 'json'],
                    'transactionAmount' => ['type' => 'number', 'location' => 'json'],
                    'transactionStatus' => ['type' => 'string', 'location' => 'json'],
                    'destinationBankName' => ['type' => 'string', 'location' => 'json'],
                    'transactionMessage' => ['type' => 'string', 'location' => 'json'],
                    'paymentType' => ['type' => 'string', 'location' => 'json'],
                    'miaId' => ['type' => 'string', 'location' => 'json'],
                    'creditorRef' => ['type' => 'string', 'location' => 'json'],
                    'iban' => ['type' => 'string', 'location' => 'json'],
                ],
            ],
            'TransactionListDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'transactionsInfo' => ['type' => 'array', 'items' => ['$ref' => 'TransactionInfo'], 'location' => 'json'],
                ],
            ],
            'TtlDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'length' => ['type' => 'integer', 'location' => 'json'],
                    'units' => ['type' => 'string', 'enum' => ['ss', 'mm'], 'location' => 'json'],
                ],
            ],
            'VbPayeeQrDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'header' => ['$ref' => 'VbPayeeQrHeaderDto'],
                    'extension' => ['$ref' => 'VbPayeeQrExtensionDto'],
                ],
            ],
            'VbPayeeQrExtensionDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'creditorAccount' => ['$ref' => 'PayeeAccountDto'],
                    'amount' => ['$ref' => 'MoneyDto'],
                    'amountMin' => ['$ref' => 'MoneyDto'],
                    'amountMax' => ['$ref' => 'MoneyDto'],
                    'dba' => ['type' => 'string'],
                    'remittanceInfo4Payer' => ['type' => 'string'],
                    'creditorRef' => ['type' => 'string'],
                    'ttl' => ['$ref' => 'TtlDto'],
                ],
            ],
            'VbPayeeQrHeaderDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'qrType' => ['type' => 'string', 'enum' => ['DYNM', 'STAT', 'HYBR']],
                    'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free']],
                    'pmtContext' => ['type' => 'string'],
                ],
            ],
            'DemoPayDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
                'properties' => [
                    'qrHeaderUUID' => ['type' => 'string', 'required' => true],
                ],
            ],
            'ReconciliationTransactionsDto' => [
                'type' => 'object',
                'additionalProperties' => ['location' => 'json'],
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
                    'additionalParameters' => ['location' => 'query'],
                ],
                #endregion

                #region Authentication Operations
                'getToken' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/identity/token',
                    'summary' => 'Get tokens',
                    'responseModel' => 'getResponse',
                    'parameters' => self::getProperties($models, 'AuthTokenDto', 'formParam'),
                    'additionalParameters' => ['location' => 'formParam'],
                ],
                #endregion

                #region QR Operations
                'createPayeeQr' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'POST',
                    'uri' => '/api/v1/qr',
                    'summary' => 'CreatePayeeQr - Register new payee-presented QR code',
                    'responseModel' => 'CreatePayeeQrResponse',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                        'width' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image width (Default: 300)'],
                        'height' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image height (Default: 300)'],
                    ], self::getProperties($models, 'VbPayeeQrDto')),
                    'additionalParameters' => ['location' => 'json'],
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
                    'additionalParameters' => ['location' => 'json'],
                ],
                'cancelPayeeQr' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}',
                    'summary' => 'CancelPayeeQr-Cancel payee-resented QR code, including active extension, if exists',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                'cancelHybrExtension' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/active-extension',
                    'summary' => 'CancelHybrExtention - Cancel active extension of hybrid payee-presented QR code',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                'getPayeeQrStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/status',
                    'summary' => 'getPayeeQrStatus - Get status of payee-presented QR code header, statuses of N last extensions and list of M last payments against each extension',
                    'responseModel' => 'PayeeQrStatusDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfExt' => ['type' => 'integer', 'location' => 'query'],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                'getQrExtensionStatus' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr-extensions/{qrExtensionUUID}/status',
                    'summary' => 'getQrExtensionStatus - Get status of QR code extension and list of last N payments against it',
                    'responseModel' => 'PayeeQrExtensionStatusDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                #endregion

                #region Transaction Operations
                'getReconciliationTransactions' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/reconciliation/transactions',
                    'summary' => 'Transaction list for reconciliation',
                    'responseModel' => 'TransactionListDto',
                    'parameters' => array_merge([
                        'authToken' => $authorizationHeader,
                    ], self::getProperties($models, 'ReconciliationTransactionsDto', 'query')),
                    'additionalParameters' => ['location' => 'query'],
                ],

                'reverseTransaction' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/transaction/{id}',
                    'summary' => 'Reverse already processed transaction',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
                ],
                #endregion

                #region Signal Operations
                'getSignal' => [
                    'extends' => 'baseOp',
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/signal/{qrExtensionUUID}',
                    'responseModel' => 'SignalDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                    'additionalParameters' => ['location' => 'query'],
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
                    'additionalParameters' => ['location' => 'json'],
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
        $props  = $models[$modelName]['properties'] ?? [];
        $result = [];

        foreach ($props as $name => $prop) {
            $prop['location'] = $location;
            $result[$name]    = $prop;
        }

        return $result;
    }
}
