<?php
//
//namespace Application;
//
//use Zend\Mvc\ModuleRouteListener;
//
//class Module
//{
////    public function onBootstrap($e)
////    {
//////        $e->getApplication()->getServiceManager()->get('translator');
////        $eventManager        = $e->getApplication()->getEventManager();
////        $moduleRouteListener = new ModuleRouteListener();
////		$moduleRouteListener->attach($eventManager);
////		$eventManager->attach('dispatch', array($this, 'setLayout'));
//////			$controller      = $e->getTarget();
//////	        $controllerClass = get_class($controller);
//////			error_log("controller:". $controller->getName());
//////			error_log("controllerClass:". $controllerClass);
////
////	    $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
//////			$controller      = $e->getTarget();
////	        $controllerClass = get_class($controller);
////	        $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
////		}, 200);
////    }
//
//    /**
//     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
//     * @return void
//     */
//    public function setLayout($e)
//	{
//		return;
//		error_log("TEST");
//        $matches    = $e->getRouteMatch();
//		$controller = $matches->getParam('controller');
////		error_log("controller:", var_export($controller, true));
////		error_log("namespace:", var_export(__NAMESPACE__, true));
////        if (false === strpos($controller, __NAMESPACE__)) {
////            // not a controller from this module
////            return;
////        }
//
//        // Set the layout template
//        $viewModel = $e->getViewModel();
//        $viewModel->setTemplate('content/layout');
//	}
//
//    public function getConfig()
//	{
//		return include __DIR__ . '/config/module.config.php';
//    }
//
//    public function getAutoloaderConfig()
//	{
//        return array(
//            'Zend\Loader\StandardAutoloader' => array(
//                'namespaces' => array(
//                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
//                ),
//            ),
//        );
//    }
//
//}
?>
