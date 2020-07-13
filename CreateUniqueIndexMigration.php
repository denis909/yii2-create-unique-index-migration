<?php

namespace denis909\yii;

use Yii;
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

        $class = get_called_class();

        Assert::notEmpty($this->tableName, $class . '::tableName is not defined.');

        Assert::notEmpty($this->primaryKey, $class . '::primaryKey is not defined.');
    
        Assert::notEmpty($this->indexName, $class . '::indexName is not defined.');

        Assert::notEmpty($this->attributeName, $class . '::attributeName is not defined.');
    }

    public function findDupes()
    {
        $subquery = new Query;

        $subquery->from($this->tableName);

        $subquery->select($this->attributeName);

        $subquery->groupBy($this->attributeName);

        $query = new Query;

        $query->from($this->tableName);

        $query->where(['in', $this->attributeName, $subquery]);

        return $query->all();
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
        if ($this->uniqualize)
        {
            $uniqueNames = [];

            foreach($this->findDupes() as $row)
            {
                if (!array_key_exists($row[$this->attributeName], $uniqueNames))
                {
                    $uniqueNames[$row[$this->attributeName]] = 1;
                }
                else
                {
                    $uniqueNames[$row[$this->attributeName]]++;
                }

                if ($uniqueNames[$row[$this->attributeName]] > ($this->uniqualizeFirst ? 0 : 1))
                {
                    $row[$this->attributeName] = $row[$this->attributeName] . ' #' . $uniqueNames[$row[$this->attributeName]];

                    $this->updateRow($row);
                }
            }
        }

        $this->createIndex($this->getIndexName($this->indexName),  $this->tableName, $this->attributeName, true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex($this->getIndexName($this->indexName), $this->tableName);
    }

}