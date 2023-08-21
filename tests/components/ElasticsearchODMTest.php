<?php

namespace Nadia\ElasticsearchODM\Yii2\Tests\components;

use Nadia\ElasticsearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticsearchODM\Document\IndexNameProvider;
use Nadia\ElasticsearchODM\Document\Manager;
use Nadia\ElasticsearchODM\Yii2\components\Cache\Pool;
use Nadia\ElasticsearchODM\Yii2\components\ElasticsearchODM;
use Nadia\ElasticsearchODM\Yii2\components\EmptyClient;
use Nadia\ElasticsearchODM\Yii2\Tests\components\Cache\Yii2Cache;
use Nadia\ElasticsearchODM\Yii2\Tests\components\Document\Log;
use Nadia\ElasticsearchODM\Yii2\Tests\components\Document\Repository\LogRepository;
use Nadia\ElasticsearchODM\Yii2\Tests\TestCase;

class ElasticsearchODMTest extends TestCase
{
    public function testElasticsearchODM()
    {
        $this->mockWebApplication();

        $odm = ElasticsearchODM::make();

        $this->assertInstanceOf(ElasticsearchODM::class, $odm);
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
