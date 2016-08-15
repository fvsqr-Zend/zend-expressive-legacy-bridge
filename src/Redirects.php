<?php
namespace Zf1ExpBridge;

class Redirects
{
    public function __invoke($req, $res, $next)
    {
        $url  = $req->getUri();
        $path = $url->getPath();
        
        return $this->redirect('/zf.php/tracking' . $path, $url, $res);
        
        $controller = $req->getAttribute('controller', false);
        if ($controller ==  'tracking') {
            #$res = ResponseFactory::create();
            return $this->redirect('/zf.php' . $path, $url, $res);
        }
        
        return $next($req, $res);
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