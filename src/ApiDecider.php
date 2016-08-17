<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Stratigility\Http\Request;

class ApiDecider
{
    public function __invoke(Request $req, $res, $next)
    {
        $accept = explode(',', $req->getHeader('Accept')[0]);
        
        if (in_array('application/json', $accept)) {
            return $next($req, $res);
        }
        
        $url  = $req->getUri();
        $path = $url->getPath();
        return $this->redirect('/zf.php' . $path, $url, $res);
    }
        
    private function redirect($path, $url, $res, $query = [])
    {
        $url = $url->withPath($path);
    
        if (count($query)) {
            $url = $url->withQuery(http_build_query($query));
        }
    
        return $res
        ->withStatus(301)
        ->withHeader('Location', (string) $url);
    }
}