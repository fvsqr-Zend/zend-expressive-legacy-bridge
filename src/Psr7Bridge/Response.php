<?php
namespace Zend\Expressive\LegacyBridge\Psr7Bridge;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Hydrator\HydrationInterface;
use \Zend_Controller_Response_Abstract as ZendResponse;
use \Zend_View_Interface as View;

final class Response
{

    public static function fromZf1ViewToJson(
        ZendResponse $zendResponse,
        HydrationInterface $hydrator,
        View $view,
        $apiPrefix = ''
    ) {
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
            public function __construct($data, $hydrator, $status)
            {
                return parent::__construct($hydrator->extract($data), $status);
            }
        };
    }

    public static function fromSfParameterHolderToJson(
        \sfParameterHolder $paramHolder,
        HydrationInterface $hydrator,
        $status
    ) {
        return new class($paramHolder, $hydrator, $status) extends JsonResponse
        {
            public function __construct($paramHolder, $hydrator, $status)
            {
                $data = new \ArrayObject($paramHolder->getAll());
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
