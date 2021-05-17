<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;

class ProcessController extends Controller
{

    const POINT_EUROS_VAL = "0.001";
    const USER = "UTILISATEUR";
    const ITEM1 = "PRODUIT 1";
    const ITEM2 = "PRODUIT 2";
    const ITEM3 = "PRODUIT 3";
    const ITEM4 = "PRODUIT 4";
    const DATE = "DATE";

    public function upload(Request $request)
    {
        $file = $request->file('customFile');
        $csvAsArray = array_map('str_getcsv', file($file));

        $processed = array_map(function ($a) {
            return array_pop($a);
        }, $csvAsArray);

        $defs = [];

        $timeslots = [
            'PERIODE 1' => [
                'start' => DateTime::createFromFormat("d/m/Y", "01/01/2021"),
                'end' => DateTime::createFromFormat("d/m/Y", "30/04/2021")
            ],
            'PERIODE 2' => [
                'start' => DateTime::createFromFormat("d/m/Y", "01/05/2021"),
                'end' => DateTime::createFromFormat("d/m/Y", "31/08/2021"),
            ],
            'PERIODE 3' => [
                'start' => DateTime::createFromFormat("d/m/Y", "01/10/2021"),
                'end' => DateTime::createFromFormat("d/m/Y", "31/12/2021"),
            ]
        ];

        // CHECK IF CSV IS CORRECTLY FILLED IN
        $headerCols = 0;
        $headerCols = explode(";", $processed[0]);
        unset($processed[0]);

        $data = $this->checkCSV($headerCols, $processed);

        if (!empty($data['status']) && !$data['status']) {
            return redirect('index');
        }

        $results = [];

        foreach ($data['data'] as $key => $item) {
            $userID = $item[$this::USER];
            if (!in_array($userID, $results)) {
                foreach ($timeslots as $targetKey => $targetPeriod) {
                    $results[$userID][$targetKey] = [
                        $this::ITEM1 => 0,
                        $this::ITEM2 => 0,
                        $this::ITEM3 => 0,
                        $this::ITEM4 => 0
                    ];
                }
                $results[$userID]["HORS_PERIODE"] = [
                    $this::ITEM1 => 0,
                    $this::ITEM2 => 0,
                    $this::ITEM3 => 0,
                    $this::ITEM4 => 0
                ];
            }
        }


        foreach ($data['data'] as $item) {
            $userID = $item[$this::USER];


            $period = DateTime::createFromFormat("d/m/Y", $item[$this::DATE]);
            $periodKey = "HORS_PERIODE";

            foreach ($timeslots as $targetKey => $targetPeriod) {
                if ($targetPeriod['start'] <= $period && $targetPeriod['end'] >= $period) {
                    $periodKey = $targetKey;
                }
            }
            unset($item[$this::DATE]);

            foreach ($item as $key => $value) {
                switch ($key) {
                    case $this::ITEM1:
                        $results[$userID][$periodKey][$this::ITEM1] += ((int)$value * 5);
                        break;
                    case $this::ITEM2:
                        if ($results[$userID][$periodKey][$this::ITEM1] > 0) {
                            $results[$userID][$periodKey][$this::ITEM2] += ((int)$value * 5);
                        }
                        break;
                    case $this::ITEM3:
                        $modulo = (int)$value / 2;
                        $results[$userID][$periodKey][$this::ITEM3] += ((int)$modulo * 15);
                        break;
                    case $this::ITEM4:
                        $results[$userID][$periodKey][$this::ITEM4] += ($value * 35);
                        break;
                }
            }
        }

        foreach ($results as $userID => $periodKey) {
            foreach ($periodKey as $key => $periodValue) {
                $results[$userID][$key]['TOTAL']['POINTS'] = $results[$userID][$key][$this::ITEM1] + $results[$userID][$key][$this::ITEM2] + $results[$userID][$key][$this::ITEM3] + $results[$userID][$key][$this::ITEM4];
                $results[$userID][$key]['TOTAL']['EUROS'] = $results[$userID][$key]['TOTAL']['POINTS'] * $this::POINT_EUROS_VAL;
            }
        }


        return view('results', compact('results', 'headerCols'));
    }

    private function checkCSV($headers, $data)
    {
        $headerCols = $headers;

        $resp = [];
        $resp = [
            'status' => true
        ];

        foreach ($data as $parent => $line) {

            $dataLine = explode(";", $line);
            // CHECK IF ALL COLS ARE NOT EMPTY AND THE SAME COUNT
            if (count($headerCols) !=  count($dataLine)) {
                $resp['status'] = false;
                break;
            }

            // TRANSFORM CSV DATA TO PHP ASSOC ARRAY
            foreach ($dataLine as $key => $value) {
                $resp['data'][$parent][$headerCols[$key]] = $value;
            }
        }

        return $resp;
    }
}
