<?php

namespace Nadia\Yii2ElasticsearchODM\Tests\components;

use Nadia\ElasticsearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticsearchODM\Document\IndexNameProvider;
use Nadia\ElasticsearchODM\Document\Manager;
use Nadia\Yii2ElasticsearchODM\components\Cache\Pool;
use Nadia\Yii2ElasticsearchODM\components\Yii2ElasticsearchODM;
use Nadia\Yii2ElasticsearchODM\components\EmptyClient;
use Nadia\Yii2ElasticsearchODM\Tests\components\Cache\Yii2Cache;
use Nadia\Yii2ElasticsearchODM\Tests\components\Document\Log;
use Nadia\Yii2ElasticsearchODM\Tests\components\Document\Repository\LogRepository;
use Nadia\Yii2ElasticsearchODM\Tests\TestCase;

class ElasticsearchODMTest extends TestCase
{
    public function testElasticsearchODM()
    {
        $this->mockWebApplication();

        $odm = Yii2ElasticsearchODM::make();

        $this->assertInstanceOf(Yii2ElasticsearchODM::class, $odm);
        $this->assertInstanceOf(LogRepository::class, $odm->getRepository(Log::class));
        $this->assertInstanceOf(Manager::class, $odm->getManager());
        $this->assertInstanceOf(EmptyClient::class, $odm->getClient());
        $this->assertInstanceOf(ClassMetadataLoader::class, $odm->getClassMetadataLoader());
        $this->assertInstanceOf(IndexNameProvider::class, $odm->getIndexNameProvider());
        $this->assertInstanceOf(Pool::class, $odm->getCachePool());
        $this->assertInstanceOf(Yii2Cache::class, $odm->cache);

        $this->assertEquals([Log::class], $odm->getDocumentClassNames());

        $this->destroyApplication();
    }
}
