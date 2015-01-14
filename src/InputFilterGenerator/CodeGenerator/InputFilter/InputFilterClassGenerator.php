<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 18.12.14
 * Time: 08:22
 */

namespace Application\CodeGenerator\InputFilter;

use Application\CodeGenerator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\PropertyGenerator;

class InputFilterClassGenerator {

    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @param ClassGenerator $classGenerator
     */
    public function setClassGenerator(ClassGenerator $classGenerator) {
        $this->classGenerator = $classGenerator;
    }

    /**
     * @return ClassGenerator
     */
    public function getClassGenerator() {
        return $this->classGenerator;
    }

    /**
     * @param ClassGenerator $classGenerator
     */
    public function __construct(ClassGenerator $classGenerator) {
        $this->setClassGenerator($classGenerator);
    }

    /**
     * @return ClassGenerator
     */
    public function __invoke() {
        return $this->classGenerator;
    }

    /**
     * @param $abstract
     */
    public function setAbstract($abstract) {
        $this->classGenerator->setAbstract($abstract);
    }


    /**
     * @param $name
     * @param $prefix
     */
    public function setName($name, $prefix) {
        $this->classGenerator->setName($name);
        if ($prefix) {
            $this->classGenerator->setName( $prefix . $this->classGenerator->getName() );
        }
    }

    /**
     * @param $mixed
     * @param null $defaultValue
     * @param int $flag
     */
    public function addProperties($mixed, $defaultValue = null, $flag = PropertyGenerator::FLAG_PROTECTED) {
        if (is_array($mixed)) {
            foreach($mixed as $val) {
                $this->addProperties($val);
            }
        } else {
            $this->classGenerator->addProperty($mixed, $defaultValue, $flag);
        }
    }

    public function save($destination) {

        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $fileName = $this->getClassGenerator()->getName().'.php';
        $filePath = $destination . '/' . $fileName;

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($this->getClassGenerator());
        $fileGenerator->setFilename($filePath);
        $fileGenerator->write();

        @chmod($filePath, 0777);
    }

} 