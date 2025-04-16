<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/sgdoc_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/arquivo_query.php");
require_once(__DIR__."/../querys/sgdocpag_query.php");
require_once(__DIR__."/../querys/sgdocupd_query.php");
require_once(__DIR__."/../querys/carimbo_query.php");
require_once(__DIR__."/../querys/sgdocpagtemplate_query.php");
require_once(__DIR__."/../querys/sgdoctipodocumento_query.php");
require_once(__DIR__."/../querys/sgdoctipodocumentocampos_query.php");
require_once(__DIR__."/../querys/sgdoctipodocumentoopcao_query.php");
require_once(__DIR__."/../querys/fluxostatushistobs_query.php");
require_once(__DIR__."/../querys/evento_query.php");
require_once(__DIR__."/../querys/nf_query.php");
require_once(__DIR__."/../querys/_status_query.php");
require_once(__DIR__."/../querys/sgdepartamento_query.php");
require_once(__DIR__."/../querys/pessoaobjeto_query.php");

// CONTROLLERS
require_once(__DIR__."/../controllers/unidade_controller.php");
class DocumentoController extends Controller{

    // ----- FUNÇÕES -----
    public static function buscarPorChavePrimaria ( $idSgdoc ){
        $result = (SQL::ini(SgdocQuery::buscarPorChavePrimaria(),['pkval'=>$idSgdoc])::exec());

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
        
    }

    public static function buscarArrayDeUnidadesDisponiveisParaOModulo($idSgdoc, $tipoObjeto){
        return UnidadeController::buscarUnidadesDisponiveisPorUnidadeObjeto($idSgdoc, $tipoObjeto);
    }

