<?php
return [
    'id'          => 'yii2-cron-app',
    'basePath'    => dirname(__DIR__),
    'vendorPath'  => dirname(dirname(__DIR__)) . '/vendor',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'components'  => [
        'cron' => [
            'class' => 'yii\cron\Cron',
        ],
    ],
];
