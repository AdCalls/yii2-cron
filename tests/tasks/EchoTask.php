<?php

namespace tests\tasks;

use yii\cron\Task;

class EchoTask extends Task {
    protected function onRun() : bool {
        echo 'Hello there :)' . PHP_EOL;
        return true;
    }
}