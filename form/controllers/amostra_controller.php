<?

require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/../querys/amostra_query.php");
require_once(__DIR__ . "/../querys/_auditoria_query.php");
require_once(__DIR__ . "/../querys/dadosamostra_query.php");
require_once(__DIR__ . "/../querys/descricaoamostra_query.php");
require_once(__DIR__ . "/../querys/isolamentoamostra_query.php");
require_once(__DIR__ . "/../querys/amostracampos_query.php");
require_once(__DIR__ . "/../querys/subtipoamostra_query.php");
require_once(__DIR__ . "/../querys/especiefinalidade_query.php");
require_once(__DIR__ . "/../querys/unidade_query.php");
require_once(__DIR__ . "/../querys/identificador_query.php");
require_once(__DIR__ . "/../querys/formalizacao_query.php");
require_once(__DIR__ . "/../querys/impetiqueta_query.php");
require_once(__DIR__ . "/../querys/notafiscalitens_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/pessoa_query.php");
require_once(__DIR__ . "/../querys/resultado_query.php");
require_once(__DIR__ . "/../querys/resultadoassinatura_query.php");
require_once(__DIR__ . "/../querys/fluxostatushistobs_query.php");
require_once(__DIR__ . "/../querys/modulocom_query.php");
require_once(__DIR__ . "/../querys/nfscidadesiaf_query.php");
require_once(__DIR__ . "/../querys/empresaimagem_query.php");
require_once(__DIR__ . "/../querys/pessoacrmv_query.php");
require_once(__DIR__ . "/../querys/prodservvinculo_query.php");


class AmostraController extends Controller
{
    public static $motivosPendencia = [
        "SEMASSINATURA" => "Sem Assinatura",
        "AUSENCIAINFORMACAO" => "Ausência de informação",
        "TESTEINCOMPATIVEL" => "Teste Incompatível"
    ];

    public static function buscarDadosCabecalhoReportAmostra($idamostra)
    {
        $results = SQL::ini(AmostraQuery::buscarDadosCabecalhoReportAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return $results;
        }
    }

    private static function buscarEmpresaAmostra($idamostra)
    {
        $results = SQL::ini(AmostraQuery::buscarEmpresaAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return cb::idempresa();
        } else {

            return $results->data[0]['idempresa'];
        }
    }


