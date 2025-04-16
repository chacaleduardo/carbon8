<?
require_once(__DIR__ . "/_controller.php");
// require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../../form/querys/log_query.php");
require_once(__DIR__ . "/../../form/querys/evento_query.php");
require_once(__DIR__ . "/../../form/querys/carimbo_query.php");
require_once(__DIR__ . "/../../form/querys/immsgconf_query.php");
require_once(__DIR__ . "/../../form/querys/immsgconflog_query.php");
require_once(__DIR__ . "/../../form/querys/immsgconfdest_query.php");
require_once(__DIR__ . "/../../form/querys/immsgconffiltros_query.php");
require_once(__DIR__ . "/../../form/querys/fluxostatuspessoa_query.php");


class RemoveAlertaSislaudoController extends ControllerCron{

    public static function inserirLog($idempresa,$sessao,$tipoobjeto,$idobjeto,$tipolog,$log,$status,$info,$criadoem,$data){
        $results = SQL::ini(LogQuery::inserirLog(), [          
            "idempresa" => $idempresa,
            "sessao" => $sessao,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "tipolog" => $tipolog,
            "log" => $log,
            "status" => $status,
            "info" => $info,
            "criadoem" => $criadoem,
            "data" => $data,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
            
        }
    }

    public static function buscarConfiguracoesDaRemocaoDeMensagem(){
        $results = SQL::ini(ImMsgConfQuery::buscarConfiguracoesDaRemocaoDeMensagem(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }

    public static function buscarCamposDaConfiguracoesDoEnvioDeMensagem($idimmsgconf){
        $results = SQL::ini(ImMsgConfFiltrosQuery::buscarFiltrosDaSelecao(), [
            'idimmsgconf' => $idimmsgconf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
            
        }
    }

    
    public static function buscarEventosCriadosPelaConfiguracao($idimmsgconf){
        $results = SQL::ini(ImMsgConfQuery::buscarEventosCriadosPelaConfiguracao(), [
            "idimmsgconf" => $idimmsgconf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
 
    public static function filtrarIdsDaBusca($col,$tab,$ids,$clausula){
       
        $results = SQL::ini(ImMsgConfQuery::filtrarIdsDaBusca(), [
            "col" => $col,
            "tab" => $tab,
            "ids" => $ids,
            "clausula" => $clausula,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizaEventosParaConcluido($status,$idfluxostatus,$alteradopor,$idevento){
        $results = SQL::ini(EventoQuery::atualizarStatusFluxostatusDoEvento(), [
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "alteradopor" => $alteradopor,
            "idevento" => $idevento,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizarParticipantesParaOculto($idempresa,$idimmsgconf,$idpk,$modulo,$prefu){
        $results = SQL::ini(ImMsgConfLogQuery::inserirLogDeEnviando(), [
            "idempresa" => $idempresa,
            "idimmsgconf" => $idimmsgconf,
            "idpk" => $idpk,
            "modulo" => $modulo,
            "prefu" => $prefu,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function buscarEventoAlerta($idimmsgconf,$ideventotipo,$modulo,$idpk){
        $results = SQL::ini(ImMsgConfQuery::buscarEventoAlerta(), [
            "idimmsgconf" => $idimmsgconf,
            "ideventotipo" => $ideventotipo,
            "modulo" => $modulo,
            "idpk" => $idpk,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizarStatusDoEvento($idevento,$status){
        $results = SQL::ini(EventoQuery::atualizarStatusDoEvento(), [
            "status" => $status,
            "idevento" => $idevento,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizarParaNaoVisualizadoPorIdEvento($idfluxostatus,$alteradopor,$modulo,$idmodulo){
        $results = SQL::ini(FluxostatuspessoaQuery::atualizarParaNaoVisualizadoNaoOcultoPorIdmodulo(), [
            "idfluxostatus" => $idfluxostatus,
            "alteradopor" => $alteradopor,
            "modulo" => $modulo,
            "idmodulo" => $idmodulo,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function criarEventoAlerta($ideventotipo,$idempresa,$idpessoa,$modulo,$idmodulo,$titulocurto,$idfluxostatus,$prazo,$mensagem){
        $results = SQL::ini(EventoQuery::criarEventoAlerta(), [
            "ideventotipo" =>$ideventotipo,
            "idempresa" =>$idempresa,
            "idpessoa" =>$idpessoa,
            "modulo" =>$modulo,
            "idmodulo" =>$idmodulo,
            "titulocurto" =>$titulocurto,
            "idfluxostatus" =>$idfluxostatus,
            "prazo" =>$prazo,
            "mensagem" =>$mensagem,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizarLogParaSucesso( $idevento,$idimmsgconflog ){
        $results = SQL::ini(ImMsgConfLogQuery::atualizarLogParaSucesso(), [
            "idevento" => $idevento,
            "idimmsgconflog" => $idimmsgconflog,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function inserirPessoasNoEventoAlerta( $idmodulo,$idempresa,$idpessoa,$idfluxostatus){
        $results = SQL::ini(FluxostatuspessoaQuery::inserirNoEventoAlerta(), [
            "idmodulo" => $idmodulo,
            "idempresa" => $idempresa,
            "idpessoa" => $idpessoa,
            "idfluxostatus" => $idfluxostatus,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function buscarAssinaturaDoEventoAlerta($idobjeto,$tipoobjeto,$idpessoa){
        $results = SQL::ini(CarimboQuery::buscarUltimaAssinaturaPendentePorIdObjetoTipoObjetoIdPessoa(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idpessoa" => $idpessoa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function inserirAssinatura($idempresa,$idpessoa,$idobjeto,$tipoobjeto,$idobjetoext,$tipoobjetoext,$status,$criadopor,$alteradopor){
        $results = SQL::ini(CarimboQuery::inserir(), [
            "idempresa" => $idempresa,
            "idpessoa" => $idpessoa,
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idobjetoext" => $idobjetoext,
            "tipoobjetoext" => $tipoobjetoext,
            "status" => $status,
            "criadopor" => $criadopor,
            "criadoem" => date("Y-m-d H:i:s"),
            "alteradopor" => $alteradopor,
            "alteradoem" => date("Y-m-d H:i:s"),
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function removerOcultarDoEvento($ideventotipo,$idevento,$idpessoa){
        $results = SQL::ini(FluxostatuspessoaQuery::removerOcultarDoEvento(), [
            "ideventotipo" => $ideventotipo,
            "idevento" => $idevento,
            "idpessoa" => $idpessoa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
    
    public static function atualizarAlertasParaAberto(){
        $results = SQL::ini(ImMsgConfQuery::atualizarAlertasParaAberto(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $results;
        } else {
            return $results;
            
        }
    }
}
?>
