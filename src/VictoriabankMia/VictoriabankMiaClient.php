<?php

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Victoriabank MIA API client
 * @link https://test-ipspj.victoriabank.md
 * @link https://test-ipspj-demopay.victoriabank.md/swagger/
 */
class VictoriabankMiaClient extends GuzzleClient
{
    const DEFAULT_BASE_URL = 'https://ips-api-pj.vb.md/';
    const TEST_BASE_URL = 'https://test-ipspj.victoriabank.md/';
    const TEST_DEMOPAY_URL = 'https://test-ipspj-demopay.victoriabank.md/api/pay/';

    /**
     * @param ClientInterface      $client
     * @param DescriptionInterface $description
     * @param array                $config
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?DescriptionInterface $description = null,
        array $config = []
    ) {
        $client = $client instanceof ClientInterface ? $client : new Client();
        $description = $description instanceof DescriptionInterface ? $description : new VictoriabankMiaDescription();
        parent::__construct($client, $description, null, null, null, $config);
    }

    /**
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Health-get_api_v1_health_status
     */
    public function getHealthStatus()
    {
        return parent::getHealthStatus();
    }

    /**
     * Get tokens.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Token-post_identity_token
     * @param string $grant_type
     * @param string $username
     * @param string $password
     * @param string $refresh_token
     */
    public function getToken($grant_type, $username, $password, $refresh_token = null)
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
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr
     * @param array  $qrData
     * @param string $authToken
     * @param int    $width
     * @param int    $height
     */
    public function createPayeeQr($qrData, $authToken, $width = null, $height = null)
    {
        $args = $qrData;

        $args['width'] = $width;
        $args['height'] = $height;

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQr($args);
    }

    /**
     * Register new extension for HYBR or STAT payee-presented QR code.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-post_api_v1_qr__qrHeaderUUID__extentions
     * @param string $qrHeaderUUID
     * @param array  $extensionData
     * @param string $authToken
     */
    public function createPayeeQrExtension($qrHeaderUUID, $extensionData, $authToken)
    {
        $args = $extensionData;

        $args['qrHeaderUUID'] = $qrHeaderUUID;

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQrExtension($args);
    }

    /**
     * Cancel payee-resented QR code, including active extension, if exists.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID_
     * @param string $qrHeaderUUID
     * @param string $authToken
     */
    public function cancelPayeeQr($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelPayeeQr($args);
    }

    /**
     * Cancel active extension of hybrid payee-presented QR code.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-delete_api_v1_qr__qrHeaderUUID__active_extension
     * @param string $qrHeaderUUID
     * @param string $authToken
     */
    public function cancelHybrExtension($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelHybrExtension($args);
    }

    /**
     * Get status of payee-presented QR code header, statuses of N last extensions and list of M last payments against each extension.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr__qrHeaderUUID__status
     * @param string $qrHeaderUUID
     * @param string $authToken
     * @param int    $nbOfExt
     * @param int    $nbOfTxs
     */
    public function getPayeeQrStatus($qrHeaderUUID, $authToken, $nbOfExt = null, $nbOfTxs = null)
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
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Qr-get_api_v1_qr_extensions__qrExtensionUUID__status
     * @param string $qrExtensionUUID
     * @param string $authToken
     * @param int    $nbOfTxs
     */
    public function getQrExtensionStatus($qrExtensionUUID, $authToken, $nbOfTxs = null)
    {
        $args = [
            'qrHeaderUUID' => $qrExtensionUUID,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getQrExtensionStatus($args);
    }

    /**
     * Transaction list for reconciliation.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Reconciliation-get_api_v1_reconciliation_transactions
     * @param string $authToken
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $messageId
     */
    public function getReconciliationTransactions($authToken, $dateFrom = null, $dateTo = null, $messageId = null)
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
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Signal-get_api_v1_signal__qrExtensionUUID_
     * @param string $qrExtensionUUID
     * @param string $authToken
     */
    public function getSignal($qrExtensionUUID, $authToken)
    {
        $args = [
            'qrExtensionUUID' => $qrExtensionUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getSignal($args);
    }

    /**
     * Reverse already processed transaction.
     * @link https://test-ipspj.victoriabank.md/index.html#operations-Transaction-delete_api_v1_transaction__id_
     * @param string $id
     * @param string $authToken
     */
    public function reverseTransaction($id, $authToken)
    {
        $args = [
            'id' => $id,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::reverseTransaction($args);
    }

    /**
     * @param array  $args
     * @param string $authToken
     */
    private static function setBearerAuthToken(&$args, $authToken)
    {
        $args['authToken'] = "Bearer $authToken";
    }

    /**
     * Decode and validate the callback data signature.
     * @param string $callbackJwt
     * @param string $certificate
     */
    public static function decodeValidateCallback($callbackJwt, $certificate)
    {
        $algorithm = 'RS256';
        $publicKey = openssl_pkey_get_public($certificate);
        $decoded_payload = JWT::decode($callbackJwt, new Key($publicKey, $algorithm));

        return $decoded_payload;
    }

    /**
     * Extract payment transaction ID from payment reference string.
     * @param string $paymentReference
     */
    public static function getPaymentTransactionId($paymentReference)
    {
        //NOTE: Victoriabank MIA API provides only a composed reference string that needs to be parsed
        $transactionComponents = explode('|', $paymentReference);
        $transactionId = $transactionComponents[3];

        return $transactionId;
    }

    /**
     * Extract payment RRN (Retrieval Reference Number) from payment reference string.
     * @param string $paymentReference
     */
    public static function getPaymentRrn($paymentReference)
    {
        //NOTE: Victoriabank MIA API provides only a composed transaction string that needs to be parsed
        $transactionId = self::getPaymentTransactionId($paymentReference);
        $paymentRrn = strlen($transactionId) > 12
            ? substr($transactionId, -12)
            : $transactionId;

        return $paymentRrn;
    }
}
