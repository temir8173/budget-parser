<?php

namespace app\serializers;

use app\helpers\CompanyBudgetHelper;

class CompanyBudgetSerializer
{
    public const EXCLUDE_ROW = 'Total';
    public const STOP_ROW = 'CO-OP';

    public function serialize(array $data): array
    {
        $companies = [];
        $productsByCompany = [];
        $budget = [];

        $currentCompany = '';
        $currentProduct = '';
        if (isset($data['values'])) {
            // проходимся по строкам
            foreach ($data['values'] as $item) {
                // проверяем первый столбец
                if (!empty($item[0])) {
                    if ($item[0] === self::STOP_ROW) {
                        break;
                    }
                    if ($item[0] === self::EXCLUDE_ROW) {
                        continue;
                    }
                    // категория
                    if (count($item) == 1) {
                        $currentCompany = $item[0];
                        $companies[] = $currentCompany;
                        $budget[$currentCompany] = [];
                    // продукт
                    } elseif (count($item) > 1) {
                        $currentProduct = $item[0];
                        $productsByCompany[$currentCompany][] = $currentProduct;
                        $budget[$currentCompany][$currentProduct] = [];

                        for ($i = 1; $i <= 12; $i++) {
                            $budget[$currentCompany][$currentProduct][$i] = CompanyBudgetHelper::CurrencyParser($item[$i]);
                        }
                    }
                }
            }
        }

        return [$companies, $productsByCompany, $budget];
    }
}
