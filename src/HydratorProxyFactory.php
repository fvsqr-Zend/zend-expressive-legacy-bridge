<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\ServiceManager\ServiceManager;

class HydratorProxyFactory
{
    public function __invoke(ServiceManager $container)
    {
        return function ($routeName) use ($container) {
            $hydrator = $container->get('Hydrator');

            $strategies = $container->get('hydratorstrategies');

            if (!array_key_exists($routeName, $strategies)) {
                return $hydrator;
            }

            foreach ($strategies[$routeName] as $name => $strategy) {
                $hydrator->addStrategy($name, $container->get($strategy));
            }

            return $hydrator;
        };
    }
}
