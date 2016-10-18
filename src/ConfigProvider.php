<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\Expressive\LegacyBridge\ApiDecider;
use Zend\Expressive\LegacyBridge\ApiDeciderFactory;
use Zend\Expressive\LegacyBridge\Zf1;
use Zend\Expressive\LegacyBridge\Zf1\ViewRendererFactory;
use Zend\Expressive\LegacyBridge\Sf1;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Hydrator\ObjectProperty;

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
                    
                    return $res
                        ->withStatus(301)
                        ->withHeader('Location', (string) $url);
                }
            ],
            'invokables' => [
                RouterInterface::class => FastRouteRouter::class,
                'Hydrator' => ObjectProperty::class
            ],
            'factories' => [
                ApiDecider::class => ApiDeciderFactory::class,
                Zf1\Bridge::class => Zf1\BridgeFactory::class,
                Sf1\Bridge::class => Sf1\BridgeFactory::class,
                'ViewRenderer' => ViewRendererFactory::class,
                'RequestParamsProxy' => RequestParamsProxyFactory::class,
                'HydratorProxy' => HydratorProxyFactory::class
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