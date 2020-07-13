<?php

namespace denis909\yii;

use denis909\yii\UniqualizeBehavior;
use yii\db\Query;
use denis909\yii\Assert;

abstract class CreateUniqueIndexMigration extends \denis909\yii\Migration
{

    public $primaryKey;

    public $attributeName;

    public $indexName;

    public $uniqualize = true;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->uniqualize)
        {
            $uniqueNames = [];

            $subquery = new Query;

            $subquery->select($this->attributeName);

            $subquery->from($this->tableName);

            $subquery->groupBy($this->attributeName);

            $query = new Query;

            $query->from($this->tableName);

            $query->where(['in', $this->attributeName, $subquery]);

            foreach($query->all() as $row)
            {
                if (!array_key_exists($row[$this->attributeName], $uniqueNames))
                {
                    $uniqueNames[$row[$this->attributeName]] = 0;
                }

                $uniqueNames[$row[$this->attributeName]]++;

                $row[$this->attributeName] = $row[$this->attributeName] . ' #' . $uniqueNames[$row[$this->attributeName]];
            }
        }

        $this->createIndex($this->indexPrefix . $this->indexName,  $this->tableName, $this->indexAttributes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex($this->indexPrefix . $this->indexName, $this->tableName);
    } 

}