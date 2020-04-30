<?php

namespace yii\cron;

use Carbon\Carbon;
use yii\base\Component;
use yii\cron\behaviors\CronLogBehavior;

/**
 * @mixin CronLogBehavior
 * @note All the moment properties are based on the values of the Carbon library. It is recommended to use the available
 *       Carbon constants for e.g. days of the week.
 */
abstract class Task extends Component {
    const EVENT_BEFORE_RUN = 'beforeRun';
    const EVENT_AFTER_RUN  = 'afterRun';
    protected $cronServerOnly = false;
    /**
     * @var array An array containing all other time properties that will get assigned based in this value.
     */
    public $when;
    /**
     * @var array The years in which this task should be run.
     */
    public $years;
    /**
     * @var array The months in which this task should be run.
     */
    public $months;
    /**
     * @var array The weeks in which this task should be run.
     */
    public $weeks;
    /**
     * @var array The days on which this task should be run.
     */
    public $days;
    /**
     * @var array The days of a week on which this task should be run.
     */
    public $daysOfWeek;
    /**
     * @var array The hours of a day on which this task should be run.
     */
    public $hours;
    /**
     * @var array The minutes of an hour on which this task should be run.
     */
    public $minutes;

    public function __construct($config = []) {
        if (array_key_exists('when', $config)) {
            $moments = array_filter($config['when'], function ($key) {
                return in_array($key, [
                    'years',
                    'months',
                    'weeks',
                    'days',
                    'daysOfWeek',
                    'hours',
                    'minutes',
                ]);
            }, ARRAY_FILTER_USE_KEY);
            unset($config['when']);
            $config = array_merge($moments, $config);
        }
        parent::__construct($config);
    }

    public function behaviors() {
        return [
            'log' => CronLogBehavior::class,
        ];
    }

    public function getName() : string {
        return stripslashes(get_called_class());
    }

    public function run() : bool {
        if (!$this->beforeRun()) {
            return false;
        }
        $result = $this->onRun();
        $this->afterRun();
        return $result;
    }

    protected function beforeRun() : bool {
        $this->trigger(static::EVENT_BEFORE_RUN);
        return true;
    }

    abstract protected function onRun() : bool;

    protected function afterRun() : void {
        $this->trigger(static::EVENT_AFTER_RUN);
    }

    public function isRunning() : bool {
        return $this->getStatus() === CronLogBehavior::STATUS_RUNNING;
    }

    /**
     * @param Cron $cron
     * @return bool If the task can be run based on the $cron server and configured moments.
     */
    public function canRun(Cron $cron) : bool {
        if ($this->cronServerOnly && !$cron->cronServer) {
            return false;
        }
        if ($this->getStatus() !== CronLogBehavior::STATUS_IDLE) {
            return false;
        }
        $date = Carbon::now($cron->timezone);
        if  (!empty($this->years) && !in_array($date->year, $this->years)) {
            return false;
        }
        if (!empty($this->months) && !in_array($date->month, $this->months)) {
            return false;
        }
        if (!empty($this->weeks) && !in_array($date->week, $this->weeks)) {
            return false;
        }
        if (!empty($this->daysOfWeek) && !in_array($date->dayOfWeek, $this->daysOfWeek)) {
            return false;
        }
        if (!empty($this->days) && !in_array($date->day, $this->days)) {
            return false;
        }
        if (!empty($this->hours) && !in_array($date->hour, $this->hours)) {
            return false;
        }
        if (!empty($this->minutes) && !in_array($date->minute, $this->minutes)) {
            return false;
        }
        return true;
    }
}
