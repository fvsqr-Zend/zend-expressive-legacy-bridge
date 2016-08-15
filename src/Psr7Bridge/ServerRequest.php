<?php
namespace Zf1ExpBridge\Psr7Bridge;

use Psr\Http\Message\ServerRequestInterface;

final class ServerRequest
{
    public static function toZf1(ServerRequestInterface $psr7Request, callable $paramsStrategy)
    {
        require_once 'Zend/Controller/Request/Http.php';
        
        $uri = $psr7Request->getAttribute('originalUri');
        
        $uri = str_replace('/api', '', $uri);

        $zendRequest = new \Zend_Controller_Request_Http((string) $uri);
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
