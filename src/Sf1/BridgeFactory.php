<?php
namespace Zend\Expressive\LegacyBridge\Sf1;

use Zend\ServiceManager\ServiceManager;

class BridgeFactory
{
    public function __invoke(ServiceManager $container)
    {
        $config = ($container->get('config'));

        if (array_key_exists('sf1-prereq', $config)) {
            $config['sf1-prereq']();
        }
        if (array_key_exists('route-mapping', $config)) {
            $routeMapping = $config['route-mapping'];
        } else {
            $routeMapping = [];
        }

        $configuration = \ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
        $context = \sfContext::createInstance($configuration);

        $dispatcher = $context->getEventDispatcher();
        $resOptions = $context->getResponse()->getOptions();
        $resOptions['send_http_headers'] = false;
        $context->getResponse()->initialize($dispatcher, $resOptions);

        $context->getController()->setRenderMode(\sfView::RENDER_NONE);

        $hydrator = $container->get('HydratorProxy');

        return new Bridge($context, $hydrator, $routeMapping);
    }
}
