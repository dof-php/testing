<?php

declare(strict_types=1);

namespace DOF\Testing;

// TODO: A mock http server during http request testing
class HTTPServer
{
    public $addr;

    public $port = 80;

    public $ssl = false;

    public function __construct(string $addr = null, int $port = 80, bool $ssl = false)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->ssl = $ssl;
    }
}