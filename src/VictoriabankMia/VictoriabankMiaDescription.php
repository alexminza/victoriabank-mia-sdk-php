<?php

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Command\Guzzle\Description;

class VictoriabankMiaDescription extends Description
{
    public function __construct(array $options = [])
    {
        $authorizationHeader = [
            'type' => 'string',
            'location' => 'header',
            'sentAs' => 'Authorization',
            'description' => 'Bearer Authentication with JWT Token',
            'required' => true,
        ];

        $description = [
            'baseUrl' => 'https://ips-api-pj.vb.md/',
            'name' => 'IPS Business WebApi',
            'version' => 'v1.0',

            'operations' => [
                // Health Operations
                'getHealthStatus' => [
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/health/status',
                ],

                // Token Operations
                'getToken' => [
                    'httpMethod' => 'POST',
                    'uri' => '/identity/token',
                    'summary' => 'Get tokens',
                    'parameters' => [
                        'grant_type' => ['type' => 'string', 'location' => 'formParam'],
                        'username' => ['type' => 'string', 'location' => 'formParam'],
                        'password' => ['type' => 'string', 'location' => 'formParam'],
                        'refresh_token' => ['type' => 'string', 'location' => 'formParam'],
                    ],
                ],

                // QR Operations
                'createPayeeQr' => [
                    'httpMethod' => 'POST',
                    'uri' => '/api/v1/qr',
                    'summary' => 'CreatePayeeQr - Register new payee-presented QR code',
                    'responseModel' => 'CreatePayeeQrResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'width' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image width (Default: 300)'],
                        'height' => ['type' => 'integer', 'location' => 'query', 'description' => 'QR code image height (Default: 300)'],
                        'qrData' => ['location' => 'json', 'schema' => ['$ref' => 'VbPayeeQrDto']],
                    ],
                ],
                'createPayeeQrExtension' => [
                    'httpMethod' => 'POST',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/extentions',
                    'summary' => 'CreatePayeeQrExtention - Register new extension for HYBR or STAT payee-presented QR code',
                    'responseModel' => 'StringResponse',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'extensionData' => ['location' => 'json', 'schema' => ['$ref' => 'VbPayeeQrExtensionDto']],
                    ],
                ],
                'cancelPayeeQr' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}',
                    'summary' => 'CancelPayeeQr-Cancel payee-resented QR code',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'cancelHybrExtension' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/active-extension',
                    'summary' => 'Cancel active extension of hybrid payee-presented QR code',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
                'getPayeeQrStatus' => [
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr/{qrHeaderUUID}/status',
                    'summary' => 'Get status of payee-presented QR code header',
                    'responseModel' => 'PayeeQrStatusDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrHeaderUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfExt' => ['type' => 'integer', 'location' => 'query'],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                ],
                'getQrExtensionStatus' => [
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/qr-extensions/{qrExtensionUUID}/status',
                    'summary' => 'Get status of QR code extension',
                    'responseModel' => 'PayeeQrExtensionStatusDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                        'nbOfTxs' => ['type' => 'integer', 'location' => 'query'],
                    ],
                ],

                // Reconciliation Operations
                'getReconciliationTransactions' => [
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/reconciliation/transactions',
                    'summary' => 'Transaction list for reconciliation',
                    'responseModel' => 'TransactionListDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'dateFrom' => ['type' => 'string', 'format' => 'date-time', 'location' => 'query'],
                        'dateTo' => ['type' => 'string', 'format' => 'date-time', 'location' => 'query'],
                        'messageId' => ['type' => 'string', 'location' => 'query'],
                    ],
                ],

                // Signal Operations
                'getSignal' => [
                    'httpMethod' => 'GET',
                    'uri' => '/api/v1/signal/{qrExtensionUUID}',
                    'responseModel' => 'SignalDto',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'qrExtensionUUID' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],

                // Transaction Operations
                'reverseTransaction' => [
                    'httpMethod' => 'DELETE',
                    'uri' => '/api/v1/transaction/{id}',
                    'summary' => 'Reverse already processed transaction',
                    'parameters' => [
                        'authToken' => $authorizationHeader,
                        'id' => ['type' => 'string', 'location' => 'uri', 'required' => true],
                    ],
                ],
            ],

            'models' => [
                // Generic Models
                'StringResponse' => ['type' => 'object', 'properties' => ['result' => ['type' => 'string', 'location' => 'json']]],

                // Schema-based Models
                'CreatePayeeQrResponse' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'qrHeaderUUID' => ['type' => ['string', 'null']],
                        'qrExtensionUUID' => ['type' => ['string', 'null']],
                        'qrAsText' => ['type' => ['string', 'null']],
                        'qrAsImage' => ['type' => ['string', 'null'], 'format' => 'byte'],
                    ],
                ],
                'MoneyDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'sum' => ['type' => 'number'],
                        'currency' => ['type' => ['string', 'null']],
                    ],
                ],
                'PayeeAccountDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'iban' => ['type' => ['string', 'null']],
                    ],
                ],
                'PayeeQrExtensionStatusDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
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
                    'additionalProperties' => false,
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
                    'additionalProperties' => false,
                    'properties' => [
                        'system' => ['type' => ['string', 'null']],
                        'reference' => ['type' => ['string', 'null']],
                        'amount' => ['$ref' => 'MoneyDto'],
                    ],
                ],
                'SignalDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
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
                    'additionalProperties' => false,
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
                    'additionalProperties' => false,
                    'properties' => [
                        'transactionsInfo' => ['type' => ['array', 'null'], 'items' => ['$ref' => 'TransactionInfo']],
                    ],
                ],
                'TtlDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'length' => ['type' => 'integer'],
                        'units' => ['type' => ['string', 'null'], 'pattern' => '^(ss|mm)$'],
                    ],
                ],
                'VbPayeeQrDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'header' => ['$ref' => 'VbPayeeQrHeaderDto'],
                        'extension' => ['$ref' => 'VbPayeeQrExtensionDto'],
                    ],
                ],
                'VbPayeeQrExtensionDto' => [
                    'type' => 'object',
                    'additionalProperties' => false,
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
                    'additionalProperties' => false,
                    'properties' => [
                        'qrType' => ['type' => 'string', 'enum' => ['DYNM', 'STAT', 'HYBR']],
                        'amountType' => ['type' => 'string', 'enum' => ['Fixed', 'Controlled', 'Free']],
                        'pmtContext' => ['type' => ['string', 'null']],
                    ],
                ],
            ],
        ];

        parent::__construct($description, $options);
    }
}
