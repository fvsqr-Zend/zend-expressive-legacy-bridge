<?php
namespace Zend\Expressive\LegacyBridge\Sf1;

use Zend\Hydrator\HydratorInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\LegacyBridge\Psr7Bridge\ServerRequest;
use Zend\Expressive\LegacyBridge\Psr7Bridge\Response;
use Psr\Http\Message\ServerRequestInterface;

class Bridge {
    /**
     * @var \sfContext
     */
    private $context;
    
    /**
     * @var HydratorInterface
     */
    private $responseHydrator;
    
    private $routeMapping;
    
    public function __construct(
        \sfContext $context,
        callable $responseHydrator,
        array $routeMapping
    ) {
        $this->context = $context;
        $this->responseHydrator = $responseHydrator;
        $this->routeMapping = $routeMapping;        
    }
    
    public function __invoke(ServerRequestInterface $req, $res, $next) {
        $routeResult = $req->getAttribute(RouteResult::class);
        $routeName = $routeResult->getMatchedRouteName();
        
        if (array_key_exists($routeName, $this->routeMapping)) {
            $parameters = $this->context->getRouting()->parse($this->routeMapping[$routeName]);
            $this->context->getRequest()->addRequestParameters($parameters);
        }
        
        $this->context->dispatch();
        
        $action = $this->context->getActionStack()->popEntry()->getActionInstance();
        $varHolder = $action->getVarHolder();
        
        $status = $this->context->getResponse()->getStatusCode();
        
        return Response::fromSfParameterHolderToJson(
            $varHolder,
            ($this->responseHydrator)($routeName),
            $status
        );
    }
}