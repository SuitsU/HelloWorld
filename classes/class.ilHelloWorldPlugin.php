<?php

declare(strict_types=1);

include 'Util/class.HelloWorldUtilities.php';

require_once __DIR__ . '/../vendor/autoload.php';

/*spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});*/

class ilHelloWorldPlugin extends ilCronHookPlugin
{
    public const PLUGIN_CLASS_NAME = ilHelloWorldPlugin::class;
    public const PLUGIN_ID = 'hewo';
    public const PLUGIN_NAME = 'HelloWorld';

    /** Instance of this class
     * @var self|null
     */
    protected static $instance = null;

    /**
     * @return ilHelloWorldPlugin|null
     */
    public static function getInstance(): ?ilHelloWorldPlugin
    {
        if (self::$instance) {
            return self::$instance;
        }

        return new ilHelloWorldPlugin();
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::PLUGIN_ID;
    }


    public function getCronJobInstances(): array
    {
        return [new ilHelloWorldJob()];
    }

    public function getCronJobInstance($a_job_id): ilHelloWorldJob
    {
        return new ilHelloWorldJob();
    }

    /**
     * @inheritDoc
     */
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }
}
