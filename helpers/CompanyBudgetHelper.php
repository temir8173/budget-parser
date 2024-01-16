<?php

namespace app\helpers;

class CompanyBudgetHelper
{
    public static function CurrencyParser(string $rawCurrency): float
    {
        $cleanedAmountString = str_replace(['$', ','], '', $rawCurrency);

        return (float)$cleanedAmountString;
    }
}
