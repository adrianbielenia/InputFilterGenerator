<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 12.01.15
 * Time: 11:34
 */

namespace Application\CodeGenerator\InputFilter\FilterGenerator;

interface FilterGeneratorInterface {

    public function setField($field, $params = []);
    public function generate();

} 