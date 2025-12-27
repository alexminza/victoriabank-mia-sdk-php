<?php

namespace Victoriabank\VictoriabankMia\Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Victoriabank\VictoriabankMia\VictoriabankMiaClient;

/**
 * @group integration
 */
class VictoriabankMiaIntegrationTest extends TestCase
{
    protected static $username;
    protected static $password;
    protected static $iban;
    protected static $publicKey;
    protected static $baseUrl;

    // Shared state
    protected static $accessToken;
    protected static $qrHeaderUUID;
    protected static $qrExtensionUUID;
    protected static $newQrExtensionUUID;

    /**
     * @var VictoriabankMiaClient
     */
    protected $client;

    public static function setUpBeforeClass(): void
    {
        self::$username  = getenv('VICTORIABANK_MIA_USERNAME');
        self::$password  = getenv('VICTORIABANK_MIA_PASSWORD');
        self::$iban      = getenv('VICTORIABANK_IBAN');
        self::$publicKey = getenv('VICTORIABANK_PUBLIC_KEY');
        self::$baseUrl   = VictoriabankMiaClient::TEST_BASE_URL;

        if (!self::$username || !self::$password || !self::$iban || !self::$publicKey) {
            self::markTestSkipped('Integration test credentials not provided.');
        }
    }

    protected function setUp(): void
    {
        $this->client = new VictoriabankMiaClient(new Client(['base_uri' => self::$baseUrl]));
    }

    protected function debugLog($message, $data)
    {
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken('password', self::$username, self::$password);
        $this->debugLog('getToken', $response);

        $this->assertArrayHasKey('accessToken', $response);
        $this->assertNotEmpty($response['accessToken']);

        self::$accessToken = $response['accessToken'];
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreatePayeeQr()
    {
        $qrData = [
            'header' => [
                'qrType' => 'DYNM',
                'amountType' => 'Fixed',
                'pmtContext' => 'e'
            ],
            'extension' => [
                'creditorAccount' => [
                    'iban' => self::$iban
                ],
                'amount' => [
                    'sum' => 10.00,
                    'currency' => 'MDL'
                ],
                'dba' => 'Test Merchant',
                'remittanceInfo4Payer' => 'Order #123',
                'creditorRef' => '123',
                'ttl' => [
                    'length' => 60,
                    'units' => 'mm'
                ]
            ]
        ];

        $response = $this->client->createPayeeQr($qrData, self::$accessToken);
        $this->debugLog('createPayeeQr', $response);

        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertNotEmpty($response['qrHeaderUUID']);
        $this->assertNotEmpty($response['qrExtensionUUID']);

        self::$qrHeaderUUID = $response['qrHeaderUUID'];
        self::$qrExtensionUUID = $response['qrExtensionUUID'];
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testGetPayeeQrStatus()
    {
        $response = $this->client->getPayeeQrStatus(self::$qrHeaderUUID, self::$accessToken);
        $this->debugLog('getPayeeQrStatus', $response);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertEquals(self::$qrHeaderUUID, $response['uuid']);
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testCreatePayeeQrExtension()
    {
        $this->markTestSkipped();

        $extensionData = [
            'creditorAccount' => [
                'iban' => self::$iban
            ],
            'amount' => [
                'sum' => 20.00,
                'currency' => 'MDL'
            ],
            'dba' => 'Test Merchant Updated',
            'remittanceInfo4Payer' => 'Updated Payment for Order #123',
            'creditorRef' => 'ORD-123-UPD',
            'ttl' => [
                'length' => 3600,
                'units' => 'ss'
            ]
        ];

        $response = $this->client->createPayeeQrExtension(self::$qrHeaderUUID, $extensionData, self::$accessToken);
        $this->debugLog('createPayeeQrExtension', $response);

        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertNotEmpty($response['qrExtensionUUID']);

        self::$newQrExtensionUUID = $response['qrExtensionUUID'];
    }

    /**
     * @depends testCreatePayeeQrExtension
     */
    public function testGetQrExtensionStatus()
    {
        $this->markTestSkipped();

        $response = $this->client->getQrExtensionStatus(self::$newQrExtensionUUID, self::$accessToken);
        $this->debugLog('getQrExtensionStatus', $response);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertEquals(self::$newQrExtensionUUID, $response['uuid']);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testDemoPay()
    {
        // Demo pay requires qrHeaderUUID
        try {
            $response = $this->client->demoPay(self::$qrHeaderUUID, self::$accessToken);
            $this->debugLog('demoPay', $response);

            // The response structure for demoPay isn't fully clear from the description
            // but usually Guzzle command results act like arrays
            $this->assertNotNull($response);
        } catch (\Exception $e) {
            // Demo pay might fail if the QR is not in a payable state or other reasons in test env
            // We'll log it but not fail strictly if it's a known limitation
            $this->debugLog('demoPay failed', $e->getMessage());
            // For now, let's fail if it throws, to catch issues.
            throw $e;
        }
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testCancelPayeeQr()
    {
        $response = $this->client->cancelPayeeQr(self::$qrHeaderUUID, self::$accessToken);
        $this->debugLog('cancelPayeeQr', $response);

        // Delete operations might return empty body or 204 No Content
        // Guzzle Command Result might be empty or contain status code
        $this->assertNotNull($response);
    }
}