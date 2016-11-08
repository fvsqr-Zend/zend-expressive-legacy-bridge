<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Stratigility\Http\Request;

class ApiDecider
{
    private $legacyRedirector;
    private $app;
    
    public function __construct($legacyRedirector) {
        $this->legacyRedirector = $legacyRedirector;   
    }
    
    public function __invoke(Request $req, $res, $next)
    {
        if ($req->getHeaderLine('Accept') == 'application/json') {
            return $next($req, $res);
        }

        return ($this->legacyRedirector)($req, $res);
    }
}
