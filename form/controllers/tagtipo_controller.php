<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/tag_query.php");
require_once(__DIR__."/../querys/plantel_query.php");
require_once(__DIR__."/../querys/tagtipo_query.php");
require_once(__DIR__."/../querys/tagclass_query.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/eventotipo_query.php");
require_once(__DIR__."/../querys/tipotagcampos_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");

class TagTipoController extends Controller{

    // ----- FUNÇÕES -----
    public static function buscarTipoTagCamposPorCampoIdTagTipo ( $campo, $idtagtipo ){
        $results = SQL::ini(TipoTagCamposQuery::buscarTipoTagCamposPorCampoIdTagTipo(), [
            'campo' => $campo,
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return (count($results->data) > 0) ? $results->data[0]["idtipotagcampos"] : "";
        }
    }

    public static function buscarTagsPorTagTipo ( $idTagTipo, $toFillSelect = false, $coluna = 'descr', $orderBy = 'descricao' )
    {
        $orderBy = " ORDER BY t.$orderBy";

        $results = SQL::ini(TagQuery::buscarTagsPorTagTipo(), [
            'idtagtipo' => $idTagTipo,
            'orderby' => $orderBy
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];
            
            foreach($results->data as $result)
            {
                $arrRetorno[$result['idtag']] = $result[$coluna];
            }

            return $arrRetorno;
        }
        
        return $results->data;
    }

    public static function buscarAtividadesVinculadasPorIdTagTipo ( $idtagtipo ){
        $results = SQL::ini(TagTipoQuery::buscarAtividadesVinculadasPorIdTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarEventoTipoPorIdTagTipo ( $idtagtipo ) {
        $results = SQL::ini(EventoTipoQuery::buscarEventoTipoPorIdTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTagClassPorIdTagClass ( $sqlIdempresa, $idtagclass ) {
        $results = SQL::ini(TagClassQuery::buscarTagClassPorIdTagClass(), [
            'sqlidempresa' => $sqlIdempresa,
            'idtagclass' => $idtagclass
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarTagTipoPorIdTagClass($idTagClass, $toFillSelect = false, $idtagtipo = false)
    {
        $results = SQL::ini(TagTipoQuery::buscarTagTipoPorIdTagClass(), [
            'idtagclass' => $idTagClass,
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($results->data as $tipo)
            {
                $arrRetorno[$tipo['idtagtipo']] = $tipo['tagtiposigla'];
            }
    
            return $arrRetorno;
        }

        return $results->data;
    }

    public static function buscarProdservVinculadasAoTagTipo ( $idtagtipo ) {
        $results = SQL::ini(ProdservQuery::buscarProdservVinculadasAoTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTagsTipoLocalizacaoVinculadasAoTagTipo ( $idtagtipo ) {
        $results = SQL::ini(ObjetoVinculoQuery::buscarTagsTipoLocalizacaoVinculadasAoTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTagsTipoVinculadasAoTagTipo ( $idtagtipo ) {
        $results = SQL::ini(ObjetoVinculoQuery::buscarTagsTipoVinculadasAoTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTagClassPorTipoObjetoEIdPrativ($idtagclass, $tipoobjeto, $idprativ) 
    {
        $results = SQL::ini(TagTipoQuery::buscarTagClassPorTipoObjetoEIdPrativ(),[
            "idtagclass" => $idtagclass,
            "tipoobjeto" => $tipoobjeto,
            "idprativ" => $idprativ
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarTagTipoSemVinculo($autocomplete = false)
    {
        $tagTipo = SQL::ini(TagTipoQuery::buscarTagTipoSemVinculo())::exec();

        if($tagTipo->error()){
            parent::error(__CLASS__, __FUNCTION__, $tagTipo->errorMessage());
            return [];
        }

        if($autocomplete)
        {
            $arrRetorno = [];

            foreach($tagTipo->data as $key => $tipo)
            {
                $arrRetorno[$key]['value'] = $tipo['idtagtipo'];
                $arrRetorno[$key]['label'] = $tipo['tagtipo'];
            }

            return $arrRetorno;
        }

        return $tagTipo->data;
    }
    // ----- FUNÇÕES -----

    // ----- AUTOCOMPLETE -----
    public static function listarTagsTipoNaoVinculadasAoTagTipo ($idtagtipo ) {
        $results = SQL::ini(TagTipoQuery::listarTagsTipoNaoVinculadasAoTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function listarTagsTipoLocalizacaoNaoVinculadasAoTagTipo ( $idtagtipo ) {
        $results = SQL::ini(TagTipoQuery::listarTagsTipoLocalizacaoNaoVinculadasAoTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function listarProdservsNaoVinculadasAoTagTipo ( ) {
        $results = SQL::ini(ProdservQuery::listarProdservsNaoVinculadasAoTagTipo())::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function listarFillSelectTagPorIdTagClass($idtagclass) 
    {
        $results = SQL::ini(TagTipoQuery::buscarTagPorIdTagClass(),[
            "idtagclass" => $idtagclass
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($results->data);
        }
    }

    public static function listarFillSelectTagPorIdTagClassEStatus($idtagclass) 
    {
        $results = SQL::ini(TagTipoQuery::buscarTagPorIdTagClassEStatus(),[
            "idtagclass" => $idtagclass
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarTagTipoPorIdTagClassEShare($idtagclass) 
    {
        $tagTipoAtividade = share::otipo('cb::usr')::tagTipoAtividade("t.idtagtipo");
        $results = SQL::ini(TagTipoQuery::buscarTagTipoPorIdTagClassEShare(),[
            "idtagclass" => $idtagclass,
            "tagTipoAtividade" => $tagTipoAtividade
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        }else{
            $i = 0;
            $arrTag = [];
			foreach($results->data as $tag)
            {
                $arrTag[$i]["idtagtipo"] = $tag["idtagtipo"];
                $arrTag[$i]["tagtipo"] = $tag["tagtipo"];
                $i++;
            }

            return $arrTag;
        }
    }
    // ----- AUTOCOMPLETE -----

    // ----- FILLSELECTS -----
    public static $statusFillSelect = [
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo',
    ];

    public static $calendarioFillSelect = [
        'N' => 'Não',
        'Y' => 'Sim',
    ];

    public static function buscarTodasTagClassAtivasPorEmpresaFillSelect ( $sqlIdempresa ) {
        $results = SQL::ini(TagClassQuery::buscarTodasTagClassAtivasPorEmpresa(), [
            'sqlidempresa' => $sqlIdempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarPlantelPorEmpresaFillSeletec ( $sqlIdempresa ) {
        $results = SQL::ini(PlantelQuery::buscarPlantelPorEmpresaeProdserv(), [
            'sqlidempresa' => $sqlIdempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($results->data);
        }
    }
    // ----- FILLSELECTS -----

    // ----- Variáveis de apoio -----

    // PARA ADICIONAR NOVOS CAMPOS, BASTA ACRESCENTAR NO FINAL DA LISTA ABAIXO O NOME DO CAMPO(banco de dados) E QUAL O NOME QUE IRÁ APARECER NA TELA(tela Tag Tipo)
    public static $equipamentos = array("_fabricante" => "Fabricante","_modelo" => "Modelo","_localizacao" => "Localização",
    "_funcionario" => "Funcionário/Pessoa","_nserie" => "Nº Série","_ip" => "IP","_processador" => "Processador",
    "_memoria" => "Memória","_hd" => "HD","_video" => "Vídeo","_so" => "SO","_nfe" => "NFe","_exatidaorequerida" => "Exatidão Requrida",
    "_padraotempmin" => "Parâmetro Mínimo", "_padraotempmax" => "Parâmetro Máximo","_nemei" => "Nº Emei","_plano" => "Plano",
    "_nchip" => "Nº Chip", "_office" => "Office", "_voltagem" => "Voltagem", "_consumo" => "Consumo", "_datacalibracao" => "Vencimento Calibração", 
    "_dataqualificacao" => "Vencimento Qualificação", "_calibracao" => "Calibração", "_qualificacao" => "Qualificação", "_varcarbon" => "Var Carbon", 
    "_macaddress" => "Mac Address", "_temperaturam5" => "Temperatura M5", "_umidadem5" => "Umidade M5", "_pressaom5" => "Pressão M5",
    "_certificado" => "Certificado", "_remoto" => "Remoto", "_linguagem" => "Linguagem", "_cor" => "Cor", '_uploadcertificado' => 'Upload Certificado');

    public static $salas = array(
        "_funcionario" => "Funcionário/Pessoa",
        "_padraotempmin" => "Parâmetro Mínimo", 
        "_padraotempmax" => "Parâmetro Máximo",
        "_localizacao" => "Localização",
        "_indpressao" => "Indicador de Pressão",
        "_cor" => "Cor");

    public static $veiculos = array("_renavam" => "Renavam", 
                                    "_placa" => "Placa",
                                    "_tara"=>"Tara",
                                    "_tpCar" => "Carroceria",
                                    "_tpRod" => "Rodado",
                                    "_uf" => "UF Licenciamento",
                                    "_funcionario" => "Funcionário/Pessoa",
                                    "_satusveiculo" => "Status Veículo",
                                    "_chassi" => "Chassi",
                                    "_modelo" => "Modelo",
                                    "_fabricante" => "Fabricação",
                                    "_crlv" => "Ano CRLV",
                                    "_ano" => "Ano Veículo",
                                    "_cor" => "Cor",
                                    "_categoria" => "Categoria",                                    
                                    "_combustivel" => "Combustível",
                                    "_contrato" => "Contrato",
                                    "_valoraluguel" => "Valor de Aluguel",
                                    "_equipe" => "Equipe",
                                    "_gps" => "GPS",
                                    "_seguradora" => "Seguro",
                                    "_pedagio" => "Pedágio",
                                    "_kmtrocaoleo" => "Margem Troca Óleo (KM)");

    public static $prateleiras = array("_funcionario" => "Funcionário/Pessoa","_padraotempmin" => "Parâmetro Mínimo", "_padraotempmax" => "Parâmetro Máximo");

    public static $mobiliarios = array("_funcionario" => "Funcionário/Pessoa","_padraotempmin" => "Parâmetro Mínimo", "_padraotempmax" => "Parâmetro Máximo", "_localizacao" => "Localização",
    "_nfe" => "NFe","_fabricante" => "Fabricante");

    public static $quartosTermicos = array("_fabricante" => "Fabricante","_modelo" => "Modelo","_localizacao" => "Localização",
    "_funcionario" => "Funcionário/Pessoa","_nserie" => "Nº Série","_ip" => "IP","_processador" => "Processador",
    "_memoria" => "Memória","_hd" => "HD","_video" => "Vídeo","_so" => "SO","_nfe" => "NFe","_exatidaorequerida" => "Exatidão Requrida",
    "_padraotempmin" => "Parâmetro Mínimo", "_padraotempmax" => "Parâmetro Máximo","_nemei" => "Nº Emei","_plano" => "Plano",
    "_nchip" => "Nº Chip", "_office" => "Office", "_voltagem" => "Voltagem", "_consumo" => "Consumo", "_datacalibracao" => "Vencimento Calibração", 
    "_dataqualificacao" => "Vencimento Qualificação", "_calibracao" => "Calibração", "_qualificacao" => "Qualificação", "_varcarbon" => "Var Carbon", 
    "_macaddress" => "Mac Address", "_temperaturam5" => "Temperatura M5", "_umidadem5" => "Umidade M5", "_pressaom5" => "Pressão M5",
    "_certificado" => "Certificado", "_remoto" => "Remoto");

    // ----- Variáveis de apoio -----
}
?>