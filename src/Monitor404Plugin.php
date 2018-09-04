<?php
namespace frontwise\monitor404;

use Craft;

use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\ErrorHandler;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;
use yii\web\HttpException;
use frontwise\monitor404\elements\Web404;

class Monitor404Plugin extends Plugin
{

    public function init()
    {
        parent::init();

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $e) {
            $exception = $e->exception;
            if ($exception instanceof HttpException && $exception->statusCode === 404) {
                $request = Craft::$app->getRequest();

                $web404 = new Web404();
                $web404->remoteIP = $request->getRemoteIP();
                $web404->url = $request->getAbsoluteUrl();
                $web404->userAgent = $request->getUserAgent();
                $web404->message = $exception->getMessage();
                $web404->filePath = $exception->getFile();
                $web404->fileLine = $exception->getLine();
                $web404->siteId = Craft::$app->sites->getCurrentSite()->id;
                $success = Craft::$app->elements->saveElement($web404, true, false);
            }
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        Craft::info('frontwise/monitor404 plugin loaded', __METHOD__);
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'monitor404' => 'monitor404/web404/index',
            'monitor404/<siteId:\d+>' => 'monitor404/web404/index',
            'monitor404/<siteId:\d+>/<id:\d+>' => 'monitor404/web404/web404',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

}