    public static function buscarCaminhoImagemTipoHeaderProduto($idamostra)
    {
        $idempresa = self::buscarEmpresaAmostra($idamostra);
        $idempresa = "AND idempresa = $idempresa";

        $results = SQL::ini(EmpresaImagemQuery::buscarCaminhoImagemTipoHeaderProduto(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {

            $caminhoImagem = $results->data[0]['caminho'];
        }

        return $caminhoImagem;
    }

    public static function contarResultadosAssinados($idamostra){

        $results = SQL::ini(AmostraQuery::contarResultadosAssinados(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {

            return $results->data[0]['assinado'];
        }
    }

    public static function buscarTemperaturaAmostra($idamostra,$objeto){

        $results = SQL::ini(DadosAmostraQuery::buscarPorIdamostraEObjeto(), [
            'idamostra' => $idamostra,
            'objeto' => $objeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {

            return $results->data[0];
        }
    }

    public static function buscarUnidadesAtivas($idempresa){

        $results = SQL::ini(UnidadeQuery::buscarUnidadesPorIdEmpresa(), [
            'idempresa' => $idempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {

            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarAmostracamposPorIdunidade($idunidade){

        $results = SQL::ini(AmostraCamposQuery::buscarPorIdunidade(), [
            'idunidade' => $idunidade,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if($results->numRows() > 0){
                $arrConf = array();
                foreach ($results->data as $k => $row) {
                    $arrConf['config'][$row['campo']][$row["idsubtipoamostra"]]['obrigatorio'] = $row['obrigatorio'];
                    $arrConf['config'][$row['campo']][$row["idsubtipoamostra"]]['visualizatra'] = $row['visualizatra'];
                    $arrConf['config'][$row['campo']][$row["idsubtipoamostra"]]['visualizaemissao'] = $row['visualizaemissao'];
                    $arrConf['colunas'][$row["idunidade"]][$row["idsubtipoamostra"]][$row["campo"]]=$row["campo"];
                    $arrConf["idamostracampos"][$row["idunidade"]][$row["idsubtipoamostra"]][$row["campo"]]=$row["idamostracampos"];
                }
                return $arrConf;
            }else{
                return [];
            }
            
        }
    }

    public static function buscarSubtipoamostraPorIdunidade($idunidade){

        $results = SQL::ini(SubtipoAmostraQuery::buscarSubtipoamostraPorIdunidade(), [
            'idunidade' => $idunidade,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrtmp = array();
            foreach ($results->data as $i => $r) {
                if($r["normativa"]){$strnormativa=$r["normativa"];}else{$strnormativa='';}
                $arrtmp[$r["idunidade"]][$i]["idsubtipoamostra"]=$r["idsubtipoamostra"];
                $arrtmp[$r["idunidade"]][$i]["value"]=$r["idsubtipoamostra"];
                $arrtmp[$r["idunidade"]][$i]["label"]=  $r["tiposubtipo"].$strnormativa;
            }
            return parent::toJson($arrtmp);
        }
    }

    public static function buscarServicosDaUnidade($idunidade,$idamostra){
        if(is_numeric($idamostra))
            $str = "and not exists (select 1 from resultado r where idtipoteste=p.idprodserv and idamostra=".$idamostra." and r.status not in ('CANCELADO','OFFLINE'))";
        $results = SQL::ini(ProdservQuery::buscarServicosDaUnidadeEEmpresa(), [
            'idunidade' => $idunidade,
            'str'=> $str
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrtmp = array();
            foreach ($results->data as $i => $r) {
                $arrtmp[$i]["value"]=$r["idprodserv"];
                $arrtmp[$i]["label"]= ($r["descr"]);
                $arrtmp[$i]["ofc"]= $r['logoinmetro'];
            }
            return parent::toJson($arrtmp);
        }
    }

    public static function buscarEspeciefinalidade($idempresa){
        $results = SQL::ini(EspecieFinalidadeQuery::buscarEspeciefinalidadeComPlantel(), [
            'idempresa' => $idempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrtmp = array();
            foreach ($results->data as $i => $r) {
                $arrtmp[$r["idespeciefinalidade"]]["especie"]=($r["especie"]);
                $arrtmp[$r["idespeciefinalidade"]]["tipoespecie"]=($r["tipoespecie"]);
                $arrtmp[$r["idespeciefinalidade"]]["finalidade"]= ($r["finalidade"]);
                $arrtmp[$r["idespeciefinalidade"]]["calculoidade"]=($r["calculoidade"]);
                $arrtmp[$r["idespeciefinalidade"]]["flgcalculo"]= ($r["flgcalculo"]);
                $arrtmp[$r["idespeciefinalidade"]]["rotulo"]= $r["rotulo"];
            }
            return parent::toJson($arrtmp);
        }
    }

    public static function buscarClientesAmostra($getidempresa){
        $results = SQL::ini(PessoaQuery::buscarClientesAmostra(), [
            'getidempresa' => $getidempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrmaster = array();
            $arrtmpdet = array();
            $arrtmp = array();
            foreach ($results->data as $i => $r) {
                $arrtmp[$i]["value"]=$r["idpessoa"];
                $arrtmp[$i]["label"]=($r["nome"]);
                $arrtmpdet[$r["idpessoa"]]["observacaore"]=($r["observacaore"]);
                $arrtmpdet[$r["idpessoa"]]["cpfcnpj"]=formatarCPF_CNPJ(($r["cpfcnpj"]));
                $arrtmpdet[$r["idpessoa"]]["secretaria"]=($r["secretaria"]);
                $arrtmpdet[$r["idpessoa"]]["idsecretaria"]=$r["idsecretaria"];
                $arrtmpdet[$r["idpessoa"]]["pedidocp"]=$r["pedidocp"];
            }
            $arrmaster['arrtmpdet'] = $arrtmpdet;
            $arrmaster['arrtmp']= $arrtmp;
            return ($arrmaster);
        }
    }

    public static function listarTestesDaAmostra($idamostra){
        $results = SQL::ini(AmostraQuery::buscarTestesDaAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return ($results->data);
        }
    }

    public static function buscarSecretariaPessoa($idpessoa){
        $results = SQL::ini(PessoaQuery::buscarSecretariaPessoa(), [
            'idpessoa' => $idpessoa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarPedidopreferencia($idpessoa){
        $results = SQL::ini(PessoaQuery::buscarPedidopreferenciaPessoa(), [
            'idpessoa' => $idpessoa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function verificarSeHaResultadosNaNotafiscalitem($idresultado){
        $results = SQL::ini(NotaFiscalItensQuery::buscarItensPorIdresultado(), [
            'idresultado' => $idresultado,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function verificarSeHaResultadosNaNotafiscalitemPorIdamostra($idamostra){
        $results = SQL::ini(NotaFiscalItensQuery::buscarItensPorIdamostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function buscarLogsDeImpetiqueta($idamostra){
        $results = SQL::ini(ImpetiquetaQuery::buscarImpetiquetaComCodprodserv(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarLogsTestesCancelados($idamostra){
        $results = SQL::ini(ResultadoQuery::buscarTestesCanceladosPorIdAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarLogDeReabertura($idamostra,$modulo){
        $results = SQL::ini(AmostraQuery::buscarLogDeReabertura(), [
            'idamostra' => $idamostra,
            'modulo' => $modulo,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarUltimaAmostra($idunidade){
        $results = SQL::ini(AmostraQuery::buscarUltimaAmostra(), [
            'idunidade' => $idunidade,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarSolfab($idamostra){
        $results = SQL::ini(AmostraQuery::buscarSolfab(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarInformacoesTRAAssocioado($idamostra){
        $results = SQL::ini(AmostraQuery::buscarInformacoesTRAAssocioado(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarAmostraComSubtipo($idamostra){
        $results = SQL::ini(AmostraQuery::buscarAmostraComSubtipo(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarTRAAmostra($idamostra){
        $results = SQL::ini(AmostraQuery::buscarTRAAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarLoteAtiv($idamostra){
        $results = SQL::ini(AmostraQuery::buscarLoteAtiv(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarHoraDB(){
        $results = SQL::ini("SELECT dmahms(now()) as dmahms", [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarLoteDaFormalizacao($idloteativ){
        $results = SQL::ini(FormalizacaoQuery::buscarLoteDaFormalizacao(), [
            "idloteativ" => $idloteativ
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdentificadoresDaAmostra($idamostra){
        $results = SQL::ini(IdentificadorQuery::buscarIdentificadorPorIdobjetoTipoobjeto(), [
            "idobjeto" => $idamostra,
            "tipoobjeto" => 'amostra'
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCidadesNfscidadesiaf(){
        $results = SQL::ini(NfscidadesiafQuery::montarConsultaParaFillselect(), [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarAssinaturaTEA($idamostra,$modulo){
        $results = SQL::ini(CarimboQuery::buscarAssinaturaTEA(), [
            'idamostra' => $idamostra,
            'modulo' => $modulo,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarTRAVinculado($idamostra){
        $results = SQL::ini(AmostraQuery::buscarTRAVinculado(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarArquivosAmostra($idamostra){
        $results = SQL::ini(AmostraQuery::buscarArquivosAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarConferenciaAmostra($idamostra){
        $results = SQL::ini(CarimboQuery::buscarConferenciaAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarComentariosCoferenciaDeAmostra($idamostra){
        $results = SQL::ini(ModulocomQuery::buscarComentariosCoferenciaDeAmostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function verificarSeAmostraFoiAssinada($idamostra,$idempresa){
        $results = SQL::ini(CarimboQuery::buscarAssinaturaAmostra(), [
            'idamostra' => $idamostra,
            'idempresa' => $idempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function criarTestesDaAmostraERetorna($idempresa,$idamostra,$idtipoteste,$quantidade,$idsecretaria,$loteetiqueta,$npedido,$ord,$idfluxostatus,$usuario,$cobrar){
        $results = SQL::ini(ResultadoQuery::criarTestes(), [
            'idempresa' => $idempresa,
            'idamostra' => $idamostra,
            'idtipoteste' => $idtipoteste,
            'quantidade' => $quantidade,
            'idsecretaria' => $idsecretaria,
            'loteetiqueta' => $loteetiqueta,
            'npedido' => $npedido,
            'ord' => $ord,
            'idfluxostatus' => $idfluxostatus,
            'usuario' => $usuario,
            'cobrar' => $cobrar,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $results1 = SQL::ini(ResultadoQuery::buscarPorChavePrimaria(), [
                'pkval' => $results->lastInsertId(),
            ])::exec();
            if ($results1->error()) {
                parent::error(__CLASS__, __FUNCTION__, $results1->errorMessage());
                return [];
            }else{
                return $results1->data[0];
            }
        }
    }

    public static function buscarProdservVinculada($idamostra,$idprodserv){
        $results = SQL::ini(ProdservVinculoQuery::buscarProdservVinculada(), [
            'idamostra' => $idamostra,
            'idprodserv' => $idprodserv,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function inserirIndetificador($idempresa,$idamostra,$identificacao,$usuario){
        $results = SQL::ini(IdentificadorQuery::inserirIdentificador(), [
            'idempresa' => $idempresa,
            'idamostra' => $idamostra,
            'identificacao' => $identificacao,
            'usuario' => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarResultadosAssinadosDaAmostra($idamostra){
        $results = SQL::ini(ResultadoQuery::buscarResultadosAssinadosDaAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarDatasAmostra($idamostra){
        $results = SQL::ini(AmostraQuery::buscarDatasAmostra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarAmostraPorEnderecoEFinalidade($idamostra)
    {
        $results = SQL::ini(AmostraQuery::buscarAmostraPorEnderecoEFinalidade(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) 
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarResultado($idamostra)
    {
        $results = SQL::ini(AmostraQuery::buscarResultadoEAmostraPorIdAmostraTra(), [
            'idamostra' => $idamostra
        ])::exec();

        if ($results->error()) 
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if($results->numRows() < 1)
            {
                $results = SQL::ini(AmostraQuery::buscarResultadoEAmostraPorIdAmostra(), [
                    'idamostra' => $idamostra
                ])::exec();

                foreach ($results->data as $value) 
                {
                    foreach ($value as $col => $val) 
                    {
                        $arrret[$value["idresultado"]][$col] = $val; 
                    }
                }
            }

            return $arrret;
        }
    }

    public static function fecharResultadosAssinados($idamostra,$idfluxostatus){
        $results = SQL::ini(ResultadoQuery::fecharResultadosAssinados(), [
            'idamostra' => $idamostra,
            'idfluxostatus' => $idfluxostatus
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function deletarResultadoAssinaturaPorIdamostra($idamostra){
        $results = SQL::ini(ResultadoAssinaturaQuery::deletarResultadoAssinaturaPorIdamostra(), [
            'idamostra' => $idamostra,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function inserirRegistroAuditoria($idempresa,$linha,$acao,$objeto,$idobjeto,$coluna,$valor,$usuario,$HTTP_REFERER){
        $results = SQL::ini(AuditoriaQuery::inserirRegistroAuditoria(), [
            'idempresa' => $idempresa,
            'linha' => $linha,
            'acao' => $acao,
            'objeto' => $objeto,
            'idobjeto' => $idobjeto,
            'coluna' => $coluna,
            'valor' => $valor,
            'usuario' => $usuario,
            'HTTP_REFERER' => $HTTP_REFERER
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function solicitarAssinaturaAmostra($idamostra){
        $results = SQL::ini(AmostraQuery::solicitarAssinaturaAmostra(), ['idamostra' => $idamostra])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function verificarAssinaturaAmostra($idamostra,$modulo){
        $results = SQL::ini(CarimboQuery::verificarAssinaturaAmostra(), [
            'idamostra' => $idamostra,
            'modulo' => $modulo,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function deletarAssinaturasPendentesAmostra($idamostra,$idobjetoext,$tipoobjetoext){
        $results = SQL::ini(CarimboQuery::deletarAssinaturasPendentesPorIdobjetoIdobjetoextTipoobjetoext(), [
            'idobjeto' => $idamostra,
            'idobjetoext' => $idobjetoext,
            'tipoobjetoext' => $tipoobjetoext,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function inserirAssinaturaAmostra($idempresa,$idpessoa,$idobjeto,$tipoobjeto,$idobjetoext,$tipoobjetoext,$status,$criadopor,$criadoem,$alteradopor,$alteradoem){
        $results = SQL::ini(CarimboQuery::inserir(), [
            "idempresa" => $idempresa,
            "idpessoa" => $idpessoa,
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idobjetoext" => $idobjetoext,
            "tipoobjetoext" => $tipoobjetoext,
            "status" => $status,
            "criadopor" => $criadopor,
            "criadoem" => $criadoem,
            "alteradopor" => $alteradopor,
            "alteradoem" => $alteradoem
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarCRMV($idpessoa){
        $results = SQL::ini(PessoaCrmvQuery::buscarPorChavePrimaria(), [
            "pkval" => $idpessoa,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarCampoDaAmostra($idamostra,$campo,$valor){
        $results = SQL::ini(AmostraQuery::atualizarCampoDaAmostra(), [
            "campo" => $campo,
            "valorcampo" => $valor,
            "idamostra" => $idamostra,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarIndicadoresApagar($idobjeto, $identificacao, $limitselect){
        $results = SQL::ini(AmostraQuery::buscarIndicadoresApagar(), [
            "idobjeto" => $idobjeto,
            "identificacao" => $identificacao,
            "limitselect" => $limitselect,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function apagarIndicadores($ididentificador){
        $results = SQL::ini(AmostraQuery::apagarIndicadores(), [
            "ididentificador" => $ididentificador
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } 
    }

    public static function alterarDataAssinatura($idcarrimbo,$data){
        $results = SQL::ini(CarimboQuery::alterarDataAssinatura(), [
            "idcarrimbo" => $idcarrimbo,
            "data" => $data,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function inserirDadosAmostra($idempresa,$idamostra,$objeto,$valorobjeto,$usuario){
        $results = SQL::ini(DadosAmostraQuery::inserir(), [
            'idempresa' => $idempresa,
            'idamostra' => $idamostra,
            'objeto' => $objeto,
            'valorobjeto' => $valorobjeto,
            'usuario' => $usuario,
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return true;
        }
    }

    public static function buscarNomePessoa($idpessoa){
        $results = SQL::ini(PessoaQuery::buscarNomePessoa(), [
            'idpessoa' => $idpessoa
            ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function listarFillSelectSubtipoamostraPorIdEmpresa()
    {
        $results = SQL::ini(SubtipoAmostraQuery::buscarSubtipoamostraPorIdEmpresa(), [
            'getidempresa' => getidempresa('idempresa', 'subtipoamostra'),
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarSubtipoamostraEmpresaPorIdEmpresa()
    {
        $results = SQL::ini(SubtipoAmostraQuery::buscarSubtipoamostraEmpresaPorIdEmpresa(), [
            'getidempresa' => getidempresa('e.idempresa', 'prodserv'),
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarAmostrasPorDataClausulaEClausulaIdEmpresa($data, $clausula, $clausulaIdEmpresa)
    {
        $amostras = SQL::ini(AmostraQuery::buscarAmostrasPorDataClausulaEClausulaIdEmpresa(), [
            'data' => $data,
            'clausula' => $clausula,
            'clausulaidempresa' => $clausulaIdEmpresa
        ])::exec();

        $_SESSION["SEARCH"]["SQL"] = $amostras->sql();

	    echo "<!-- ".$amostras->sql()." -->";

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return [];
        }

        return $amostras->data;
    }

    public static function buscarAmostraPorIdPlantelGetIdEmpresaDataEClausula($idPlantel, $getIdEmpresa, $data, $clausula)
    {
        $amostras = SQL::ini(AmostraQuery::buscarAmostraPorIdPlantelGetIdEmpresaDataEClausula(), [
            'idplantel' => $idPlantel,
            'getidempresa' => $getIdEmpresa,
            'data' => $data,
            'clausula' => $clausula
        ])::exec();

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return [];
        }

        return $amostras->data[0];
    }

    public static function inserirComentarioDeRestauracaoResultado($idempresa,$idmodulo,$modulo,$motivo,$motivoobs,$status,$idfluxostatus,$versao,$versaorigem,$criadopor,$alteradopor)
    {
        $amostras = SQL::ini(FluxostatushistobsQuery::inserirFluxoStatusHistObsComVersao(), [
            "idempresa" => $idempresa,
            "idmodulo" => $idmodulo,
            "modulo" => $modulo,
            "motivo" => $motivo,
            "motivoobs" => $motivoobs,
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "versao" => $versao,
            "versaoorigem" => $versaorigem,
            "criadopor" => $criadopor,
            "alteradopor" => $alteradopor,
        ])::exec();

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarDescricaoAmostraAtivo(){
        $amostras = SQL::ini(DescricaoAmostraQuery::buscarDescricaoAmostraAtivo(), [])::exec();

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return [];
        }

        return $amostras->data;
    }

    public static function buscarIsolamentoAmostraAtivo(){
        $amostras = SQL::ini(IsolamentoAmostraQuery::buscarIsolamentoAmostraAtivo(), [])::exec();

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return [];
        }

        return $amostras->data;
    }
    
    public static function exigeConferenciaAmostra($idamostra){
        $amostras = SQL::ini(SubtipoAmostraQuery::buscarExigenciaConferenciaAmostra(), [
            "idamostra" => $idamostra
        ])::exec();

        if ($amostras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostras->errorMessage());
            return false;
        }

        if($amostras->data[0]["conferencia"] == "S"){
            return true;
        }else{
            return false;
        }
    }    

    public static function buscarResultadosCliente($idPessoa, $idUnidade, $idEmpresa = 0) {
        if(!$idEmpresa) $idEmpresa = cb::idempresa();

        $resultados = SQL::ini(AmostraQuery::buscarResultadosCliente(), [
            'idpessoa' => $idPessoa,
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        echo "<!-- {$resultados->sql()} -->";

        if ($resultados->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultados->errorMessage());
            return [];
        }

        return $resultados->data;
    }

    public static function alterarStatus($idAmostra, $idFluxoStatus, $status) {
        $amostraUpdate = SQL::ini(AmostraQuery::alterarStatus(), [
            'idfluxostatus' => $idFluxoStatus,
            'status' => $status,
            'idamostra' => $idAmostra
        ])::exec();

        if ($amostraUpdate->error()) {
            parent::error(__CLASS__, __FUNCTION__, $amostraUpdate->errorMessage());
            return false;
        }

        return true;
    }
    
    public static $arrCores = ["silver", "#cc0000", "#0000cc", "#00cc00", "#990000","#ff6600", "#fcd202", "#b0de09", "#0d8ecf",  "#cd0d74"];
}
