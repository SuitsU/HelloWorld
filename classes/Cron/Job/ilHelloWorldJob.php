<?php

declare(strict_types=1); //strict_types declaration must be the very first statement in the script

class ilHelloWorldJob extends ilCronJob
{

    /**
     * @var ilSetting
     */
    protected $settings;

    public function __construct()
    {
        $this->settings = new ilSetting(ilHelloWorldPlugin::PLUGIN_ID);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "HelloWorldJob";
    }

    public function getTitle(): string
    {
        return 'Chris amazing '.ilHelloWorldPlugin::PLUGIN_NAME.' CronJob';
    }

    public function getDescription(): string
    {
        return ilHelloWorldPlugin::getInstance()->txt("cron_description");
    }

    /**
     * @inheritDoc
     */
    public function hasAutoActivation(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType(): int
    {
        return ilCronJob::SCHEDULE_TYPE_DAILY;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue(): int
    {
        return 1;
    }

    /**
     * @return bool
     */
    public function hasCustomSettings(): bool
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
        $major = explode('.', $version)[0];
        $minor = explode('.', $version)[1];

        // Array Key ist Select Value
        $options = [
            'minor' => 'Prüfe Minor- & Major-Updates (empfohlen)',
            'major' => 'Prüfe nur Major-Updates',
        ];

        $level = new ilSelectInputGUI(
            'Überprüfung',
            'level'
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
        $update_url->setInfo('Zum Überprüfen genutzte URL. Sollte nicht geändert werden wenn Sie nicht genau wissen was diese Einstellung bedeutet!');
        $update_url->setValue($this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v'));
        $update_url->setRequired(true);
        $a_form->addItem($update_url);

        $email_recipients = new ilTextInputGUI('Email Empfänger', 'email_recipients');
        $email_recipients->setInfo('Leer = Keine Emails, Mehrere Empfänger mit Semicolon (;) trennen.');
        $email_recipients->setValue($this->settings->get('email_recipients', ''));
        // $email_recipients->setRequired(true);
        $a_form->addItem($email_recipients);
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
        $this->settings->set('email_recipients', $a_form->getInput('email_recipients'));
        return true;
    }

    /** @return array recipients split by ; */
    public function getEmailRecipients() : array
    {
        $recipients_str = $this->settings->get('email_recipients', '');

        if (str_contains($recipients_str,';'))
            $recipients = explode(';',$recipients_str);
        else
            $recipients[0] = $recipients_str;

        return $recipients;
    }

    public function getNotificationTitle() :string
    {
        return sprintf("Update Notification %s", date('[d.m.Y]'));
    }

    public function getInsistenceLevel() :string
    {
        return $this->settings->get('insistence', 'middle');
    }

    public function getLevel() :string
    {
        return $this->settings->get('level', 'minor');
    }

    public function getDismissable() :bool
    {
        return ($this->getInsistenceLevel() != 'high');
    }

    /**
     * Dissmissed are saved in il_adn_dismiss. Reset Notifications
     * @param int         $id
     * @param String      $message
     * @param String|null $title
     * @return void
     */
    public function updateNotification(int $id, String $body) :void
    {
        $il_adn_notification = new ilADNNotification($id);
        $il_adn_notification->setTitle($this->getNotificationTitle());
        $il_adn_notification->setActive(true);
        $il_adn_notification->setBody($body);
        $il_adn_notification->setDismissable($this->getDismissable());
        $il_adn_notification->resetForAllUsers();
        $il_adn_notification->update();

        /*
        $ilDB->manipulate("UPDATE il_adn_notifications ".
            " SET
                    `title` = ".$ilDB->quote('Update Notification: '.$title, "text").",
                    `active` = 1,
                    `link` = ".$ilDB->quote($url, "text").",
                    `body` = ".$ilDB->quote($message. " <a href=\"$url\" style=\"text-decoration: none; color: lightblue;\">[read more...]</a>", "text").",
                    `dismissable` = ".$ilDB->quote((($insistence_level == 'high') ? 0 : 1), "integer").",
                    `event_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `display_start` = ".$ilDB->quote(strtotime('now'), "text").",
                    `last_update` = ".$ilDB->quote(strtotime('now'), "text")."
             WHERE
                 `id` =  ".$ilDB->quote($id, "integer").
            ";");

        */
    }

    public function removeNotification(int $id) :void
    {
        try {
            $il_adn_notification = new ilADNNotification($id);
            $il_adn_notification->setActive(false);
            $il_adn_notification->setDisplayEnd((new \DateTimeImmutable('now')));
            $il_adn_notification->update();
        } catch (\Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function getNotificationBody($newest_version_numeric, $url='#') :string
    {
        $version_numeric = ILIAS_VERSION_NUMERIC;
        return "Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric  <a style='text-decoration: none; color: lightblue;' href='$url' target='_blank'>[read more...]</a>";
    }

    public function getMailBody($newest_version_numeric, $url='#') :string
    {
        $version_numeric = ILIAS_VERSION_NUMERIC;
        return "Ihre Ilias-Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric. Mehr dazu auf: $url";
    }


    /**
     * @inheritDoc
     */
    public function createNotification(String $body): void
    {
        $il_adn_notification = new ilADNNotification();
        $il_adn_notification->setTitle($this->getNotificationTitle());
        $il_adn_notification->setBody($body);
        $il_adn_notification->setType(3);
        $il_adn_notification->setTypeDuringEvent(3);
        $il_adn_notification->setDismissable($this->getDismissable());
        $il_adn_notification->setPermanent(true);
        // $il_adn_notification->setCreatedBy(6); is done in create
        $il_adn_notification->setActive(true);
        $il_adn_notification->setLimitToRoles(false); // we don't know the role ids
        //$il_adn_notification->setLimitedToRoleIds(2);
        //interruptive is false by default
        //no link setters => setLink, type and target
        $il_adn_notification->setEventStart(new DateTimeImmutable('now'));
        $il_adn_notification->setEventEnd(new DateTimeImmutable('now'));
        $il_adn_notification->setDisplayStart(new DateTimeImmutable('now'));
        $il_adn_notification->setDisplayEnd(new DateTimeImmutable('now'));
        // $il_adn_notification->setCreateDate(new DateTimeImmutable('now')); is done in create
        // no setter for `last_update`

        $il_adn_notification->create();

        /*
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
            $ilDB->quote($message_text . ' <a style="text-decoration: none; color: lightblue;" href="'.$url.'">[read more...]</a>', "text").",".
            $ilDB->quote(3, "integer").",". #`type`,
            # 3,0,1,"[0,13,6]",,6,,1,"[""2""]",1,0,"",0,_top,1654960131,1654960131,1654960131,1654960131,1654960131,1654960131
            $ilDB->quote(3, "integer").",". #`type_during_event`,
            $ilDB->quote((($insistence_level == 'high') ? 0 : 1), "integer").",".  #`dismissable`,
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
        */
    }

    public function getCurrentNotification() :array
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $ilDB;

        if (!$ilDB->tableExists('il_adn_notifications')) { throw new Exception('il_adn_notifications does not exist!'); }

        $set = $ilDB->query("SELECT count(`id`) as `entity_amount`, max(`id`) as `highest_id`, max(`create_date`) as `newest_date` FROM il_adn_notifications WHERE `title` LIKE '%Update Notification%' AND `active` = 1;");
        $records = $ilDB->fetchAssoc($set);
        if (!empty($records)) {
            $ids = intval($records['entity_amount']);
            $highest_id = intval($records['highest_id']);
            $newest_date = intval($records['newest_date']);

            self::log(compact('highest_id', 'newest_date', 'ids'));
            self::log('newest date: '. date('Y-m-d', $newest_date));
            self::log('today: '. date('Y-m-d', strtotime('now')));
            $date1 = date('Y-m-d', $newest_date); // new DateTime(date('Y-m-d',$newest_date));
            $date2 = date('Y-m-d', strtotime('now')); // new DateTime(date('Y-m-d',strtotime('now')));
            self::log('compare dates: '. (($date1 == $date2) ? 'same' : 'not same'));


            return [
                'id' => $highest_id,
                'created' => $newest_date,
                'amount_of_notifications' => $ids,
            ];
        }
        return [
            'id' => 0,
            'created' => 0,
            'amount_of_notifications' => 0,
        ];

    }

    public function checkUrl($url) :array
    {
        self::log($url);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url); // https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v7.13 < try 7.14 und 8.0 und nehme das höchste das einen content zurück gibt.

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, '3');
        $content = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        self::log([
            "content" => (($content) ? 'not empty' : 'empty'),
            "status_code" => $status_code,
        ]);

        return [
          'status_code' => $status_code,
          'content' => $content
        ];
    }

    public function getNewestMayorVersion() :string
    {
        $newest_version_numeric = strval(ILIAS_VERSION_NUMERIC);
        $major = intval(explode('.', $newest_version_numeric)[0]);
        $minor = 0;
        if ($major <= 0) {
            throw new \Exception("Major version cannot be 0 or lower!");
        }
        $major = ($major + 1);

        $url = $this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v').$major.'.'.$minor;

        $result = $this->checkUrl($url);

        if ($result['status_code'] == 404) {
            return $newest_version_numeric;
        } else if ($result['content'] === false or empty($result['content'])) {
            return $newest_version_numeric;
        }
        else {
            return "$major.$minor"; // 8.0
        }
    }

    public function getNewestMinorVersion(string $newest_version_numeric = null) :string
    {
        if(is_null($newest_version_numeric)) {
            $newest_version_numeric = strval(ILIAS_VERSION_NUMERIC);
        }
        $major = intval(explode('.', $newest_version_numeric)[0]);
        $minor = intval(explode('.', $newest_version_numeric)[1]);
        if ($major <= 0) {
            throw new \Exception("Major version cannot be 0 or lower!");
        }
        for ($i = 0; $i <= 20; $i++) {

            $url = $this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v').$major.'.'.$minor;

            $result = $this->checkUrl($url);

            if ($result['status_code'] === 404) {
                $minor = $minor - 1;
                break;
            }
            if ($result['content'] === false or empty($result['content'])) {
                $minor = $minor - 1;
                break;
            } else {
                $minor++;
            }
        }

        return "$major.$minor";
    }



    public function sendMail(array $recipients, string $body)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $sender = $DIC->user()->getId();

        $mail = new ilMail($sender);

        foreach ($recipients as $recipient) {
            if(empty($recipient) OR !str_contains($recipient,'@')) {
                continue;
            }
            $mail->enqueue(
                $recipient,
                "",
                "",
                $this->getNotificationTitle(),
                $body,
                []
            );
        }

    }


    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        $result->setCode(200);

        $info = $this->getCurrentNotification();

        $version_numeric = strval(ILIAS_VERSION_NUMERIC);

        if($this->getLevel() == 'minor')
        {
            $newest_version_numeric = $this->getNewestMayorVersion();
            $newest_version_numeric = $this->getNewestMinorVersion($newest_version_numeric);
        }
        else {
            $newest_version_numeric = $this->getNewestMayorVersion();
        }

        $url = $this->settings->get('update_url', 'https://github.com/ILIAS-eLearning/ILIAS/releases/tag/v').$newest_version_numeric;

        /*
         * can be 'major' 'minor'
         */
        $notification_level = $this->settings->get('level', 'minor');

        /*
         * can be 'high' 'middle' 'low'
         */
        $insistence_level = $this->getInsistenceLevel();

        self::log(compact(
            'version_numeric',
            'newest_version_numeric',
            'notification_level',
            'insistence_level'
        ));

        //minor
        if ($version_numeric != $newest_version_numeric) {
            self::log('$version_numeric != $newest_version_numeric');

            //if (date('Y-m-d', $info['created']) != date('Y-m-d')) { Set in Settings

            if ($insistence_level == 'low')
            {
                self::log('$insistence_level == \'low\'');
                ilLoggerFactory::getLogger(ilHelloWorldPlugin::PLUGIN_ID)->log("Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric");
                $result->setMessage("Ihre Version $version_numeric ist nicht aktuell! Die aktuelle Version ist: $newest_version_numeric");
                return $result;
            }

            self::log('$insistence_level != \'low\'');

            if ($info['amount_of_notifications'] > 0) {
                self::log("update notification {$info['id']}");

                $this->updateNotification(
                    $info['id'],
                    $this->getNotificationBody($newest_version_numeric, $url)
                );
            } else {
                self::log('create notification...');

                $this->createNotification(
                    $this->getNotificationBody($newest_version_numeric, $url)
                );
            }

            $email_recipients = $this->getEmailRecipients();
            if (!empty($email_recipients[0])) {
                $this->sendMail(
                    $email_recipients,
                    $this->getNotificationBody($newest_version_numeric, $url)
                );
            }

            //} else { self::log('There is already a message for today!'); } // date1 = date2

            $result->setMessage('Version nicht aktuell!');
            return $result;
        }
        else {
            self::log('Version aktuell!');

            $this->removeNotification($info['id']);

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
    public static function log($message, String $level='debug'): void
    {
        HelloWorldUtilities::log($message, $level);
    }

    public static function getRandomTitle(): String
    {
        return 'Random Customized Title '.HelloWorldUtilities::generateRandomString(5).' for '.date('d.m.Y', strtotime('now'));
    }
}
