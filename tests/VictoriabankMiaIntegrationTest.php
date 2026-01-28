<?php

declare(strict_types=1);

namespace Victoriabank\VictoriabankMia\Tests;

use Victoriabank\VictoriabankMia\VictoriabankMiaClient;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class VictoriabankMiaIntegrationTest extends TestCase
{
    protected static $username;
    protected static $password;
    protected static $iban;
    protected static $certificate;
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
        self::$username    = getenv('VB_MIA_USERNAME');
        self::$password    = getenv('VB_MIA_PASSWORD');
        self::$iban        = getenv('VB_IBAN');
        self::$certificate = getenv('VB_CERTIFICATE');

        self::$baseUrl = VictoriabankMiaClient::TEST_BASE_URL;

        if (empty(self::$username) || empty(self::$password) || empty(self::$iban) || empty(self::$certificate)) {
            self::markTestSkipped('Integration test credentials not provided.');
        }
    }

    protected function setUp(): void
    {
        $options = [
            'base_uri' => self::$baseUrl,
            'timeout' => 30
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
                $responseBody = !empty($response) ? (string) $response->getBody() : '';
                $exceptionMessage = $t->getMessage();

                $this->debugLog($responseBody, $exceptionMessage);
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

    protected function redactData($data)
    {
        if (is_array($data)) {
            if (isset($data['qrAsImage'])) {
                // NOTE: remove redundant large image data
                $data['qrAsImage'] = null;
            }
        }

        return $data;
    }

    public function testAuthenticate()
    {
        $response = $this->client->getToken('password', self::$username, self::$password);
        // $this->debugLog('getToken', $response);

        $this->assertNotEmpty($response);
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

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertArrayHasKey('qrAsText', $response);
        $this->assertNotEmpty($response['qrHeaderUUID']);
        $this->assertNotEmpty($response['qrExtensionUUID']);
        $this->assertNotEmpty($response['qrAsText']);

        self::$qrHeaderUUID = $response['qrHeaderUUID'];
        self::$qrExtensionUUID = $response['qrExtensionUUID'];
        self::$qrData = $qrData;
    }

    /**
     * @depends testAuthenticate
     */
    public function testCreatePayeeQrValidationError()
    {
        $qrData = [
            'header' => [
                'qrType' => 'DYNM',
                'amountType' => 'Fixed',
                'pmtContext' => 'e'
            ],
            'extension' => [
                'amount' => [
                    'sum' => 10.00,
                    'currencyABC' => 'MDL' // Invalid field
                ],
                'ttl' => [
                    'length' => 60,
                    'units' => 'hh' // Invalid field
                ]
            ]
        ];

        try {
            $this->expectException(\GuzzleHttp\Command\Exception\CommandException::class);
            $this->expectExceptionMessage('[currency] is a required string');

            $response = $this->client->createPayeeQr($qrData, self::$accessToken);
            $this->debugLog('createPayeeQr', $response);
        } catch(\Exception $ex) {
            $this->debugLog('qrCreate', $ex->getMessage());
            throw $ex;
        }
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

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertArrayHasKey('qrAsText', $response);
        $this->assertNotEmpty($response['qrHeaderUUID']);
        $this->assertNotEmpty($response['qrExtensionUUID']);
        $this->assertNotEmpty($response['qrAsText']);

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

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('body', $response);
        $this->assertNotEmpty($response['body']);

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
        $qrToCancelResponse = $this->client->createPayeeQr(self::$qrData, self::$accessToken);
        $this->assertNotEmpty($qrToCancelResponse);
        $this->assertArrayHasKey('qrHeaderUUID', $qrToCancelResponse);
        $this->assertNotEmpty($qrToCancelResponse['qrHeaderUUID']);

        $response = $this->client->cancelPayeeQr($qrToCancelResponse['qrHeaderUUID'], self::$accessToken);
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

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals(self::$qrHeaderUUID, $response['uuid']);
        $this->assertNotEmpty($response['status']);
    }

    /**
     * @depends testCreatePayeeQr
     */
    public function testGetQrExtensionStatus()
    {
        $response = $this->client->getQrExtensionStatus(self::$qrExtensionUUID, self::$accessToken);
        // $this->debugLog('getQrExtensionStatus', $response);

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals(self::$qrExtensionUUID, $response['uuid']);
        $this->assertNotEmpty($response['status']);
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

    protected function waitDemoPay()
    {
        $maxRetries = 5;
        $response = null;

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(5);
            $response = $this->client->getPayeeQrStatus(self::$qrHeaderUUID, self::$accessToken, 1, 1);
            // $this->debugLog('getPayeeQrStatus', $response);

            $this->assertNotEmpty($response);
            $this->assertArrayHasKey('status', $response);
            $this->assertNotEmpty($response['status']);

            if (strtolower($response['status']) === 'paid') {
                break;
            }
        }

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('extensions', $response);
        $this->assertNotEmpty($response['extensions'], 'QR status should have extensions');
        $this->assertArrayHasKey('payments', $response['extensions'][0]);
        $this->assertNotEmpty($response['extensions'][0]['payments'], 'QR extension should have payments after demoPay');

        return $response;
    }

    /**
     * @depends testGetSignal
     */
    public function testReverseTransaction()
    {
        $response = $this->waitDemoPay();

        $payment = $response['extensions'][0]['payments'][0];
        $this->assertNotEmpty($payment);
        $this->assertArrayHasKey('reference', $payment);

        $transactionId = VictoriabankMiaClient::getPaymentTransactionId($payment['reference']);
        $this->assertNotEmpty($transactionId);

        $response = $this->client->reverseTransaction($transactionId, self::$accessToken);
        // $this->debugLog('reverseTransaction', $response);

        $this->assertNotNull($response);
        $this->assertEmpty($response);
    }

    /**
     * @depends testDemoPay
     */
    public function testGetSignal()
    {
        $response = $this->waitDemoPay();

        $response = $this->client->getSignal(self::$qrExtensionUUID, self::$accessToken);
        // $this->debugLog('getSignal', $response);

        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('signalCode', $response);
        $this->assertArrayHasKey('qrHeaderUUID', $response);
        $this->assertArrayHasKey('qrExtensionUUID', $response);
        $this->assertEquals(self::$qrExtensionUUID, $response['qrExtensionUUID']);
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
        $this->assertArrayHasKey('transactionsInfo', $response);
        $this->assertNotEmpty($response['transactionsInfo']);
    }

    public function testDecodeValidateCallback()
    {
        $callbackBody = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWduYWxDb2RlIjoiRXhwaXJhdGlvbiIsInNpZ25hbER0VG0iOiIyMDI0LTEwLTAxVDE1OjA3OjQ1KzAzOjAwIiwicXJIZWFkZXJVVUlEIjoiYmQxMjA0OWItNjUxZC00MGEwLWIyYmMtZDZhMGY3ZTJiN2M3IiwicXJFeHRlbnNpb25VVUlEIjoiNjU0YWNkNjktNjAyYy00MzUxLTk1OTItODE0M2FlMjhkM2U0IiwicGF5bWVudCI6bnVsbH0.WJ5t8jtg2_6DPrxQNIcu50gsW7cDC8IMdjvOBO9wW3toIdeAljlMPxd_lLCWJiKXToRAVHU7a1EB4mLyzyw1iCcRadnsSqm21TrpDZWTjv3uL-XiMLrWOsGBf0aJJRFcGbysU_ym9YLonQMmYLF0voq39yAPMHO7CLCniSMhVdJ9Q5xnrq52y6Yn5YzefCNb2tAQ-erm-8_mCaF0DWd0UFhPA6TRXyV2l5GCkLbyhlUB9gVoVTdSN-XxA_1aoNTusheZPDH1InL03Bx3G8muaVxOMrMIsVCJJYAaTFKiQTBf0M49oTQpdPWeeS9wHaS7aSS3gUcFsOOEPavj7J8vxg';
        $callbackData = (array) VictoriabankMiaClient::decodeValidateCallback($callbackBody, self::$certificate);
        // $this->debugLog('decodeValidateCallback', $callbackData);

        $this->assertNotEmpty($callbackData);
        $this->assertArrayHasKey('signalCode', $callbackData);
        $this->assertEquals('Expiration', $callbackData['signalCode']);
    }
}
