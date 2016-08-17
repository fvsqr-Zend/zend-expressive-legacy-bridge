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
        $accept = explode(',', $req->getHeader('Accept')[0]);
        
        if (in_array('application/json', $accept)) {
            return $next($req, $res);
        }

        return ($this->legacyRedirector)($req, $res);
    }
}