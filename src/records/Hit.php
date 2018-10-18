<?php

namespace frontwise\monitor404\records;


use craft\db\ActiveRecord;

class Hit extends ActiveRecord
{

    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%frontwise_web_404s_hits}}';
    }

}