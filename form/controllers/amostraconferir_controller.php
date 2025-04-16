<?
require_once(__DIR__."/_controller.php");


require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/amostra_query.php");


class AmostraConferirController extends Controller{

    public static function buscarAmostrasParaTransferencia($exercicio,$nome,$idregistro_1,$idregistro_2,$dataamostra_1,$dataamostra_2,$unidadepadrao,$idempresa){
        $clausula= "";
        $erro = false;
        $msg = [];
        if(!empty($exercicio)){
            if(is_numeric($exercicio)){
                $clausula .= " and a.exercicio = " . $exercicio;
            }else{
               $erro = true;
                array_push($msg,"O Exercício informado possui caracteres inválidos: [".$exercicio."]");
            }
        }
        
        if (!empty($nome)){
            $clausula .= " and pe.nome like ('%".$nome."%')  ";
        }
        
        if (!empty($idregistro_1) or !empty($idregistro_2)){
            if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
                $clausula .= " and (a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 .")";
            }else{
                $erro = true;
                array_push($msg,"Os Nºs de Registro informados são inválidos: [".$idregistro_1."] e [".$idregistro_2."]");
            }
        }
        if (!empty($dataamostra_1) or !empty($dataamostra_2)){
            $dataini = validadate($dataamostra_1);
            $datafim = validadate($dataamostra_2);
            if ($dataini and $datafim){
                $clausula .= " and (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
            }else{
                $erro = true;
                array_push($msg,"A Data informada é inválida!");
            }
        }

        if(!$erro){
            $results = SQL::ini(AmostraQuery::buscarCorpoTransfereciaAmostra(),[
                "clausula" => $clausula,
                "idunidadepadrao" => $unidadepadrao,
                "idempresa" => $idempresa,
                ])::exec();
    
            if($results->error()){
                 parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                 return [];
            }else{
                return array("numRows"=>$results->numRows(),
                            "sql"=>$results->sql(),
                            "data"=>$results->data);
            }
        }else{
            return array("msg"=>$msg);
        }
        
    }

}
?>