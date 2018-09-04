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

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();

        echo " done\n";
    }

    public function safeDown()
    {
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
            'remoteIP' => $this->string()->notNull(),
            'userAgent' => $this->string(),
            'message' => $this->string(),
            'filePath' => $this->string(),
            'fileLine' => $this->integer()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey(null, '{{%frontwise_web_404s}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
    }
}
