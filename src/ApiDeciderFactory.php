<?php
namespace Zend\Expressive\LegacyBridge;

use Zend\ServiceManager\ServiceManager;

class ApiDeciderFactory
{
    public function __invoke(ServiceManager $container)
    {
        $legacyRedirector = $container->get('legacyRedirector');
        $pathCreator = $container->get('pathCreator');
        
        return new ApiDecider($legacyRedirector, $pathCreator);
    }
}
