<?
require_once(__DIR__."/_controller.php");


require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/nucleo_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/nfscidadesiaf_query.php");
require_once(__DIR__."/../querys/vwespeciefinalidade_query.php");


class NucleoController extends Controller{

    public static function buscarTipoAves($idnucleo){
        $results = SQL::ini(NucleoQuery::buscarPorChavePrimaria(), [
            'pkval' => $idnucleo
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            if($results->data[0]["tipoaves"] == "FRANGO"){
                $results1 = SQL::ini("SELECT CAST((datediff(curdate(),alojamento)) as SIGNED) as idade,'Dia(s)' as tipoidade FROM nucleo where idnucleo = $idnucleo", [])::exec();
                if($results1->error()){
                    parent::error(__CLASS__, __FUNCTION__, $results1->errorMessage());
                    return [];
                }else{
                    return $results1->data[0];
                }
            }else{
                $results1 = SQL::ini("SELECT CAST((datediff(curdate(),alojamento)/7) as SIGNED) as idade,'Semana(s)' as tipoidade FROM nucleo where  idnucleo = $idnucleo", [])::exec();
                if($results1->error()){
                    parent::error(__CLASS__, __FUNCTION__, $results1->errorMessage());
                    return [];
                }else{
                    return $results1->data[0];
                }
            }
        }
    }

    public static function buscarClientesParaNucleo($getidempresa){
        $results = SQL::ini(PessoaQuery::buscarClientesParaNucleo(),[
            "getidempresa" => $getidempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarEspecieFinalidadePorEmpresa($idempresa){
        $results = SQL::ini(VwEspecieFinalidadeQuery::buscarEspecieFinalidadePorEmpresa(),[
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarCidade($uf){
        $results = SQL::ini(NfscidadesiafQuery::buscarCidadePorEstado(),[
            "uf" => $uf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarSecretariaPessoaNucleo($idpessoa){
        $results = SQL::ini(PessoaQuery::buscarSecretariaPessoaNucleo(),[
            "idpessoa" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarAmostrasPorNucleo($idnucleo){
        $results = SQL::ini(AmostraQuery::buscarAmostrasPorNucleo(),[
            "idnucleo" => $idnucleo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }
}?>