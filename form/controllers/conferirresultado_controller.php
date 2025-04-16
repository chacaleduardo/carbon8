<?
require_once(__DIR__."/_controller.php");


require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/resultado_query.php");


class ConferirResultadoController extends Controller{

    public static function buscarResultadosParaConferencia($exercicio,$statusres,$idpessoa,$nome,$idnucleo,$nucleoamostra,$lote,$tipogmt,$idtipoteste,$idresultado,$controle,$idunidade,$idregistro,$idregistro_1,$idregistro_2,$dataamostra_1,$dataamostra_2,$idade,$tc,$partida,$idtipoamostra,$idsubtipoamostra,$idpessoalogada,$getidempresa)
	{
        $clausula = "";
        $erro = false;
        $msg = [];
        if(!empty($exercicio)){
            if(is_numeric($exercicio)){
                $clausula .= " a.exercicio = " . $exercicio ." and ";
            }else{
                $erro = true;
                array_push($msg,"O Exercício informado possui caracteres inválidos: [".$exercicio."]");
            }
        }
        
        if(!empty($statusres)){
            $clausula .=" a.status='".$statusres."' and ";
        }else{
            $clausula .=" a.status='FECHADO' and ";
        }
        
        if (!empty($idpessoa)){
            $clausula .= " a.idpessoa = " .$idpessoa ." and ";
        }
        if (!empty($nome)){
            $clausula .= " a.nome like ('%".$nome."%') and ";
        }
        
        if (!empty($idnucleo)){
            $clausula .= " a.idnucleo = " .$idnucleo ." and ";
        }
        if (!empty($nucleoamostra)){
                $clausula .= " a.nucleoamostra = '" .$nucleoamostra ."' and ";
        }
        if (!empty($lote)){
                $clausula .= " a.lote = '" .$lote ."' and ";
        }
        if (!empty($tipogmt)){
                $clausula .= " a.tipogmt = '" .$tipogmt ."' and ";
        }
        if (!empty($idtipoteste)){
            $clausula .= " a.idtipoteste = " .$idtipoteste ." and ";
        }
        if (!empty($idresultado)){
            $clausula .= " a.idresultado = " .$idresultado." and ";
        }
        if (!empty($controle)){
            $clausula .= " a.idresultado in (SELECT ni.idresultado FROM notafiscal nf, notafiscalitens ni WHERE ni.idnotafiscal = nf.idnotafiscal and nf.controle = ". $controle .") and ";
        }
        if(!empty($idunidade)){
            $clausula.="  a.idunidade  in(".$idunidade.") and ";
        }
        if (!empty($idregistro)){
            if (is_numeric($idregistro)){
                $clausula .= " a.idregistro = " .$idregistro." and ";
            }else{
                $erro = true;
                array_push($msg,"O Nº de Registro informado é inválido: [".$idregistro."]");
            }
        }
        if (!empty($idregistro_1) or !empty($idregistro_2)){
            if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
                $clausula .= " (a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 .")"." and ";
            }else{
                $erro = true;
                array_push($msg,"Os Nºs de Registro informados são inválidos: [".$idregistro_1."] e [".$idregistro_2."]");
            }
        }
        if (!empty($dataamostra_1) or !empty($dataamostra_2)){
            $dataini = validadate($dataamostra_1);
            $datafim = validadate($dataamostra_2);
            if ($dataini and $datafim){
                $clausula .= " (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
            }else{
                $erro = true;
                array_push($msg,"A Data informada é inválida!");
            }
        }
        if (!empty($idade)){
            $clausula .= " a.idade = '" .$idade ."' and ";
        }
        if (!empty($tc)){
            $clausula .= " a.tc = '" .$tc ."' and ";
        }
        if (!empty($partida)){
            $clausula .= " a.partida = '" .$partida ."' and ";
        }
        if (!empty($idtipoamostra)){
            $clausula .= " a.idtipoamostra = " .$idtipoamostra ." and ";
        }
        if (!empty($idsubtipoamostra)){
            $clausula .= " a.idsubtipoamostra = " .$idsubtipoamostra ." and ";
        }
        
        if(empty($exercicio)){
            $exercicio=date("Y");
        }

        if (!empty($clausula)){
            $clausula = 'where ' . substr($clausula,1,strlen($clausula) - 5);
        }

        if(!$erro){
            $results = SQL::ini(ResultadoQuery::buscarResultadosParaConferencia(), [
                "idpessoa" => $idpessoalogada,
                "clausula" => $clausula,
                "usuario" => $_SESSION['SESSAO']['USUARIO'],
                "getidempresa" => $getidempresa,
            ])::exec();
    
            if($results->error())
            {
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return Array("erro"=>true,"msg"=>$results->errorMessage(),"sql"=>$results->sql());;
            }else{
                return Array("erro"=>false,"sql"=>$results->sql(),"data" =>$results->data,"count"=>$results->numRows());
            }
        }else{
            return Array("erro"=>true,"msg"=>$msg);
        }
	}

    public static function buscarFluxoParaResultados($idobjeto){
        $results = SQL::ini(ResultadoQuery::buscarFluxoParaResultados(), [
            "idobjeto" => $idobjeto,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }
}?>