<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/loteformula_query.php");
require_once(__DIR__."/../querys/loteformulains_query.php");
require_once(__DIR__."/../querys/lotecons_query.php");
require_once(__DIR__."/../querys/lotefracaomov_query.php");
require_once(__DIR__."/../querys/lotecusto_query.php");
require_once(__DIR__."/../querys/loteitem_query.php");
require_once(__DIR__."/../querys/loteativ_query.php");
require_once(__DIR__."/../querys/modulohistorico_query.php");

//Controllers
require_once(__DIR__."/nfvolume_controller.php");
require_once(__DIR__."/soltag_controller.php");

class LoteController  extends Controller{

    public static $statusEstoque = [       
             
        'CONTAMINADO' => 'PERDA - CONTAMINADO',
        'FORMINCORRETA' => 'PERDA - FORMULAÇÃO INCORRETA',
        'INVENTARIO' => 'PERDA - INVENTARIO',
        'PERDA - QUEBRA' => 'PERDA - QUEBRA',
        'SEMENTECANCELADA' => 'PERDA - SEMENTE CANCELADA',        
        'PERDA - VACINA RETIDA' => 'PERDA - VACINA RETIDA',
        'REPROVADO' => 'REPROVADO',     
        'VENCIDO' => 'VENCIDO'
    ];

