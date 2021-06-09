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

    public function AllGroups($sentido = 'asc'){
        $voos = RequestController::getVoosJson();

        $groupVoos = $this->GroupByPrecoTotal($voos);

        ksort($groupVoos);
        if($sentido !== 'asc'){
            krsort($groupVoos);
        }

        $groupFormatado = $this->FormatGroups($groupVoos);
        $groupVoos = $this->FormatJson($groupFormatado);

        return $groupVoos;
    }

    /**
     * Recebe o array voos, trata e formata para o formato final de impressão.
     */
    private function FormatJson($groupFormatado){
        $voos = RequestController::getVoosJson();

        $groupMaisBarato = $this->PickGroupMaisBartao($groupFormatado);

        $jsonFormatado = [
            'flights' => $voos,
            'groups' =>  $groupFormatado,
            'totalGroups' => count($groupFormatado),
            'totalFlights' => count($voos),
            'cheapestPrice' => $groupMaisBarato['totalPrice'],
            'cheapestGroup' => $groupMaisBarato['uniqueId'],
        ];

        return json_encode($jsonFormatado);
    }

    /**
     * Recebe o array de grupos de voos, formata e gera um id único.
     * @return array grupoDevoos = [['uniqueId','totalPrice','outbound'=> [...], 'inbound' => [...]], [...]];
     */
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

    /**
     * Recebe o array de voos já com uniqueId formatado e retorna o mais barato já formatdo com o preco e o id.
     * @return array grupoDevoo = ['totalPrice','uniqueId'];
     */
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

    /**
     * Recebe o array de voos separa em idas e voltas e depois gera os grupos com o mesmo tipo de tarifa, tendo como chave o valor total de ida + volta.
     * @return array grupoDevoos = ['precoTotal' => ['idas' => [...], 'voltas' => [...]];
     */
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

    /**
     * Recebe o array de voos completo e separa em idas e voltas.
     * @return array ['idas' => [...], 'voltas' => [...]]
     */
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
