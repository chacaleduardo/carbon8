<?
// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/pessoa_query.php");
require_once(__DIR__."/../../form/querys/pessoaobjeto_query.php");
require_once(__DIR__."/../../form/querys/empresaimagem_query.php");
require_once(__DIR__."/../../form/querys/emailvirtualconf_query.php");


class WebMailAssinaturaTemplateController extends Controller{

    public static function buscarImagensDeRodape(){
        $results = SQL::ini(EmpresaImagemQuery::buscarCaminhoImagemPorTipo(), [
            'tipo' => 'RODAPEEMAIL',
            'idempresa' => cb::idempresa(),
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return self::toJson([]);
        }else{
            $arrtmp = array();
            foreach($results->data as $i => $row){
                $arrtmp[$i]["title"] = str_replace("../upload/imagenssistema/","",$row["caminho"]);
                $row["caminho"] = str_replace("../","",$row["caminho"]);
                $arrtmp[$i]["value"] = $row["caminho"];
            }
            return  self::toJson($arrtmp);
        }
    }

    public static function buscarPessoasSetorDepartamentosAreas(){
         $results = SQL::ini(PessoaQuery::buscarPessoasSetorDepartamentosAreas(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return self::toJson([]);
        }else{
            $arrtmp = array();
            foreach($results->data as $i =>$row){
                if( $row["tipo"] != 'pessoa' and $row["tipo"] != 'colaboradores'){
                    // Consultar pessoas dos setores, departamentos e áreas
                    // $qr = "SELECT p.idpessoa,p.nomecurto from pessoaobjeto po join pessoa p on (po.idpessoa = p.idpessoa) where po.idobjeto = ".$row["idobjeto"]." and po.tipoobjeto ='".$row["tipo"]."'";
                    $rs = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjetoJoinPessoa(),[])::exec();

                    if( count($rs->data) > 0 ){
                        // $k = 0;
                        foreach( $rs->data as $k => $rw){
                            $arrtmp[$i]["pessoaobjeto"][$k]["idobjeto"] = $rw["idpessoa"];
                            $arrtmp[$i]["pessoaobjeto"][$k]["objeto"]   = $rw["nomecurto"];
                            // $k++;
                        }
                        $arrtmp[$i]["idobjeto"] = $row["idobjeto"];
                        $arrtmp[$i]["objeto"]   = $row["objeto"];
                        $arrtmp[$i]["tipo"]     = $row["tipo"];
                        // $i++;
                    }
                }else if($row["tipo"] == 'colaboradores'){
                    
                    $rs1 = SQL::ini(PessoaQuery::buscarPessoaPorIdTipoPessoaEGetIdEmpresa(),[
                        "idtipopessoa" => 1,
                        "getidempresa" =>getidempresa('p.idempresa','pessoa')
                    ])::exec();

                    if( count($rs1->data) > 0 ){
                        // $k1 = 0;
                        foreach($rs1->data as $k1 => $rw1){
                            $arrtmp[$i]["pessoaobjeto"][$k1]["idobjeto"] = $rw1["idpessoa"];
                            $arrtmp[$i]["pessoaobjeto"][$k1]["objeto"]   = $rw1["nomecurto"];
                            // $k1++;
                        }
                        $arrtmp[$i]["idobjeto"] = $row["idobjeto"];
                        $arrtmp[$i]["objeto"]   = $row["objeto"];
                        $arrtmp[$i]["tipo"]     = $row["tipo"];
                        // $i++;
                    }
                }else{
                    $arrtmp[$i]["idobjeto"] = $row["idobjeto"];
                    $arrtmp[$i]["objeto"]   = $row["objeto"];
                    $arrtmp[$i]["tipo"]     = $row["tipo"];
                    // $i++;
                }
            }
            return self::toJson($arrtmp);
        }
    }

    public static function buscarGruposDeEmail(){
            $results = SQL::ini(EmailVirtualConfQuery::buscarEmailOriginal(), [])::exec();

            if($results->error()){
                parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
                return self::toJson([]);
            }else{
                $arrtmp = array();
                foreach($results->data as $i => $row){
                    $arrtmp[$i]["idobjeto"] = $row["idemailvirtualconf"];
                    $arrtmp[$i]["objeto"]   = $row["email_original"];
                    $arrtmp[$i]["tipo"]     = 'pessoa';
                }
                return  self::toJson($arrtmp);
            }
    }

}
?>