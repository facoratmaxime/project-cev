<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessController extends Controller
{

    const HORS_PERIODE = "HORS PERIODE";
    const P1 = "PERIODE 1";
    const P2 = "PERIODE 2";
    const P3 = "PERIODE 3";

    const POINT_EUROS_VAL = "0.001";
    const USER = "UTILISATEUR";
    const ITEM1 = "PRODUIT 1";
    const ITEM2 = "PRODUIT 2";
    const ITEM3 = "PRODUIT 3";
    const ITEM4 = "PRODUIT 4";
    const DATE = "DATE";
    const DATE_FORMAT = "d/m/Y";

    public function upload(Request $request)
    {
        try {
            if (!empty($request->file('customeFile'))) {
                $file = $request->file('customFile');
                $csvAsArray = array_map('str_getcsv', file($file));
            } else {
                $file = Storage::disk('local')->path('resultats_users.csv');
                $csvAsArray = array_map('str_getcsv', file($file));
            }
        } catch (Exception $e) {
            $request->session()->flash('alert_danger', "Une erreur est survenue, le CSV est corrumpu ou invalide");
            return redirect('index');
        }

        $processed = array_map(function ($a) {
            return array_pop($a);
        }, $csvAsArray);

        $defs = [];

        $timeslots = [
            $this::P1 => [
                'start' => DateTime::createFromFormat($this::DATE_FORMAT, "01/01/2021"),
                'end' => DateTime::createFromFormat($this::DATE_FORMAT, "30/04/2021")
            ],
            $this::P2 => [
                'start' => DateTime::createFromFormat($this::DATE_FORMAT, "01/05/2021"),
                'end' => DateTime::createFromFormat($this::DATE_FORMAT, "31/08/2021"),
            ],
            $this::P3 => [
                'start' => DateTime::createFromFormat($this::DATE_FORMAT, "01/10/2021"),
                'end' => DateTime::createFromFormat($this::DATE_FORMAT, "31/12/2021"),
            ]
        ];

        // CHECK IF CSV IS CORRECTLY FILLED IN
        $headerCols = 0;
        $headerCols = explode(";", $processed[0]);
        unset($processed[0]);

        $data = $this->checkCSV($headerCols, $processed);

        if (!empty($data['status']) && !$data['status']) {
            $request->session()->flash('alert_danger', "Une erreur est survenue, le CSV n'a pu être correctement parsé");
            return redirect('index');
        }

        $results = [];

        try {
            // INTIALIZATION OF DEFAULT VALUES
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

                    // IF TIMETABLE IS NOT INTO PUBLISHED TIME RANGES
                    $results[$userID][$this::HORS_PERIODE] = [
                        $this::ITEM1 => 0,
                        $this::ITEM2 => 0,
                        $this::ITEM3 => 0,
                        $this::ITEM4 => 0
                    ];
                }
            }
        } catch (Exception $e) {
            $request->session()->flash('alert_danger', "Une erreur est survenue, le traitement a été stoppé lors de l'initialisation");
            return redirect('index');
        }

        try {
            foreach ($data['data'] as $item) {
                $userID = $item[$this::USER];

                $period = DateTime::createFromFormat($this::DATE_FORMAT, $item[$this::DATE]);
                $periodKey = $this::HORS_PERIODE;

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
                            $blockBy2Units = (int)$value / 2;
                            $results[$userID][$periodKey][$this::ITEM3] += ((int)$blockBy2Units * 15);
                            break;
                        case $this::ITEM4:
                            $results[$userID][$periodKey][$this::ITEM4] += ($value * 35);
                            break;
                    }
                }
            }
        } catch (Exception $e) {
            $request->session()->flash('alert_danger', "Une erreur est survenue, le traitement a été stoppé lors de la construction du tableau");
            return redirect('index');
        }

        try {
            foreach ($results as $userID => $periodKey) {
                foreach ($periodKey as $key => $periodValue) {
                    $results[$userID][$key]['TOTAL']['POINTS'] = $results[$userID][$key][$this::ITEM1] + $results[$userID][$key][$this::ITEM2] + $results[$userID][$key][$this::ITEM3] + $results[$userID][$key][$this::ITEM4];
                    $results[$userID][$key]['TOTAL']['EUROS'] = $results[$userID][$key]['TOTAL']['POINTS'] * $this::POINT_EUROS_VAL;
                }
            }
        } catch (Exception $e) {
            $request->session()->flash('alert_danger', "Une erreur est survenue, le traitement a été stoppé lors du calcul des points des utilisateurs");
            return redirect('index');
        }

        $dateFormat = $this::DATE_FORMAT;
        return view('results', compact('results', 'headerCols', 'timeslots', 'dateFormat'));
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
