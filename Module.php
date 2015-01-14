<?php

/**
 * 
 */

namespace User;

use Zend\Mvc\MvcEvent;

/**
 * Moduł użytkownika
 */
class Module extends \Core\Mvc\Module\ModuleAbstract
{

    /**
     * 
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        
    }

    /**
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Pobranie konfiguracji service managera
     * 
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
            ),
            'factories' => array(
                
            ),
        );
    }

    /**
     * Pobiera konfiguracje helperów widoku
     * 
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(

            ),
        );
    }

}