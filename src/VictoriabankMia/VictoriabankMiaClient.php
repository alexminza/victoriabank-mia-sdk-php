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
}
