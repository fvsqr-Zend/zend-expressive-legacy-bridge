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
}