    public static function buscarDocsVinculadosSemAvaliacaoTreinamento( $idSgdoc,$idempresa ){
        $result = SQL::ini(SgdocQuery::buscarDocsVinculadosSemAvaliacaoTreinamento(),[
                'idsgdoc' => $idSgdoc,
                "idempresa" => $idempresa
            ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }
    
    public static function buscarDocsVinculados( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarDocsVinculados(),['idsgdoc' => $idSgdoc])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }
    
    public static function buscarParticipantesDeDocsVinculados( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarParticipantesDeDocsVinculados(),['idsgdoc' => $idSgdoc])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarPessoasParaVincularAoDoc( $idsTipoPessoa ){
        $pessoaPorCbUserIdempresa = share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa");

		$pessoaPorCbUserIdempresa = $pessoaPorCbUserIdempresa?$pessoaPorCbUserIdempresa:' AND p.idempresa = '.cb::idempresa();
        $result = SQL::ini(PessoaQuery::listarPessoaPorIdTipoPessoa(),[
            "status" => " AND p.status = 'ATIVO'",
			"idtipopessoa" => $idsTipoPessoa,
			"pessoaPorCbUserIdempresa" => $pessoaPorCbUserIdempresa,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarParticipantesVinculadosComSetor( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarParticipantesVinculadosComSetor(),['idsgdoc'=> $idSgdoc])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarParticipantesVinculadosSemSetor( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarParticipantesVinculadosSemSetor(),['idsgdoc'=> $idSgdoc])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarSetorDepsAreasVaziosNoDoc( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarSetorDepsAreasVaziosNoDoc(),['idsgdoc'=> $idSgdoc])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function buscarUltimaAssinatura( $idPessoa, $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarUltimaAssinatura(),[
            'idpessoa'=> $idPessoa,
            'idsgdoc' => $idSgdoc
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
    }
    
    public static function verificarPermissao( $idPessoa, $idSgdoc ){
        $result = SQL::ini(SgdocQuery::verificaPermissaoSgdoc(),[
            'idpessoa'=> $idPessoa,
            'idsgdoc'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return ($result->numRows() > 0)?true:false;
        }
    }

    public static function verificarPermissaoEdicao( $idPessoa, $idSgdoc ){
        $result = SQL::ini(SgdocQuery::verificaPermissaoSgdocEdicao(),[
            'idpessoa'=> $idPessoa,
            'idsgdoc'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data;
        }
    }

    public static function pegarDataVencimento( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::pegarDataVencimento(),[
            'idsgdoc'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return $result->data[0];
        }
    }

    public static function buscarAnexosPorTipoObjetoIdObjeto( $tipoObjeto, $idObjeto){
        $result = SQL::ini(ArquivoQuery::buscarAnexosPorTipoObjetoIdObjeto(),[
            'tipoobjeto'=> $tipoObjeto,
            'idobjeto'=> $idObjeto
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarCamposVisiveisPorIdsgdoctipodocumento( $idSgdoctipodocumento ){
        $result = SQL::ini(SgdoctipodocumentocamposQuery::buscarCamposVisiveisPorIdsgdoctipodocumento(),[
            'idsgdoctipodocumento'=> $idSgdoctipodocumento
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarPaginas( $idSgdoc ){
        $result = SQL::ini(SgdocpagQuery::buscarPaginasOrdenadas(),[
            'idsgdoc'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarTemplate( $idSgdocTipoDocumento, $pagina ){
        $result = SQL::ini(SgdocpagtemplateQuery::buscarTemplate(),[
            'idsgdoctipodocumento'=> $idSgdocTipoDocumento,
            'pagina' => $pagina
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarPorIdsgdoctipodocumentocampos( $IdSgdoctipodocumentocampos ){
        $result = SQL::ini(SgdoctipodocumentoopcaoQuery::buscarPorIdsgdoctipodocumentocampos(),[
            'idsgdoctipodocumentocampos'=> $IdSgdoctipodocumentocampos
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarPorChavePrimariaIdsgdoctipodocumento( $IdSgdoctipodocumento ){
        $result = SQL::ini(SgdoctipodocumentoQuery::buscarPorChavePrimaria(),[
            'pkval'=> $IdSgdoctipodocumento
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarVersoesDoc( $idSgdoc ){
        $result = SQL::ini(SgdoctipodocumentoQuery::buscarPorChavePrimaria(),[
            'pkval'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarAvaliacoesVinculadas( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarDocVinculadosPorTipo(),[
            'idsgdoc'=> $idSgdoc,
            'tipo'=> 'avaliacao'
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarTreinamentosVinculados( $idSgdoc ){
        $result = SQL::ini(SgdocQuery::buscarDocVinculadosPorTipo(),[
            'idsgdoc'=> $idSgdoc,
            'tipo'=> 'treinamento'
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarRestauracoesSgdoc( $idSgdoc, $modulo ){
        $result = SQL::ini(FluxostatushistobsQuery::buscarRestauracoesPorModuloIdmodulo(),[
            'idmodulo'=> $idSgdoc,
            'modulo'  => $modulo
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarHistoricoDeVersoes( $idSgdoc ){
        $result = SQL::ini(SgdocupdQuery::buscarHistoricoDeVersoes(),[
            'idsgdoc'=> $idSgdoc
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarPendenciasPessoa( $idPessoa ){
        $result = SQL::ini(NfQuery::buscarPendenciasPessoa(),[
            'idpessoa'=> $idPessoa
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarEventosVinculadosAoDoc( $modulo, $idpk ){
        $result = SQL::ini(EventoQuery::buscarEventosVinculadosAoModulo(),[
            '_modulo'=> $modulo,
            'idpk'=> $idpk,
            'and'=> "",
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarUltimaVersaoAprovada( $idSgdoc, $versao ){
        $result = SQL::ini(SgdocupdQuery::buscarUltimaVersaoAprovada(),[
            'idsgdoc'=> $idSgdoc,
            'versao'=> $versao
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarUltimaAssinaturaPendente( $idSgdoc, $idPessoa ){
        $result = SQL::ini(CarimboQuery::buscarUltimaAssinaturaPendentePorIdObjetoTipoObjetoIdPessoa(),[
            'idobjeto'=> $idSgdoc,
            'tipoobjeto'=> 'documento',
            'idpessoa'=> $idPessoa
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function deletarTodasSgdocpag( $idSgdoc ){
        $result = SQL::ini(SgdocpagQuery::deletarSgdocpagPorIdsgdoc(),[
            'idsgdoc'=> $idSgdoc,
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return false;
        }else{
            return ($result->numRows() > 0)?true:false;
        }
    }

    public static function buscarPessoasSemAssinatura( $idSgdoc, $modulo, $versao = 0 ){
        $result = SQL::ini(SgdocQuery::buscarPessoasSemAssinatura(),[
            'idsgdoc'=> $idSgdoc,
            'modulo'=> $modulo,
            'versao'=> $versao,
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarIdStatusInicialPorModuloIdobjetoTipoObjeto( $tipoDoc, $modulo ){
        $result = SQL::ini(_StatusQuery::buscarIdStatusInicialPorModuloIdobjetoTipoObjeto(),[
            'idobjeto'=> $tipoDoc,
            'tipoobjeto'=> 'idsgdoctipo',
            'modulo'=> $modulo,
            'getidempresa'=> getidempresa('ms.idempresa', "fluxo"),
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data[0]);
        }
    }

    public static function buscarTodosSetoresDeUmDepartamento( $idsgdepartamento ){
        $result = SQL::ini(SgDepartamentoQuery::buscarTodosSetoresDeUmDepartamento(),[
            'idsgdepartamento'=> $idsgdepartamento,
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    public static function buscarPessoasDeUmObjeto( $idObjeto, $tipoobjeto ){
        $result = SQL::ini(PessoaobjetoQuery::buscarPorIdobjetoTipoobjeto(),[
            'idobjeto'=> $idObjeto,
            'tipoobjeto'=> $tipoobjeto,
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    // public static function verificarSeEParticipante( $idObjeto, $tipoobjeto, $idModulo, $modulo ){
    //     $result = SQL::ini(FluxostatuspessoaQuery::buscarPorIdobjetoTipoobjetoModuloIdmodulo(),[
    //         'idobjeto'=> $idObjeto,
    //         'tipoobjeto'=> $tipoobjeto,
    //         'modulo'=> $modulo,
    //         'idmodulo'=> $idModulo,
    //     ])::exec();
        
    //     if($result->error()){
    //         parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
    //         return [];
    //     }else{
    //         return ($result->data[0]);
    //     }
    // }

    // ----- AUTOCOMPLETE -----
    public static function buscarJsonTipoDocumento( $tipoDoc,$idPessoa ){
        if($tipoDoc == 'questionario'){
            $result = SQL::ini(SgdocQuery::buscarInfosTipoDocumentoQueOUsuarioPossui(),[
                'idpessoa'    => $idPessoa,
                'idsgdoctipo' => $tipoDoc
            ])::exec();
        }else{
            $result = SQL::ini(SgdocQuery::buscarInfosTipoDocumento(),['idsgdoctipo' => $tipoDoc])::exec();
        }

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function buscarDocsQuePodemSerVinculados( $idSgdoc, $sqlIdEmpresa = '' ){
        $result = SQL::ini(SgdocQuery::buscarDocsQuePodemSerVinculados(),[
            'idsgdoc'      => $idSgdoc,
            'sqlidempresa' =>$sqlIdEmpresa
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toJson($result->data);
        }
    }

    public static function buscarParticipantesParaVincularAoDoc( $idSgdoc ){
        $share = share::odie(false)::otipo('cb::usr');
        $documentopessoaPorSessionIdempresa = !empty($share::omodulo('funcionario')::moduloFiltrosPesquisa('p.idpessoa'))?'AND EXISTS '.$share::omodulo('funcionario')::moduloFiltrosPesquisa('p.idpessoa'): "";
        $documentosgsetorPorSessionIdempresa = !empty($share::omodulo('sgsetor')::moduloFiltrosPesquisa('sa.idsgsetor'))?'AND EXISTS '.$share::omodulo('sgsetor')::moduloFiltrosPesquisa('sa.idsgsetor'): "";
        $documentosgdepartamentoPorSessionIdempresa = !empty($share::omodulo('sgdepartamento')::moduloFiltrosPesquisa('a.idsgdepartamento'))?'AND EXISTS '.$share::omodulo('sgdepartamento')::moduloFiltrosPesquisa('a.idsgdepartamento'): "";
        $documentosgareaPorSessionIdempresa = !empty($share::omodulo('sgarea')::moduloFiltrosPesquisa('a.idsgarea'))?'AND EXISTS '.$share::omodulo('sgarea')::moduloFiltrosPesquisa('a.idsgarea'): "";
        $documentosgcoselhoPorSessionIdempresa = !empty($share::omodulo('sgconselho')::moduloFiltrosPesquisa('c.idsgconselho'))?'AND EXISTS '.$share::omodulo('sgconselho')::moduloFiltrosPesquisa('c.idsgconselho'): "";

        $documentopessoaPorSessionIdempresa = $documentopessoaPorSessionIdempresa?$documentopessoaPorSessionIdempresa:' AND p.idempresa in (select e1.idempresa from empresa e1 where e1.idempresa ='.cb::idempresa().' union select m1.idmatriz as idempresa from matrizconf m1 where m1.idempresa = '.cb::idempresa().')';
        $documentosgsetorPorSessionIdempresa = $documentosgsetorPorSessionIdempresa?$documentosgsetorPorSessionIdempresa:' AND sa.idempresa in (select e1.idempresa from empresa e1 where e1.idempresa ='.cb::idempresa().' union select m1.idmatriz as idempresa from matrizconf m1 where m1.idempresa = '.cb::idempresa().')';
        $documentosgdepartamentoPorSessionIdempresa = $documentosgdepartamentoPorSessionIdempresa?$documentosgdepartamentoPorSessionIdempresa:' AND a.idempresa in (select e1.idempresa from empresa e1 where e1.idempresa ='.cb::idempresa().' union select m1.idmatriz as idempresa from matrizconf m1 where m1.idempresa = '.cb::idempresa().')';
        $documentosgareaPorSessionIdempresa = $documentosgareaPorSessionIdempresa?$documentosgareaPorSessionIdempresa:' AND a.idempresa in (select e1.idempresa from empresa e1 where e1.idempresa ='.cb::idempresa().' union select m1.idmatriz as idempresa from matrizconf m1 where m1.idempresa = '.cb::idempresa().')';
        $documentosgcoselhoPorSessionIdempresa = $documentosgcoselhoPorSessionIdempresa?$documentosgcoselhoPorSessionIdempresa:' AND c.idempresa in (select e1.idempresa from empresa e1 where e1.idempresa ='.cb::idempresa().' union select m1.idmatriz as idempresa from matrizconf m1 where m1.idempresa = '.cb::idempresa().')';
        $result = SQL::ini(SgdocQuery::buscarParticipantesParaVincularAoDoc(),[
            'idsgdoc' => $idSgdoc,
            'documentopessoaPorSessionIdempresa' => $documentopessoaPorSessionIdempresa,
            'documentosgsetorPorSessionIdempresa' => $documentosgsetorPorSessionIdempresa,
            'documentosgdepartamentoPorSessionIdempresa' => $documentosgdepartamentoPorSessionIdempresa,
            'documentosgareaPorSessionIdempresa' => $documentosgareaPorSessionIdempresa,
            'documentosgcoselhoPorSessionIdempresa' => $documentosgcoselhoPorSessionIdempresa,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return ($result->data);
        }
    }

    // ----- FILLSELECT -----

    public static function buscarTiposDeDocQueOUsuarioPodeCriar( $idPessoa ){
        $result = SQL::ini(SgdocQuery::buscarTiposDeDocQueOUsuarioPodeCriar(),[
            'idpessoa'=> $idPessoa
        ])::exec();
        
        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarSubTiposDeAvaliacoesParaVincular(){
        $result = SQL::ini(SgdocQuery::buscarInfosTipoDocumento(),[
            'idsgdoctipo'=> 'avaliacao'
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }

    public static function buscarSubTiposDeTreinamentosParaVincular(){
        $result = SQL::ini(SgdocQuery::buscarInfosTipoDocumento(),[
            'idsgdoctipo'=> 'treinamento'
        ])::exec();
        
            if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($result->data);
        }
    }
}
?>