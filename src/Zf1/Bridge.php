<?php
namespace Zend\Expressive\LegacyBridge\Zf1;

use Zend\Hydrator\HydratorInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\LegacyBridge\Psr7Bridge\ServerRequest;
use Zend\Expressive\LegacyBridge\Psr7Bridge\Response;
use Psr\Http\Message\ServerRequestInterface;
use \Zend_Application as Application;
use \Zend_Controller_Action_Helper_ViewRenderer as ViewRenderer;

class Bridge {
    /**
     * @var \Zend_Application
     */
    private $application;

    /**
     * @var \Zend_Controller_Action_Helper_ViewRenderer
     */
    private $viewRenderer;
    
    /**
     * @var \Zend_Controller_Request_Abstract
     */
    private $zendRequest;
    
    /**
     * @var HydratorInterface
     */
    private $responseHydrator;
    
    private $paramsSetter;
   
    public function __construct(
        callable $requestParamsStrategy,
        Application $application, 
        ViewRenderer $viewRenderer,
        callable $responseHydrator
    ) {

        $this->requestParamsStrategy = $requestParamsStrategy;
        $this->application = $application;
        $this->viewRenderer = $viewRenderer;
        $this->responseHydrator = $responseHydrator;
    }
    
    public function __invoke(ServerRequestInterface $req, $res, $next) {
        $routeResult = $req->getAttribute(RouteResult::class);
        $routeName = $routeResult->getMatchedRouteName();
        
        $apiPrefix = $req->getAttribute('api-prefix');
        
        $req = ServerRequest::toZf1($req, ($this->requestParamsStrategy)($routeName));
        
        $this->application->bootstrap();
        
        $front = $this->application->getBootstrap()->getResource('FrontController');
        $front->setRequest($req);
        $front->returnResponse(true);
        
        $this->application->run();
    
        $view = $this->viewRenderer->getActionController()->view;
        
        $response = $this->viewRenderer->getActionController()->getResponse();
        
        return Response::fromZf1ViewToJson($response, ($this->responseHydrator)($routeName), $view, $apiPrefix);
    }
}