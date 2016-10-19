<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\ServiceManager\ServiceManager;

class RequestParamsProxyFactory
{
    public function __invoke(ServiceManager $container)
    {
        return function ($routeName) use ($container) {
            $strategies = $container->get('RequestParamsStrategyMapper');

            if (!array_key_exists($routeName, $strategies)) {
                return $container->get('RequestParamsStrategyDefault');
            }

            return $container->get($strategies[$routeName]);
        };
    }
}
