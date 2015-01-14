<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace InputFilterGenerator\Controller;

use Application\Service\InputFilterGeneratorService;

class InputFilterGeneratorController extends AbstractActionController
{
    public function indexAction()
    {

    }


    /**
     * @throws \Exception
     */
    public function generateInputFiltersAction() {
        /**
         * @var $inputFilterGeneratorService InputFilterGeneratorService
         */
        $inputFilterGeneratorService = $this->getService('InputFilterGenerator');
        return $inputFilterGeneratorService->generateInputFilters();
    }


}
