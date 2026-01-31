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
    public const TEST_BASE_URL    = 'https://test-ipspj.victoriabank.md/';
    public const TEST_DEMOPAY_URL = 'https://test-ipspj-demopay.victoriabank.md/';

    public function __construct(?ClientInterface $client = null, ?DescriptionInterface $description = null, array $config = [])
    {
        $client      = $client ?? new Client();
        $description = $description ?? new VictoriabankMiaDescription();

        parent::__construct($client, $description, null, null, null, $config);
    }

    #region Health
    /**
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Health-get_api_v1_health_status
     */
    public function getHealthStatus(): Result
    {
        return parent::getHealthStatus();
    }
    #endregion

    #region Authentication
    /**
     * Get tokens.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Token-post_identity_token
     */
    public function getToken(string $grant_type, string $username, string $password, ?string $refresh_token = null): Result
    {
        $getTokenData = [
            'grant_type' => $grant_type,
            'username' => $username,
            'password' => $password,
            'refresh_token' => $refresh_token
        ];

        return parent::getToken($getTokenData);
    }
    #endregion

    #region QR
    /**
     * Register new payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr
     */
    public function createPayeeQr(array $qrData, string $authToken, ?int $width = null, ?int $height = null): Result
    {
        $qrData['width']  = $width;
        $qrData['height'] = $height;

        self::setBearerAuthToken($qrData, $authToken);
        return parent::createPayeeQr($qrData);
    }

    /**
     * Register new extension for HYBR or STAT payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr__qrHeaderUUID__extentions
     */
    public function createPayeeQrExtension(string $qrHeaderUUID, array $extensionData, string $authToken): Result
    {
        $extensionData['qrHeaderUUID'] = $qrHeaderUUID;

        self::setBearerAuthToken($extensionData, $authToken);
        return parent::createPayeeQrExtension($extensionData);
    }

    /**
     * Cancel payee-resented QR code, including active extension, if exists.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID_
     */
    public function cancelPayeeQr(string $qrHeaderUUID, string $authToken): Result
    {
        $cancelPayeeQrData = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($cancelPayeeQrData, $authToken);
        return parent::cancelPayeeQr($cancelPayeeQrData);
    }

    /**
     * Cancel active extension of hybrid payee-presented QR code.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID__active_extension
     */
    public function cancelHybrExtension(string $qrHeaderUUID, string $authToken): Result
    {
        $cancelHybrExtensionData = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($cancelHybrExtensionData, $authToken);
        return parent::cancelHybrExtension($cancelHybrExtensionData);
    }

    /**
     * Get status of payee-presented QR code header, statuses of N last extensions and list of M last payments against each extension.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr__qrHeaderUUID__status
     */
    public function getPayeeQrStatus(string $qrHeaderUUID, string $authToken, ?int $nbOfExt = null, ?int $nbOfTxs = null): Result
    {
        $getPayeeQrStatusData = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'nbOfExt' => $nbOfExt,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($getPayeeQrStatusData, $authToken);
        return parent::getPayeeQrStatus($getPayeeQrStatusData);
    }

    /**
     * Get status of QR code extension and list of last N payments against it.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr_extensions__qrExtensionUUID__status
     */
    public function getQrExtensionStatus(string $qrExtensionUUID, string $authToken, ?int $nbOfTxs = null): Result
    {
        $getQrExtensionStatusData = [
            'qrExtensionUUID' => $qrExtensionUUID,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($getQrExtensionStatusData, $authToken);
        return parent::getQrExtensionStatus($getQrExtensionStatusData);
    }
    #endregion

    #region Transaction
    /**
     * Transaction list for reconciliation.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Reconciliation-get_api_v1_reconciliation_transactions
     */
    public function getReconciliationTransactions(string $authToken, ?string $dateFrom = null, ?string $dateTo = null, ?string $messageId = null): Result
    {
        $getReconciliationTransactionsData = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'messageId' => $messageId,
        ];

        self::setBearerAuthToken($getReconciliationTransactionsData, $authToken);
        return parent::getReconciliationTransactions($getReconciliationTransactionsData);
    }

    /**
     * Reverse already processed transaction.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Transaction-delete_api_v1_transaction__id_
     */
    public function reverseTransaction(string $id, string $authToken): Result
    {
        $reverseTransactionData = [
            'id' => $id,
        ];

        self::setBearerAuthToken($reverseTransactionData, $authToken);
        return parent::reverseTransaction($reverseTransactionData);
    }
    #endregion

    #region Signal
    /**
     * Get Last Signal by QR Extension UUID.
     *
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Signal-get_api_v1_signal__qrExtensionUUID_
     */
    public function getSignal(string $qrExtensionUUID, string $authToken): Result
    {
        $getSignalData = [
            'qrExtensionUUID' => $qrExtensionUUID,
        ];

        self::setBearerAuthToken($getSignalData, $authToken);
        return parent::getSignal($getSignalData);
    }
    #endregion

    #region Demo Payment
    /**
     * Demo Pay (Test)
     *
     * @link https://test-ipspj-demopay.victoriabank.md/swagger/index.html#operations-Pay-post_api_Pay
     */
    public function demoPay(string $qrHeaderUUID, string $authToken): Result
    {
        $demoPayData = [
            'qrHeaderUUID' => $qrHeaderUUID,
            '@http' => [
                'base_uri' => self::TEST_DEMOPAY_URL
            ]
        ];

        self::setBearerAuthToken($demoPayData, $authToken);
        return parent::demoPay($demoPayData);
    }
    #endregion

    #region Signature
    /**
     * Decode and validate the callback data signature.
     *
     * @see Firebase\JWT\JWT::decode
     */
    public static function decodeValidateCallback(string $callbackJwt, string $certificate)
    {
        $key = new Key($certificate, 'RS256');
        return JWT::decode($callbackJwt, $key);
    }
    #endregion

    #region Utility
    /**
     * Extract payment transaction ID from payment reference string.
     * Victoriabank MIA API provides only a composed reference string that needs to be parsed.
     */
    public static function getPaymentTransactionId(string $paymentReference)
    {
        $transactionComponents = explode('|', $paymentReference);

        $transactionId = count($transactionComponents) >= 4
            ? $transactionComponents[3]
            : null;

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

    private static function setBearerAuthToken(array &$args, string $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }
    #endregion
}
