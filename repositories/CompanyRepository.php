<?php

namespace app\repositories;

use yii\db\Exception;
use yii\db\Query;

class CompanyRepository
{
    /**
     * @param string[] $companies
     * @return int
     *
     * @throws Exception
     */
    public function bulkInsert(array $companies): int
    {
        $companies = array_map(
            function ($value) {
                return [$value];
            },
            $companies
        );
        return (new Query())->createCommand()
            ->batchInsert('companies', ['name'], $companies)
            ->execute();
    }

    /**
     * @param string[] $companies
     * @return int[]
    */
    public function getIdsByName(array $companies): array
    {
        $companiesByName = (new Query())
            ->select(['id', 'name'])
            ->from('companies')
            ->where(['IN', 'name', $companies])
            ->indexBy('name')
            ->all();

        return array_map(
            function ($value) {
                return $value['id'];
            },
            $companiesByName
        );
    }

    /**
     * @param string[] $companies
     * @return string[]
     */
    public function getExisted(array $companies): array
    {
        return (new Query())
            ->select(['name'])
            ->from('companies')
            ->where(['IN', 'name', $companies])
            ->column();
    }
}
