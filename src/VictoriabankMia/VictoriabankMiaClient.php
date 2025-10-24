<?php

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Exception\BadResponseException;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    public function getHealthStatus()
    {
        return parent::getHealthStatus();
    }

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

    public function createPayeeQr($qrData, $authToken, $width = null, $height = null)
    {
        $args = $qrData;

        $args['width'] = $width;
        $args['height'] = $height;

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQr($args);
    }

    public function createPayeeQrExtension($qrHeaderUUID, $extensionData, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'extensionData' => $extensionData,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::createPayeeQrExtension($args);
    }

    public function cancelPayeeQr($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelPayeeQr($args);
    }

    public function cancelHybrExtension($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::cancelHybrExtension($args);
    }

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

    public function getQrExtensionStatus($qrExtensionUUID, $authToken, $nbOfTxs = null)
    {
        $args = [
            'qrHeaderUUID' => $qrExtensionUUID,
            'nbOfTxs' => $nbOfTxs,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getQrExtensionStatus($args);
    }

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

    public function getSignal($qrExtensionUUID, $authToken)
    {
        $args = [
            'qrExtensionUUID' => $qrExtensionUUID,
        ];

        self::setBearerAuthToken($args, $authToken);
        return parent::getSignal($args);
    }

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
     * Decode callback payload and verify signature.
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
     * Extract payment RRN (Retrieval Reference Number).
     * @param string $paymentReference
     */
    public static function getPaymentRrn($paymentReference)
    {
        //NOTE: Victoriabank MIA API provides only a composed transaction string that needs to be parsed
        $transactionId = self::getPaymentTransactionId($paymentReference);
        $paymentRrn = strlen($transactionId) < 12
            ? $transactionId
            : substr($transactionId, -12);

        return $paymentRrn;
    }
}
