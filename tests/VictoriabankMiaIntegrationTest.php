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
    protected static $hybridQrHeaderUUID;
    protected static $hybridQrExtensionUUID;
    protected static $paymentId;

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

    public function testGetHealthStatus()
    {
        $response = $this->client->getHealthStatus();
        $this->debugLog('getHealthStatus', $response);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals($response['status'], 'Healthy');
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
     * @depends testAuthenticate
     */
    public function testCreateHybridQr()
    {
        $qrData = [
            'header' => [
                'qrType' => 'HYBR',
                'amountType' => 'Fixed',
                'pmtContext' => 'e'
            ],
            'extension' => [
                'creditorAccount' => [
                    'iban' => self::$iban
                ],
                'amount' => [
                    'sum' => 15.00,
                    'currency' => 'MDL'
                ],
                'dba' => 'Test Hybrid Merchant',
                'remittanceInfo4Payer' => 'Hybrid Order #H1',
                'creditorRef' => 'H1',
                'ttl' => [
                    'length' => 60,
                    'units' => 'mm'
                ]
            ]
        ];

        $response = $this->client->createPayeeQr($qrData, self::$accessToken);
        $this->debugLog('createHybridQr', $response);

        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertArrayHasKey('qrAsText', $response);

        self::$hybridQrHeaderUUID = $response['qrHeaderUUID'];
        self::$hybridQrExtensionUUID = $response['qrExtensionUUID'];
    }

    /**
     * @depends testCreateHybridQr
     */
    public function testCreateHybridQrExtension()
    {
        $extensionData = [
            'creditorAccount' => [
                'iban' => self::$iban
            ],
            'amount' => [
                'sum' => 25.00,
                'currency' => 'MDL'
            ],
            'dba' => 'Test Hybrid Merchant',
            'remittanceInfo4Payer' => 'Hybrid Order #H1 Updated',
            'creditorRef' => 'H1-UPD',
            'ttl' => [
                'length' => 10,
                'units' => 'mm'
            ]
        ];

        $response = $this->client->createPayeeQrExtension(self::$hybridQrHeaderUUID, $extensionData, self::$accessToken);
        $this->debugLog('createHybridQrExtension', $response);

        $this->assertArrayHasKey('body', $response);
        self::$hybridQrExtensionUUID = $response['body'];
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
     * @depends testCreatePayeeQrExtension
     */
    public function testGetQrExtensionStatus()
    {
        $response = $this->client->getQrExtensionStatus(self::$qrExtensionUUID, self::$accessToken);
        $this->debugLog('getQrExtensionStatus', $response);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals(self::$qrExtensionUUID, $response['uuid']);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testDemoPay()
    {
        $response = $this->client->demoPay(self::$qrHeaderUUID, self::$accessToken);
        $this->debugLog('demoPay', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateHybridQr
     */
    public function testCancelPayeeQr()
    {
        $response = $this->client->cancelPayeeQr(self::$hybridQrHeaderUUID, self::$accessToken);
        $this->debugLog('cancelPayeeQr', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testGetSignal()
    {
        $response = $this->client->getSignal(self::$qrHeaderUUID, self::$accessToken);
        $this->debugLog('getSignal', $response);
        $this->assertNotNull($response);
    }

    /**
     * @depends testCreateHybridQrExtension
     */
    public function testCancelHybridExtension()
    {
        $response = $this->client->cancelHybrExtension(self::$hybridQrHeaderUUID, self::$accessToken);
        $this->debugLog('cancelHybridExtension', $response);

        $this->assertNotNull($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testReconciliationTransactions()
    {
        $dateFrom = (new \DateTime('-1 day'))->format('Y-m-d\TH:i:s\Z');
        $dateTo = (new \DateTime('+1 day'))->format('Y-m-d\TH:i:s\Z');

        $response = $this->client->getReconciliationTransactions(self::$accessToken, $dateFrom, $dateTo);
        $this->debugLog('getReconciliationTransactions', $response);

        $this->assertNotNull($response);
    }

    public function testValidateCallback()
    {
        // Generate a temporary key pair for testing the JWT decoding
        $res = openssl_pkey_new([
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)['key'];

        // Create a JWT
        $payload = [
            'iss' => 'Victoriabank',
            'iat' => time(),
            'data' => 'test'
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');

        // Test decoding using the public key we just generated
        $decoded = VictoriabankMiaClient::decodeValidateCallback($jwt, $publicKey);

        $this->assertEquals('Victoriabank', $decoded->iss);
    }
}