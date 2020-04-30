<?php

namespace yii\cron;

use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\cron\behaviors\CronLogBehavior;
use yii\helpers\VarDumper;

class Cron extends Component {
    public $tasksFile   = '@app/config/tasks.php';
    public $timezone    = 'Europe/Amsterdam';
    public $cronServer  = false;
    /**
     * @var Task[]
     */
    private $tasks = [];

    /**
     * @note Tasks are run in a try/catch to prevent an error from stopping further tasks from running.
     * @throws InvalidConfigException
     */
    public function run() {
        $this->loadTasks();
        foreach ($this->tasks as $task) {
            try {
                if (!$task->run()) {
                    Yii::warning('Failed to correctly run cron task: ' . $task->getName(), __METHOD__);
                }
            } catch (Throwable $e) {
                $task->setStatus(CronLogBehavior::STATUS_ERROR);
                $this->handleException($e);
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function loadTasks() {
        $tasksFile = Yii::getAlias($this->tasksFile);
        if (!file_exists($tasksFile)) {
            throw new InvalidConfigException('The cron tasks file does not exist: ' . $tasksFile);
        }
        $tasks = require $tasksFile;
        foreach ($tasks as $config) {
            try {
                /** @var Task $task */
                $task = Yii::createObject($config);
                if ($task->isRunning()) {
                    continue;
                }
                if ($task->canRun($this)) {
                    $this->tasks[] = $task;
                }
            } catch (Throwable $e) {
                Yii::error(
                    'Failed to load cron task: ' . VarDumper::dumpAsString($config) . PHP_EOL . VarDumper::dumpAsString($e),
                    __METHOD__
                );
            }
        }
    }

    public function handleException(Throwable $e) {
        Yii::error($e, __METHOD__);
    }
}
