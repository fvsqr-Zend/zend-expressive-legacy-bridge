<?php 
namespace Zend\Expressive\LegacyBridge\Zf1;

final class ViewRendererFactory
{
    public function __invoke()
    {
        return new class extends \Zend_Controller_Action_Helper_ViewRenderer {
            public function initView($path = null, $prefix = null, array $options = []) {
                parent::initView($path, $prefix, array_merge(['noRender' => true], $options));
            }
            public function getName() {
                return 'ViewRenderer';
            }
        };
    }
}