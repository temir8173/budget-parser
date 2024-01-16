<?php

namespace app\repositories;

use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ProductRepository
{
    /**
     * @throws Exception
     */
    public function bulkInsert(array $products): int
    {
        return (new Query())->createCommand()
            ->batchInsert('products', ['company_id', 'name'], $products)
            ->execute();
    }

    /**
     * @param int[] $companyIds
     * @return string[][]
     */
    public function getIdsByNameAndCompany(array $companyIds): array
    {
        $products = (new Query())
            ->select(['products.id', 'products.name', 'companies.name companyName'])
            ->from('products')
            ->leftJoin('companies', 'companies.id = products.company_id')
            ->where(['IN', 'companies.id', $companyIds])
            ->all();

        return ArrayHelper::map($products, 'name', 'id', 'companyName');
    }

    /**
     * @param int[] $companyIds
     * @return string[][]
    */
    public function getExistedByCompanies(array $companyIds): array
    {
        $products = (new Query())
            ->select(['products.id', 'products.name', 'companies.name companyName'])
            ->from('products')
            ->leftJoin('companies', 'companies.id = products.company_id')
            ->where(['IN', 'companies.id', $companyIds])
            ->all();

        return ArrayHelper::map($products, 'id', 'name', 'companyName');
    }


    /**
     * @param string $companyName
     * @return int[]
     */
    public function getIdsByCompany(string $companyName): array
    {
        return (new Query())
            ->select(['products.id'])
            ->from('products')
            ->leftJoin('companies', 'companies.id = products.company_id')
            ->where(['companies.name' => $companyName])
            ->column();
    }

    public function getIdByName(string $productName): int
    {
        return (int)(new Query())
            ->select(['id'])
            ->from('products')
            ->where(['name' => $productName])
            ->scalar();
    }
}
