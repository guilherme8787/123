<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

/**
 * @author Guilherme Mendes de Paula
 * Documentation https://github.com/guilherme8787/123
 * 
 * @method manipuladorDeGrupo
 * @method pegaVolta
 * @method getGroups
 * @method pegaVolta
 * 
 */

class ApiTeste extends Controller
{

    /**
     * Manipula o retorno do cURL e cria grupos por tipo de tarifa e subgrupos de preço
     * @param array array de dados do cURl
     * @return array
     */
    public function manipuladorDeResultado($array){

        if($array){
            $origensUnicas = array_unique(array_column($array, 'origin'));
            $tarifasUnicas = array_unique(array_column($array, 'fare'));
            $precosUnicos = array_unique(array_column($array, 'price'));
        
            foreach($tarifasUnicas as $tarifa){
                foreach($precosUnicos as $preco){
                    $final[$tarifa][$preco] = array_filter($array, function($value) use ($preco) {
                            return $value['price'] == $preco;
                    });
                }
            }
            return $final;
        }
        return $array;

    }

    /**
     * Manipula o retorno do manipuladorDeResultado ordena os preços e retorna grupos do mesmo preço
     * @param array array de dados de retorno do manipuladorDeResultado
     * @return array
     */
    public function manipuladorDeGrupo($travels){
        if($travels){
            foreach ($travels as $tarifa => $preco) {
                ksort($preco);
                foreach ($preco as $grupo => $travel) {
                    $grupos[] = $travel;
                }
            }

            return $grupos;
        }
        return $travels;
    }

    /**
     * Devolve viagem de volta com o menor preço dentro da tarifa do grupo
     * @param array array com os dados das viagens de volta disponiveis
     * @param string tarifaGrupo tipo de tarifa desejada para o grupo a ser montado
     * @return array
     */
    public static function pegaVolta($voltas, $tarifaGrupo){
        if($voltas == false){
            return Array('group' => Array("0" => "Sem voos nessa data"), 'price' => 0);
        }
        foreach($voltas as $volta){
            if(is_array($volta)){
                $volta = array_values($volta);
                if($tarifaGrupo == $volta[0]['fare']){
                    return Array('group' => $volta, 'price' => $volta[0]['price']);
                }
            } else {
                if($tarifaGrupo == $volta[$i]['fare']){
                    return Array('group' => $volta, 'price' => $volta[$i]['price']);
                }
            }
        }
    }

    /**
     * Manipula os dados retornados da api: http://prova.123milhas.net/api/flights
     * e retorna no formato desejado em JSON
     * @return void
     */
    public function getGroups()
    {        
        $flights = FlightsManager::getResults('');
        if($flights == false){
            $flights = Array("0" => "Nenhum voo encontrado");
        }

        $ida = $this->manipuladorDeResultado(FlightsManager::getResults('?outbound=1'));
        $volta = $this->manipuladorDeResultado(FlightsManager::getResults('?inbound=1'));
        
        $idas = $this->manipuladorDeGrupo($ida);
        $voltas = $this->manipuladorDeGrupo($volta);
    
        if($idas == false and $voltas == false){
            $grupos[] = Array(
                'uniqueId' => 0,
                'outbound' => Array("0" => "Sem voos nessa data"),
                'inbound' =>  Array("0" => "Sem voos nessa data"),
                'totalPrice' => 0
            );
            $semdados = true;
        } else if($idas == false and $voltas != false){
            $idas = $voltas;
            $voltas = false;
            $sovoltas = true;
        }

        if($idas != false){
            for($i = 0; $i < count($idas); $i++){
                if(is_array($idas[$i])){
                    $idas[$i] = array_values($idas[$i]);
                    $tarifaGrupoIda = $idas[$i][0]['fare'];
                    $valorIda = $idas[$i][0]['price'];
                } else {
                    $tarifaGrupoIda = $idas[$i]['fare'];
                    $valorIda = $idas[$i]['price'];
                }

                $voltasGroup = null;
                if(isset($voltas[$i])){
                    if(is_array($voltas[$i])){
                        $voltas[$i] = array_values($voltas[$i]);
                        $tarifaGrupoVolta = $voltas[$i][0]['fare'];
                        $valorVolta = $voltas[$i][0]['price'];
                    } else {
                        $tarifaGrupoVolta = $voltas[$i]['fare'];
                        $valorVolta = $voltas[$i]['price'];
                    }
                    if($tarifaGrupoIda == $tarifaGrupoVolta){
                        $voltasGroup = $voltas[$i];
                    }
                }
                if($voltasGroup == null){
                    $exec = self::pegaVolta($voltas, $tarifaGrupoIda);
                    $voltasGroup = $exec['group'];
                    $valorVolta = $exec['price'];
                }

                $price = $valorIda + $valorVolta;

                if(isset($sovoltas)){
                    $grupos[] = Array(
                        'uniqueId' => $i,
                        'outbound' => $voltasGroup,
                        'inbound' =>  $idas[$i],
                        'totalPrice' => $price
                    );
                } else {
                    $grupos[] = Array(
                        'uniqueId' => $i,
                        'outbound' => $idas[$i],
                        'inbound' =>  $voltasGroup,
                        'totalPrice' => $price
                    );
                }
            }
        }


        $keys = array_column($grupos, 'totalPrice');
		array_multisort($keys, SORT_ASC, $grupos);
		for($x = 1; $x < count($grupos); $x++){
            $grupos[$x-1]['uniqueId'] = $x;
        }

        if(isset($semdados)){
            $totaldegrupos = 0;
        } else {
            $totaldegrupos = count($grupos);
        }

        $vetorFinal = Array(
            'flights' => $flights,
            'groups' => $grupos,
            'totalGroups' => $totaldegrupos,
            'totalFlights' => count($flights),
            'cheapestPrice' => $grupos[0]['totalPrice'],
            'cheapestGroup' => $grupos[0]['uniqueId']
        );

        return response()->json($vetorFinal, 200);

    }

    //
}
