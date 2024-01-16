<?php

namespace app\repositories;

use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class BudgetRepository
{
    /**
     * @throws Exception
     */
    public function bulkInsert(array $budget): int
    {
        return (new Query())->createCommand()
            ->batchInsert(
                'budget',
                [
                    'product_id',
                    'year',
                    'month',
                    'amount',
                ],
                $budget
            )
            ->execute();
    }

    /**
     * @throws Exception
     */
    public function create(int $productId, string $year, int $month, float $amount): int
    {
        return (new Query())->createCommand()
            ->insert('budget', [
                'product_id' => $productId,
                'year' => $year,
                'month' => $month,
                'amount' => $amount,
            ])->execute();
    }

    /**
     * @throws Exception
     */
    public function update(int $productId, string $year, int $month, float $amount): int
    {
        return (new Query())->createCommand()
            ->update('budget', [
                    'amount' => $amount,
                ], [
                    'product_id' => $productId,
                    'year' => $year,
                    'month' => $month,
                ])
            ->execute();
    }

    /**
     * @throws \Exception
     */
    public function getForCollation(string $year): array
    {
        $products = (new Query())
            ->select(['budget.amount', 'budget.month', 'products.name productName', 'companies.name companyName'])
            ->leftJoin('products', 'products.id = budget.product_id')
            ->leftJoin('companies', 'companies.id = products.company_id')
            ->from('budget')
            ->where(['year' => $year])
            ->all();

        $result = [];
        foreach ($products as $product) {
            $productName = ArrayHelper::getValue($product, 'productName');
            $amount = ArrayHelper::getValue($product, 'amount');
            $companyName = ArrayHelper::getValue($product, 'companyName');
            $month = ArrayHelper::getValue($product, 'month');

            $result[$companyName][$productName][$month] = $amount;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function deleteByProductIds(array $redundantProductIds): int
    {
        return (new Query())->createCommand()
            ->delete(
                'budget',
                ['IN', 'product_id', $redundantProductIds]
            )->execute();
    }
}
