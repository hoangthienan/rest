<?php

namespace go1\rest\wrapper;

use DI\Container;

class ServiceUrl
{
    private $container;
    private $pattern;
    private $env;

    public function __construct(Container $c)
    {
        $this->container = $c;

        $this->pattern = $this->container->has('servicePattern')
            ? $this->container->get('servicePattern')
            : 'http://SERVICE.ENVIRONMENT.go1.service';

        $this->env = $this->container->has('env') ? $this->container->get('env') : 'dev';
    }

    public function get(string $serviceName): string
    {
        return str_replace(['SERVICE', 'ENVIRONMENT'], [$serviceName, $this->env], $this->pattern);
    }
}
