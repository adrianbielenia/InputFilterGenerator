<?php
namespace Application\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InputFilterFactory implements AbstractFactoryInterface
{

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $filePath = 'module/Application/src/Application/InputFilter/'.$requestedName.'.php';

        return ((bool)strpos($requestedName, 'InputFilter')
            AND file_exists($filePath)
        );
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $inputFilterName = '\Application\\InputFilter\\' . $requestedName;
        return new $inputFilterName();
    }

}
