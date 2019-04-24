<?php
namespace frontwise\monitor404\controllers;

use Craft;
use craft\web\Controller;
use craft\db\Query;
use frontwise\monitor404\elements\Web404;
use yii\web\Response;
use craft\helpers\UrlHelper;
use frontwise\monitor404\records\Hit;

class Web404Controller extends Controller
{
    /**
     * @param  int $siteId
     * @return Response
     */
    public function actionIndex(int $siteId = null): Response
    {
        if (Craft::$app->request->getIsPost() && Craft::$app->request->post('delete')) {
            if ($this->_deleteWeb404s()) {
                Craft::$app->getSession()->setNotice(Craft::t('monitor404', 'Requests deleted.'));

                // Redirect to index
                return $this->redirect(UrlHelper::cpUrl('monitor404'));
            }
        }

        $subQuery = (new Query())
            ->select(['COUNT(*)'])
            ->from(Hit::tableName())
            ->where('web404 = {{%frontwise_web_404s}}.id')
        ;

        $query = (new Query())
            ->select(['hits' => $subQuery, '{{%frontwise_web_404s}}.url as url', '{{%frontwise_web_404s}}.id as id', '{{%elements}}.dateUpdated as dateUpdated', '{{%elements_sites}}.siteId as siteId'])
            ->from('{{%frontwise_web_404s}}')
            ->leftJoin('{{%elements_sites}} ON {{%frontwise_web_404s}}.id = elementId')
            ->leftJoin('{{%elements}} ON {{%frontwise_web_404s}}.id = {{%elements}}.id')
            ->where('{{%elements}}.dateDeleted IS NULL')
            ->orderBy('hits DESC, dateUpdated DESC');

        if ($siteId) {
            $query->where('siteId = ' . $siteId);
        }

        $web404s = $query->all();

        $sources = Web404::sources();

        return $this->renderTemplate('monitor404/index', [
            'sources' => $sources,
            'web404s' => $web404s,
            'selectedSource' => $siteId,
            'fullPageForm' => true,
        ]);
    }

    /**
     * @param int $siteId
     * @param int $id web404Id
     * @return Response
     */
    public function actionHits(int $siteId, int $id): Response
    {
        $element = Craft::$app->elements->getElementById($id, Web404::class, $siteId);
        $url = null;
        $hits = [];
        if ($element) {
            $url = $element->url;
            $hits = Hit::find()->where(['web404' => $element->id])->orderBy(['dateCreated' => SORT_DESC])->all();
        }

        return $this->renderTemplate('monitor404/hit', [
            'url' => $url,
            'siteId' => $siteId,
            'showSiteMenu' => false,
            'hits' => $hits,
            'id' => $id,
            'crumbs' => [
                [
                    'url' => UrlHelper::cpUrl('monitor404/'),
                    'label' => Craft::t('monitor404', 'All 404 requests'),
                ],
            ]
        ]);
    }

    public function actionDeleteHit(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $hitId = $request->getRequiredBodyParam('id');
        $success = Craft::$app->getDb()->createCommand()
            ->delete(Hit::tableName(), ['id' => $hitId])
            ->execute();

        return $this->asJson(['success' => $success]);
    }

    public function actionDeleteWeb404(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $id = $request->getRequiredBodyParam('id');
        $success = Craft::$app->elements->deleteElementById($id);
        if ($success) {
            // Delete 404 hits
            Craft::$app->getDb()->createCommand()
                ->delete(Hit::tableName(), ['web404' => $id])
                ->execute();

            Craft::$app->getSession()->setNotice(Craft::t('monitor404', 'Requests deleted.'));
        }

        // Redirect to index
        return $this->redirect(UrlHelper::cpUrl('monitor404'));
    }

    private function _deleteWeb404s(): bool
    {
        foreach (Craft::$app->sites->getAllSites() as $site) {
            $web404s = Web404::find()->site($site)->all();
            foreach ($web404s as $web404) {
                Craft::$app->elements->deleteElement($web404);

                // Delete 404 hits
                Craft::$app->getDb()->createCommand()
                    ->delete(Hit::tableName(), ['web404' => $web404->id])
                    ->execute();
            }
        }

        return true;
    }
}