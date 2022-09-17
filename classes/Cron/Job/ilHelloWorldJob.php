<?php declare(strict_types=1);

class ilHelloWorldJob extends ilCronJob
{
    /**
     * @var ilSetting
     */
    protected $settings;

    public function __construct()
    {
        $this->settings = new ilSetting('helloworld');
    }

    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return "HelloWorldJob";
    }

    public function getTitle() : string
    {
        return 'Chris amazing '.ilHelloWorldPlugin::PLUGIN_NAME.' CronJob';
    }

    public function getDescription() : string
    {
        return ilHelloWorldPlugin::getInstance()->txt("cron_description");
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
        return 2;
    }

    /**
     * @return bool
     */
    public function hasCustomSettings()
    {
        return true;
    }

    /**
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
        /*
        $test = new ilNumberInputGUI('Test', 'test');
        $test->setSize(5);
        $test->setSuffix('Unit');
        $test->setRequired(true);
        $test->allowDecimals(false);
        $test->setMinValue(1);
        $test->setInfo('Info');
        $test->setValue($this->settings->get('test', 30));
        */

        $version = strval(ILIAS_VERSION_NUMERIC);
        $mayor = explode('.',$version)[0];
        $minor = explode('.',$version)[1];

        // Array Key ist Select Value
        $options = [
            'exact' => 'Streng (Immer die aktuellste Version)',
            'minor' => "Mittel (Benachrichtigung bei neuer Minor-Version > $mayor.$minor)",
            'mayor' => "Schwach (Benachrichtigung wenn ein neues Ilias (".(intval($mayor)+1).") erscheint)",
        ];

        $level = new ilSelectInputGUI(
            'Überprüfung',
            "level"
        );
        $level->setOptions($options);
        $level->setInfo('Wie streng soll das Plugin die Ilias Version überprüfen?');
        $level->setValue($this->settings->get('level', 'minor'));

        $a_form->addItem($level);

        // Array Key ist Select Value
        $options = [
            'high' => 'Streng (Können nicht geschlossen werden!)', // TODO Müssen nach update entfernt werden (wenn $version = $newest_version)
            'middle' => 'Mittel (Können geschlossen werden, werden nicht erneut angezeigt) [Empfohlen]',
            'low' => 'Schwach (Es gibt nur einen Log-Eintrag!)',
        ];

        $insistence = new ilSelectInputGUI(
            'Benachrichtigungen',
            "insistence"
        );
        $insistence->setOptions($options);
        $insistence->setInfo('Wie streng sollen die Benachrichtigungen sein?');
        $insistence->setValue($this->settings->get('insistence', 'middle'));

        $a_form->addItem($insistence);

        $update_url = new ilTextInputGUI('Update Check URL', 'update_url');
        $insistence->setInfo('Zum Überprüfen genutzte URL. Sollte nicht geändert werden wenn Sie nicht genau wissen was diese Einstellung bedeutet!');
        $update_url->setValue($this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v'));
        $update_url->setRequired(true);
        $a_form->addItem($update_url);

    }

    /**
     * @param ilPropertyFormGUI $a_form
     * @return bool
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->settings->set('level', $a_form->getInput('level'));
        $this->settings->set('insistence', $a_form->getInput('insistence'));
        $this->settings->set('update_url', $a_form->getInput('update_url'));
        return true;
    }

    /**
     * IF DISMISSED CANNOT BE RESHOWN! MAKE SURE YOU GET YOUR SETTINGS RIGHT!
     * @param int         $id
     * @param String      $message
     * @param String|null $title
     * @return void
     */
    public function updateNotification(int $id, String $message, String $title=null, String $url='#', String $insistence_level='high') : void
    {
        global $ilDB;
        if ($title == null) $title = 'Title #'.HelloWorldUtilities::generateRandomString(5).' for day: '.date('d.m.Y', strtotime('now'));

        $ilDB->manipulate("UPDATE il_adn_notifications ".
            " SET
                    `title` = ".$ilDB->quote('Update Notification: '.$title, "integer").",
                    `active` = 1,
                    `link` = ".$ilDB->quote($url, "text").",
                    `body` = ".$ilDB->quote($message. " <a href=\"$url\" style=\"text-decoration: none; color: lightblue;\">[read more...]</a>", "text").",
                    `dismissable` = ".$ilDB->quote((($insistence_level == 'high')? 0:1), "integer").",
                    `event_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `display_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `last_update` = ".$ilDB->quote(strtotime('now'), "text")."
             WHERE
                 `id` =  ".$ilDB->quote($id, "integer").
            ";");
    }

    public function removeNotification(int $id) {
        global $ilDB;

        try {
            $ilDB->manipulate("UPDATE il_adn_notifications ".
                        " SET
                                `active` = 0,
                         WHERE
                             `id` =  ".$ilDB->quote($id, "integer").
                        ";");
        } catch (\Exception $ex)
        {
            self::log($ex->getMessage());
        }

    }


    /**
     * @inheritDoc
     */
    public function createNotification(int $id, String $message_text, String $title = null, String $url='#', String $insistence_level='high') : void
    {
        global $ilDB;
        if ($title == null) self::getRandomTitle();

        $ilDB->manipulate("INSERT INTO il_adn_notifications ".
            "(
                    `id`,
                    `title`,
                    `body`,
                    `type`,
                    `type_during_event`,
                    `dismissable`,
                    `permanent`,
                    `allowed_users`,
                    `parent_id`,
                    `created_by`,
                    `last_update_by`,
                    `active`,
                    `limited_to_role_ids`,
                    `limit_to_roles`,
                    `interruptive`,
                    `link`,
                    `link_type`,
                    `link_target`,
                    `event_start`,
                    `event_end`,
                    `display_start`,
                    `display_end`,
                    `create_date`,
                    `last_update`
                ) VALUES (".
            $ilDB->quote($id, "integer").",".
            $ilDB->quote('Update Notification: '.$title, "text").",".
            $ilDB->quote($message_text . ' <a style="text-decoration: none; color: lightblue;" href="'.$url.'">[read more here]</a>', "text").",".
            $ilDB->quote(3, "integer").",". #`type`,
            # 3,0,1,"[0,13,6]",,6,,1,"[""2""]",1,0,"",0,_top,1654960131,1654960131,1654960131,1654960131,1654960131,1654960131
            $ilDB->quote(3, "integer").",". #`type_during_event`,
            $ilDB->quote((($insistence_level == 'high')? 0:1), "integer").",".  #`dismissable`,
            $ilDB->quote(1, "integer").",". #`permanent`,
            $ilDB->quote("[0,13,6]", "text").",". #`allowed_users`,
            "NULL,". #`parent_id`,
            $ilDB->quote(6, "integer").",".  #`created_by`,
            "NULL,".  #`last_update_by`,
            $ilDB->quote(1, "integer").",".  #`active`,
            $ilDB->quote('["2"]', "text").",". #`limited_to_role_ids`,
            $ilDB->quote(1, "integer").",". #`limit_to_roles`,
            $ilDB->quote(0, "integer").",".  #`interruptive`,
            $ilDB->quote($url, "text").",". #`link`,
            $ilDB->quote(0, "integer").",". #`link_type`,
            $ilDB->quote("_top", "text").",". #`link_target`,
            $ilDB->quote(strtotime('now'), "text").",". #`event_start`,
            $ilDB->quote(strtotime('now'), "text").",". #`event_end`,
            $ilDB->quote(strtotime('now'), "text").",". #`display_start`,
            $ilDB->quote(strtotime('now'), "text").",". #`display_end`,
            $ilDB->quote(strtotime('now'), "text").",". #`create_date`,
            $ilDB->quote(strtotime('now'), "text"). #`last_update`
            ")");
    }


    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run() : ilCronJobResult
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $ilDB;

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        $result->setCode(200);

        $set = $ilDB->query("SELECT count(`id`) as `entity_amount`, max(`id`) as `highest_id`, max(`create_date`) as `newest_date` FROM il_adn_notifications WHERE `title` LIKE '%Update Notification%' AND `active` = 1;");
        $records = $ilDB->fetchAssoc($set);
        $ids = 0;
        $highest_id = 1;
        if(!empty($records)) {
            $ids = intval($records['entity_amount']);
            $highest_id = intval($records['highest_id']);
            $newest_date = intval($records['newest_date']);

            self::log(compact('highest_id','newest_date','ids'));

            self::log('newest date: '. date('Y-m-d',$newest_date));
            self::log('today: '. date('Y-m-d',strtotime('now')));
            $date1 = date('Y-m-d',$newest_date); // new DateTime(date('Y-m-d',$newest_date));
            $date2 = date('Y-m-d',strtotime('now')); // new DateTime(date('Y-m-d',strtotime('now')));
            self::log('compare dates: '. (($date1 == $date2)?'same':'not same'));
        }

        // $version = ILIAS_VERSION;
        $version_numeric = strval(ILIAS_VERSION_NUMERIC);

        // $newest_version = '7.13 2022-08-31';
        $newest_version_numeric = strval(ILIAS_VERSION_NUMERIC);

        $mayor = intval(explode('.',$newest_version_numeric)[0]);
        $minor = intval(explode('.',$newest_version_numeric)[1]);

        self::log(compact(
            'version_numeric',
            'newest_version_numeric',
            'mayor',
            'minor'
        ));

        if($mayor <= 0) {
            throw new \Exception("Mayor version cannot be 0 or lower!");
        }

        $url = "";
        for ($i = 0; $i <= 20; $i++) {
            //$html = file_get_contents('https://docu.ilias.de/goto_docu_root_1.html');
            $ch = curl_init();

            $url = $this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v').$mayor.'.'.$minor;
            self::log($url);

            curl_setopt($ch, CURLOPT_URL, $url); // https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v7.13 < try 7.14 und 8.0 und nehme das höchste das einen content zurück gibt.

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, '3');
            $content = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            self::log([
                "content" => ( ($content)?'not empty':'empty' ),
                "status_code" => $status_code,
            ]);

            if($status_code === 404) {
                $minor = $minor - 1;
                break;
            }
            if($content === false OR empty($content)) {
                $minor = $minor - 1;
                break;
            }

            else $minor++;
        }

        $newest_version_numeric = "$mayor.$minor";
        $url = $this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v')."$mayor.$minor";

        /*
         * can be 'mayor' 'minor' 'exact'
         */
        $notification_level = $this->settings->get('level', 'minor');

        /*
         * can be 'high' 'middle' 'low'
         */
        $insistence_level = $this->settings->get('insistence', 'low');

        self::log(compact(
            'version_numeric',
            'newest_version_numeric',
            'notification_level',
            'insistence_level'
        ));

        //minor
        if ($version_numeric != $newest_version_numeric) {

            self::log('$version_numeric != $newest_version_numeric');

            if(($date1 != $date2)) {

                self::log('$date1 != $date2');

                $lipsum = new joshtronic\LoremIpsum();
                $test = $lipsum->words(25);
                self::log($test);

                if ($ilDB->tableExists('il_adn_notifications')) {

                    self::log('$ilDB->tableExists(\'il_adn_notifications\')');

                    if($insistence_level == 'low') {

                        self::log('$insistence_level == \'low\'');
                        self::log("Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric", 'info');
                        $result->setMessage("Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric");
                        return $result;

                    }

                    self::log('$insistence_level != \'low\'');

                    if (!empty($records) AND !($ids == 0)) { // ids == 0 means none with title Update Notification

                        self::log("update notification $highest_id");

                        $this->updateNotification(
                            $highest_id,
                            "Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric",
                            date('[Y.m.d]'),
                            $url,
                            $insistence_level
                        );

                    } else {

                        self::log('create notification...');

                        $set = $ilDB->query("SELECT max(`id`) as `highest_id` FROM il_adn_notifications WHERE 1;");
                        $records = $ilDB->fetchAssoc($set);
                        $highest_id = intval($records["highest_id"]);

                        self::log("create notification $highest_id");

                        $this->createNotification(
                            ($highest_id + 1),
                            "Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric",
                            date('[Y.m.d]'),
                            $url,
                            $insistence_level
                        );

                    }
                    self::log('Table does not exist!!', 'error');
                } // table exists
                self::log('Today there is already a message!');
            } // date1 = date2
            $result->setMessage('Version nicht aktuell!');
            return $result;
        } // version compare
        else
        {

            self::log('Version aktuell!');

            $this->removeNotification($highest_id);

            $result->setMessage('Version aktuell!');
            return $result;
        }
    }

    /**
     * @param        $message
     * @param String $level
     * @return void
     * @throws HelloWorldException
     */
    static function log($message, String $level='debug') : void
    {
        HelloWorldUtilities::log($message, $level);
    }

    static function getRandomTitle() : String
    {
        return 'Random Customized Title '.HelloWorldUtilities::generateRandomString(5).' for '.date('d.m.Y', strtotime('now'));
    }

}