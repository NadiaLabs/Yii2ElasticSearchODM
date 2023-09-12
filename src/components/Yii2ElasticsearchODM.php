<?php

namespace Nadia\Yii2ElasticsearchODM\components;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Elastic\Elasticsearch\ClientBuilder;
use Nadia\ElasticsearchODM\ClassMetadata\ClassMetadataLoader;
use Nadia\ElasticsearchODM\Document\IndexNameProvider;
use Nadia\ElasticsearchODM\Document\Manager;
use Nadia\ElasticsearchODM\Helper\ElasticsearchHelper;
use Nadia\Yii2ElasticsearchODM\components\Cache\Pool;
use Nadia\Yii2ElasticsearchODM\components\Cache\Yii2CacheInterface;
use yii\base\Component;

class Yii2ElasticsearchODM extends Component
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
     *     '\My\Namespace\ElasticsearchDocument\\' => '/path/to/documents',
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
     * @var array
     */
    public $clientBuilderOptions = [
        // Basic authentication username.
        'username' => '',
        // Basic authentication password.
        'password' => '',
        // Enable or disable the SSL verification (default is true).
        'sslVerification' => true,
        // An array ['cert' => $cert, 'password' => $password]
        // $cert is the name of a file containing a PEM formatted certificate,
        // $password if the certificate requires a password.
        'sslCert' => ['cert' => '', 'password' => ''],
        // An array ['key' => $key, 'password' => $password]
        // $key is the name of a file containing a private SSL key,
        // $password if the private key requires a password.
        'sslKey' => ['key' => '', 'password' => ''],
        // SSL CA bundle (The file path of http_ca.crt).
        'sslCA' => '',
    ];

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var \Elastic\Elasticsearch\Client|\Elasticsearch\Client
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

        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * @param string $documentClassName
     *
     * @return \Nadia\ElasticsearchODM\Document\Repository
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
     * @return \Elastic\Elasticsearch\Client|\Elasticsearch\Client
     */
    public function getClient()
    {
        $className = ElasticsearchHelper::getClientBuilderClassName();

        if (null === $this->client) {
            if (!empty($this->hosts)) {
                $options = $this->clientBuilderOptions;
                $builder = (new $className());

                $builder->setHosts($this->hosts);

                if (array_key_exists('username', $options) && array_key_exists('password', $options)) {
                    $builder->setBasicAuthentication($options['username'], $options['password']);
                }
                if (array_key_exists('sslVerification', $options)) {
                    $builder->setSSLVerification($options['sslVerification']);
                }
                if (
                    array_key_exists('sslCert', $options)
                    && !empty($options['sslCert']['cert'])
                    && !empty($options['sslCert']['password'])
                ) {
                    $builder->setSSLCert($options['sslCert']['cert'], $options['sslCert']['password']);
                }
                if (
                    array_key_exists('sslKey', $options)
                    && !empty($options['sslKey']['key'])
                    && !empty($options['sslKey']['password'])
                ) {
                    $builder->setSSLKey($options['sslKey']['key'], $options['sslKey']['password']);
                }
                if (array_key_exists('sslCA', $options)) {
                    $builder->setCABundle($options['sslCA']);
                }

                $this->client = $builder->build();
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
            foreach ($this->doGetDocumentClassNames($classNamePrefix, $dir) as $className) {
                $documentClassNames[] = $className;
            }
        }

        return $documentClassNames;
    }

    public function doGetDocumentClassNames($classNamePrefix,  $dir)
    {
        $documentClassNames = [];

        foreach (scandir($dir) as $filename) {
            if ('.' === $filename || '..' === $filename) {
                continue;
            }

            $filepath = $dir . '/' . $filename;

            if (is_file($filepath)) {
                $documentClassNames[] = $classNamePrefix . substr($filename, 0, -4);
            } elseif (is_dir($filepath)) {
                $classNamePrefix .= $filename . '\\';

                foreach ($this->doGetDocumentClassNames($classNamePrefix, $filepath) as $className) {
                    $documentClassNames[] = $className;
                }
            }
        }

        return $documentClassNames;
    }
}
