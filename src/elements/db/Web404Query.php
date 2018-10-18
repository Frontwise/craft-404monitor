<?php

namespace frontwise\monitor404\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use frontwise\monitor404\elements\Web404;

class Web404Query extends ElementQuery
{
    public $url;

    public function url($value)
    {
        $this->url = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('frontwise_web_404s');

        $this->query->select([
            'elements_sites.siteId',
            'frontwise_web_404s.url',
        ]);

        if ($this->url) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.url', $this->url));
        }

        return parent::beforePrepare();
    }
}