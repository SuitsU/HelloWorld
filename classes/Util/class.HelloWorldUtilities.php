<?php

declare(strict_types=1);

class HelloWorldUtilities
{
    # /var/www/dev7/ilias/Customizing/global/plugins/Services/Cron/CronHook/HelloWorld/classes/Util/info.log
    public static $plugin_path;

    public static function init()
    {
        self::$plugin_path = substr(__DIR__, 0, (strpos(__DIR__, '/classes')));
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param        $message
     * @param String $level
     * @return void
     * @throws HelloWorldException
     */
    public static function log($message, String $level='info')
    {
        if (gettype($message) == 'array') {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }
        if (gettype($message) == 'boolean') {
            $message = (($message) ? 'true' : 'false');
        }

        self::init();
        $log_path = self::$plugin_path.'/logs/';

        if (!file_exists($log_path)) {
            mkdir($log_path, 0774);
        }

        if (!file_exists("$log_path/$level.log")) {
            touch("$log_path/$level.log");
            chmod("$log_path/$level.log", 0774);
        }

        try {
            $logFile = fopen("$log_path/$level.log", "a");
            $date = date('Y-m-d H:i:s');
            fwrite($logFile, PHP_EOL."$date: $message");
            fclose($logFile);
        } catch (Exception $ex) {
            $ex = new HelloWorldException($ex);
            $ex->addAdditionalInfo("$date: Error: [{$ex->getMessage()}] in [Line: {$ex->getLine()}], log_path: $log_path, level: $level, message: $message");
            throw ($ex);
        }
    }
}
class HelloWorldException extends \Exception
{
    /**
     * @var string  $additionalInfo
     */
    private $additionalInfo = "";

    /**
     * @var Exception $parent_exception
     */
    private $parent_exception = null;

    /**
     * @param Exception $ex
     */
    public function __construct(\Exception $ex)
    {
        $this->parent_exception = $ex;
        parent::__construct($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
    }

    /**
     * @param String $additionalInfo
     * @return void
     */
    public function addAdditionalInfo(String $additionalInfo)
    {
        $this->message = $this->parent_exception->getMessage() . PHP_EOL
            . "AdditionalInformation:" . PHP_EOL
            . $additionalInfo;
    }

    /**
     * @return String|null
     */
    public function getAdditionalInfo(): String
    {
        return $this->additionalInfo;
    }

    /**
     * @return Exception
     */
    public function getParentException(): \Exception
    {
        return $this->parent_exception?? new \Exception("Empty Exception", 100);
    }
}
