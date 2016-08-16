<?php 
namespace Zend\Expressive\LegacyBridge\Psr7Bridge;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Hydrator\HydrationInterface;

final class Response
{
    
    public static function fromZf1ViewToJson(
        \Zend_Controller_Response_Abstract $zendResponse, 
        HydrationInterface $hydrator,
        \Zend_View_Interface $view,
        $apiPrefix = ''
    )
    {
        $status = $zendResponse->getHttpResponseCode();
        if ($status == 302) {
            $headers = $zendResponse->getHeaders();
            foreach ($headers as $key => $header) {
                if ($header['name'] == 'Location') {
                    $uri = $apiPrefix . $header['value'];
                    unset($headers[$key]);
                    break;
                }
            }
            return new RedirectResponse($uri, 302, $headers);
        }
        
        return new class($view, $hydrator, $status) extends JsonResponse {
            public function __construct($data, $hydrator, $status) {
                return parent::__construct($hydrator->extract($data), $status);
            }
        };
    }

    /**
     * Do not allow instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}