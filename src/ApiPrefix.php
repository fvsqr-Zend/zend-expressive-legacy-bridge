<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Stratigility\Http\Request;

class ApiPrefix
{
    public function __invoke(Request $req, $res, $next)
    {
        $path = $req->getUri()->getPath();
        $origPath = $req->getOriginalRequest()->getUri()->getPath();

        $apiPrefix = str_replace($path, '', $origPath);

        $req = $req->withAttribute('api-prefix', $apiPrefix);

        return $next($req, $res);
    }
}
