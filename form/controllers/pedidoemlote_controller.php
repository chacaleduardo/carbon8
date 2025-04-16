<?

require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/uf_query.php");
require_once(__DIR__."/../querys/nf_query.php");
require_once(__DIR__."/../querys/fluxo_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/empresa_query.php");

 class PedidoEmLoteController extends Controller{
    public static function buscarEmpresasDoFiltro($idpessoa, $toFillSelect = false)
    {
        $results = SQL::ini(EmpresaQuery::buscarEmpresasQueAPessoaAcessa(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        if($toFillSelect) {
            $arrRetorno = [];

            foreach($results->data as $empresa) $arrRetorno[$empresa['idempresa']] = $empresa['empresa'];

            return $arrRetorno;
        }
        
        return $results->data;
    }

    public static function buscarUf($toFillSelect = false)
    {
        $results = SQL::ini(UfQuery::buscarTodasUf(), [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        if($toFillSelect) {
            $arrRetorno = [];

            foreach($results->data as $uf) $arrRetorno[$uf['uf']] = $uf['uf'];

            return $arrRetorno;
        }

        return $results->data;
    }

    public static function buscarFiltrosStatus($modulo)
    {
        $results = SQL::ini(FluxoQuery::buscarStatusDoModulo(), [
            "modulo" => $modulo,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }
    public static function buscarStatustipo($modulo,$idfluxostatus)
    {
        $results = SQL::ini(FluxoQuery::buscarStatusTipoPorModulo(), [
            "modulo" => $modulo,
            "idfluxostatus" => $idfluxostatus,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return ($results->data[0]);
        }
    }

    public static function executaConsulta($query){
        $results = SQL::ini($query,[])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results;
        }
    }

    public static function buscarTransportadoras(){
        $results = SQL::ini(PessoaQuery::listarTransportadoraSemShare(),[])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function atualizarTransportadoraPedido($idnf,$tranportadora){
        $results = SQL::ini(NfQuery::atualizarTrasnportadoraNF(),[
            "idnf" => $idnf,
            "idtransportadora" => $tranportadora,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function atualizarStatusNfePedido($idnf,$statuenvio){
        $results = SQL::ini(NfQuery::atualizarEnvioNfe(),[
            "idnf" => $idnf,
            "envionfe" => $statuenvio,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarPedidoAtualizado($idnf){
        $results = SQL::ini(NfQuery::buscarNfPorId(),[
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
 }

?>
