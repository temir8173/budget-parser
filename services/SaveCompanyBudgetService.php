<?php

namespace app\services;

use app\repositories\BudgetRepository;
use app\repositories\CompanyRepository;
use app\repositories\ProductRepository;
use yii\db\Exception;

class SaveCompanyBudgetService
{
    private array $companyIdsByName;
    private array $productIdsByName;
    private string $currentYear;

    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ProductRepository $productRepository,
        private readonly BudgetRepository $budgetRepository,
    )
    {}

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function process(array $companies, array $productsByCompany, array $budget, string $currentYear): void
    {
        $this->currentYear = $currentYear;

        $this->saveCompanies($companies);
        $this->saveProducts($productsByCompany);

        $budgetFromDb = $this->budgetRepository->getForCollation($currentYear);

        $this->saveBudget($budget, $budgetFromDb);
        $this->removeRedundantBudget($budget, $budgetFromDb);
    }

    /**
     * @throws Exception
     */
    private function saveBudget(array $budget, array $budgetFromDb): void
    {
        $bulkBudget = [];

        if (empty($budgetFromDb)) {
            $bulkBudget = $this->prepareBulkBudget($budget);
        } else {
            foreach ($budget as $companyName => $budgetByProducts) {
                // если такой компании нет
                if (!isset($budgetFromDb[$companyName])) {
                    $bulkBudget = array_merge(
                        $this->prepareBulkBudget([$companyName => $budgetByProducts]),
                        $bulkBudget
                    );
                } else {
                    foreach ($budgetByProducts as $productName => $budgetByMonth) {
                        // если такого продукта нет
                        if (!isset($budgetFromDb[$companyName][$productName])) {
                            $bulkBudget = array_merge(
                                $this->prepareBulkBudget([
                                    $companyName => [$productName => $budgetByMonth]
                                ]),
                                $bulkBudget
                            );
                        } else {
                            foreach ($budgetByMonth as $month => $amount) {
                                // проверяем ячейки
                                // по идее если есть отчет для продукта, то они есть для всех месяцев, но проверка не
                                // помешает
                                if (!isset($budgetFromDb[$companyName][$productName][$month])) {
                                    $bulkBudget = array_merge(
                                        [
                                            $this->productIdsByName[$companyName][$productName],
                                            $this->currentYear,
                                            $month,
                                            $amount
                                        ],
                                        $bulkBudget
                                    );
                                } elseif ((float)$budgetFromDb[$companyName][$productName][$month] !== $amount) {
                                    $this->budgetRepository->update(
                                        productId: $this->productIdsByName[$companyName][$productName],
                                        year: $this->currentYear,
                                        month: $month,
                                        amount: $amount
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->budgetRepository->bulkInsert($bulkBudget);
    }

    /**
     * @throws Exception
     */
    private function removeRedundantBudget(array $budget, array $budgetFromDb): void
    {
        $redundantProductIds = [];

        if (!empty($budgetFromDb)) {
            foreach ($budgetFromDb as $companyName => $budgetByProducts) {
                if (!isset($budget[$companyName])) {
                    $redundantProductIds = array_merge(
                        $this->productRepository->getIdsByCompany($companyName),
                        $redundantProductIds
                    );
                    // remove company's budget
                } else {
                    foreach ($budgetByProducts as $productName => $budgetByMonth) {
                        if (!isset($budget[$companyName][$productName])) {
                            $productId = $this->productRepository->getIdByName($productName);
                            if ($productId) {
                                $redundantProductIds[] = $productId;
                            }
                        }
                    }
                }
            }
        }

        $this->budgetRepository->deleteByProductIds($redundantProductIds);
    }

    /**
     * @throws Exception
     */
    private function saveCompanies(array $companies): void
    {
        $existedCompanies = $this->companyRepository->getExisted($companies);
        $newCompanies = array_diff($companies, $existedCompanies);
        $this->companyRepository->bulkInsert($newCompanies);

        $this->companyIdsByName = $this->companyRepository->getIdsByName($companies);
    }

    /**
     * @throws Exception
     */
    private function saveProducts(array $productsByCompanies): void
    {
        $newProductsBulk = [];

        $existedProductsByCompanies = $this->productRepository->getExistedByCompanies($this->companyIdsByName);
        foreach ($productsByCompanies as $companyName => $products) {
            if (!isset($existedProductsByCompanies[$companyName])) {
                $newProductsBulk = array_merge(
                    $this->prepareBulkProducts([$companyName => $products]),
                    $newProductsBulk
                );
            } else {
                foreach ($products as $product) {
                    if (!in_array($product, $existedProductsByCompanies[$companyName])) {
                        $newProductsBulk = array_merge(
                            $this->prepareBulkProducts([$companyName => [$product]]),
                            $newProductsBulk
                        );
                    }
                }
            }
        }

        $this->productRepository->bulkInsert($newProductsBulk);
        $this->productIdsByName = $this->productRepository->getIdsByNameAndCompany($this->companyIdsByName);
    }

    private function prepareBulkProducts(array $productsByCompany): array
    {
        $bulkProducts = [];

        foreach ($productsByCompany as $companyName => $products) {
            foreach ($products as $productName) {
                $bulkProducts[] = [$this->companyIdsByName[$companyName], $productName];
            }
        }

        return $bulkProducts;
    }

    private function prepareBulkBudget(array $budgetByCompanies): array
    {
        $bulkBudget = [];

        foreach ($budgetByCompanies as $companyName => $budgetByProducts) {
            foreach ($budgetByProducts as $productName => $budgetByMonth) {
                foreach ($budgetByMonth as $month => $amount) {
                    $bulkBudget[] = [
                        $this->productIdsByName[$companyName][$productName],
                        $this->currentYear,
                        $month,
                        $amount,
                    ];
                }
            }
        }

        return $bulkBudget;
    }
}
