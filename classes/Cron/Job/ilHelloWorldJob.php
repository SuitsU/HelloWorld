<?php declare(strict_types=1);

class ilHelloWorldJob extends ilCronJob
{

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return "HelloWorldJob";
    }

    /**
     * @inheritDoc
     */
    public function hasAutoActivation() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType() : int
    {
        return ilCronJob::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue() : int
    {
        return 60;
    }

    /**
     * @inheritDoc
     */
    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        $result->setCode(200);
        $result->setMessage('This is a test! Hello World.');

        $logFile = fopen("../cron.log", "w") or die("Unable to open file!");
        $txt = print_r($result, true);
        fwrite($logFile, $txt);
        fclose($logFile);

        return $result;
    }
}