<?php

use app\repositories\BudgetRepository;
use app\repositories\CompanyRepository;
use app\repositories\ProductRepository;
use app\serializers\CompanyBudgetSerializer;
use app\services\SaveCompanyBudgetService;

return [
    'definitions' => [
        SaveCompanyBudgetService::class => SaveCompanyBudgetService::class,
        CompanyBudgetSerializer::class => CompanyBudgetSerializer::class,
        CompanyRepository::class => CompanyRepository::class,
        ProductRepository::class => ProductRepository::class,
        BudgetRepository::class => BudgetRepository::class,
    ],
];
