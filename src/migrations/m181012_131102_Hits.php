<?php

namespace frontwise\monitor404\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use frontwise\monitor404\elements\Web404;
use frontwise\monitor404\records\Hit;

/**
 * m181012_131102_Hits migration.
 */
class m181012_131102_Hits extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        echo " Updating 404 monitor\n";
        // create new hits table
        $this->createTables();

        // get all existing 404s
        foreach (Craft::$app->sites->getAllSites() as $site) {
            $web404s = Web404::find()->site($site)->orderBy(['id' => SORT_ASC])->all();
            foreach ($web404s as $web404) {
                $original = (new Query())
                    ->select(['*'])
                    ->from('{{%frontwise_web_404s}}')
                    ->where(['id' => $web404->id])
                    ->one();

                $same = (new Query())
                    ->select(['*'])
                    ->from('{{%frontwise_web_404s}}')
                    ->where(['url' => $web404->url])
                    ->orderBy(['id' => SORT_DESC])
                    ->all();

                $parentId = $same[0]['id'];

                // Create hit for existing 404
                $this->insert(Hit::tableName(), [
                    'web404' => $parentId,
                    'remoteIP' => $original['remoteIP'],
                    'userAgent' => $original['userAgent'],
                    'message' => $original['message'],
                    'filePath' => $original['filePath'],
                    'fileLine' => $original['fileLine'],
                    'dateCreated' => $original['dateCreated'],
                    'dateUpdated' => $original['dateUpdated'],
                ]);

                // If there are multiple duplicate urls in the db, delete this one
                if (count($same) > 1) {
                    Craft::$app->elements->deleteElement($web404);
                }
            }
        }

        // Now drop the columns
        $this->dropColumn('{{%frontwise_web_404s}}', 'remoteIP');
        $this->dropColumn('{{%frontwise_web_404s}}', 'userAgent');
        $this->dropColumn('{{%frontwise_web_404s}}', 'message');
        $this->dropColumn('{{%frontwise_web_404s}}', 'filePath');
        $this->dropColumn('{{%frontwise_web_404s}}', 'fileLine');

        // // Create index on url, make it unique
        $this->createIndex(null, '{{%frontwise_web_404s}}', ['url'], true);

        echo " Successfully updated\n";
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181012_131102_Hits cannot be reverted.\n";
        return false;
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
        $this->dropTableIfExists(Hit::tableName());
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

        $this->addForeignKey(null, Hit::tableName(), ['web404'], '{{%frontwise_web_404s}}', ['id'], 'CASCADE', null);

        // Create index on remoteIP
        $this->createIndex(null, Hit::tableName(), ['remoteIP'], false);
    }
}
