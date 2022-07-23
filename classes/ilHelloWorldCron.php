<?php declare(strict_types=1);

/**
 * A simple Hello-World plugin...
 * @author Christian Pietras <christian.pietras@colin-kiegel.com>
 */
class ilHelloWorldCron extends ilCronJob
{

    private const PLUGIN_ID='hewo';

    private const PLUGIN_NAME = 'Hello-World';

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return self::PLUGIN_ID;
    }

    /**
     * @return string
     */
    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
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
        return $result;
    }
}