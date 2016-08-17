<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\ServiceManager\ServiceManager;

class ApiDeciderFactory
{
    public function __invoke(ServiceManager $container)
    {
        $legacyRedirector = $container->get('legacyRedirector');
        
        return new ApiDecider($legacyRedirector);
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