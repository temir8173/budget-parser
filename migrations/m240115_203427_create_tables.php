<?php

use yii\db\Migration;

/**
 * Class m240115_203427_create_tables
 */
class m240115_203427_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('companies', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
        ]);

        $this->createTable('products', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        $this->createIndex(
            'idx-unique-company-product',
            'products',
            ['company_id', 'name'],
            true
        );

        $this->addForeignKey(
            'fk-product-company_id',
            'products',
            'company_id',
            'companies',
            'id',
            'CASCADE'
        );

        $this->createTable('budget', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'year' => $this->smallInteger(),
            'month' => $this->smallInteger(),
            'amount' => $this->decimal(10, 2),
        ]);

        $this->createIndex(
            'idx-unique-product-year-month',
            'budget',
            ['product_id', 'year', 'month'],
            true
        );

        $this->addForeignKey(
            'fk-budget-product_id',
            'budget',
            'product_id',
            'products',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-budget-product_id', 'budget');
        $this->dropTable('budget');

        $this->dropForeignKey('fk-product-company_id', 'products');
        $this->dropTable('products');

        $this->dropTable('companies');
    }
}
