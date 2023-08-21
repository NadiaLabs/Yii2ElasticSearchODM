<?php

namespace Nadia\ElasticsearchODM\Yii2\commands;

use Nadia\ElasticsearchODM\Yii2\components\ElasticsearchODM;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Update Elasticsearch Index Templates
 */
class UpdateTemplatesController extends Controller
{
    /**
     * @var bool Update Index Template when $force = true
     */
    public $force = false;

    /**
     * @var bool Show Index Template contents
     */
    public $show = false;

    /**
     * @var bool Show Index Template difference
     */
    public $diff = false;

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['force', 'show', 'diff']
        );
    }

    /**
     * Update Elasticsearch Index Templates
     *
     * @param string $documentClassName Update one Index Template for the given Document class name (include namespace)
     *
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public function actionIndex($documentClassName = '')
    {
        $odm = ElasticsearchODM::make();
        $manager = $odm->getManager();

        $odm->getClassMetadataLoader()->enableUpdateCache();

        if ('' === $documentClassName) {
            $documentClassNames = $odm->getDocumentClassNames();
        } else {
            $documentClassNames = [$documentClassName];
        }

        foreach ($documentClassNames as $className) {
            $metadata = $manager->getClassMetadata($className);
            $template = $metadata->template;

            foreach ($template['index_patterns'] as &$indexPattern) {
                $indexPattern = $metadata->indexNamePrefix . $indexPattern;
            }

            $this->stdout('Index class name: ' . $className . "\n");
            $this->stdout('Update index template name: ' . $metadata->templateName . "\n");

            if ($this->force) {
                $result = $manager->updateIndexTemplate($className);

                $this->stdout('Result: ' . json_encode($result, JSON_UNESCAPED_SLASHES) . "\n");
            } elseif ($this->show) {
                $this->stdout(
                    'Params: ' . json_encode($template, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n"
                );
            } elseif ($this->diff) {
                $diff = $this->diff($metadata->templateName, $template);

                $this->stdout('Diff result: ' . "\n");

                foreach (['index_patterns', 'order', 'settings', 'mappings'] as $key) {
                    if (empty($diff[$key])) {
                        $this->stdout('  ' . $key . ': is empty!?' . "\n");
                        continue;
                    }

                    $this->stdout('  ' . $key . ': ');

                    if ($key === 'settings' || $key === 'mappings') {
                        $this->stdout("\n");

                        foreach ($diff[$key] as $key2 => $diff2) {
                            $this->stdout('    ' . $key2 . ': ');
                            if (!empty($diff2['same'])) {
                                $this->stdout(json_encode($diff2['new']) . "\n");
                            } else {
                                $this->stdout("\n");
                                $this->stdout('      - ' . json_encode($diff2['old']) . "\n");
                                $this->stdout('      + ' . json_encode($diff2['new']) . "\n");
                            }
                        }
                    } else {
                        if (!empty($diff[$key]['same'])) {
                            $this->stdout(json_encode($diff[$key]['new']) . "\n");
                        } else {
                            $this->stdout("\n");
                            $this->stdout('    - ' . json_encode($diff[$key]['old']) . "\n");
                            $this->stdout('    + ' . json_encode($diff[$key]['new']) . "\n");
                        }
                    }
                }

                $this->stdout("\n");
            }
        }
    }

    private function diff($templateName, array $newTemplate)
    {
        $client = ElasticsearchODM::make()->getClient();
        $template = [];
        $diff = [];
        $val = function ($array, $key, $default) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        };

        if ($client->indices()->existsTemplate(['name' => $templateName])) {
            $template = $client->indices()->getTemplate(['name' => $templateName]);
            $template = $template[$templateName];
        }

        if (!empty($template['template'])) {
            $old = explode(',', $template['template']);
            $old = array_filter($old);
            $old = array_map('trim', $old);
        } else {
            $old = $val($template, 'index_patterns', []);
        }
        $new = $val($newTemplate, 'index_patterns', []);
        sort($old);
        sort($new);
        if (json_encode($old) !== json_encode($new)) {
            $diff['index_patterns'] = ['old' => $old, 'new' => $new];
        } else {
            $diff['index_patterns'] = ['old' => $old, 'new' => $new, 'same' => true];
        }

        $old = $val($template, 'order', 0);
        $new = $val($newTemplate, 'order', 0);
        if ($old != $new) {
            $diff['order'] = ['old' => $old, 'new' => $new];
        } else {
            $diff['order'] = ['old' => $old, 'new' => $new, 'same' => true];
        }

        $old = $val($template, 'settings', []);
        $new = ['index' => $val($newTemplate, 'settings', [])];
        ksort($old);
        ksort($new);
        foreach ($old as $key => $value) {
            if (empty($new[$key])) {
                $diff['settings'][$key] = ['old' => $value, 'new' => new \stdClass()];
            } elseif (json_encode($value) !== json_encode($new[$key])) {
                $diff['settings'][$key] = ['old' => $value, 'new' => $new[$key]];
            } else {
                $diff['settings'][$key] = ['old' => $value, 'new' => $new[$key], 'same' => true];
            }
        }
        foreach ($new as $key => $value) {
            if (empty($old[$key])) {
                $diff['settings'][$key] = ['old' => new \stdClass(), 'new' => $value];
            } elseif (json_encode($value) !== json_encode($old[$key])) {
                $diff['settings'][$key] = ['old' => $old[$key], 'new' => $value];
            }
        }

        $oldMappings = $val($template, 'mappings', []);
        $newMappings = $val($newTemplate, 'mappings', []);
        $old = [];
        $new = [];
        foreach ($oldMappings as $type => $mappings) {
            if (isset($mappings['properties'])) {
                $old = $mappings['properties'];
            }
        }
        foreach ($newMappings as $type => $mappings) {
            if (isset($mappings['properties'])) {
                $new = $mappings['properties'];
            }
        }
        $this->deepKSort($old);
        $this->deepKSort($new);
        foreach ($old as $key => $value) {
            if (empty($new[$key])) {
                $diff['mappings'][$key] = ['old' => $value, 'new' => new \stdClass()];
            } elseif (json_encode($value) !== json_encode($new[$key])) {
                $diff['mappings'][$key] = ['old' => $value, 'new' => $new[$key]];
            } else {
                $diff['mappings'][$key] = ['old' => $value, 'new' => $new[$key], 'same' => true];
            }
        }
        foreach ($new as $key => $value) {
            if (empty($old[$key])) {
                $diff['mappings'][$key] = ['old' => new \stdClass(), 'new' => $value];
            } elseif (json_encode($value) !== json_encode($old[$key])) {
                $diff['mappings'][$key] = ['old' => $old[$key], 'new' => $value];
            }
        }

        return $diff;
    }

    private function deepKSort(array &$array)
    {
        ksort($array);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->deepKSort($array[$key]);
            }
        }
    }
}
