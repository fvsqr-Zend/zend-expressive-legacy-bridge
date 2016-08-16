<?php
namespace Zend\Expressive\LegacyBridge\Zf1;

use Zend\ServiceManager\ServiceManager;

class BridgeFactory
{
    public function __invoke(ServiceManager $container)
    {
        ($container->get('config'))['zf1-prereq']();
        
        require_once 'Zend/Application.php';
        
        $application = new \Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
        );
        
        $viewRenderer = $container->get('ViewRenderer');
        \Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);
        
        $redirector = new \Zend_Controller_Action_Helper_Redirector();
        $redirector->setExit(false);
        \Zend_Controller_Action_HelperBroker::addHelper($redirector);
        
        $hydrator = $container->get('HydratorProxy');
        
        $requestParamsStrategy = $container->get('RequestParamsProxy');
        
        return new Bridge($requestParamsStrategy, $application, $viewRenderer, $hydrator);
    }
}