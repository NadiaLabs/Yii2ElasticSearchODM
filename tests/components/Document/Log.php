<?php

namespace Nadia\ElasticsearchODM\Yii2\Tests\components\Document;

use Nadia\ElasticsearchODM\Annotations as ES;
use Nadia\ElasticsearchODM\Document\Traits\ColumnId;

/**
 * @ES\Document(
 *     index_type_name="log",
 *     repository_class_name="Nadia\ElasticsearchODM\Yii2\Tests\components\Document\Repository\LogRepository",
 * )
 * @ES\Template(
 *     name="template-%s-access-log",
 *     index_patterns={"game-money-log-*"},
 * )
 */
class Log
{
    use ColumnId;

    /**
     * @var string
     *
     * @ES\Column(name="text", mapping=@ES\Mappings\Text())
     */
    private $text;
}
