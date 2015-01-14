<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 12.01.15
 * Time: 11:36
 */

namespace Application\CodeGenerator\InputFilter\FilterGenerator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

class FilterGeneratorFactory {

    /**
     * @param array $field
     * @param array $params
     * @return FilterGeneratorInterface
     * @throws \Exception
     */
    public static function getFilterGenerator(array $field, $params = []) {

        if (!$field['type']) {
            throw new \Exception('Błędny typ danych');
        }


        SWITCH ($field['type']) {
            case 'string':
                return new StringFilterGenerator();
                break;
            case 'text':
                break;
            case 'datetime':
                break;
            case 'integer':
                break;
            case ClassMetadataInfo::ONE_TO_ONE:
                return new OneToOneFilterGenerator();
                break;
            case ClassMetadataInfo::ONE_TO_MANY:
                return new OneToManyFilterGenerator();
                break;
            case ClassMetadataInfo::MANY_TO_ONE:
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
                break;
            default:
                return false;
            break;
        }

    }

} 