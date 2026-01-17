<?php

declare(strict_types=1);

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Victoriabank MIA API client
 *
 * @link https://test-ipspj.victoriabank.md
 * @link https://test-ipspj-demopay.victoriabank.md/swagger/
 */
class VictoriabankMiaClient extends GuzzleClient
{
    public const DEFAULT_BASE_URL = 'https://ips-api-pj.vb.md/';
    public const TEST_BASE_URL = 'https://test-ipspj.victoriabank.md/';
    public const TEST_DEMOPAY_URL = 'https://test-ipspj-demopay.victoriabank.md/';

    public function __construct(?ClientInterface $client = null, ?DescriptionInterface $description = null, array $config = [])
    {
        $client = $client ?? new Client();
        $description = $description ?? new VictoriabankMiaDescription();
        parent::__construct($client, $description, null, null, null, $config);
    }

    /**
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Health-get_api_v1_health_status
     */
    public function getHealthStatus(): Result
    {
        return parent::getHealthStatus();
    }

    /**
     * Get tokens.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Token-post_identity_token
     */
    public function getToken(string $grant_type, string $username, string $password, ?string $refresh_token = null): Result
    {
        $args = [
            'grant_type' => $grant_type,
            'username' => $username,
            'password' => $password,
            'refresh_token' => $refresh_token
        ];

        return parent::getToken($args);
    }

    /**
     * Register new payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr
     */
    public function createPayeeQr(array $qrData, string $authToken, ?int $width = null, ?int $height = null): Result
    {
        $args = $qrData;
        $args['width'] = $width;
        $args['height'] = $height;

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQr($args);
    }

    /**
     * Register new extension for HYBR or STAT payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr__qrHeaderUUID__extentions
     */
    public function createPayeeQrExtension(string $qrHeaderUUID, array $extensionData, string $authToken): Result
    {
        $args = $extensionData;
        $args['qrHeaderUUID'] = $qrHeaderUUID;

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQrExtension($args);
    }

    /**
     * Cancel payee-resented QR code, including active extension, if exists.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID_
     */
    public function cancelPayeeQr(string $qrHeaderUUID, string $authToken): Result
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelPayeeQr($args);
    }

    /**
     * Cancel active extension of hybrid payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID__active_extension
     */
    public function cancelHybrExtension(string $qrHeaderUUID, string $authToken): Result
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelHybrExtension($args);
    }

    /**
     * Get status of payee-presented QR code header, statuses of N last extensions and list of M last payments against each extension.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr__qrHeaderUUID__status
     */
    public function getPayeeQrStatus(string $qrHeaderUUID, string $authToken, ?int $nbOfExt = null, ?int $nbOfTxs = null): Result
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'nbOfExt' => $nbOfExt,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getPayeeQrStatus($args);
    }

    /**
     * Get status of QR code extension and list of last N payments against it.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr_extensions__qrExtensionUUID__status
     */
    public function getQrExtensionStatus(string $qrExtensionUUID, string $authToken, ?int $nbOfTxs = null): Result
    {
        $args = [
            'qrExtensionUUID' => $qrExtensionUUID,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getQrExtensionStatus($args);
    }

    /**
     * Transaction list for reconciliation.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Reconciliation-get_api_v1_reconciliation_transactions
     */
    public function getReconciliationTransactions(string $authToken, ?string $dateFrom = null, ?string $dateTo = null, ?string $messageId = null): Result
    {
        $args = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'messageId' => $messageId,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getReconciliationTransactions($args);
    }

    /**
     * Get Last Signal by QR Extension UUID.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Signal-get_api_v1_signal__qrExtensionUUID_
     */
    public function getSignal(string $qrExtensionUUID, string $authToken): Result
    {
        $args = [
            'qrExtensionUUID' => $qrExtensionUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getSignal($args);
    }

    /**
     * Reverse already processed transaction.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Transaction-delete_api_v1_transaction__id_
     */
    public function reverseTransaction(string $id, string $authToken): Result
    {
        $args = [
            'id' => $id,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::reverseTransaction($args);
    }

    /**
     * Demo Pay (Test)
     *
     * @link https://test-ipspj-demopay.victoriabank.md/swagger/index.html#operations-Pay-post_api_Pay
     */
    public function demoPay(string $qrHeaderUUID, string $authToken): Result
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            '@http' => [
                'base_uri' => self::TEST_DEMOPAY_URL
            ]
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::demoPay($args);
    }

    private static function setBearerAuthToken(array &$args, string $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }

    /**
     * Decode and validate the callback data signature.
     *
     * @see Firebase\JWT\JWT::decode
     */
    public static function decodeValidateCallback(string $callbackJwt, string $certificate)
    {
        $algorithm = 'RS256';
        $publicKey = openssl_pkey_get_public($certificate);
        $decoded_payload = JWT::decode($callbackJwt, new Key($publicKey, $algorithm));

        return $decoded_payload;
    }

    /**
     * Extract payment transaction ID from payment reference string.
     * Victoriabank MIA API provides only a composed reference string that needs to be parsed.
     */
    public static function getPaymentTransactionId(string $paymentReference): string
    {
        $transactionComponents = explode('|', $paymentReference);
        $transactionId = $transactionComponents[3];

        return $transactionId;
    }

    /**
     * Extract payment RRN (Retrieval Reference Number) from payment reference string.
     * Victoriabank MIA API provides only a composed transaction string that needs to be parsed.
     */
    public static function getPaymentRrn(string $paymentReference): string
    {
        $transactionId = self::getPaymentTransactionId($paymentReference);
        $paymentRrn = strlen($transactionId) > 12
            ? substr($transactionId, -12)
            : $transactionId;

        return $paymentRrn;
    }
}
