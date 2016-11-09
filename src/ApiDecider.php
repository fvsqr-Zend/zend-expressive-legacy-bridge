<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Stratigility\Http\Request;

class ApiDecider
{
    private $legacyRedirector;
    private $pathCreator;
    
    public function __construct(callable $legacyRedirector, callable $pathCreator) {
        $this->legacyRedirector = $legacyRedirector;
        $this->pathCreator = $pathCreator;
    }
    
    public function __invoke(Request $req, $res, $next)
    {
        if ($req->getHeaderLine('Accept') == 'application/json') {
            return $next($req, $res);
        }

        return ($this->legacyRedirector)($req, $res, $this->pathCreator);
    }
}
