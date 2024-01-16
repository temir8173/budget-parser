<?php

namespace app\commands;

use app\serializers\CompanyBudgetSerializer;
use app\services\SaveCompanyBudgetService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\helpers\Json;

class ParseBudgetController extends Controller
{
    // https://sheets.googleapis.com/v4/spreadsheets/{SPREADSHEET_ID}/values/{SHEET_ID}?key={API_KEY}
    private const GOOGLE_SHEET_API_URL = 'https://sheets.googleapis.com/v4/';

    public function __construct(
        $id,
        $module,
        private readonly SaveCompanyBudgetService $saveCompanyBudgetService,
        private readonly CompanyBudgetSerializer $companyBudgetSerializer,

        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(
        string $spreadsheetId = '10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw',
        string $sheetId = 'Totals'
    )
    {
        $currentYear = date("Y");

        try {
            $client = new Client();
            $res = $client->request(
                'GET',
                self::GOOGLE_SHEET_API_URL . "spreadsheets/$spreadsheetId/values/$sheetId",
                [
                    'query' => [
                        'key' => Yii::$app->params['googleApiKey']
                    ]
                ]
            );
        } catch (GuzzleException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }

        $data = Json::decode($res->getBody());
        [$companies, $productsByCompany, $budget] = $this->companyBudgetSerializer->serialize($data);

        try {
            $this->saveCompanyBudgetService->process($companies, $productsByCompany, $budget, $currentYear);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }

        echo 'ok' . PHP_EOL;
    }
}
