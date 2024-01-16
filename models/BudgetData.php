<?php

namespace app\models;

use yii\db\ActiveRecord;

class BudgetData extends ActiveRecord
{
    public $year;
    public $month;
    public $category;
    public $product;
    public $amount;

    public static function tableName(): string
    {
        return 'budget_data';
    }
}