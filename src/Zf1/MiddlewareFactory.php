<?php
namespace Zend\Expressive\LegacyBridge\Zf1;

use Zend\ServiceManager\ServiceManager;

class MiddlewareFactory
{
    public function __invoke(ServiceManager $container)
    {
        defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
        
        defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
        
        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../src'),
            realpath(APPLICATION_PATH . '/../vendor'),
            zend_deployment_library_path('Zend Framework 1'),
            get_include_path(),
        )));
        
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