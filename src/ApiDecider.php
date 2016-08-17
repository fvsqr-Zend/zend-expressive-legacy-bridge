<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Stratigility\Http\Request;

class ApiDecider
{
    private $legacyRedirector;
    private $app;
    
    public function __construct($legacyRedirector, $app) {
        $this->legacyRedirector = $legacyRedirector;   
        $this->app = $app;
    }
    
    public function __invoke(Request $req, $res, $next)
    {
        $accept = explode(',', $req->getHeader('Accept')[0]);
        
        $app->pipe($this->legacyRedirector);
        
        if (in_array('application/json', $accept)) {
            return $next($req, $res);
        }

        return ($this->legacyRedirector)($req, $res);
    }
}