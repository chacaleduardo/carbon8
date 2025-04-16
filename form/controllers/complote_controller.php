<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/nucleovacina_query.php");
require_once(__DIR__."/../querys/nucleo_query.php");
require_once(__DIR__."/../querys/lote_query.php");


class ComparativoController extends Controller{
    public static function buscarUnidadesClientes( $idempresa, $claus ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(PessoaQuery::buscarUnidadesClientes(),['idempresa' => $idempresa,"clausula" => $claus])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret = array();
            foreach($results->data as $k => $r){
                $arrret["unidades"][$r["idpessoa"]]["nome"] = acentos2ent($r["nome"]);
            }
            return $arrret;
        }
    }

    public static function buscarNucleos( $idempresa, $idpessoa ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AmostraQuery::buscarNucleosComparativo(),['idempresa' => $idempresa,"idpessoa" => $idpessoa])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarNucleoVacina( $idnucleo ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NucleoVacinaQuery::buscarNucleosParaComparativo(),['idnucleo' => $idnucleo])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			foreach($results->data as $k => $rvac){
				$arrVacinas["nucleos"][$rvac["idnucleo"]][$k]["gmt"] = 0;
				$arrVacinas["nucleos"][$rvac["idnucleo"]][$k]["idres"] = 0;
				$arrVacinas["nucleos"][$rvac["idnucleo"]][$k]["vacinas"] = "<br>".$rvac["vacinas"];
				$arrVacinas["nucleos"][$rvac["idnucleo"]][$k]["idade"] = $rvac["datavacina"];
			}
            return $arrVacinas;
        }
    }

    public static function buscarLotesParaComparativo( $getidempresa1, $getidempresa2 ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteQuery::buscarLotesParaComparativo(),[
            'getidempresa1' => $getidempresa1,
            "getidempresa2" => $getidempresa2
            ])::exec();
        

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarNucleosBioterio( $getidempresa, $idlote ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NucleoQuery::buscarNucleosComparativo(),[
            'getidempresa' => $getidempresa,
            "idlote" => $idlote
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }
}?>