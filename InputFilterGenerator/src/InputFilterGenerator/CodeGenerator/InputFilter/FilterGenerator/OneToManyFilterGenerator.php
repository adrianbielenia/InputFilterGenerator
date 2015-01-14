<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 12.01.15
 * Time: 11:39
 */

namespace Application\CodeGenerator\InputFilter\FilterGenerator;

use Zend\Code\Generator\ParameterGenerator;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Code\Generator\MethodGenerator;

class OneToManyFilterGenerator implements FilterGeneratorInterface {

    private $field = [];
    private $params = [];

    public function setField($field, $params = []) {
        $this->field = $field;
        $this->params = $params;
    }

    private function getFilters() {
        return [];
    }

    private function getValidators() {
        return [];
    }

    public function generate() {
        $filter = new UnderscoreToCamelCase();

        $classFilterName = $this->getFilterClassName();

        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( lcfirst( $filter->filter($this->field['fieldName']) ) . 'InputFilter' );
        $fieldParameter = new ParameterGenerator( 'value' );
        $methodGenerator->setParameter( $fieldParameter );

        $body = <<<CONTENT
/**
 * @var \$inputFilter\Application\InputFilter\\$classFilterName
 */

 \$collectionInputFilter = new \Zend\InputFilter\CollectionInputFilter();

         foreach((array)\$value as \$record) {
            if (!\$record) continue;
            \$inputFilter = \$this->getServiceLocator()->get('$classFilterName');
            \$inputFilter->enlistInputs(\$record);

            \$collectionInputFilter->setInputFilter(\$inputFilter);
        }

 return \$collectionInputFilter;

CONTENT;

        $methodGenerator->setBody($body);
        return $methodGenerator;
    }

    public function pricesInputFilter($value) {
        /**
         * @var $inputFilter\Application\InputFilter\PlatformPriceInputFilter
         */
        $collectionInputFilter = new \Zend\InputFilter\CollectionInputFilter();

        foreach((array)$value as $record) {
            if (!$record) continue;
            $inputFilter = $this->getServiceLocator()->get('PlatformPriceInputFilter');
            $inputFilter->setData($record);
            $inputFilter->enlistInputs();

            $collectionInputFilter->setInputFilter($inputFilter);
        }

        return $collectionInputFilter;
    }

    private function getFilterClassName() {
        if (!isset($this->field['targetEntity']) or !$this->field['targetEntity']) {

        }

        $withPatchName = $this->field['targetEntity'];

        if (strpos($withPatchName,'\\')) {
            $exploded = array_reverse(explode('\\', $withPatchName));
            return reset($exploded) . 'InputFilter';
        } else {
            return $withPatchName . 'InputFilter';
        }

    }

} 