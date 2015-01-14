<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 12.01.15
 * Time: 11:39
 */

namespace Application\CodeGenerator\InputFilter\FilterGenerator;

use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Code\Generator\MethodGenerator;

class StringFilterGenerator implements FilterGeneratorInterface {

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

        $methodGenerator = new MethodGenerator();
        $methodGenerator->setName( lcfirst( $filter->filter($this->field['fieldName']) ) . 'InputFilter' );

        if (isset($this->field['nullable']) and !$this->field['nullable']) {
            $required = true;
        } else {
            $required = false;
        }

        $filters = $this->getFilters($this->field);
        $validators = $this->getValidators($this->field);

        $inputFilter = [
            'name' => $this->field['fieldName'],
            'required' => $required,
            'validators' => $validators,
            'filters' => $filters,
        ];

        $methodGenerator->setBody('return ' . var_export($inputFilter, true) . ';');
        return $methodGenerator;
    }

} 