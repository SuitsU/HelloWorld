<?php declare(strict_types=1);

class ilHelloWorldPlugin extends ilCronHookPlugin
{

    private const PLUGIN_ID = 'hewo';
    private const PLUGIN_NAME = 'Hello World';


    public function getCronJobInstances()
    {
        return [
            $this->loadJobInstance(ilHelloWorldJob::class),
        ];
    }

    public function getCronJobInstance($a_job_id)
    {
        switch ($a_job_id) {
            case 0: // ilHelloWorldJob::JOB_ID:
                return $this->loadJobInstance(ilHelloWorldJob::class);

            default:
                return $this->loadJobInstance(ilHelloWorldJob::class);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }
}