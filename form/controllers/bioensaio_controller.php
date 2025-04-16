<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__ . "/../querys/tag_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/sgdoc_query.php");
require_once(__DIR__ . "/../querys/nucleo_query.php");
require_once(__DIR__ . "/../querys/pessoa_query.php");
require_once(__DIR__ . "/../querys/analise_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/unidade_query.php");
require_once(__DIR__ . "/../querys/sequence_query.php");
require_once(__DIR__ . "/../querys/lotecons_query.php");
require_once(__DIR__ . "/../querys/ficharep_query.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/resultado_query.php");
require_once(__DIR__ . "/../querys/bioensaio_query.php");
require_once(__DIR__ . "/../querys/servicoensaio_query.php");
require_once(__DIR__ . "/../querys/localensaio_query.php");
require_once(__DIR__ . "/../querys/formalizacao_query.php");
require_once(__DIR__ . "/../querys/bioterioanalise_query.php");
require_once(__DIR__ . "/../querys/especiefinalidade_query.php");


class BioensaioController extends Controller{

    public static function buscarClientesParaEstudo( $getidempresa ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(PessoaQuery::buscarClientesParaBioensaio(),['getidempresa' => $getidempresa])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret = array();
            foreach($results->data as $k => $r){
                $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
            }
            return $arrret;
        }
    }

    public static function buscarProdutosParaEstudo( $getidempresa, $idunidade ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ProdservQuery::buscarProdutoParaEstudo(),[
            'getidempresa' => $getidempresa,
            'idunidade' => $idunidade])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret = array();
            foreach($results->data as $k => $r){
                $arrret[$r["idlote"]]["descr"]=$r["descr"];
            }
            return $arrret;
        }
    }

    public static function buscarLoteAnimalParaEstudo( $getidempresa, $idunidade, $qtd, $plantel ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteQuery::buscarLoteAnimalParaEstudo(),[
            'getidempresa' => $getidempresa,
            'plantel' => $plantel,
            'idunidade' => $idunidade,
            'qtd' => $qtd])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $arrret1 = array();
            foreach($results->data as $k => $r){
                $arrret1[$r["idlotefracao"]]["descr"]=$r["descr"];
            }
            return $arrret1;
        }
    }

    public static function buscarResultadosVinculadosAoEnsaio( $idservicoensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::buscarResultadosVinculadosAoEnsaio(),[
            'idservicoensaio' => $idservicoensaio
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarModuloPorUnidade( $idunidade,$modulo ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(_ModuloQuery::buscarModuloPorUnidade(),[
            'idunidade' => $idunidade,
            'modulo' => $modulo
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0]['idobjeto'];
        }
    }

    public static function verificarSeExisteResultadoNaAnalise( $idanalise ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::verificarSeExisteResultadoNaAnalise(),[
            'idanalise' => $idanalise,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            $deleteproto = true;
            if(count($results->data) == 0) return true;

            foreach($results->data as $k => $r){
                if($r['servicoresultado'] == 0 ){
                    $deleteproto = false;
                }
            }

            return $deleteproto;
        }
    }

    public static function buscarEspecieFinalidade( $idunidade ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(EspecieFinalidadeQuery::listarEspecieFinalidadePorUnidade(),[
            'idunidade' => $idunidade,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }
    public static function listarEspecieFinalidadePorEmpresa($idempresa){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(EspecieFinalidadeQuery::listarEspecieFinalidadePorEmpresa(),[
            'idempresa' => $idempresa,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }

    }

    public static function buscarDescrProduto( $idlote ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteQuery::buscarDescrProdutoBioensaio(),[
            'idlote' => $idlote,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarDescrLote( $idlote ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteQuery::buscarDescrLoteBioensaio(),[
            'idlote' => $idlote,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarFichaRepBioensaio( $idficharep ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(FicharepQuery::buscarFichaRepELote(),[
            'idficharep' => $idficharep,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarFormalizacaoParaBioensaio( $idbioensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(FormalizacaoQuery::buscarFormalizacaoParaBioensaio(),[
            'idbioensaio' => $idbioensaio,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarDocumentoParaBioensaio( $idsgtipodoc,$idempresa ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SgdocQuery::buscarDocumentoParaBioensaio(),[
            'idsgtipodoc' => $idsgtipodoc,
            'idempresa' => $idempresa,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarDocumentoDoBioensaio( $idbioensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SgdocQuery::buscarDocumentoDoBioensaio(),[
            'idbioensaio' => $idbioensaio,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarPaisEFilhosDoBioensaios( $idbioensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarPaisEFilhosDoBioensaios(),[
            'idbioensaio' => $idbioensaio,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarDesenhoExperimental( $idexperimental,$idbioensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarDesenhoExperimental(),[
            'idbioensaio' => $idbioensaio,
            'idbioensaioexperimental' => $idexperimental,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarEstudosParaControle( $idpessoa,$getidempresa ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarDesenhoExperimental(),[
            'idpessoa' => $idpessoa,
            'getidempresa' => $getidempresa,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarAnalisesDoEnsaio( $idbioensaio ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::buscarAnalisesPorIdBioensaio(),[
            'idbioensaio' => $idbioensaio,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function verificaSeHacontroleNaAnalise( $idanalise ){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::verificaSeHaAnalisePai(),[
            'idanalise' => $idanalise,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return '';
        }else{
            $existe = "";
            if(count($results->data) < 1) return $existe;
            foreach($results->data as $k => $v){
                if($v['qtd'] > 0) return "disabled='disabled'";
            }
            return $existe;
        }
    }

    public static function buscarTipoAnalises(){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioterioAnaliseQuery::buscarTipoAnalises(),[])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data;
        }
    }

    public static function buscarBioensaiosParaControle($idpessoa,$idbioensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarBioensaiosParaControle(),[
            "idpessoa" => $idpessoa,
            "idbioensaio" => $idbioensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data;
        }
    }

    public static function buscarBioensaioDaAnalise($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::buscarBioensaioDaAnalise(),[
            "idanalise" => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data[0];
        }
    }

    public static function buscarTestesDoEnsaio($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioterioAnaliseQuery::buscarTestesDoEnsaio(),[
            "idanalise" => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data;
        }
    }

    public static function buscarServicosParaEnsaio($getidempresa,$idunidade){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ProdservQuery::buscarServicosParaEnsaio(),[
            "getidempresa" => $getidempresa,
            "idunidade" => $idunidade,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data;
        }
    }

    public static function buscarLocaisEnsaio($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LocalEnsaioQuery::buscarLocaisEnsaio(),[
            "idanalise" => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data[0];
        }
    }

    public static function buscarDatasDosServicos($idanalise,$idbioensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::buscarDatasDosServicos(),[
            "idanalise" => $idanalise,
            "idbioensaio" => $idbioensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data[0];
        }
    }

    public static function buscarGaiolasBioensaio($idtag){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(TagQuery::buscarGaiolasBioensaio(),[
            "idtag" => $idtag,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{

            return $results->data[0];
        }
    }

    public static function buscarExamesDeUmaGaiola($idplantel,$r3data,$fdata,$idunidade,$share){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarServicosDeUmaGaiola(),[
            "idplantel" => $idplantel,
            "r3data" => $r3data,
            "fdata" => $fdata,
            "idunidade" => $idunidade,
            "share" => $share,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function verificaSeHaVagasNaGaiola($idtag){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::verificaSeHaVagasNaGaiola(),[
            "idtag" => $idtag
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function verificaSeExsisteSequence(){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SequenceQuery::verificaSeExisteSequence(),[
            "sequence" => 'bioensaio'
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            
            if($results->data[0]['quant'] == 0) return false;
            if($results->data[0]['quant'] >= 1) return true;

            return false;
        }
    }

    public static function criarNovaSequence(){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SequenceQuery::inserir(),[
            'sequence' => 'bioensaio',
            'idempresa'=> cb::idempresa(),
            'chave1' => 0,
            'chave2' => null,
            'chave3' => null,
            'exercicio' => 'year(current_date)',
            'descricao'  => null
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function atualizarSequence(){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SequenceQuery::atualizarChavePorIdEmpresaExercicio(),[
            'sequence' => 'bioensaio',
            'idempresa'=> cb::idempresa(),
            'exercicio' => 'year(current_date)',
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function buscarSequenceDoAno(){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(SequenceQuery::buscarSequenceDoAno(),[
            'sequence' => 'bioensaio',
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function apagarResultadosPendentesDaAnalise($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::apagarResultadosPendentesDaAnalise(),[
            'idanalise' => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function apagarServicosPendentesDaAnalise($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::apagarServicosPendentesDaAnalise(),[
            'idanalise' => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function inserirServicosDaConfiguracao($idanalise,$idempresa,$dtinicio,$idbioterioanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::inserirServicosDaConfiguracao(),[
            'idanalise' => $idanalise,
            'idempresa' => $idempresa,
            'dtinicio' => $dtinicio,
            'usuario' => $_SESSION['SESSAO']['USUARIO'],
            'idbioterioanalise' => $idbioterioanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function atualizarDosesVolumeViaDoBioensaioPelaConfiguracao($idanalise,$idbioterioanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::atualizarDosesVolumeViaDoBioensaioPelaConfiguracao(),[
            'idanalise' => $idanalise,
            'idbioterioanalise' => $idbioterioanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function buscarUnidadeParaExames($getidempresa){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(UnidadeQuery::buscarUnidadeParaExamesBioterio(),[
            'getidempresa' => $getidempresa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 10;
        }else{
            return $results->data[0]['destino'];
        }
    }

    public static function buscarConfiguracoesDoExame($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::buscarConfiguracoesDoExame(),[
            'idanalise' => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function inserirAmostra($idunidade,$status,$idfluxostatus,$idregistro,$exercicio,$idespeciefinalidade,$idsubtipoamostra,$idpessoa,$idnucleo,$tipoidade,$idade,$dataamostra,$nucleoamostra,$partida,$tipoobjetosolipor,$idobjetosolipor){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AmostraQuery::inserirAmostra(),[
            'idempresa' => cb::idempresa(),
            'idunidade' => $idunidade,
            'status' => $status,
            'idfluxostatus' => $idfluxostatus,
            'idregistro' => $idregistro,
            'exercicio' => $exercicio,
            'idespeciefinalidade' => $idespeciefinalidade,
            'idsubtipoamostra' => $idsubtipoamostra,
            'idpessoa' => $idpessoa,
            'idnucleo' => $idnucleo,
            'tipoidade' => $tipoidade,
            'idade' => $idade,
            'dataamostra' => $dataamostra,
            'nucleoamostra' => $nucleoamostra,
            'partida' => $partida,
            'tipoobjetosolipor' => $tipoobjetosolipor,
            'idobjetosolipor' => $idobjetosolipor,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("lastid" => $results->lastInsertId(),"erro"=>false);
        }
    }

    public static function atualizarAmostraDoServico($idamostra,$idservicoensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::atualizarAmostraDoServico(),[
            'idamostra' => $idamostra,
            'idservicoensaio' => $idservicoensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function inserirResultado($idamostra,$idempresa,$idprodserv,$idservicoensaio,$qtd,$status,$idfluxostatus,$usuario){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::criarTestesBioensaio(),[
            "idamostra" => $idamostra,
            "idempresa" => $idempresa,
            "idprodserv" => $idprodserv,
            "idservicoensaio" => $idservicoensaio,
            "qtd" => $qtd,
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "usuario" => $usuario,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("lastid" => $results->lastInsertId(),"erro"=>false);
        }
    }

    public static function buscarConfiguracaoDoEnsaio($idservicoensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::buscarConfiguracaoDoEnsaio(),[
            "idservicoensaio" => $idservicoensaio
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarFimDoEnsaio($day,$idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::buscarFimDoEnsaio(),[
            "day" => $day,
            "idanalise" =>$idanalise
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function inserirAnalise($idobjeto,$objeto){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::inserirAnalise(),[
            "idobjeto" => $idobjeto,
            "objeto" =>$objeto,
            "usuario" =>$_SESSION['SESSAO']["USUARIO"],
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro"=>true);
        }else{
            return array("erro"=>false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function copiarAnalise($idbioensaio,$idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::copiarAnalise(),[
            "idbioensaio" => $idbioensaio,
            "idanalise" =>$idanalise,
            "usuario" =>$_SESSION['SESSAO']["USUARIO"],
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro"=>true);
        }else{
            return array("erro"=>false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function buscarLoteConsBioensaio($idobjeto,$idlote){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteconsQuery::buscarConsumoLoteconsBioensaio(),[
            "idobjeto" => $idobjeto,
            "idlote" =>$idlote,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarBioensaio($idbioensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(BioensaioQuery::buscarPorChavePrimaria(),[
            "pkval" => $idbioensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarInfosDoLote($idlote,$getidempresa){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LoteQuery::buscarInfosLoteBioensaio(),[
            "idlote" => $idlote,
            "getidempresa" => $getidempresa,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function inserirNucleo($idobjeto,$idempresa,$idpessoa,$idunidade,$idespeciefinalidade,$objeto,$nucleo,$lote){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NucleoQuery::inserirNucleo(),[
            "idobjeto" => $idobjeto,
            "idempresa" => $idempresa,
            "idpessoa" => $idpessoa,
            "idunidade" => $idunidade,
            "idespeciefinalidade" => $idespeciefinalidade,
            "objeto" => $objeto,
            "nucleo" => $nucleo,
            "lote" => $lote,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function atualizarNucleoCompleto($idlote,$nucleo,$idpessoa,$idespeciefinalidade,$lote,$usuario,$idnucleo){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NucleoQuery::atualizarNucleoCompleto(),[
            "idlote" => $idlote,
            "nucleo" => $nucleo,
            "idpessoa" => $idpessoa,
            "idespeciefinalidade" => $idespeciefinalidade,
            "lote" => $lote,
            "usuario" => $usuario,
            "idnucleo" => $idnucleo,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function atualizarNucleoParcial($nucleo,$idpessoa,$idespeciefinalidade,$usuario,$idnucleo){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NucleoQuery::atualizarNucleoParcial(),[
            "nucleo" => $nucleo,
            "idpessoa" => $idpessoa,
            "idespeciefinalidade" => $idespeciefinalidade,
            "usuario" => $usuario,
            "idnucleo" => $idnucleo,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function atualizarBioterioAnalise($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::atualizarBioterioAnalise(),[
           "idanalise" => $idanalise
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function inserirLocalEnsaio($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LocalEnsaioQuery::inserirLocalEnsaio(),[
           "idanalise" => $idanalise
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function copiarConfServicoBioterio($idanalise,$idanalisectr){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::copiarConfServicoBioterio(),[
           "idanalise" => $idanalise,
           "idanalisectr" => $idanalisectr,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function apagarAnalise($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(AnaliseQuery::apagarAnalise(),[
           "idanalise" => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function apagarServicoEnsaio($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ServicoEnsaioQuery::apagarServicoEnsaio(),[
           "idanalise" => $idanalise,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function atualizarLocalEnsaioTag($idanalise){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LocalEnsaioQuery::atualizarTagLocalEnsaio(),[
           "idanalise" => $idanalise,
           "idtag" => 'NULL',
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array("erro" => true);
        }else{
            return array("erro" => false,"lastid"=>$results->lastInsertId());
        }
    }

    public static function atualizarLocalEnsaioStatus($idbioensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(LocalEnsaioQuery::finalizaLocalEnsaioPorIdbioensaio(),[
           "idbioensaio" => $idbioensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            return true;
        }
    }

    public static function buscarResultadosVinculadosAoServico($idservicoensaio){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ResultadoQuery::buscarResultadosPorIdServicoEnsaio(),[
           "idservicoensaio" => $idservicoensaio,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }
}
?>