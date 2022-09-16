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
        $test = new ilNumberInputGUI('Test', 'test');
        $test->setSize(5);
        $test->setSuffix('Unit');
        $test->setRequired(true);
        $test->allowDecimals(false);
        $test->setMinValue(1);
        $test->setInfo('Info');
        $test->setValue($this->settings->get('test', 30));

        // Array Key ist Select Value
        $options = [
            'exact' => 'Streng',
            'minor' => 'Mittel',
            'mayor' => 'Schwach',
        ];

        $test2 = new ilSelectInputGUI(
            'Überprüfung',
            "level"
        );
        $test2->setOptions($options);
        $test2->setInfo('Wie streng soll das Plugin die Ilias Version überprüfen?');
        $test2->setValue($this->settings->get('level', 'minor'));

        $a_form->addItem($test2);
        $a_form->addItem($test);
    }

    /**
     * @param ilPropertyFormGUI $a_form
     * @return bool
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        $this->settings->set('test', $a_form->getInput('test'));
        $this->settings->set('level', $a_form->getInput('level'));
        return true;
    }

    public function updateNotification(int $id, String $message_text, String $title = null) : void
    {
        global $ilDB;
        if ($title == null) $title = 'Title #'.HelloWorldUtilities::generateRandomString(5).' for day: '.date('d.m.Y', strtotime('now'));

        $ilDB->manipulate("UPDATE il_adn_notifications ".
            " SET
                    `title` = ".$ilDB->quote('Update Notification: '.$title, "integer").",
                    `body` = ".$ilDB->quote($message_text, "text").",
                    `active` = 1,
                    `event_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `display_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `last_update` = ".$ilDB->quote(strtotime('now'), "text")."
             WHERE
                 `id` =  ".$ilDB->quote($id, "integer").
            ";");
    }


    /**
     * @inheritDoc
     */
    public function createNotification(int $id, String $message_text, String $title = null) : void
    {
        global $ilDB;
        if ($title == null) $title = 'Title #'.HelloWorldUtilities::generateRandomString(5).' for day: '.date('d.m.Y', strtotime('now'));

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
            $ilDB->quote($message_text . '... <a href="#">[read more here]</a>', "text").",".
            $ilDB->quote(3, "integer").",". #`type`,
            # 3,0,1,"[0,13,6]",,6,,1,"[""2""]",1,0,"",0,_top,1654960131,1654960131,1654960131,1654960131,1654960131,1654960131
            $ilDB->quote(3, "integer").",". #`type_during_event`,
            $ilDB->quote(1, "integer").",".  #`dismissable`,
            $ilDB->quote(1, "integer").",". #`permanent`,
            $ilDB->quote("[0,13,6]", "text").",". #`allowed_users`,
            "NULL,". #`parent_id`,
            $ilDB->quote(6, "integer").",".  #`created_by`,
            "NULL,".  #`last_update_by`,
            $ilDB->quote(1, "integer").",".  #`active`,
            $ilDB->quote('["2"]', "text").",". #`limited_to_role_ids`,
            $ilDB->quote(1, "integer").",". #`limit_to_roles`,
            $ilDB->quote(0, "integer").",".  #`interruptive`,
            $ilDB->quote("", "text").",". #`link`,
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

        $set = $ilDB->query("SELECT count(`id`) as `entity_amount`, max(`id`) as `highest_id`, max(`create_date`) as `newest_date` FROM il_adn_notifications WHERE `title` LIKE '%Update Notification%';");
        $records = $ilDB->fetchAssoc($set);
        if(!empty($records)) {
            $ids = $records['entity_amount'];
            $highest_id = $records['highest_id'];
            $newest_date = intval($records['newest_date']);

            if(gettype($highest_id) != 'integer'){
                $highest_id = intval($highest_id);
            }

            HelloWorldUtilities::log(compact('highest_id','newest_date','ids'));

            HelloWorldUtilities::log('newest date: '. date('Y-m-d',$newest_date));
            HelloWorldUtilities::log('date: '. date('Y-m-d',strtotime('now')));
            $date1 = date('Y-m-d',$newest_date); // new DateTime(date('Y-m-d',$newest_date));
            $date2 = date('Y-m-d',strtotime('now')); // new DateTime(date('Y-m-d',strtotime('now')));
            HelloWorldUtilities::log('compare date: '. (($date1 == $date2)?'same':'not same'));
        }

        // maybe get settings from another plugin table => plugin helloworldsettings => how to input settings
        /*if ($ilDB->tableExists('rep_robj_xhew_data')) {
            $set = $ilDB->query("SELECT `name` as settings FROM `rep_robj_xhew_data` WHERE 1 LIMIT 1;"); // WHERE username=...
            $records = $ilDB->fetchAssoc($set);
            $settings = $records['settings'];
            HelloWorldUtilities::log(compact('settings'));
            //$settings = base64_decode($settings);
            //$settings = json_decode($settings);
        }*/

        // $version = ILIAS_VERSION;
        $version_numeric = ILIAS_VERSION_NUMERIC;

        // $newest_version = '7.13 2022-08-31';
        $newest_version_numeric = ILIAS_VERSION_NUMERIC;
        $mayor = intval(explode(ILIAS_VERSION_NUMERIC, '.')[0]);
        $minor = intval(explode(ILIAS_VERSION_NUMERIC, '.')[1]);


        $content = "";
        while ($content !== false){
            //$html = file_get_contents('https://docu.ilias.de/goto_docu_root_1.html');
            $ch = curl_init();

            $url = 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v'.$mayor.'.'.$minor;
            HelloWorldUtilities::log($url);

            curl_setopt($ch, CURLOPT_URL, $url); // https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v7.13 < try 7.14 und 8.0 und nehme das höchste das einen content zurück gibt.

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, '3');
            $content = curl_exec($ch);
            curl_close($ch);

            HelloWorldUtilities::log(( ($content)?'not empty':'empty' ));

            if($content === false) continue;
            else $minor++;
        }

        $newest_version_numeric = "$mayor.$minor";

        //if($settings->preferences->notify_on_difference == 'exact') //compare version
        //if($settings->preferences->notify_on_difference == 'minor') //compare version numeric
        //if($settings->preferences->notify_on_difference == 'mayor') //compare version explode on '.' first index

        //minor
        if ($version_numeric != $newest_version_numeric) {
            if(($date1 != $date2)) {
                $lipsum = new joshtronic\LoremIpsum();
                if ($ilDB->tableExists('il_adn_notifications')) {

                    // TODO FIND IN 'NOTIFICATION-CRONJOB-TABLE' ENTRY WITH VERSION NUMBER AND GET NOTIFICATION ID TO UPDATE

                    //if($settings->preferences->insistent == 'high') maybe every day new and not dismissable

                    //if($settings->preferences->insistent == 'middle') every day new and dismissable
                    //$this->createNotification(($highest_id+1),$lipsum->words(15));

                    //if($settings->preferences->insistent == 'low') every day update
                    if(!empty($records)) {
                        $this->updateNotification($highest_id, "Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric");
                    }
                    else {
                        $set = $ilDB->query("SELECT max(`id`) as `highest_id` FROM il_adn_notifications WHERE 1;");
                        $records = $ilDB->fetchAssoc($set);
                        $highest_id = intval($records["highest_id"]);

                        $this->createNotification(($highest_id + 1), "Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric");
                    }


                } // table exists
            } // date1 = date2
        } // version compare

        $result->setMessage('Hello World.');

        return $result;
    }

}