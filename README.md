<h1 align="center">Yii2 cron extension</h1>

An extension for running cron tasks via PHP.

<h2>Basic usage</h2>
Tasks are stored in a config array in a file. The default file is `@app/config/tasks.php`, but this can be overwritten
by the `tasksFile` property.
The `tasksFile` should contain an array of task config arrays.

The `tasksFile` should look similar like below:
```php
<?php
return [
    'app\tasks\EchoTask', // Run every minute
    [
        'class' => 'app\tasks\WeeklyTask',
        'daysOfWeek' => [Carbon::MONDAY],
        'hours'      => [8],
        'minutes'    => [15],
    ], // Run every monday at 08:15
];
```

The supported properties for when to execute are listed below. Use the constants from [Carbon](https://carbon.nesbot.com/docs/) for the "days" and "daysOfWeek" properties.
* years
* months
* weeks
* days
* daysOfWeek
* hours
* minutes