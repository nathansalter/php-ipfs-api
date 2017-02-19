<?php

namespace NathanSalter\PhpIpfsApi;

final class IpfsClient implements IpfsInterface
{
    /**
     * @var string
     */
    private $gatewayIp;

    /**
     * @var int
     */
    private $gatewayPort;

    /**
     * @var int
     */
    private $gatewayApiPort;

    function __construct(string $ip = "localhost", int $port = 8080, int $apiPort = 5001)
    {
        $this->gatewayIp      = $ip;
        $this->gatewayPort    = $port;
        $this->gatewayApiPort = $apiPort;
    }

    public function cat (string $hash): string
    {
        $ip = $this->gatewayIp;
        $port = $this->gatewayPort;
        return $this->curl(sprintf('http://%s:%s/ipfs/%s', $ip, $port, $hash);

    }

    public function add ($content): string
    {
        $ip = $this->gatewayIp;
        $port = $this->gatewayApiPort;

        $req = $this->curl(sprintf('http://%s:%s/api/v0/add?stream-channels=true', $ip, $port), $content);
        $req = json_decode($req, true);

        return $req['Hash'];
    }

    public function ls ($hash): array
    {
        $ip = $this->gatewayIp;
        $port = $this->gatewayApiPort;

        $response = $this->curl("http://$ip:$port/api/v0/ls/$hash");

        $data = json_decode($response, true);

        return $data['Objects'][0]['Links'];
    }

    public function size ($hash): int
    {
        $ip = $this->gatewayIp;
        $port = $this->gatewayApiPort;

        $response = $this->curl("http://$ip:$port/api/v0/object/stat/$hash");
        $data = json_decode($response, true);

        return $data['CumulativeSize'];
    }

    public function pinAdd ($hash): array
    {

        $ip = $this->gatewayIp;
        $port = $this->gatewayApiPort;

        $response = $this->curl("http://$ip:$port/api/v0/pin/add/$hash");
        $data = json_decode($response, true);

        return $data;
    }

    public function version (): string
    {
        $ip = $this->gatewayIp;
        $port = $this->gatewayApiPort;
        $response = $this->curl("http://$ip:$port/api/v0/version");
        $data = json_decode($response, true);
        return $data["Version"];
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
        if ($output == FALSE) {
            //todo: when ipfs doesn't answer
        }
        curl_close($ch);

        return $output;
    }
}


