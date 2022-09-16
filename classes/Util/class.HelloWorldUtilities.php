<?php declare(strict_types=1);

class HelloWorldUtilities
{
    # /var/www/dev7/ilias/Customizing/global/plugins/Services/Cron/CronHook/HelloWorld/classes/Util/info.log
    static $plugin_path;

    static function init() {
        self::$plugin_path = substr(__DIR__, 0, (strpos(__DIR__,'/classes')));
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function log($message, String $level='info') {
        if (gettype($message) == 'array') {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }
        if (gettype($message) == 'boolean') {
            $message = (($message)? 'true':'false');
        }

        self::init();
        $log_path = self::$plugin_path.'/logs/';

        // throw new Exception($log_path);

        if (!file_exists($log_path)) mkdir($log_path,0775);

        if(!file_exists("$log_path/$level.log")) {
            touch("$log_path/$level.log");
        }

        $logFile = fopen("$log_path/$level.log", "a");
        $date = date('Y-m-d H:i:s');
        fwrite($logFile, PHP_EOL."$date: $message");
        fclose($logFile);
    }


}
