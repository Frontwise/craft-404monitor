<?php
namespace frontwise\monitor404\queue\jobs;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\queue\BaseJob;
use frontwise\monitor404\Monitor404Plugin;
use frontwise\monitor404\elements\Web404;
use frontwise\monitor404\records\Hit;

class Remove404Requests extends BaseJob
{
    public function execute($queue)
    {
        $settings = Monitor404Plugin::$plugin->getSettings();
        // Only continue if storage period is set
        if (!is_numeric($settings->storePeriod) || $settings->storePeriod <= 0) {
            return;
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $settings->storePeriod . 'D'));

        // Remove all web404 elements created before storagePeriod
        $web404s = (new Query)
            ->select(['id'])
            ->from('{{%frontwise_web_404s}}')
            ->where(['<', 'dateCreated', Db::prepareDateForDb($date)])
            ->all();
        foreach($web404s as $web404) {
            Craft::$app->elements->deleteElementById($web404['id']);
        }

        // Remove all hits created before storagePeriod
        $success = Craft::$app->getDb()->createCommand()
            ->delete(Hit::tableName(), ['<', 'dateCreated', Db::prepareDateForDb($date)])
            ->execute()
        ;
    }

    public function defaultDescription()
    {
        return Craft::t('monitor404', 'Removing 404 requests');
    }
}