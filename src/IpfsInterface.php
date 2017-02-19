<?php

namespace NathanSalter\PhpIpfsApi;

interface IpfsInterface
{
    public function cat (string $hash): string;

    public function add ($content): string;

    public function ls ($hash): array;

    public function size ($hash): int;

    public function pinAdd ($hash): array;

    public function version (): string;
}