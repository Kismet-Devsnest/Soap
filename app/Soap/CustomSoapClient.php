<?php

namespace App\Soap;

use CodeDredd\Soap\SoapClient as BaseSoapClient;
use Soap\Psr18Transport\Middleware\SoapHeaderMiddleware;
use Soap\Xml\Builder\SoapHeader;
use function VeeWee\Xml\Dom\Builder\children;
use function VeeWee\Xml\Dom\Builder\element;
use function VeeWee\Xml\Dom\Builder\value;

class CustomSoapClient extends BaseSoapClient
{
    public function withSoapHeader(string $namespace, string $name, array $values): static
    {
        $elements = [];
        foreach ($values as $key => $val) {
            $elements[] = element($key, value((string) $val));
        }

        $this->middlewares = array_merge_recursive($this->middlewares, [
            new SoapHeaderMiddleware(
                new SoapHeader($namespace, $name, children(...$elements))
            ),
        ]);

        return $this;
    }
}


