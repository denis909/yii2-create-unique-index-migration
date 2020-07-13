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

    public $uniqualizeFirst = true;

    public function init()
    {
        parent::init();

        Assert::notEmpty($this->tableName);

        Assert::notEmpty($this->primaryKey);
    
        Assert::notEmpty($this->indexName);

        Assert::notEmpty($this->attributeName);
    }

    public function updateRow(array $row)
    {
        $where = [$this->primaryKey => $row[$this->primaryKey]];

        Yii::$app->db
            ->createCommand()
            ->update(
                $this->tableName, 
                [
                    $this->attributeName => $row[$this->attributeName] 
                ], 
                $where
            )->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $modelClass = $this->modelClass;

        if ($this->uniqualize)
        {
            $uniqueNames = [];

            $subquery = new Query;

            $subquery->from($this->tableName);

            $subquery->select($this->attributeName);

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

                if ($uniqueNames[$row[$this->attributeName]] > ($this->uniqualizeFirst ? 0 : 1))
                {
                    $row[$this->attributeName] = $row[$this->attributeName] . ' #' . $uniqueNames[$row[$this->attributeName]];

                    $this->updateRow($row);
                }
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