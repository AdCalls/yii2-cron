<?php

namespace app\controllers;

use Yii;
use yii\console\Controller;

class CronController extends Controller {
    public function actionIndex() {
        Yii::$app->cron->run();
    }
}
