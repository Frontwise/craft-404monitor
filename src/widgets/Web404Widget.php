<?php
namespace frontwise\monitor404\widgets;

use Craft;
use craft\base\Widget;
use craft\db\Query;
use craft\helpers\Db;
use craft\i18n\Locale;

use frontwise\monitor404\records\Hit;
use frontwise\monitor404\assetbundles\monitor404\Monitor404Asset;

class Web404Widget extends Widget {

    /**
     * @var int The total number of entries that the widget should show
     */
    public $period = 30;

    /**
     * @var string The type of the chart (column|line)
     */
    public $chartType = 'column';

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['period'], 'number', 'integerOnly' => true];
        $rules[] = [['chartType'], 'in', 'range' => ['line', 'bar']];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string {
        return Craft::t('monitor404', '404 monitor');
    }

    /**
     * @inheritdoc
     */
    protected static function allowMultipleInstances(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml() {
        $view = Craft::$app->getView();


        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $this->period . 'D'));

        $data = (new Query())
            ->select(['dateCreated', 'COUNT(*) as total'])
            ->from(Hit::tableName())
            ->where(['>', 'dateCreated', Db::prepareDateForDb($date)])
            ->groupBy('DAY(dateCreated)')
            ->all();

        $hits = array_fill_keys($this->_getDateRange(), 0);
        $formatter = Craft::$app->getFormatter();
        foreach ($data as $row) {
            $hits[$formatter->asDate($row['dateCreated'], Locale::LENGTH_SHORT)] = $row['total'];
        }
        // Strip keys, these are provided as labels to the chart
        $hits = array_values($hits);

        $view->registerAssetBundle(Monitor404Asset::class);
        return $view->renderTemplate('monitor404/widget', [
            'widget' => $this,
            'hits' => $hits,
            'dateRangeChart' => $this->_getDateRange(),
            'chartType' => $this->chartType,
        ]);
    }

    public function getSettingsHtml() {
        return Craft::$app->getView()->renderTemplate('monitor404/widgetSettings', [
            'widget' => $this,
        ]);
    }

    private function _getDateRange() {
        $now = strtotime('now');
        $formatter = Craft::$app->getFormatter();
        $dates = [];
        for ($i=0; $i < $this->period; $i++) {
            $date = strtotime("-$i day", $now);
            $date = $formatter->asDate($date, Locale::LENGTH_SHORT);
            array_unshift($dates, $date);
        }

        return $dates;
    }
}