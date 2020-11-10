<?php

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use Aws\Sts\StsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

$stsClient = new StsClient([
    'version' => '2011-06-15',
    'region' => 'ap-northeast-1',
    'credentials' => [
        'key' => config('aws.access_key'),
        'secret' => config('aws.secret_key'),
    ],
]);

$result = $stsClient->assumeRole([
    'RoleArn' => config('aws.sa_api_role'),
    'RoleSessionName' => 'sp-api'
]);

$credentials = $result->get('Credentials');

$uri = new Uri();
$uri = $uri->withHost('sellingpartnerapi-fe.amazon.com')
    ->withPath('/products/pricing/v0/competitivePrice')
    ->withQuery('MarketplaceId=A1VC38T7YXB528&Asins=4815601828%2C4295000728&ItemType=ASIN');
    
$request = new Request('GET', $uri, [
    'X-Amz-Access-Token' => 'access_token',
    'User-Agent' => 'SA_API_TEST/1.0 (Language=PHP/7.3;Platform=Centos7)',
]);

$credentials = new Credentials($credentials['AccessKeyId'], $credentials['SecretAccessKey'], $credentials['SessionToken']);
$v4 = new SignatureV4('execute-api', 'us-west-2');
$request = $v4->signRequest($request, $credentials);

try {
     /**
     * headerの中身
     * - Host: sellingpartnerapi-fe.amazon.com
     * - X-Amz-Access-Token: Atza|IwEBI...
     * - X-Amz-Date: 20201110T024931Z
     * - Authorization: AWS4-HMAC-SHA256 Credential=ASIASXUN5...
     * - X-Amz-Security-Token: FwoGZXIvYXdzEBQaDNeA21KW
     */
    $client = new Client();
    $response = $client->send($request)->getBody()->getContents();    
    var_dump($response);
    
} catch (\Exception $e) {
    echo $e->getMessage();
}



