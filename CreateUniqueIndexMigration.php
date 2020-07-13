<?php

namespace denis909\yii;

use denis909\yii\UniqualizeBehavior;
use yii\db\Query;
use denis909\yii\Assert;

abstract class CreateUniqueIndexMigration extends \denis909\yii\Migration
{

    public $primaryKey;

    public $indexAttribute;

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

            $subquery->from($this->tableName);

            $query = new Query;

            $query->from($this->tableName);

            $query->where(['in', $this->attribute, $subquery]);

            foreach($query->all() as $row)
            {
                if (!array_key_exists($row[$indexAttribute], $uniqueNames))
                {
                    $uniqueNames[$row[$indexAttribute]] = 0;
                }

                $uniqueNames[$row[$indexAttribute]]++;

                $row[$indexAttribute] = $row[$indexAttribute] . ' #' . $uniqueNames[$row[$indexAttribute]];
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