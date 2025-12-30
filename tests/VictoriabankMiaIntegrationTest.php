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
    protected static $qrData;
    protected static $hybridQrData;

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
        $options = [
            'base_uri' => self::$baseUrl,
            'timeout' => 15
        ];

        #region Logging
        $classParts = explode('\\', self::class);
        $logName = end($classParts) . '_guzzle';
        $logFileName = "$logName.log";

        $log = new \Monolog\Logger($logName);
        $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG));

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

        $options['handler'] = $stack;
        #endregion

        $this->client = new VictoriabankMiaClient(new Client($options));
    }

    protected function onNotSuccessfulTest(\Throwable $t): void
    {
        if ($this->isDebugMode()) {
            // https://github.com/guzzle/guzzle/issues/2185
            if ($t instanceof \GuzzleHttp\Command\Exception\CommandException) {
                $response = $t->getResponse();
                $responseBody = (string) $response->getBody();
                $this->debugLog($responseBody, $t->getMessage());
            }
        }

        parent::onNotSuccessfulTest($t);
    }

    protected function isDebugMode()
    {
        // https://stackoverflow.com/questions/12610605/is-there-a-way-to-tell-if-debug-or-verbose-was-passed-to-phpunit-in-a-test
        return in_array('--debug', $_SERVER['argv'] ?? []);
    }

    protected function debugLog($message, $data)
    {
        $data = $this->redactData($data);
        $data_print = print_r($data, true);
        error_log("$message: $data_print");
    }

    protected function redactData($data) {
        if ($data) {
            if (isset($data['qrAsImage'])) {
                $data['qrAsImage'] = '[REDACTED]';
            }
        }

        return $data;
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken('password', self::$username, self::$password);
        // $this->debugLog('getToken', $response);

        $this->assertArrayHasKey('accessToken', $response);
        $this->assertNotEmpty($response['accessToken']);

        self::$accessToken = $response['accessToken'];
    }

    public function testGetHealthStatus()
    {
        $response = $this->client->getHealthStatus();
        // $this->debugLog('getHealthStatus', $response);

        $this->assertNotEmpty($response);
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
        // $this->debugLog('createPayeeQr', $response);

        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertNotEmpty($response['qrHeaderUUID']);
        $this->assertNotEmpty($response['qrExtensionUUID']);

        self::$qrHeaderUUID = $response['qrHeaderUUID'];
        self::$qrExtensionUUID = $response['qrExtensionUUID'];
        self::$qrData = $qrData;
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreatePayeeQrHybrid()
    {
        $hybridData = [
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

        $response = $this->client->createPayeeQr($hybridData, self::$accessToken);
        // $this->debugLog('createPayeeQr', $response);

        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertArrayHasKey('qrAsText', $response);

        self::$hybridQrHeaderUUID = $response['qrHeaderUUID'];
        self::$hybridQrExtensionUUID = $response['qrExtensionUUID'];
        self::$hybridQrData = $hybridData;
    }

    /**
     * @depends testCreatePayeeQrHybrid
     */
    public function testCreatePayeeQrExtension()
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
        // $this->debugLog('createPayeeQrExtension', $response);

        $this->assertArrayHasKey('body', $response);
        self::$hybridQrExtensionUUID = $response['body'];
    }

    /**
     * @depends testCreatePayeeQrExtension
     */
    public function testCancelHybrExtension()
    {
        $response = $this->client->cancelHybrExtension(self::$hybridQrHeaderUUID, self::$accessToken);
        // $this->debugLog('cancelHybrExtension', $response);

        $this->assertNotNull($response);
        $this->assertEmpty($response);
    }

    /**
     * @depends testCancelHybrExtension
     */
    public function testCancelPayeeQr()
    {
        $response = $this->client->cancelPayeeQr(self::$hybridQrHeaderUUID, self::$accessToken);
        // $this->debugLog('cancelPayeeQr', $response);

        $this->assertNotNull($response);
        $this->assertEmpty($response);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testGetPayeeQrStatus()
    {
        $response = $this->client->getPayeeQrStatus(self::$qrHeaderUUID, self::$accessToken);
        // $this->debugLog('getPayeeQrStatus', $response);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals(self::$qrHeaderUUID, $response['uuid']);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testGetQrExtensionStatus()
    {
        $response = $this->client->getQrExtensionStatus(self::$qrExtensionUUID, self::$accessToken);
        // $this->debugLog('getQrExtensionStatus', $response);

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
        // $this->debugLog('demoPay', $response);

        $this->assertNotNull($response);
        $this->assertEmpty($response);
    }

    /**
     * @depends testDemoPay
     */
    public function testReverseTransaction()
    {
        $maxRetries = 5;
        $response = null;

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(5);
            $response = $this->client->getPayeeQrStatus(self::$qrHeaderUUID, self::$accessToken, 1, 1);
            // $this->debugLog('getPayeeQrStatus', $response);

            $this->assertNotEmpty($response);
            $this->assertArrayHasKey('status', $response);

            if (strtolower($response['status']) === 'paid') {
                break;
            }
        }

        $this->assertNotEmpty($response['extensions'], 'QR status should have extensions');
        $this->assertNotEmpty($response['extensions'][0]['payments'], 'QR extension should have payments after demoPay');

        $payment = $response['extensions'][0]['payments'][0];
        $transactionId = VictoriabankMiaClient::getPaymentTransactionId($payment['reference']);

        $this->assertNotEmpty($transactionId);

        $response = $this->client->reverseTransaction($transactionId, self::$accessToken);
        // $this->debugLog('testReverseTransaction', $response);

        $this->assertNotNull($response);
        $this->assertEmpty($response);
    }

    /**
     * @depends testDemoPay
     */
    public function testGetSignal()
    {
        $response = $this->client->getSignal(self::$qrExtensionUUID, self::$accessToken);
        // $this->debugLog('getSignal', $response);

        $this->assertNotEmpty($response);
    }

    /**
     * @depends testAuthenticate
     */
    public function testGetReconciliationTransactions()
    {
        $dateFrom = (new \DateTime('today'))->format('Y-m-d\TH:i:s\Z'); // '-1 day'
        $dateTo = (new \DateTime('tomorrow'))->format('Y-m-d\TH:i:s\Z'); // '+1 day'

        $response = $this->client->getReconciliationTransactions(self::$accessToken, $dateFrom, $dateTo);
        // $this->debugLog('getReconciliationTransactions', $response);

        $this->assertNotEmpty($response);
    }
}
