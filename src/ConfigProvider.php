<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Expressive\LegacyBridge\ApiDecider;
use Zend\Expressive\LegacyBridge\ApiDeciderFactory;
use Zend\Expressive\LegacyBridge\Zf1\Bridge;
use Zend\Expressive\LegacyBridge\Zf1\BridgeFactory;
use Zend\Expressive\LegacyBridge\Zf1\ViewRendererFactory;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\FastRouteRouter;

class ConfigProvider
{
    public function getDependencyConfig() {
        return [
            'services' => [
                'RequestParamsStrategyDefault' => function(ServerRequestInterface $req) {
                    return array(
                        'controller' => $req->getAttribute('controller', false),
                        'action' => $req->getAttribute('action', false),
                        'id' => $req->getAttribute('id', false)
                    );
                },
                'legacyRedirector' => function($req, $res) {
                    $url  = $req->getUri();
                    $path = '/zf.php' . $url->getPath();
                    
                    $url = $url->withPath($path);
                    
                    #if (count($query)) {
                    #    $url = $url->withQuery(http_build_query($query));
                    #}
                    
                    return $res
                        ->withStatus(301)
                        ->withHeader('Location', (string) $url);
                }
            ],
            'invokables' => [
                RouterInterface::class => FastRouteRouter::class,
                'Hydrator' => 'Zend\Hydrator\ObjectProperty'
            ],
            'factories' => [
                ApiDecider::class => ApiDeciderFactory::class,
                Bridge::class => BridgeFactory::class,
                'ViewRenderer' => ViewRendererFactory::class,
                'RequestParamsProxy' => function ($container) {
                    return function($routeName) use ($container) {
                        $strategies = $container->get('RequestParamsStrategyMapper');
                
                        if (!array_key_exists($routeName, $strategies)) return $container->get('RequestParamsStrategyDefault');
                
                        return $container->get($strategies[$routeName]);
                    };
                },
                'HydratorProxy' => function ($container) {
                    return function ($routeName) use ($container) {
                        $hydrator = $container->get('Hydrator');
                
                        $strategies = $container->get('hydratorstrategies');
                
                        if (!array_key_exists($routeName, $strategies)) return $hydrator;
                
                        foreach ($strategies[$routeName] as $name => $strategy) {
                            $hydrator->addStrategy($name, $container->get($strategy));
                        }
                
                        return $hydrator;
                    };
                }
            ]    
        ];
    }
    
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }
}