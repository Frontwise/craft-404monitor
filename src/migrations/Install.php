<?php

namespace frontwise\monitor404\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\elements\User;
use craft\helpers\StringHelper;
use craft\mail\Mailer;
use craft\mail\transportadapters\Php;
use craft\models\Info;
use craft\models\Site;
use frontwise\monitor404\records\Hit;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();

        echo " done\n";
    }

    public function safeDown()
    {
        $this->dropTableIfExists(Hit::tableName());
        $this->dropTableIfExists('{{%frontwise_web_404s}}');
        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%frontwise_web_404s}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createIndex(null, '{{%frontwise_web_404s}}', ['url'], true);
        $this->addForeignKey(null, '{{%frontwise_web_404s}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);

        $this->createTable(Hit::tableName(), [
            'id' => $this->primaryKey(),
            'web404' => $this->integer()->notNull(),
            'remoteIP' => $this->string()->notNull(),
            'userAgent' => $this->string(),
            'message' => $this->string(),
            'filePath' => $this->string(),
            'fileLine' => $this->integer()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Hit::tableName(), ['remoteIP'], false);
        $this->addForeignKey(null, Hit::tableName(), ['web404'], '{{%frontwise_web_404s}}', ['id'], 'CASCADE', null);
    }
}
