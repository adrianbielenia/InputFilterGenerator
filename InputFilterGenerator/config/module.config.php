<?php

return array(
    'console' => [
        'router' => array(
            'routes' => array(
                'input_filter_generator' => array(
                    'type'    => 'simple',       // <- simple route is created by default, we can skip that
                    'options' => array(
                        'route'    => 'inputFilterGenerate',
                        'defaults' => array(
                            'controller' => 'InputFilterGenerator\Controller\InputFilterGeneratorController',
                            'action'     => 'generateInputFilters'
                        )
                    )
                )
            )
        )
    ],
    'controllers' => array(
        'invokables' => array(

        ),
    ),
    'service_manager' => array(
        'abstract_factories' => [
            'Application\Factory\InputFilterFactory',
        ],
        'factories' => array(

        ),
    ),
);
