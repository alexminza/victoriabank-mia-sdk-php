# PHP SDK for Victoriabank MIA API
* Victoriabank IPS Business WebApi docs: https://test-ipspj.victoriabank.md
* Victoriabank IPS DemoPay WebApi https://test-ipspj-demopay.victoriabank.md/swagger/
* GitHub project https://github.com/alexminza/victoriabank-mia-sdk-php

## Installation
To easily install or upgrade to the latest release, use `composer`:
```shell
composer require alexminza/victoriabank-mia-sdk
```

## Getting started
Import SDK:

```php
require_once __DIR__ . '/vendor/autoload.php';

use Victoriabank\VictoriabankMia\VictoriabankMiaClient;
```

Add project configuration:

```php
const DEBUG = getenv('DEBUG');

const VB_MIA_BASE_URI = getenv('VB_MIA_BASE_URI');
const VB_MIA_USERNAME = getenv('VB_MIA_USERNAME');
const VB_MIA_PASSWORD = getenv('VB_MIA_PASSWORD');
const VB_CERTIFICATE  = getenv('VB_CERTIFICATE');
const VB_COMPANY_NAME = getenv('VB_COMPANY_NAME');
const VB_COMPANY_IBAN = getenv('VB_COMPANY_IBAN');
```

Initialize client:

```php
$options = [
    'base_uri' => VB_MIA_BASE_URI,
    'timeout' => 15
];

if (DEBUG) {
    $logName = 'victoriabank_mia_guzzle';
    $logFileName = "$logName.log";

    $log = new \Monolog\Logger($logName);
    $log->pushHandler(new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG));

    $stack = \GuzzleHttp\HandlerStack::create();
    $stack->push(\GuzzleHttp\Middleware::log($log, new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)));

    $options['handler'] = $stack;
}

$guzzleClient = new \GuzzleHttp\Client($options);
$vbMiaClient = new VictoriabankMiaClient($guzzleClient);
```

## SDK usage examples
### Get Access Token with username and password

```php
$tokenResponse = $vbMiaClient->getToken('password', VB_MIA_USERNAME, VB_MIA_PASSWORD);
$accessToken = $tokenResponse['accessToken'];
```

### Create a dynamic order payment QR

```php
$qrData = array(
    'header' => array(
        'qrType' => 'DYNM', # Type of QR code: DYNM - Dynamic QR, STAT - Static QR, HYBR - Hybrid QR
        'amountType' => 'Fixed', # Specifies the type of amount: Fixed - Dynamic QR, Controlled - Static QR, Free - Hybrid QR
        'pmtContext' => 'e' #Payment context: m - mobile payment, e - e-commerce payment, i - invoice payment, 0 - other
    ),
    'extension' => array(
        'creditorAccount' => array(
            'iban' => VB_COMPANY_IBAN
        ),
        'amount' => array(
            'sum' => 123.45,
            'currency' => 'MDL'
        ),
        'dba' => VB_COMPANY_NAME,
        'remittanceInfo4Payer' => 'Order #123',
        'creditorRef' => '123',
        'ttl' => array(
            'length' => 60, #The duration for which the QR code is valid.
            'units' => 'mm' #The unit of time for the TTL: ss - seconds, mm - minutes
        )
    )
);

$createQrResponse = $vbMiaClient->createPayeeQr($qrData, $authToken);
print_r($createQrResponse);
```

### Decode callback and validate signature

```php
$vbCertificate = file_get_contents(VB_CERTIFICATE);
$callbackBody = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWduYWxDb2RlIjoiRXhwaXJhdGlvbiIsInNpZ25hbER0VG0iOiIyMDI0LTEwLTAxVDE1OjA3OjQ1KzAzOjAwIiwicXJIZWFkZXJVVUlEIjoiYmQxMjA0OWItNjUxZC00MGEwLWIyYmMtZDZhMGY3ZTJiN2M3IiwicXJFeHRlbnNpb25VVUlEIjoiNjU0YWNkNjktNjAyYy00MzUxLTk1OTItODE0M2FlMjhkM2U0IiwicGF5bWVudCI6bnVsbH0.WJ5t8jtg2_6DPrxQNIcu50gsW7cDC8IMdjvOBO9wW3toIdeAljlMPxd_lLCWJiKXToRAVHU7a1EB4mLyzyw1iCcRadnsSqm21TrpDZWTjv3uL-XiMLrWOsGBf0aJJRFcGbysU_ym9YLonQMmYLF0voq39yAPMHO7CLCniSMhVdJ9Q5xnrq52y6Yn5YzefCNb2tAQ-erm-8_mCaF0DWd0UFhPA6TRXyV2l5GCkLbyhlUB9gVoVTdSN-XxA_1aoNTusheZPDH1InL03Bx3G8muaVxOMrMIsVCJJYAaTFKiQTBf0M49oTQpdPWeeS9wHaS7aSS3gUcFsOOEPavj7J8vxg';

$callbackData = VictoriabankMiaClient::decodeValidateCallback($callbackBody, $vbCertificate);
print_r($callbackData);
```
