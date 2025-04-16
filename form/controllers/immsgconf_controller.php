<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/imgrupo_query.php");
require_once(__DIR__."/../querys/_modulo_query.php");
require_once(__DIR__."/../querys/eventotipo_query.php");
require_once(__DIR__."/../querys/immsgconfdest_query.php");
require_once(__DIR__."/../querys/immsgconfplataforma_query.php");


// CONTROLLERS
require_once(__DIR__."/_controller.php");

class ImMsgConfController extends Controller{

    public static function buscarModulosParaVincular()
    {
        $results = SQL::ini(_ModuloQuery::buscarModuloETabComPK(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        $arrret=array();
        foreach($results->data as $k => $r){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["modulo"]]["idmodulo"]=$r["idmodulo"];
            $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
            $arrret[$r["modulo"]]["tab"]=$r["tab"];
            $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
        }
        return $arrret;
    }

    public static function buscarModulosDestParaVincular()
    {
        $results = SQL::ini(_ModuloQuery::buscarModuloETab(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        $arrret=array();
        foreach($results->data as $k => $r){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
            $arrret[$r["modulo"]]["idmodulo"]=$r["idmodulo"];
            $arrret[$r["modulo"]]["tab"]=$r["tab"];
            $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
        }
        return $arrret;
    }

    public static function buscarGruposParaVincular($idimmsgconf)
    {
        $results = SQL::ini(ImGrupoQuery::buscarGruposNaoVinculadosAoAlerta(), [
            'getidempresa' => getidempresa('s.idempresa','immsgconf'),
            'idimmsgconf' => $idimmsgconf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        $arrtmp=array();
        foreach($results->data as $k => $r){
            $arrtmp[$k]["value"]=$r["idimgrupo"];
            $arrtmp[$k]["label"]= $r["grupo"];
        }
        return $arrtmp;
    }

    public static function buscarSetoresVinculados($idimmsgconf)
    {
        $results = SQL::ini(ImGrupoQuery::buscarGruposVinculadosAoAlerta(), [
            'idimmsgconf' => $idimmsgconf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

    public static function buscarPessoasVinculadas($idimmsgconf)
    {
        $results = SQL::ini(ImMsgConfDestQuery::buscarPessoasVinculadasAoAlerta(), [
            'idimmsgconf' => $idimmsgconf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

    public static function buscarPessoasNaoVinculadas($idimmsgconf,$andtppes)
    {
        $results = SQL::ini(ImMsgConfDestQuery::buscarPessoasNaoVinculadasAoAlerta(), [
            'idimmsgconf' => $idimmsgconf,
            'idempresa' => cb::idempresa(),
            'andtppes' => $andtppes,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
       
        $arrtmp=array();
        foreach($results->data as $k => $r){
            $arrtmp[$k]["value"]=$r["idpessoa"];
            $arrtmp[$k]["label"]= $r["nome"];
        }
        return $arrtmp;
    }

    public static function buscarTabelasDoSchema()
    {
        $results = SQL::ini("SELECT table_schema as db,
                                    table_name as tab
                            from information_schema.tables 
                            where table_schema='laudo'
                            union all
                            SELECT table_schema,
                                    table_name as tab
                            from information_schema.tables 
                            where table_schema='carbonnovo'", [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
       
        $arrtmp=array();
        foreach($results->data as $k => $r){
            $arrtmp[$k]["value"]=$r["tab"];
            $arrtmp[$k]["label"]= $r["tab"];
            $arrtmp[$k]["db"]= $r["db"];
        }
        return $arrtmp;
    }

    public static function buscarModulosPais()
    {
        $results = SQL::ini(_ModuloQuery::buscarModuloSuperiores(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
       
        $arrtmp=array();
        foreach($results->data as $k => $r){
            $arrtmp[$k]["value"]=$r["id"];
            $arrtmp[$k]["label"]= $r["id"];
            $arrtmp[$k]["db"]= $r["id"];
        }
        return $arrtmp;
    }

    public static function buscarPlataformasDaConf($idimmsgconf)
    {
        $results = SQL::ini(ImMsgConfPlataformaQuery::buscarPlataformas(), [
            "idimmsgconf" => $idimmsgconf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }
}