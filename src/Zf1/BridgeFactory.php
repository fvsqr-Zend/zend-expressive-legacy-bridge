<?php
namespace Zend\Expressive\LegacyBridge\Zf1;

use Zend\ServiceManager\ServiceManager;
use \Zend_Application as Application;
use \Zend_Controller_Action_HelperBroker as HelperBroker;
use \Zend_Controller_Action_Helper_Redirector as Redirector;

class BridgeFactory
{
    public function __invoke(ServiceManager $container)
    {
        ($container->get('config'))['zf1-prereq']();

        require_once 'Zend/Application.php';

        $application = new Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );

        $viewRenderer = $container->get('ViewRenderer');
        HelperBroker::getStack()->offsetSet(-80, $viewRenderer);

        $redirector = new Redirector();
        $redirector->setExit(false);
        HelperBroker::addHelper($redirector);

        $hydrator = $container->get('HydratorProxy');

        $requestParamsStrategy = $container->get('RequestParamsProxy');

        return new Bridge($requestParamsStrategy, $application, $viewRenderer, $hydrator);
    }
}
