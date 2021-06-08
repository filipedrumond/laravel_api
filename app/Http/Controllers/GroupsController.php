<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;


use App\RequestControlle;

class GroupsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function show()
    {
        return RequestController::getVoosJson();
    }

    public function AllGroups(){
        $voos = RequestController::getVoosJson();
        $groupVoos = $this->FormatJson($voos);

        return $groupVoos;
    }

    private function FormatJson($voos){
        $groupVoos = $this->GroupByPrecoTotal($voos);
        $groupFormatado = $this->FormatGroups($groupVoos);
        $groupMaisBarato = $this->PickGroupMaisBartao($groupFormatado);

        $jsonFormatado = [
            'flights' => $voos,
            'groups' =>  $groupFormatado,
            'totalGroups' => count($groupVoos),
            'totalFlights' => count($voos),
            'cheapestPrice' => $groupMaisBarato['totalPrice'],
            'cheapestGroup' => $groupMaisBarato['uniqueId'],
        ];

        return $jsonFormatado;
    }

    private function FormatGroups($groupVoos){
        $groupFormatado = [];
        foreach ($groupVoos as $groupPriceTotal => $group) {
            $temp = [
                'uniqueId' => uniqid(),
                'totalPrice' => $groupPriceTotal,
                'outbound' => $group['idas'],
                'inbound' => $group['voltas'],
            ];
            $groupFormatado[] = $temp;
        }
        return $groupFormatado;
    }

    private function PickGroupMaisBartao($groupsFormatado){
        $groupMaisBarto = [
            'totalPrice' => $groupsFormatado[0]['totalPrice'],
            'uniqueId' => $groupsFormatado[0]['uniqueId'],
        ];

        foreach ($groupsFormatado as $key => $group) {
            if($group['totalPrice'] < $groupMaisBarto['totalPrice']){
                $groupMaisBarto = [
                    'totalPrice' => $group['totalPrice'],
                    'uniqueId' => $group['uniqueId'],
                ];
            }
        }
        return $groupMaisBarto;
    }

    private function GroupByPrecoTotal($voos){
        $temp = $this->SepararIdasVoltas($voos);

        $idas = $temp['idas'];
        $voltas = $temp['voltas'];

        $group = [];
        foreach ($idas as $ida) {
            foreach ($voltas as $volta) {
                if ($ida['fare'] !== $volta['fare']) {
                    continue;
                }

                $priceTotal = ($ida['price'] + $volta['price']);

                $group[$priceTotal] = $group[$priceTotal] ?? [
                    'idas' => [],
                    'voltas' => [],
                ];

                $group[$priceTotal]['idas'][$ida['id']] = $ida;
                $group[$priceTotal]['voltas'][$volta['id']] = $volta;
            }
        }
        return $group;
    }

    private function SepararIdasVoltas($voos){
        $idasGroup = [];
        $voltasGroup = [];

        foreach ($voos as $voo) {
            if ($voo['outbound'] === 1) {
                $idasGroup[] = $voo;
            } else {
                $voltasGroup[] = $voo;
            }
        }

        return [
            'idas' => $idasGroup,
            'voltas' => $voltasGroup
        ];
    }
}
