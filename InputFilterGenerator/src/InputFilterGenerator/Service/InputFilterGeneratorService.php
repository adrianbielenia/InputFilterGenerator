<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 27.08.14
 * Time: 14:43
 */

namespace Application\Service;

use Application\CodeGenerator\ClassGenerator;
use Application\CodeGenerator\InputFilter\InputFilterClassGenerator;
use Application\CodeGenerator\InputFilter\InputFilterMethodGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Stdlib\ArrayUtils;

class InputFilterGeneratorService extends AbstractService implements ServiceInterface {

    /**
     * @var FileGenerator
     */
    protected $fileFactory;

    /**
     * @var \Application\CodeGenerator\InputFilter\InputFilterClassGenerator
     */
    protected $abstractClassGenerator;

    /**
     * @var MethodGenerator
     */
    protected $methodFactory;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @throws \Exception
     */
    public function generateInputFilters() {
        /**
         * @var $metadata \Doctrine\ORM\Mapping\ClassMetadata
         * @var $abstractClassGenerator \Application\CodeGenerator\InputFilter\InputFilterClassGenerator
         */

        $inputFilterDir         = $this->getServiceLocator()->get('config')['inputFilter']['fileDestination'];
        $abstractFolderName     = $this->getServiceLocator()->get('config')['inputFilter']['abstractFolderName'];

        $allMetadata = $this->getEm()->getMetadataFactory()->getAllMetadata();

        if (!$allMetadata or !is_array($allMetadata)) {
            throw new \Exception('Brak danych metadata');
        }

        foreach ($allMetadata as $metadata) {

            $this->abstractClassGenerator = new InputFilterClassGenerator(new ClassGenerator());

            $this->abstractClassGenerator->setName($metadata->getName() . 'InputFilter' , $abstractFolderName);
            $this->abstractClassGenerator->setAbstract(true);
            $this->abstractClassGenerator->getClassGenerator()->setNamespaceName('Application\InputFilter\\'.$abstractFolderName);
            $this->abstractClassGenerator->getClassGenerator()->setExtendedClass('InputFilter');
            $this->abstractClassGenerator->getClassGenerator()->addTrait('\Application\ApiUtils\EntityManagerTrait');
            $this->abstractClassGenerator->getClassGenerator()->addTrait('\Application\ApiUtils\ServiceLocatorTrait');
            $this->abstractClassGenerator->getClassGenerator()->addUse('\Zend\InputFilter\InputFilter');
            $this->abstractClassGenerator->getClassGenerator()->addUse('\Zend\Filter\Word\UnderscoreToCamelCase');

            $methodGenerator = new InputFilterMethodGenerator();

            $enlistInputs       = $methodGenerator->getMethodEnlistInputs();
            $isValidMethod      = $methodGenerator->getMethodIsValid();
            $setExcludesMethod  = $methodGenerator->getMethodSetExcludes();
            $setDataMethod      = $methodGenerator->getMethodSetData();

            $this->abstractClassGenerator->getClassGenerator()->addMethods([$enlistInputs/*, $isValidMethod*/, $setExcludesMethod/*, $setDataMethod*/]);

            /**
             * Tworze metody validatorów
             */
            $this->createInputFilters( $metadata );

            $this->abstractClassGenerator->addProperties('excludes', [] ,PropertyGenerator::FLAG_PRIVATE);
            $this->abstractClassGenerator->save($inputFilterDir . $abstractFolderName);

            /**
             * Generujemy plik do nadpisywania
             */
            $classGenerator = new \Application\CodeGenerator\ClassGenerator();
            $classGenerator->setName($metadata->getName() . 'InputFilter');

            if (!file_exists($inputFilterDir . $classGenerator->getName() . '.php')) {
                $classGenerator->setNamespaceName('Application\InputFilter');
                $classGenerator->setExtendedClass($abstractFolderName . '\\' . $this->abstractClassGenerator->getClassGenerator()->getName());
                $classGenerator->setImplementedInterfaces(['ServiceLocatorAwareInterface']);
//                $classGenerator->addTrait('ServiceLocatorTrait'); # parent już go ma
                $classGenerator->addUse('Zend\ServiceManager\ServiceLocatorAwareInterface');
                $classGenerator->addUse('Application\ApiUtils\ServiceLocatorTrait');

                $inputFilterClass = new InputFilterClassGenerator($classGenerator);
                $inputFilterClass->save($inputFilterDir);
            }

        }
        die('skończyłem');
    }

    /**
     * @param ClassMetadata $classMetadata
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function createInputFilters(ClassMetadata $classMetadata) {

        $fields = [];

        if ($databaseColumns = $classMetadata->getFieldNames()) {
            $databaseMap = [];
            foreach( (array) $databaseColumns as $fieldName ) {
                $databaseMap[] = $classMetadata->getFieldMapping($fieldName);
            }
            $fields = ArrayUtils::merge($fields, $databaseMap);
        }

        if ($relationalFields = $classMetadata->getAssociationMappings()) {
            $fields = ArrayUtils::merge($fields, $relationalFields);
        }

        if ($fields) {
            $this->createInputFiltersFromMapping($fields);
        }

    }

    /**
     * @param $fields
     */
    private function createInputFiltersFromMapping($fields) {
        /**
         *
         */
        $methods = [];

        $filter = new UnderscoreToCamelCase();

        foreach ((array)$fields as $map) {
            $this->abstractClassGenerator->addProperties( lcfirst( $filter->filter($map['fieldName']) ), null, PropertyGenerator::FLAG_PROTECTED );

            $inputFilterMethodGenerator = new InputFilterMethodGenerator();
            $methods[] = $inputFilterMethodGenerator->createMethodInputFilterForField($map);
        }

        if ($methods) {
            $this->abstractClassGenerator->getClassGenerator()->addMethods($methods);
        }
    }

    /**
     * @param $array
     * @param $em EntityManager
     */
    static public function createObjects(&$array, $em) {
        if (gettype($array) !== 'array') return;
        if (isset($array['validators']) and is_array($array['validators'])) {
            foreach ($array['validators'] as &$validator) {
                if (isset($validator['options']['object_repository']) and is_string($validator['options']['object_repository'])) {
                    $validator['options']['object_repository'] = $em->getRepository($validator['options']['object_repository']);
                }
            }
        }
    }

} 