<?php

namespace Nadia\ElasticSearchODM\Yii2\Tests;

use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\Yii2\Cache\Pool;
use Nadia\ElasticSearchODM\Yii2\ElasticSearchODM;
use Nadia\ElasticSearchODM\Yii2\EmptyClient;
use Nadia\ElasticSearchODM\Yii2\Tests\Cache\Yii2Cache;
use Nadia\ElasticSearchODM\Yii2\Tests\Document\Log;
use Nadia\ElasticSearchODM\Yii2\Tests\Document\Repository\LogRepository;

class ElasticSearchODMTest extends TestCase
{
    public function testElasticSearchODM()
    {
        $this->mockWebApplication();

        $odm = ElasticSearchODM::make();

        $this->assertInstanceOf(ElasticSearchODM::class, $odm);
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
