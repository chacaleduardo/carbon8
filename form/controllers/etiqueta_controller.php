<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");

//QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/resultado_query.php");
require_once(__DIR__."/../querys/impetiqueta_query.php");
require_once(__DIR__."/../querys/formalizacao_query.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/nf_query.php");
require_once(__DIR__."/../querys/endereco_query.php");
require_once(__DIR__."/../querys/solmat_query.php");
require_once(__DIR__."/../querys/tag_query.php");

//Controllers
require_once(__DIR__."/../controllers/lote_controller.php");

if(empty($_GET["_modulo"])){
    SQL::setModuloAtual('etiquetazpl');
}

class EtiquetaController extends Controller{

    public static function buscarLoteEtiquetaResultado( $sqlIdEmpresa, $idAmostra ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::buscarLoteEtiquetaResultado(),['idempresa' => $sqlIdEmpresa,
                                                                            'idamostra' => $idAmostra
                                                                            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfoEtiquetaAmostra( $idAmostra, $idLoteEtiqueta ){
        //busca as informações que serão utilzadas para montar o layout da etiqueta
        $results = SQL::ini(AmostraQuery::buscarInfoEtiquetaAmostra(),['idamostra' => $idAmostra,
                                                                        'idloteetiqueta' => $idLoteEtiqueta
                                                                        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function versionarEtiqueta( $idEmpresa, $idAmostra, $user ){
        //atualiza a qtd de impressoes do lote de etiqueta
        $results = SQL::ini( ImpetiquetaQuery::atualizarImpetiqueta(), ['idempresa' => $idEmpresa,
                                                                        'idamostra' => $idAmostra,
                                                                        'user' => $user
                                                                        ] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return ["STATUS" => "OK"];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo1( $idLote ){
        //Busca as informações para montar a etiqueta tipo1 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo1(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo16zpl( $idLote ){
        //Busca as informações para montar a etiqueta tipo1 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo16zpl(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarSementesEtiquetaFormalizacaoTipo1( $idLote ){
        //busca as sementes para imprimi-las na etiqueta tipo1 da formalização
        $results = SQL::ini( LoteQuery::buscarSementesParaEtiquetaFormalizacaoTipo1(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo2( $idLote ){
        //Busca as informações para montar a etiqueta tipo2 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo2(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }


    public static function buscarVolumeFormulaDoLoteParaEtiquetaFormalizacao( $idLote ){
        //Busca o volume da formula do lote
        $results = SQL::ini( LoteQuery::buscarVolumeFormulaDoLoteParaEtiquetaFormalizacao(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo3( $idLote ){
        //Busca as informações para montar a etiqueta tipo3 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo3(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo4( $idLote ){
        //Busca as informações para montar a etiqueta tipo4 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo4(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo5( $idLote ){
        //Busca as informações para montar a etiqueta tipo5 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo5(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo5b( $idLote ){
        //Busca as informações para montar a etiqueta tipo5b da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo5b(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo8( $idLote ){
        //Busca as informações para montar a etiqueta tipo8 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo8(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo12( $idLote ){
        //Busca as informações para montar a etiqueta tipo12 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo12(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo13( $idLote ){
        //Busca as informações para montar a etiqueta tipo13 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo13e14(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo14( $idLote ){
        //Busca as informações para montar a etiqueta tipo14 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo13e14(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo15( $idLote ){
        //Busca as informações para montar a etiqueta tipo15 da formalização
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaFormalizacaoTipo15(), ['idlote'=> $idLote] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosEtiquetaImpetiquetaLoteAlmox( $idLote, $str, $idTipoUnidade ){
        //Busca as informações para montar a etiqueta impetiquetaalmox
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaImpetiquetaLoteAlmox(), [
                                                                                    'idlote'        => $idLote,
                                                                                    'str'           => $str,
                                                                                    'idtipounidade' => $idTipoUnidade
                                                                                ] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaImpetiquetaLote( $idLote ){
        return LoteController::buscarLotePorIdLote($idLote);
    }

    public static function buscarInfosEtiquetaPedidoGeral( $idNF ){
        //Busca as informações para montar a etiqueta impetiqueta
        $results = SQL::ini( NfQuery::buscarInfosEtiquetaPedidoGeral(), ['idnf'=> $idNF] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarEnderecosEtiquetaPedidoGeral( $idEndereco ){
        //Busca as informações para montar a etiqueta impetiqueta
        $results = SQL::ini( EnderecoQuery::buscarEnderecoPorIdComnfscidadesiaf(), ['idendereco'=> $idEndereco] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosSolmatCupomFiscal( $idSolmat, $idSolicitado, $modulo ){
        //Busca as informações para montar a etiqueta impetiqueta
        $results = SQL::ini( SolmatQuery::buscarInfosParaCupomFiscal(), [
            'idsolmat'=> $idSolmat,
            'idsolicitado'=> $idSolicitado,
            'modulo'=> $modulo,
            ] )::exec();
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaTagQrCode( $idTag, $filhos = '' ){
        //Busca as informações para montar a etiqueta impetiqueta
        if(!empty($filhos)){
            $results = SQL::ini( TagQuery::buscarTagPaiComFilhos(), ["idtag" => $idTag] )::exec();
        }else{
            $results = SQL::ini( TagQuery::buscarTagAtivoEAlocada(), ["idtag" => $idTag] )::exec();
        }
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarInfosEtiquetaRotulagem15x60( $idLote ){
        //Busca as informações para montar a etiqueta impetiqueta
        $results = SQL::ini( LoteQuery::buscarInfosEtiquetaRotulagem15x60(), ["idlote" => $idLote] )::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static $cabecalhoTSPL60x40 ="
SIZE 60 mm, 40 mm
SPEED 5
DENSITY 7
DIRECTION 1
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";

    public static $cabecalhoTSPL40x20 ="SIZE 40 mm, 20 mm
SPEED 5
DENSITY 7
DIRECTION 0
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";

    public static $cabecalhoTSPL50x30 ="SIZE 50 mm, 30 mm
SPEED 5
DENSITY 7
DIRECTION 0
REFERENCE 0,0
OFFSET 0 mm
SHIFT 0
CODEPAGE UTF-8
CLS";

    public static $cabecalhoTSPL58x30 ="SIZE 58 mm, 30 mm
SPEED 1
DENSITY 15
DIRECTION 1,0
REFERENCE 0,0
OFFSET 0 mm
AUTODETECT
SHIFT 0
CODEPAGE UTF-8
CLS";
    

}

?>