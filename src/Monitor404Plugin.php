<?php
namespace frontwise\monitor404;

use Craft;

use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Dashboard;
use craft\web\ErrorHandler;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use yii\web\HttpException;
use frontwise\monitor404\elements\Web404;
use frontwise\monitor404\records\Hit;
use frontwise\monitor404\controllers\Web404Controller;
use frontwise\monitor404\models\Settings;
use frontwise\monitor404\queue\jobs\Remove404Requests;
use frontwise\monitor404\widgets\Web404Widget;

class Monitor404Plugin extends Plugin
{
    public $schemaVersion = '1.2.0';

    public $hasCpSettings = true;

    public static $plugin;

    /** @var array */
    public $controllerMap = [
        'web404' => Web404Controller::class,
    ];

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $e) {
            $exception = $e->exception;
            if ($exception instanceof HttpException && $exception->statusCode === 404) {
                $request = Craft::$app->getRequest();

                if (!$web404 = Web404::find()->url($request->getAbsoluteUrl())->one()) {
                    // Check for soft deleted element
                    if ($web404 = Web404::find()->trashed(true)->url($request->getAbsoluteUrl())->one()) {
                        Craft::$app->elements->restoreElement($web404);
                        // Save existing web404 to update the timestamp
                        $success = Craft::$app->elements->saveElement($web404, true, false);
                    } else {
                        // Create new web404
                        $web404 = new Web404();
                        $web404->url = $request->getAbsoluteUrl();
                        $web404->siteId = Craft::$app->sites->getCurrentSite()->id;
                        $success = Craft::$app->elements->saveElement($web404, true, false);
                    }

                } else {
                    // Save existing web404 to update the timestamp
                    $success = Craft::$app->elements->saveElement($web404, true, false);
                }

                if (!$success) {
                    Craft::error('Couldn\'t save web404 for url ' . $request->getAbsoluteUrl(), __METHOD__);
                    return;
                }

                $hit = new Hit();
                $hit->web404 = $web404->id;
                $hit->remoteIP = $request->getRemoteIP();
                $hit->userAgent = $request->getUserAgent();
                $hit->referrer = $request->getReferrer();
                $hit->message = $exception->getMessage();
                $hit->filePath = $exception->getFile();
                $hit->fileLine = $exception->getLine();
                $hit->save();

            }
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);
        Event::on(\craft\web\User::class, \craft\web\User::EVENT_AFTER_LOGIN, [$this, 'removeOldRequests']);
        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Web404Widget::class;
        });

        Craft::info('frontwise/monitor404 plugin loaded', __METHOD__);
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'monitor404' => 'monitor404/web404/index',
            'monitor404/<siteId:\d+>' => 'monitor404/web404/index',
            'monitor404/<siteId:\d+>/<id:\d+>' => 'monitor404/web404/hits',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    public function removeOldRequests()
    {
        $request = Craft::$app->getRequest();
        // Only on control panel
        if (!$request->getIsCpRequest()) {
            return;
        }

        Craft::$app->queue->push(new Remove404Requests());
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('monitor404/settings', [
            'settings' => $this->getSettings(),
        ]);
    }

}