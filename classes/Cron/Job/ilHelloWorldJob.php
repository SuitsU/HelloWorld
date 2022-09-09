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
        $result->setMessage('This is a test! Hello World. Path: '.__DIR__);

        $set = $ilDB->query("SELECT count(`id`) as `entity_amount`, max(`id`) as `highest_id`, max(`create_date`) as `newest_date` FROM il_adn_notifications WHERE 1;");
        $records = $ilDB->fetchAssoc($set);
        $ids = $records['entity_amount'];
        $highest_id = $records['highest_id'];
        $newest_date = intval($records['newest_date']);

        Utilities::log('newest date: '. date('Y-m-d',$newest_date));
        Utilities::log('date: '. date('Y-m-d',strtotime('now')));
        $date1 = new DateTime(date('Y-m-d 00:00:01',$newest_date));
        $date2 = new DateTime(date('Y-m-d 00:00:01',strtotime('now')));
        Utilities::log('compare date: '. ($date1 == $date2)?'same':'not same');

        if(($date1 != $date2)) {

            $lipsum = new joshtronic\LoremIpsum();

            if ($ilDB->tableExists('il_adn_notifications')) {
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
                    $ilDB->quote(($highest_id+1), "integer").",".
                    $ilDB->quote('Title #'.Utilities::generateRandomString(5).' for day: '.$date1->format('d.m.Y'), "text").",".
                    $ilDB->quote($lipsum->words(15) . '... <a href="#">[read more here]</a>', "text").",".
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


        }
        return $result;
    }

}