<?php

namespace yii\cron\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidArgumentException;
use yii\cron\Task;

/**
 * @property Task $owner
 */
class CronLogBehavior extends Behavior {
    const STATUS_IDLE    = 'IDLE';
    const STATUS_ERROR   = 'ERROR';
    const STATUS_RUNNING = 'RUNNING';
    public $filePath = '@runtime/cron_log';
    public $dirMode  = 0755;
    /**
     * @var string
     */
    private $status;

    public function attach($owner) {
        if (!$owner instanceof Task) {
            throw new InvalidArgumentException('The CronLogBehavior can only be attached to Tasks');
        }
        parent::attach($owner);
    }

    public function events() {
        return [
            Task::EVENT_BEFORE_RUN => function () {
                $this->setStatus(static::STATUS_RUNNING);
            },
            Task::EVENT_AFTER_RUN  => function () {
                $this->setStatus(static::STATUS_IDLE);
            },
        ];
    }

    private function getFilePath() : string {
        return Yii::getAlias($this->filePath . DIRECTORY_SEPARATOR . $this->owner->getName() . '.log');
    }

    public function setStatus(string $status) : void {
        $file = $this->getFilePath();
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file) . DIRECTORY_SEPARATOR, $this->dirMode, true);
        }
        if (file_put_contents($file, $status) === false) {
            Yii::error('Failed to set status to ' . $status . ' for cron task ' . $file, __METHOD__);
        }
    }

    public function getStatus() : ?string {
        if (!isset($this->status)) {
            $file = $this->getFilePath();
            if (!file_exists($file)) {
                $this->status = static::STATUS_IDLE;
            } else {
                $this->status = file_get_contents($file);
            }
        }
        return $this->status;
    }
}
