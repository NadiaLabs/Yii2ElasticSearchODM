<?php

namespace Nadia\Yii2ElasticsearchODM;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        if (\Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'Nadia\ElasticsearchODM\Yii2\commands';
            $this->setControllerPath(__DIR__ . '/commands');
        }
    }
}
