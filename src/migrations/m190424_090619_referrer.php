<?php

namespace frontwise\monitor404\migrations;

use Craft;
use craft\db\Migration;
use frontwise\monitor404\records\Hit;

/**
 * m190424_090619_referrer migration.
 */
class m190424_090619_referrer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Hit::tableName(), 'referrer', $this->string()->null()->after('userAgent'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190424_090619_referrer cannot be reverted.\n";
        return false;
    }
}
