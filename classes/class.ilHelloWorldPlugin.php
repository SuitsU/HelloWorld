<?php declare(strict_types=1);

class ilHelloWorldPlugin extends ilCronHookPlugin
{

    private const PLUGIN_CLASS_NAME = ilHelloWorldPlugin::class;
    private const PLUGIN_ID = 'hewo';
    private const PLUGIN_NAME = 'HelloWorld';

    /** Instance of this class
     * @var self|null
     */
    protected static $instance = null;

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return self::PLUGIN_ID;
    }


    public function getCronJobInstances() : array
    {
        return [new ilHelloWorldJob()];
    }

    public function getCronJobInstance($a_job_id) : ilHelloWorldJob
    {
        return new ilHelloWorldJob();
    }

    /**
     * @inheritDoc
     */
    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }
}