<?php

namespace frontwise\monitor404\assetbundles\monitor404;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Monitor404Asset extends AssetBundle {

    public function init() {
        $this->sourcePath = "@frontwise/monitor404/assetbundles/monitor404/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/plugins/Chart.min.js',
        ];

        parent::init();
    }

}