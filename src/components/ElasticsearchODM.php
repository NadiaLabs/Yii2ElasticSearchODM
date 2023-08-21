<?php

namespace Nadia\ElasticsearchODM\Yii2\components;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Nadia\ElasticSearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticSearchODM\Document\IndexNameProvider;
use Nadia\ElasticSearchODM\Document\Manager;
use Nadia\ElasticSearchODM\Helper\ElasticSearchHelper;
use Nadia\ElasticsearchODM\Yii2\components\Cache\Pool;
use Nadia\ElasticsearchODM\Yii2\components\Cache\Yii2CacheInterface;
use yii\base\Component;

class ElasticsearchODM extends Component
{
    /**
     * @var array
     */
    public $hosts = [];

    /**
     * @var string
     */
    public $indexNamePrefix;

    /**
     * @var string
     */
    public $env = 'dev';

    /**
     * @var bool
     */
    public $updateClassMetadataCache = true;

    /**
     * @var bool
     */
    public $refreshIndexAliasesCache = true;

    /**
     * @var string[] A list of directory paths to store document files.
     * format:
     * <code>
     *   [
     *     '$classNamePrefix1' => '$dir1',
     *     '\My\Namespace\ElasticSearchDocument\\' => '/path/to/documents',
     *   ]
     * </code>
     */
    public $documentDirs = [];

    /**
     * @var string A directory path to store metadata cached files.
     */
    public $cacheDir;

    /**
     * @var Yii2CacheInterface
     */
    public $cache;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var \Elastic\ElasticSearch\Client|\ElasticSearch\Client
     */
    private $client;

    /**
     * @var ClassMetadataLoader
     */
    private $classMetadataLoader;

    /**
     * @var IndexNameProvider
     */
    private $indexNameProvider;

    /**
     * @var Pool
     */
    private $cachePool;

    /**
     * @return static
     *
     * @throws \yii\base\InvalidConfigException
     */
    public static function make()
    {
        return \Yii::$app->get('ElasticsearchODM');
    }

    public function init()
    {
        if (method_exists(AnnotationRegistry::class, 'registerLoader')) {
            AnnotationRegistry::registerLoader(['Yii', 'autoload']);
        }
    }

    /**
     * @param string $documentClassName
     *
     * @return \Nadia\ElasticSearchODM\Document\Repository
     *
     * @throws \ReflectionException
     */
    public function getRepository($documentClassName)
    {
        return $this->getManager()->getRepository($documentClassName);
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        if (null === $this->manager) {
            $this->manager = new Manager(
                $this->getClient(),
                $this->getClassMetadataLoader(),
                $this->getIndexNameProvider(),
                $this->getCachePool()
            );
        }

        return $this->manager;
    }

    /**
     * @return \Elastic\ElasticSearch\Client|\ElasticSearch\Client
     */
    public function getClient()
    {
        $className = ElasticSearchHelper::getClientBuilderClassName();

        if (null === $this->client) {
            if (!empty($this->hosts)) {
                $this->client = (new $className())
                    ->setHosts($this->hosts)
                    ->build();
            } else {
                $this->client = new EmptyClient();
            }
        }

        return $this->client;
    }

    /**
     * @return ClassMetadataLoader
     */
    public function getClassMetadataLoader()
    {
        if (null === $this->classMetadataLoader) {
            $this->classMetadataLoader = new ClassMetadataLoader(
                $this->cacheDir,
                $this->updateClassMetadataCache,
                $this->indexNamePrefix,
                $this->env
            );
        }

        return $this->classMetadataLoader;
    }

    /**
     * @return IndexNameProvider
     */
    public function getIndexNameProvider()
    {
        if (null === $this->indexNameProvider) {
            $this->indexNameProvider = new IndexNameProvider(
                $this->getClient(),
                $this->indexNamePrefix,
                $this->getCachePool()
            );

            if ($this->refreshIndexAliasesCache) {
                $this->indexNameProvider->enableRefreshIndexAliasesCache();
            }
        }

        return $this->indexNameProvider;
    }

    /**
     * @return Pool
     */
    public function getCachePool()
    {
        if (null === $this->cachePool) {
            $this->cachePool = new Pool($this->cache);
        }

        return $this->cachePool;
    }

    /**
     * @return string[]
     */
    public function getDocumentClassNames()
    {
        $documentClassNames = [];

        foreach ($this->documentDirs as $classNamePrefix => $dir) {
            foreach (scandir($dir) as $filename) {
                if (is_file($dir . '/' . $filename)) {
                    $documentClassNames[] = $classNamePrefix . substr($filename, 0, -4);
                }
            }
        }

        return $documentClassNames;
    }
}
