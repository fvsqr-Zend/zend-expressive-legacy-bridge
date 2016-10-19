<?php
namespace Zend\Expressive\LegacyBridge\Psr7Bridge;

use Psr\Http\Message\ServerRequestInterface;
use \Zend_Controller_Request_Http as Request;

final class ServerRequest
{
    public static function toZf1(ServerRequestInterface $psr7Request, callable $paramsStrategy)
    {
        require_once 'Zend/Controller/Request/Http.php';

        $uri = $psr7Request->getAttribute('originalUri');

        if ($apiPrefix = $psr7Request->getAttribute('api-prefix')) {
            $uri = str_replace($apiPrefix, '', $uri);
        }

        $zendRequest = new Request((string) $uri);
        $zendRequest->setParams($paramsStrategy($psr7Request));

        return $zendRequest;
    }

    /**
     * Do not allow instantiation.
     */
    private function __construct()
    {
    }
}