    public static function buscarPorChavePrimaria($id)
    {
        $lote = SQL::ini(LoteQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if($lote->error()){
            parent::error(__CLASS__, __FUNCTION__, $lote->errorMessage());
            return [];
        }

        return $lote->data[0];
    }

    public static function buscarReservaLotePorNfitem($idnfitem){
        $results = SQL::ini(LoteQuery::buscarReservaLotePorNfitem(), [          
            "idnfitem"=>$idnfitem           
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLoteLoteativ($partida,$exercicio){
        $results = SQL::ini(LoteQuery::buscarLoteLoteativ(), [          
            "partida"=>$partida,
            "exercicio"=>$exercicio,          
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data[0];
        }
    }

    public static function buscarLoteAnaliseLote($partida,$exercicio){
        $results = SQL::ini(LoteQuery::buscarLoteAnaliseLote(), [          
            "partida"=>$partida,
            "exercicio"=>$exercicio,          
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return  $results->data[0];
        }
    }

    public static function buscarPartidaLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarPartidaLote(), [          
            "idlote"=>$idlote         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarModuloPorIdlote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarModuloPorIdlote(), [          
            "idlote"=>$idlote         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscaFormalizacaoLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscaFormalizacaoLote(), [          
            "idlote"=>$idlote         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLotelocalizacao($idlote){
        $results = SQL::ini(LoteQuery::buscarLotelocalizacao(), [          
            "idlote"=>$idlote         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarRateio($idprodserv, $idunidade, $consumodiaslote)
    {
        $results = SQL::ini(LoteQuery::buscarRateio(), [          
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade,
            "consumodiaslote" => $consumodiaslote     
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLoteNfItem($idnfitem)
    {
        $results = SQL::ini(LoteQuery::buscarLoteNfItem(), [          
            "idnfitem" => $idnfitem         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarConsumoLoteconsPorIdLoteEIdLoteFracao($idobjeto, $tipoobjeto, $idlotefracao, $idlote, $status  ='')
    {
        $condicionalStatus = '';

        if($status) $condicionalStatus = "AND c.status != '$status'";

        $results = SQL::ini(LoteConsQuery::buscarConsumoLoteconsPorIdLoteEIdLoteFracao(), [          
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "idlotefracao" => $idlotefracao,
            "idlote" => $idlote,
            "condicionalStatus" => $condicionalStatus
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarLotePorIdLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarLotePorIdLote(), [          
            "idlote" => $idlote    
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLoteFracaoPorIdloteEIdUnidade($idlote, $idunidade)
    {
        $results = SQL::ini(LoteQuery::buscarLoteFracaoPorIdloteEIdUnidade(), [          
            "idlote" => $idlote,
            "idunidade" => $idunidade    
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLotePorIdprodservIdunidade($idunidade, $idprodserv)
    {
        $results = SQL::ini(LoteQuery::buscarLotePorIdprodservIdunidade(), [ 
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLoteConsComSolmatItemPorIdUnidade($idprodserv, $idunidade, $idprodservformula, $consumodiaslote)
    {
        if (!empty($idprodservformula)) {
            $in_str = " AND l.idprodservformula = $idprodservformula";
        } else {
            $in_str = "";
        }

        $results = SQL::ini(LoteFracaoQuery::buscarLoteConsComSolmatItemPorIdUnidade(), [ 
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade,
            "in_str" => $in_str,
            "consumodiaslote" => $consumodiaslote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarUnidadeLotePorIdLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarUnidadeLotePorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarUnidadeLotePorIdLoteFracao($idlotefracao)
    {
        $results = SQL::ini(LoteQuery::buscarUnidadeLotePorIdLoteFracao(), [ 
            "idlotefracao" => $idlotefracao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarUnidadeLoteFracaoPorIdProdserv($idprodserv, $condicaoWhere)
    {
        $results = SQL::ini(LoteQuery::buscarUnidadeLoteFracaoPorIdProdserv(), [ 
            "idprodserv" => $idprodserv,
            "condicaoWhere" => $condicaoWhere
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarLoteELoteFracaoPorIdProdservEIdUnidade($idprodserv, $idunidade, $conteudo, $condicaoWhere)
    {
        $results = SQL::ini(LoteQuery::buscarLoteELoteFracaoPorIdProdservEIdUnidade(), [ 
            "idprodserv" => $idprodserv,
            "idunidade" => $idunidade,
            "conteudo" => $conteudo,
            "condicaoWhere" => $condicaoWhere
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula($idunidadeest, $idprodserv, $idprodservformula = FALSE)
    {
        if(!$idprodservformula){
            $idprodservformula = "";
        } else {
            $idprodservformula = "AND l.idprodservformula = $idprodservformula";
        }
        $results = SQL::ini(LoteFracaoQuery::buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula(), [ 
            "idunidadeest" => $idunidadeest,
            "idprodserv" => $idprodserv,
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarConvEstoque($idlotefracao)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarConvEstoque(), [ 
            "idlotefracao" => $idlotefracao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function listarEnderecoPessoaLote($idtipoendereco, $idlote)
    {
        $results = SQL::ini(LoteQuery::buscarEnderecoPessoaLote(), [ 
            "idtipoendereco" => $idtipoendereco,
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach ($results->data as $key => $valor) 
            {
                $arrret[$key] = $valor;
            }
            return $arrret;
        }
    }

    public static function buscarConsumoLoteLoteconsLoteFracao($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoLoteLoteconsLoteFracao(), [ 
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarConsumoProduto($idobjeto, $tipoobjeto, $strped)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoProduto(), [ 
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "strped" => $strped
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarConsumoProdutoRetornarEstoque($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoProdutoRetornarEstoque(), [ 
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarInsumoFormula($idlote)
    {
        $results = SQL::ini(LoteFormulaQuery::buscarInsumoFormula(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarValorMaxAtividadePorIdLoteEStatus($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::buscarValorMaxAtividadePorIdLoteEStatus(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarObjetosLote($idlote)
    {
        $results = SQL::ini(LoteObjQuery::buscarObjetosLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLoteAtivPorIdLote($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::buscarLoteAtivPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarSolfabPorIdLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarSolfabPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function apagarLoteConsRestauracaoPorIdLote($idlote)
    {
        $results = SQL::ini(LoteConsQuery::apagarLoteConsRestauracaoPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function apagarLoteAtivPorIdLote($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::deletarLoteAtivPorLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function deletarLoteObjPorLote($idlote)
    {
        $results = SQL::ini(LoteObjQuery::deletarLoteObjPorLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarEtapaLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarEtapaLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function apagarLoteFormulaInsPorIdLote($idlote)
	{
         $results = SQL::ini(LoteFormulaInsQuery::apagarLoteFormulaInsPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function apagarLoteFormulaPorIdLote($idlote)
	{
         $results = SQL::ini(LoteFormulaQuery::apagarLoteFormulaPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirFormulaInsPorSelect($idempresa, $idlote, $usuario, $idprodservformula)
	{
         $results = SQL::ini(LoteFormulaInsQuery::inserirFormulaInsPorSelect(), [ 
            "idempresa" => $idempresa,
            "idlote" => $idlote,
            "usuario" => $usuario,
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirFormulaInsSementes($idempresa, $idlote, $usuario, $idprodservformula)
	{
         $results = SQL::ini(LoteFormulaInsQuery::inserirFormulaInsSementes(), [ 
            "idempresa" => $idempresa,
            "idlote" => $idlote,
            "usuario" => $usuario,
            "idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirLoteFormulaPorSelect($idempresa, $idlote, $usuario)
	{
         $results = SQL::ini(LoteFormulaQuery::inserirLoteFormulaPorSelect(), [ 
            "idempresa" => $idempresa,
            "idlote" => $idlote,
            "usuario" => $usuario
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirAtividade($arrayAtividade)
	{
        $results = SQL::ini(LoteAtivQuery::inserirAtividade(), $arrayAtividade)::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirLoteObj($arrLoteObj)
	{
        $results = SQL::ini(LoteObjQuery::inserirLoteObj(), $arrLoteObj)::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirLoteObjPorSelect($idempresa, $idlote, $idprativ, $usuario)
	{
        $results = SQL::ini(LoteObjQuery::inserirLoteObjPorSelect(), [ 
            "idempresa" => $idempresa,
            "idlote" => $idlote,
            "idprativ" => $idprativ,
            "usuario" => $usuario
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarLoteRotuloForm($idlote, $rotuloform)
    {
        $results = SQL::ini(LoteQuery::atualizarLoteRotuloForm(), [ 
            "idlote" => $idlote,
            "rotuloform" => $rotuloform
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function atualizarDataExecucaoAtividade($idlote, $execucao)
    {
        $results = SQL::ini(LoteAtivQuery::atualizarDataExecucaoAtividade(), [ 
            "execucao" => $execucao,
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function apagarAtividadeESalasReserva($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::apagarAtividadeESalasReserva(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarIdProdservFormulaPorIdLote($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarIdProdservFormulaPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarStatusPaiProcessoPorIdLote($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::buscarStatusPaiProcessoPorIdLote(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarSalasParaReserva($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::buscarSalasParaReserva(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function apagarSalasReserva($idlote)
    {
        $results = SQL::ini(LoteAtivQuery::apagarSalasReserva(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarTestesSelecionadosFormalizacao($idlote)
    {
        $results = SQL::ini(LoteQuery::buscarTestesSelecionadosFormalizacao(), [ 
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarLotesVinculadosPorTipoObjetoConsumoEspecComUnion($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere, $idunidade)
    {
        $results = SQL::ini(LoteQuery::buscarLotesVinculadosPorTipoObjetoConsumoEspecComUnion(), [ 
            "idprodserv" => $idprodserv,
            "idobjetoconsumoespec" => $idobjetoconsumoespec,
            "tipoobjetoconsumoespec" => $tipoobjetoconsumoespec,
            "andWhere" => $andWhere,
            "idunidade" => $idunidade
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLocalizacaoDeLotesVinculadosComUnion($idsolmatitem, $idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere, $idtipounidade)
    {
        $results = SQL::ini(LoteQuery::buscarLocalizacaoDeLotesVinculados(), [ 
            "idprodserv" => $idprodserv,
            "idobjetoconsumoespec" => $idobjetoconsumoespec,
            "tipoobjetoconsumoespec" => $tipoobjetoconsumoespec,
            "andWhere" => $andWhere,
            "idtipounidade" => $idtipounidade,
            "idsolmatitem" => $idsolmatitem
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLocalizacaoDeLotesVinculados($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere)
    {
        $results = SQL::ini(LoteQuery::buscarLocalizacaoDeLotesVinculado(), [ 
            "idprodserv" => $idprodserv,
            "idobjetoconsumoespec" => $idobjetoconsumoespec,
            "tipoobjetoconsumoespec" => $tipoobjetoconsumoespec,
            "andWhere" => $andWhere
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLotesVinculadosPorTipoObjetoConsumoEspec($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere)
    {
        $results = SQL::ini(LoteQuery::buscarLotesVinculadosPorTipoObjetoConsumoEspec(), [ 
            "idprodserv" => $idprodserv,
            "idobjetoconsumoespec" => $idobjetoconsumoespec,
            "tipoobjetoconsumoespec" => $tipoobjetoconsumoespec,
            "andWhere" => $andWhere
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarConsumoLotePorTipoObjetoConsumoEspec($idlote, $idlotefracao, $idobjetoconsumoespec, $tipoobjetoconsumoespec)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoLotePorTipoObjetoConsumoEspec(), [ 
            "idlote" => $idlote,
            "idlotefracao" => $idlotefracao,
            "idobjetoconsumoespec" => $idobjetoconsumoespec,
            "tipoobjetoconsumoespec" => $tipoobjetoconsumoespec
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLocalizacaoLotePorIdLote($idlote, $tipoobjeto)
    {
        $results = SQL::ini(LoteLocalizacaoQuery::buscarLocalizacaoLotePorIdLote(), [ 
            "idlote" => $idlote,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarFracaoPorLoteEUnidade($idlote, $idunidade)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarFracaoPorLoteEUnidade(), [ 
            "idlote" => $idlote,
            "idunidade" => $idunidade
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['sql'] = $results->sql();
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarFracaoPorIdLoteFracao($idlotefracao)
    {
        $results = SQL::ini(LoteFracaoQuery::buscarFracaoPorIdLoteFracao(), [ 
            "idlotefracao" => $idlotefracao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarConsumoEUnidade($idlote, $whereCondicao)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoEUnidade(), [ 
            "idlote" => $idlote,
            "whereCondicao" => $whereCondicao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function verficarSePodeExcluirConsumo($idtransacao)
    {
        $results = SQL::ini(LoteConsQuery::buscarGrupoLoteConsPorIdTransacaoParaExclusao(), [ 
            "idtransacao" => $idtransacao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            if(!empty($results->data[0]['ids'])){
                return true;
            }else{
                return false;
            }
        }
    }

    public static function buscarGrupoLoteConsPorIdTransacao($idtransacao)
    {
        $results = SQL::ini(LoteConsQuery::buscarGrupoLoteConsPorIdTransacao(), [ 
            "idtransacao" => $idtransacao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarHistoricoDeAlteração($idobjeto,$tipoobjeto,$campo)
    {
        $results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [ 
            "idobjeto" => $idobjeto, 
            "tipoobjeto" => $tipoobjeto, 
            "campo" =>" AND h.campo = '$campo'"
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarConsumoPorLoteFracaoETipoObjeto($idlote, $whereCondicao)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoPorLoteFracaoETipoObjeto(), [ 
            "idlote" => $idlote,
            "whereCondicao" => $whereCondicao
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarReservaLotePorNfEUnidade($tipoobjeto, $idlote)
    {
        $results = SQL::ini(LoteQuery::buscarReservaLotePorNfEUnidade(), [ 
            "tipoobjeto" => $tipoobjeto,
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarSomasLoteFracao($idlote, $idunidade)
    {
        $results = SQL::ini(LoteQuery::buscarReservaLotePorNfEUnidade(), [ 
            "idlote" => $idlote,
            "idunidade" => $idunidade
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idLoteFracao)
    {
        $informacoesUnidade = SQL::ini(LoteFracaoQuery::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao(), [
            'idlotefracao' => $idLoteFracao
        ])::exec();

        if($informacoesUnidade->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $informacoesUnidade->errorMessage());
			return [];
		}

        return $informacoesUnidade->data[0];
    }

    public static function inserirLoteCons($arrayInsertLoteCons)
	{
        $results = SQL::ini(LoteConsQuery::inserirLoteCons(), $arrayInsertLoteCons)::exec();
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }

        return true;
    }

    public static function inserirLoteFracao($arrayInsertLoteFracao)
	{
        $results = SQL::ini(LoteFracaoQuery::inserirLoteFracao(), $arrayInsertLoteFracao)::exec();
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirLoteFracaoStatus($arrayInsertLoteFracao)
	{
        $results = SQL::ini(LoteFracaoQuery::inserirLoteFracaoStatus(), $arrayInsertLoteFracao)::exec();
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        } else {
            return $results->lastInsertId();
        }
    }

    public static function buscarConsumoLoteMes($ano, $mes, $idunidade, $idprodserv)
    {
        $results = SQL::ini(LoteConsQuery::buscarConsumoLoteMes(), [ 
            "ano" => $ano,
            "mes" => $mes,
            "idunidade" => $idunidade,
            "idprodserv" => $idprodserv
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLotePorResultado($idresultado)
    {
        $results = SQL::ini(LoteQuery::buscarLotePorResultado(), [ 
            "idresultado" => $idresultado
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarLotePorIdObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor)
    {
        $results = SQL::ini(LoteQuery::buscarLotePorIdObjetoSoliPor(), [ 
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarStatusLotePorPartidaEExercicio($idunidade, $partida, $exercicio)
    {
        $results = SQL::ini(LoteQuery::buscarStatusLotePorPartidaEExercicio(), [ 
            "idunidade" => $idunidade,
            "partida" => $partida,
            "exercicio" => $exercicio
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    public static function buscarLotesComPessoasEPartidasNaoNulos()
    {
        $results = SQL::ini(LoteQuery::buscarLotesComPessoasEPartidasNaoNulos())::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idlote']] = $_valor['partida'];                
            }
            return $lista;
        }
    }

    public static function atualizarPprodservFormulaLote($idlote, $idprodservformula)
    {
        $results = SQL::ini(LoteQuery::atualizarPprodservFormulaLote(), [ 
            "idprodservformula" => $idprodservformula,
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }
    

    public static function buscarAmostraProdservlote($idlote)
    {
        $lote = SQL::ini(LoteQuery::buscarAmostraProdservlote(), [
            'idlote' => $idlote
        ])::exec();

        if($lote->error()){
            parent::error(__CLASS__, __FUNCTION__, $lote->errorMessage());
            return [];
        }

        return $lote->data[0];
    }

    public static function buscarTestesProdservlote($idlote)
    {
        $lote = SQL::ini(LoteQuery::buscarTestesProdservlote(), [
            'idlote' => $idlote
        ])::exec();

        if($lote->error()){
            parent::error(__CLASS__, __FUNCTION__, $lote->errorMessage());
            return [];
        }

        return $lote->data;
    }

    public static function atualizarStatusLoteCons(int $idLoteCons, string $status)
    {
        $atualizandoStatusLoteCons = SQL::ini(LoteconsQuery::atualizarStatusLoteCons(), [
            'idlotecons' => $idLoteCons,
            'status' => $status,
            'alteradopor' => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();
 
        if($atualizandoStatusLoteCons->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoStatusLoteCons->errorMessage());
            return false;
        }

        return true;
    }

    public static function atualizarIdLoteFracaoOrigemIdLoteFracaoLotefracao(int $idLoteCons, string $status)
    {
        $atualizandoStatusLoteCons = SQL::ini(LoteFracaoQuery::atualizarIdLoteFracaoOrigemIdLoteFracaoLotefracao(), [
            'idlotefracaoorigem' => $idLoteCons,
            'idlotefracao' => $status
        ])::exec();

        if($atualizandoStatusLoteCons->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoStatusLoteCons->errorMessage());
            return false;
        }

        return true;
    }

    public static function burscarLotePorProdserv($idprodserv)
    {
        global $JSON; 
        $results = SQL::ini(LoteQuery::burscarLotePorProdserv(), [ 
            "idprodserv" => $idprodserv
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_lote)
			{	
				$array[$_lote["idlote"]]["lote"] = $_lote["lote"];
			}

			return $JSON->encode($array);
        }
    }

    public static function buscarLotesDisponivesPorIdProdserv($idProdserv, $idUnidade) {
        $statusQry = "'APROVADO'";

        if(array_key_exists("lancarinsumos", getModsUsr("MODULOS"))) $statusQry = "'APROVADO', 'QUARENTENA'";

        $lotes = SQL::ini(LoteQuery::buscarLotesDisponivesPorIdProdserv(), [
            'idunidade' => $idUnidade,
            'idprodserv' => $idProdserv,
            'status' => $statusQry
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return [];
        }

        return $lotes->data;
    }

    public static function buscarTagsVinculadasAoLote($idLote, $idprodserv) {
        $tagSala = SQL::ini(ProdservQuery::buscarTagSalaETagTipoVinculoAgrupado(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($tagSala->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagSala->errorMessage());
            return [];
        }

        $arrTags = [];

        foreach ($tagSala->data as $key => $value) {
            $tipoTag = SQL::ini(TagQuery::buscarTagsPorTagTipoEPai(), [
                "idtagtipo" => $value['idobjetovinc'],
                "idtagpai" => $value['idtag'],
                "orderby" => "order by t.idtagtipo asc, descr asc"
            ])::exec();

            if ($tipoTag->error()) {
                parent::error(__CLASS__, __FUNCTION__, $tipoTag->errorMessage());
                return [];
            }

            $tagsVinculadas = [];

            if ($tipoTag->data) {
                $tagsVinculadas = ProdServController::buscarVinculosTipoTagPorIdProdserv($idprodserv, $value['idobjetovinc'], implode(',', array_map(function ($item) {
                    return $item['idtag'];
                }, $tipoTag->data)));
            }


            $arrTags['tags'][$value['descricao']]['tags'] = $tipoTag->data;
            $arrTags['tags'][$value['descricao']]['tagsVinculadas'] = [];
            $arrTags['tags'][$value['descricao']]['tagsVinculadasManualmente'] = [];

            if($tipoTag->data && $tagsVinculadas) 
                $arrTags['tags'][$value['descricao']]['tagsVinculadas'] = $tagsVinculadas;

        }

        $tagsVinculadasManualmente = CotacaoController::buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc('lote', 'tag', $idLote);
        if($tagsVinculadasManualmente)
                $arrTags['tagsVinculadasManualmente'] = $tagsVinculadasManualmente;

        return $arrTags;
    }

    public static function buscarSeLoteConsomeTransferencia($idlotefracao) {
        $lotes = SQL::ini(LoteQuery::buscarSeLoteConsomeTransferencia(), [
            'idlotefracao' => $idlotefracao
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return [];
        }

        return $lotes->data[0];
    }

    public static function atualizarValorLote($vlrlote, $idlote) {
        $lotes = SQL::ini(LoteQuery::atualizarValorLote(), [
            "vlrlote" => $vlrlote,
            "idlote" => $idlote
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return "";
        }
    }

    public static function buscarLoteIdPorFormalizacao($idFormalizacao ,$idUnidade) {
        $lotes = SQL::ini(FormalizacaoQuery::buscarLoteIdPorFormalizacao(), [
           "idformalizacao" => $idFormalizacao,
           "idunidade" => $idUnidade
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return [];
        }

        return $lotes->data[0];
    }

    public static function buscarUltimoConsumoPorIdlote($idLote) {
        $ultimoConsumo = SQL::ini(LoteconsQuery::buscarUltimoConsumoPorIdlote(), [
           "idlote" => $idLote
        ])::exec();

        if($ultimoConsumo->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $ultimoConsumo->errorMessage());
            return [];
        }

        return $ultimoConsumo->data[0];
    }

    public static function inativarLoteCons($idLoteCons) {
        $inativarLotecons = SQL::ini(LoteconsQuery::inativarLoteCons(), [
           "idlotecons" => $idLoteCons
        ])::exec();

        if($inativarLotecons->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $inativarLotecons->errorMessage());
            return false;
        }

        return true;
    }

    public static function atualizarStatusAtividade($idLoteAtiv, $status, $bloquearStatus) {
        $atualizandoStatusAtividade = SQL::ini(LoteAtivQuery::atualizarStatusAtividade(), [
            'idloteativ' => $idLoteAtiv,
            'status' => $status,
            'bloquearstatus' => $bloquearStatus
        ])::exec();

        if($atualizandoStatusAtividade->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoStatusAtividade->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarLoteAtivAtual($idLote) {
        $loteAtiv = SQL::ini(LoteAtivQuery::buscarLoteAtivAtual(), [
            'idlote' => $idLote
        ])::exec();

        if($loteAtiv->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $loteAtiv->errorMessage());
            return [];
        }

        return $loteAtiv->data[0];
    }

    public static function buscarSalaVinculadaAoLoteAtiv($idLote, $idLoteAtiv) {
        $tagVinculada = SQL::ini(LoteObjQuery::buscarSalaVinculadaAoLoteAtiv(), [
            'idlote' => $idLote,
            'idloteativ' => $idLoteAtiv
        ])::exec();

        if($tagVinculada->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $tagVinculada->errorMessage());
            return [];
        }

        return $tagVinculada->data[0];
    }

    public static function buscarProdservPorIdLote($idLote) {
        $prodserv = SQL::ini(LoteQuery::buscarProdservPorIdLote(), [
            'idlote' => $idLote
        ])::exec();

        if($prodserv->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $prodserv->errorMessage());
            return [];
        }

        return $prodserv->data[0];
    }

    public static function reterLote($idTagDim, $idEmpresa, $qtd, $status, $idLoteFracao) {

        $consumindoLote = SQL::ini(LoteFracaoMovQuery::reterLote(), [
            'idempresa' => $idEmpresa,
            'idtagdim' => $idTagDim,
            'qtd' => $qtd,
            'status' => $status,
            'idlotefracao' => $idLoteFracao,
            'usuario' => $_SESSION['SESSAO']['USUARIO']
        ])::exec();

        
        if($consumindoLote->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $consumindoLote->errorMessage());
            return false;
        }

        return $consumindoLote->lastInsertId();
    }

    public static function vincularPosicaoPrateleira($idLote, $idObjeto, $tipoObjeto, $idEmpresa) {
        $vinculoPrateleira = SQL::ini(LoteLocalizacaoQuery::vincularPosicaoPrateleira(), [
            'idlote' => $idLote,
            'idobjeto' => $idObjeto, 
            'tipoobjeto' => $tipoObjeto,
            'idempresa' => $idEmpresa
        ])::exec();

        if($vinculoPrateleira->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $vinculoPrateleira->errorMessage());
            return false;
        }

        return $vinculoPrateleira->lastInsertId();
    }
    
    public static function consumirFracao($idLote, $idLoteFracao, $qtdDebito, $idObjeto, $tipoObjeto, $idTransacao, $idEmpresa, $obs = '') {
        $consumindoLote = SQL::ini(LoteconsQuery::consumirFracao(), [
            'idlote' => $idLote,
            'idlotefracao' => $idLoteFracao,
            'qtdd' => $qtdDebito,
            'obs' => $obs,
            'idempresa' => $idEmpresa,
            'idtransacao' => $idTransacao,
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto,
            'usuario' => $_SESSION['SESSAO']['USUARIO']
        ])::exec();

        if($consumindoLote->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $consumindoLote->errorMessage());
            return [];
        }

        return $consumindoLote->lastInsertId();
    }

    public static function buscarLoteMovPorIdLoteFracaoEIdTagDim($idLoteFracao, $idTagDim) {
        $loteFracaoMov = SQL::ini(LoteFracaoMovQuery::buscarLoteMovPorIdLoteFracaoEIdTagDim(), [
            'idtagdim' => $idTagDim,
            'idlotefracao' => $idLoteFracao
        ])::exec();

        if($loteFracaoMov->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $loteFracaoMov->errorMessage());
            return [];
        }

        return $loteFracaoMov->data[0];
    }

    public static function buscarLoteFracaoMovPorIdLoteFracaoMov($idLoteFracaoMov) {
        $loteFracaoMov = SQL::ini(LoteFracaoMovQuery::buscarLoteFracaoMovPorIdLoteFracaoMov(), [
            'idlotefracaomov' => $idLoteFracaoMov
        ])::exec();

        if($loteFracaoMov->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $loteFracaoMov->errorMessage());
            return [];
        }

        return $loteFracaoMov->data[0];
    }

    public static function buscarLoteFracaoMovPorIdLoteFracao($idLoteFracao, $idEmpresa, $idLoteFracaoRetem = false) {
        $idLotefracaoERetem = $idLoteFracao;

        if($idLoteFracaoRetem)
            $idLotefracaoERetem .= ", $idLoteFracaoRetem";

        $loteFracaoMov = SQL::ini(LoteFracaoMovQuery::buscarLoteFracaoMovPorIdLoteFracao(), [
            'idlotefracao' => $idLotefracaoERetem,
            'idempresa' => $idEmpresa,
        ])::exec();

        if($loteFracaoMov->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $loteFracaoMov->errorMessage());
            return [];
        }

        return $loteFracaoMov->data;
    }
    
    public static function buscarLoteFracaoMovPorIdTagDim($idTagDim) {
        $loteFracaoMov = SQL::ini(LoteFracaoMovQuery::buscarLoteFracaoMovPorIdTagDim(), [
            'idtagdim' => $idTagDim
        ])::exec();

        if($loteFracaoMov->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $loteFracaoMov->errorMessage());
            return [];
        }

        return $loteFracaoMov->data;
    }

    public static function buscarTagDimEPosicoesPorIDtag($idTag)
    {
        $query = LoteFracaoMovQuery::buscarTagDimEPosicoesPorIDtag();

        $tagDim = SQL::ini($query, [
            'idtag' => $idTag
        ])::exec();

        if ($tagDim->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagDim->errorMessage());
            return [];
        }

        return $tagDim->data;
    }

    public static function buscarInfoPedido($idNfItem) {
        $pedido = SQL::ini(NfItemQuery::buscarInfoPedido(), [
            'idnfitem' => $idNfItem
        ])::exec();

        if ($pedido->error()) {
            parent::error(__CLASS__, __FUNCTION__, $pedido->errorMessage());
            return [];
        }

        return $pedido->data[0] ?? [];
    }

    public static function retirarProduto($idLoteFracaoMov, $qtdRetirada) {
        $retiradaProduto = SQL::ini(LoteFracaoMovQuery::retirarProduto(), [
            'idlotefracaomov' => $idLoteFracaoMov,
            'qtdretirada' => $qtdRetirada
        ])::exec();

        if ($retiradaProduto->error()) {
            parent::error(__CLASS__, __FUNCTION__, $retiradaProduto->errorMessage());
            return false;
        }

        return true;
    }
    
    public static function alocarProdutos($idLoteFracao, $idTagDim, $qtdAlocada) {
        $retiradaProduto = SQL::ini(LoteFracaoMovQuery::alocarProdutos(), [
            'idlotefracao' => $idLoteFracao,
            'idtagdim' => $idTagDim,
            'qtdalocada' => $qtdAlocada
        ])::exec();

        if ($retiradaProduto->error()) {
            parent::error(__CLASS__, __FUNCTION__, $retiradaProduto->errorMessage());
            return false;
        }

        return true;
    }

    public static function atualizarQuantidadeLoteFracaoMov($idLoteFracaoMov, $qtd) {
        $atualizandoQtd = SQL::ini(LoteFracaoMovQuery::atualizarQuantidadeLoteFracaoMov(), [
            'idlotefracaomov' => $idLoteFracaoMov,
            'qtd' => $qtd
        ])::exec();

        if ($atualizandoQtd->error()) {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoQtd->errorMessage());
            return false;
        }

        return true;
    }
    
    public static function BuscarQtdinilote( $idlote ) {
        $results = SQL::ini(LoteQuery::BuscarQtdinilote(), [
            "idlote" => $idlote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data[0];
    }


    public static function BuscarVolumeFormula($idprodservformual ) {
        $results = SQL::ini(LoteQuery::BuscarVolumeFormula(), [
            "idprodservformula" => $idprodservformual
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data[0];
    }

    public static function buscarLotePorIdNfitem($idnfitem)
    {
        $tagDim = SQL::ini(LoteQuery::buscarLotePorIdNfitem(), [
            'idnfitem' => $idnfitem
        ])::exec();

        if ($tagDim->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagDim->errorMessage());
            return [];
        }

        return $tagDim->data;
    }
    
    public static function buscarCustoDiretoIndiretoPorIdLote($idLote) {
        $results = SQL::ini(LoteCustoQuery::buscarCustoDiretoIndiretoPorIdLote(), [
            "idlote" => $idLote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

    public static function buscarInsumosLote($idLote) {
        $results = SQL::ini(LoteItemQuery::buscarInsumosLote(), [
            "idlote" => $idLote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

    public static function buscarCustoTestes($idLote) {
        $results = SQL::ini(LoteAtivQuery::buscarCustoTestes(), [
            "idlote" => $idLote
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

    public static function atualizarLotesVencidos() {
        // Inserir historico
        $lotesVencidos = self::buscarLotesVencidos();
        // Buscar idfluxostatus pelo statustipo VENCIDO
        $idFluxosStatus = FluxoController::getIdFluxoStatus('lotealmoxarifado', 'VENCIDO');

        if(!$idFluxosStatus) return;
        
        foreach($lotesVencidos as $lote) {
            $atualizandoLote = SQL::ini(LoteQuery::atualizarLotesVencidos(), [
                'idlote' => $lote['idlote'],
                'idfluxostatus' => $idFluxosStatus
            ])::exec();

            // Salvar fluxostatushist
            FluxoController::inserirFluxoStatusHist($lote['modulo'], $lote['idlote'], $lote['idfluxostatus'], $lote['status']);

            if($atualizandoLote->error()) parent::error(__CLASS__, __FUNCTION__, $atualizandoLote->errorMessage());

            // self::inserirHistorico('VENCIDO', $lote['substatus'], 'vencimento', $lote['idlote'], $lote['_modulo'], $lote['idempresa']);
        }

        return true;
    }

    public static function buscarLotesVencidos() {
        $lotes = SQL::ini(LoteQuery::buscarLotesVencidos())::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return $lotes->data;
        }

        return $lotes->data;
    }

    public static function revalidarLotesAVencer() {
        $lotes = self::buscarLotesAVencer();

        foreach($lotes as $lote) {
            $atualizandoLotes = SQL::ini(LoteQuery::revalidarLotesAVencer(), [
                'idlote' => $lote['idlote']
            ])::exec();

            if($atualizandoLotes->error()) parent::error(__CLASS__, __FUNCTION__, $atualizandoLotes->errorMessage());

            self::inserirHistorico('REVALIDAR', $lote['substatus'], 'substatus', $lote['idlote'], $lote['modulo'], $lote['idempresa'], '30 dias ou menos para o vencimento. ');
        }
    }

    public static function buscarLotesAVencer() {
        $lotesAVencer = SQL::ini(LoteQuery::buscarLotesAVencer())::exec();

        if($lotesAVencer->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotesAVencer->errorMessage());
            return $lotesAVencer->data;
        }

        return $lotesAVencer->data;
    }

    public static function revalidarLote($idLote) {
        $atualizandoLotes = SQL::ini(LoteQuery::revalidarLote(), [
            'idlote' => $idLote
        ])::exec();

        if($atualizandoLotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoLotes->errorMessage());
            return $atualizandoLotes;
        }

        return $atualizandoLotes;
    }

    public static function inserirHistorico($valor, $valorOld, $campo, $idObjeto, $tipoobjeto, $idempresa, $justificativa = '') {
        $inserindoLote = SQL::ini(ModuloHistoricoQuery::inserirHistorico(), [
            'campo' => $campo,
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoobjeto,
            'valor_old' => $valorOld,
            'valor' => $valor,
            'justificativa' => $justificativa,
            'idempresa' => $idempresa,
            'criadopor' => $_SESSION['SESSAO']['USUARIO']
        ])::exec();

        if($inserindoLote->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $inserindoLote->errorMessage());
            return $inserindoLote;
        }

        return $inserindoLote;
    }

    public static function atualizarStatusLote($idLote, $idFluxosStatusOld, $statusOld) {
        $status = FluxoController::buscarUltimoStatusHist($idLote);

        // Inserindo historico
        $inserindoHistorico = FluxoController::inserirFluxoStatusHist('lotealmoxarifado', $idLote, $idFluxosStatusOld, $statusOld);

        $atualizandoLote = SQL::ini(LoteQuery::atualizarStatusLote(), [
            'idlote' => $idLote,
            'status' => $status['status'],
            'idfluxostatus' => $status['idfluxostatus']
        ])::exec();

        if($atualizandoLote->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoLote->errorMessage());
            return $atualizandoLote;
        }

        return $atualizandoLote;
    }
    
    public static function AlteraStatusLoteAtivPorResultado($idloteativ, $pessoapost, $statusloteativ)
    {
        $results = SQL::ini(LoteAtivQuery::alteraStatusLoteAtivPorResultado(), [
            'idloteativ' => $idloteativ,
            'pessoapost' => $pessoapost,
            'statusloteativ' => $statusloteativ
        ])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		}

		return true;
    }

    public static function BuscaResultadosVinculados($idloteativ, $idresultado)
    {
        $results = SQL::ini(LoteAtivQuery::buscaResultadosVinculados(), [
            'idloteativ' => $idloteativ,
            'idresultado' => $idresultado
            
        ])::exec();
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data[0];
    }

    public static function atualizarLoteFracaoPorIdTransacao($qtdini, $idtransacao, $status) {
        $lotes = SQL::ini(LoteFracaoQuery::atualizarLoteFracaoPorIdTransacao(), [
            'qtdini' => $qtdini,
            'idtransacao' => $idtransacao,
            'status' => $status
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return $lotes->data;
        }
    }

    public static function atualizarLoteConsPorIdTransacaoCredito($qtdc, $idtransacao, $status) {
        $lotes = SQL::ini(LoteConsQuery::atualizarLoteConsPorIdTransacaoCredito(), [
            'qtdc' => $qtdc,
            'idtransacao' => $idtransacao,
            'status' => $status
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return $lotes->data;
        }
    }


    public static function atualizarLoteConsPorIdTransacaoDebito($qtdd, $idtransacao, $status) {
        $lotes = SQL::ini(LoteConsQuery::atualizarLoteConsPorIdTransacaoDebito(), [
            'qtdd' => $qtdd,
            'idtransacao' => $idtransacao,
            'status' => $status
        ])::exec();

        if($lotes->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return $lotes->data;
        }
    }
    public static function BuscaVinculoConsumoPdi($idlote){
        $results = SQL::ini(LoteQuery::buscaVinculoConsumoPdi(), [
            'idlote' => $idlote
        ])::exec();
        
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data;
    }

}
?>