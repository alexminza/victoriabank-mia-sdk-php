<?php

namespace Victoriabank\VictoriabankMia;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Exception\BadResponseException;

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
        $args = [
            'qrData' => $qrData,
            'width' => $width,
            'height' => $height,
            'authToken' => $authToken
        ];

        return parent::createPayeeQr($args);
    }

    public function createPayeeQrExtension($qrHeaderUUID, $extensionData, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'extensionData' => $extensionData,
            'authToken' => $authToken
        ];

        return parent::createPayeeQrExtension($args);
    }

    public function cancelPayeeQr($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'authToken' => $authToken
        ];

        return parent::cancelPayeeQr($args);
    }

    public function cancelHybrExtension($qrHeaderUUID, $authToken)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'authToken' => $authToken
        ];

        return parent::cancelHybrExtension($args);
    }

    public function getPayeeQrStatus($qrHeaderUUID, $authToken, $nbOfExt = null, $nbOfTxs = null)
    {
        $args = [
            'qrHeaderUUID' => $qrHeaderUUID,
            'nbOfExt' => $nbOfExt,
            'nbOfTxs' => $nbOfTxs,
            'authToken' => $authToken
        ];

        return parent::getPayeeQrStatus($args);
    }

    public function getQrExtensionStatus($qrExtensionUUID, $authToken, $nbOfTxs = null)
    {
        $args = [
            'qrHeaderUUID' => $qrExtensionUUID,
            'nbOfTxs' => $nbOfTxs,
            'authToken' => $authToken
        ];

        return parent::getQrExtensionStatus($args);
    }

    public function getReconciliationTransactions($authToken, $dateFrom = null, $dateTo = null, $messageId = null)
    {
        $args = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'messageId' => $messageId,
            'authToken' => $authToken
        ];

        return parent::getReconciliationTransactions($args);
    }

    public function getSignal($qrExtensionUUID, $authToken)
    {
        $args = [
            'qrExtensionUUID' => $qrExtensionUUID,
            'authToken' => $authToken
        ];

        return parent::getSignal($args);
    }

    public function reverseTransaction($id, $authToken)
    {
        $args = [
            'id' => $id,
            'authToken' => $authToken
        ];

        return parent::reverseTransaction($args);
    }
}
