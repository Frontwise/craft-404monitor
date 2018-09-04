<?php

namespace frontwise\monitor404\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use frontwise\monitor404\elements\Web404;

class Web404Query extends ElementQuery
{
    public $url;
    public $remoteIP;
    public $userAgent;
    public $message;
    public $filePath;
    public $fileLine;

    public function url($value)
    {
        $this->url = $value;

        return $this;
    }

    public function remoteIP($value)
    {
        $this->remoteIP = $value;

        return $this;
    }

    public function userAgent($value)
    {
        $this->userAgent = $value;

        return $this;
    }

    public function message($value)
    {
        $this->message = $value;

        return $this;
    }

    public function filePath($value)
    {
        $this->filePath = $value;

        return $this;
    }

    public function fileLine($value)
    {
        $this->fileLine = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('frontwise_web_404s');

        // select the price column
        $this->query->select([
            'elements_sites.siteId',
            'frontwise_web_404s.url',
            'frontwise_web_404s.remoteIP',
            'frontwise_web_404s.userAgent',
            'frontwise_web_404s.message',
            'frontwise_web_404s.filePath',
            'frontwise_web_404s.fileLine',
        ]);

        if ($this->url) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.url', $this->url));
        }
        if ($this->remoteIP) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.remoteIP', $this->remoteIP));
        }
        if ($this->userAgent) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.userAgent', $this->userAgent));
        }
        if ($this->message) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.message', $this->message));
        }
        if ($this->filePath) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.filePath', $this->filePath));
        }
        if ($this->fileLine) {
            $this->subQuery->andWhere(Db::parseParam('frontwise_web_404s.fileLine', $this->fileLine));
        }

        return parent::beforePrepare();
    }
}