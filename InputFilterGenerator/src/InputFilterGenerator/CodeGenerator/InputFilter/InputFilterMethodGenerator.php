<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 18.12.14
 * Time: 08:56
 */

namespace Application\CodeGenerator\InputFilter;


use Application\CodeGenerator\InputFilter\FilterGenerator\FilterGeneratorFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\MethodReflection;
use Zend\Filter\Word\UnderscoreToCamelCase;

class InputFilterMethodGenerator {

    /**
     * @var MethodGenerator
     */
    private $methodGenerator;

    /**
     * @param MethodGenerator $methodGenerator
     */
    public function setClassGenerator(MethodGenerator $methodGenerator) {
        $this->methodGenerator = $methodGenerator;
    }

    /**
     * @return MethodGenerator
     */
    public function getClassGenerator() {
        return $this->methodGenerator;
    }

    /**
     * @return MethodGenerator
     */
    public function __invoke() {
        return $this->methodGenerator;
    }

    public function getMethodEnlistInputs() {
        $docBlock = new DocBlockGenerator();
        $docBlock->setSourceContent('Coś tworzy');
        $docBlock->setLongDescription('Long description');

        $fieldParameter = new ParameterGenerator('data','',[]);

        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( 'enlistInputs' );
        $methodGenerator->setParameter( $fieldParameter );
        $methodGenerator->setDocBlock( $docBlock );
        $body = <<<CONTENT
\$filter = new UnderscoreToCamelCase();

foreach ( (array)\$data as \$fieldName => \$fieldValue) {
    \$fieldNameNice = lcfirst(\$filter->filter(\$fieldName));
    \$methodName = \$fieldNameNice.'InputFilter';
    if (method_exists(\$this, \$methodName) && !in_array(\$fieldNameNice, \$this->excludes)) {
        \$inputFilter = \$this->{\$methodName}(\$fieldValue);
        \Application\Service\InputFilterGeneratorService::createObjects(\$inputFilter, \$this->getEm());
        \$this->add( \$inputFilter, \$fieldName );
    }
}

return \$this;

CONTENT;
        $methodGenerator->setBody($body);
        return $methodGenerator;
    }

    /**
     * @return MethodGenerator
     */
    public function getMethodIsValid() {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( 'isValid' );
        $body = <<<CONTENT
\$this->enlistInputs();

return parent::isValid();
CONTENT;
        $methodGenerator->setBody($body);
        return $methodGenerator;
    }

    /**
     * @return MethodGenerator
     */
    public function getMethodSetExcludes() {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( 'setExcludes' );
        $fieldParameter = new ParameterGenerator('fields','array',[]);
        $methodGenerator->setParameter( $fieldParameter );
        $body = <<<CONTENT
\$this->excludes = \$fields;
CONTENT;
        $methodGenerator->setBody($body);
        return $methodGenerator;
    }

    /**
     * @return MethodGenerator
     */
    public function getMethodSetData() {
        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( 'setData' );
        $fieldParameter = new ParameterGenerator('data','',[]);
        $methodGenerator->setParameter( $fieldParameter );
        $body = <<<CONTENT
\$this->data = \$data;
CONTENT;
        $methodGenerator->setBody($body);
        return $methodGenerator;
    }

    /**
     * @param array $field
     * @return MethodGenerator
     */
    public function createMethodInputFilterForField(array $field) {

        if ($methodGenerator = FilterGeneratorFactory::getFilterGenerator($field)) {
            $methodGenerator->setField($field);
            return $methodGenerator->generate();
        }

        $filter = new UnderscoreToCamelCase();

        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( lcfirst( $filter->filter($field['fieldName']) ) . 'InputFilter' );

        if (isset($field['nullable']) and !$field['nullable']) {
            $required = true;
        } else {
            $required = false;
        }

        $filters = $this->getFilters($field);
        $validators = $this->getValidators($field);

        $inputFilter = [
            'name' => $field['fieldName'],
            'required' => $required,
            'validators' => $validators,
            'filters' => $filters,
        ];

        $methodGenerator->setBody('return ' . var_export($inputFilter, true) . ';');
        return $methodGenerator;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getFilters($data) {
        /**
         *
         */

        $filters = [];

        switch ($data['type']) {
            case 'string':
                break;
            case 'text':
                break;
            case 'datetime':
                break;
            case 'integer':
                array_push($filters, ['name' => "\\Zend\\Filter\\Int"]);
                break;
            case ClassMetadataInfo::ONE_TO_ONE:
                break;
            case ClassMetadataInfo::ONE_TO_MANY:
                break;
            case ClassMetadataInfo::MANY_TO_ONE:
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                break;
            default:
                continue;
        }

        return $filters;
    }

    /**
     * @param $data
     */
    private function getValidators($data) {
        /**
         *
         */

        $validators = [];

        if (isset($data['nullable']) and !$data['nullable']) {
            array_push($validators, [
                'name' => 'Zend\Validator\NotEmpty',
            ]);
        }

        switch ($data['type']) {
            case 'string':
                break;
            case 'text':
                break;
            case 'datetime':
                break;
            case 'integer':
                break;
            case ClassMetadataInfo::ONE_TO_ONE:
//                die(print_r($data));
                break;
            case ClassMetadataInfo::ONE_TO_MANY:
//                die(print_r($data));
                # tutaj trzeba zrobić cascade
                break;
            case ClassMetadataInfo::MANY_TO_ONE:
                array_push($validators, $this->createMethodManyToOneValidators($data));
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                # z tym jeszcze nie wiadomo
                break;
            default:
                continue;
        }

        return $validators;
    }

    /**
     * @param $data
     * @return array
     */
    public function createMethodManyToOneValidators($data) {
        return [
            'name' => 'Application\\Validator\\ObjectExists',
            'options' => array(
                'object_repository' => $data['targetEntity'],
                'fields' => 'id',
            ),
        ];
    }

} 