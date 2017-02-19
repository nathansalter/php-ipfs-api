<?php

namespace NathanSalter\PhpIpfsApi;

interface IpfsInterface
{
    public function cat (string $hash): string;

    public function add (string $content): string;

    public function ls (string $hash): array;

    public function size (string $hash): int;

    public function pinAdd (string $hash): array;

    public function version (): string;
}