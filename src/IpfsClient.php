<?php

namespace NathanSalter\PhpIpfsApi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class IpfsClient implements IpfsInterface
{
    /**
     * @var Client
     */
    private $gatewayClient;

    /**
     * @var Client
     */
    private $gatewayApiClient;

    function __construct(Client $gatewayClient, Client $gatewayApiClient)
    {
        $this->gatewayClient = $gatewayClient;
        $this->gatewayApiClient = $gatewayApiClient;
    }

    public function cat(string $hash): string
    {
        $response = $this->gatewayClient->get(sprintf('/ipfs/%s', $hash), [RequestOptions::HTTP_ERRORS => false]);
        if (404 === $response->getStatusCode()) {
            throw new HashNotFoundException(sprintf('The hash %s was not found in any IPFS nodes', $hash));
        }
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException(sprintf('General failure in IPFS lookup for hash %s', $hash));
        }

        return $response->getBody()->getContents();
    }

    public function add(string $content): string
    {
        $response = $this->gatewayApiClient->post('/add?stream-channels=true', [
            RequestOptions::BODY => $content,
            RequestOptions::HTTP_ERRORS => false,
        ]);
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException('General failure saving IPFS content');
        }
        $objectInfo = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $objectInfo['Hash'];
    }

    public function ls(string $hash): array
    {
        $response = $this->gatewayApiClient->get(sprintf('/ls/%s', $hash), [RequestOptions::HTTP_ERRORS => false]);
        if (404 === $response->getStatusCode()) {
            throw new HashNotFoundException(sprintf('The hash %s was not found in any IPFS nodes', $hash));
        }
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException(sprintf('General failure in IPFS lookup for hash %s', $hash));
        }

        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $data['Objects'][0]['Links'];
    }

    public function size(string $hash): int
    {
        $response = $this->gatewayApiClient->get(sprintf('/object/stat/%s', $hash), [
            RequestOptions::HTTP_ERRORS => false
        ]);
        if (404 === $response->getStatusCode()) {
            throw new HashNotFoundException(sprintf('The hash %s was not found in any IPFS nodes', $hash));
        }
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException(sprintf('General failure in IPFS lookup for hash %s', $hash));
        }

        $objectInfo = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $objectInfo['CumulativeSize'];
    }

    public function pinAdd(string $hash): array
    {
        $response = $this->gatewayApiClient->get(sprintf('/pin/add/%s', $hash), [
            RequestOptions::HTTP_ERRORS => false
        ]);
        if (404 === $response->getStatusCode()) {
            throw new HashNotFoundException(sprintf('The hash %s was not found in any IPFS nodes', $hash));
        }
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException(sprintf('General failure in IPFS lookup for hash %s', $hash));
        }
        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $data;
    }

    public function version(): string
    {
        $response = $this->gatewayApiClient->get(sprintf('/pin/add/%s', $hash), [
            RequestOptions::HTTP_ERRORS => false
        ]);
        if (200 !== $response->getStatusCode()) {
            throw new IpfsFailureException('General failure in IPFS looking for version');
        }
        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $data["Version"];
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'file'
        ];
    }

    private function curl ($url, $data = ""): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

        if ($data != "") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data; boundary=a831rwxi1a3gzaorw1w2z49dlsor']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "--a831rwxi1a3gzaorw1w2z49dlsor\r\nContent-Type: application/octet-stream\r\nContent-Disposition: file; \r\n\r\n" . $data . "    a831rwxi1a3gzaorw1w2z49dlsor");
        }

        $output = curl_exec($ch);
        if ($output == false) {
            //todo: when ipfs doesn't answer
        }
        curl_close($ch);

        return $output;
    }
}


