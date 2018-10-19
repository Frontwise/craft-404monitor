<?php

namespace frontwise\monitor404\models;

use craft\base\Model;

class Settings extends Model
{
    public $storePeriod = NULL;

    public function rules()
    {
        return [
            [['storePeriod'], 'integer', 'min' => -1],
        ];
    }

}