<?php
namespace frontwise\monitor404\controllers;

use Craft;
use craft\web\Controller;
use craft\db\Query;
use frontwise\monitor404\elements\Web404;
use yii\web\Response;
use craft\helpers\UrlHelper;

class Web404Controller extends Controller
{
    /**
     * @param  int $siteId
     * @return Response
     */
    public function actionIndex(int $siteId = null): Response
    {
        if (Craft::$app->request->getIsPost() && Craft::$app->request->post('delete')) {
            if ($this->deleteWeb404s()) {
                Craft::$app->getSession()->setNotice(Craft::t('monitor404', '404 requests deleted'));

                // Redirect to index
                return $this->redirect(UrlHelper::cpUrl('monitor404'));
            }
        }

        $query = (new Query())
            ->select(['COUNT(*) as hits', '{{%frontwise_web_404s}}.url', '{{%frontwise_web_404s}}.id', '{{%frontwise_web_404s}}.dateCreated', '{{%elements_sites}}.siteId'])
            ->from('{{%frontwise_web_404s}}')
            ->leftJoin('{{%elements_sites}} ON {{%frontwise_web_404s}}.id = elementId')
            ->groupBy(['url'])
            ->orderBy('hits DESC, {{%frontwise_web_404s}}.dateCreated DESC');

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
     * @param int $id
     * @return Response
     */
    public function actionWeb404(int $siteId, int $id): Response
    {
        $element = Craft::$app->elements->getElementById($id, 'frontwise\monitor404\elements\Web404', $siteId);
        $url = null;
        if ($element) {
            $url = $element->url;
        }
        return $this->renderTemplate('monitor404/web404', [
            'url' => $url,
            'siteId' => $siteId,
            'showSiteMenu' => false,
            'crumbs' => [
                [
                    'url' => UrlHelper::cpUrl('monitor404/'),
                    'label' => 'All 404s',
                ],
            ]
        ]);
    }

    private function deleteWeb404s()
    {
        foreach (Craft::$app->sites->getAllSites() as $site) {
            $web404s = Web404::find()->site($site)->all();
            foreach ($web404s as $web404) {
                Craft::$app->elements->deleteElement($web404);
            }
        }

        return true;
    }
}