<?

require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/carimbo_query.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/solfab_query.php");
require_once(__DIR__."/../querys/solfabadj_query.php");
require_once(__DIR__."/../querys/solfabitem_query.php");

// CONTROLLERS
require_once(__DIR__."/empresa_controller.php");
require_once(__DIR__."/inclusaoresultado_controller.php");
require_once(__DIR__."/formalizacao_controller.php");
require_once(__DIR__."/pessoa_controller.php");
require_once(__DIR__."/plantel_controller.php");

class SolfabController  extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarItensSolfabPorIdpessoEWhere($idpessoa, $idprodserv = NULL)
    {
        if(!empty($idprodserv)){
            $WherePedido = " AND s.idsolfab IN (SELECT s2.idsolfab FROM solfab s2 JOIN lote l2 ON s2.idlote = l2.idlote WHERE l2.idprodserv = $idprodserv)";
        } else {
            $WherePedido = "";
        }

        $formalizacoes = SQL::ini(SolfabQuery::buscarItensSolfabPorIdpessoEWhere(), [
            'idpessoa' => $idpessoa,
            'WherePedido' => $WherePedido
        ])::exec();

        if($formalizacoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
             return $formalizacoes->data;
        }
    }

    public static function buscarSolfabELotePool($sqlin)
    {
        $formalizacoes = SQL::ini(SolfabQuery::buscarSolfabELotePool(), [
            'sqlin' => $sqlin
        ])::exec();

        if($formalizacoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
             return $formalizacoes->data;
        }
    }

    public static function buscarDataAprovacaoSolfab($idsolfab)
    {
        $formalizacoes = SQL::ini(SolfabQuery::buscarDataAprovacaoSolfab(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($formalizacoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
             return $formalizacoes->data[0];
        }
    }

    public static function buscarSolfabJoinLotePorIdSolfab($idsolfab)
    {
        $formalizacoes = SQL::ini(SolfabQuery::buscarSolfabJoinLotePorIdSolfab(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($formalizacoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
             return $formalizacoes->data[0];
        }
    }

    public static function buscarStatusSolfabPorIdSolfabEIdEmpresa($idsolfab)
    {
        $formalizacoes = SQL::ini(SolfabQuery::buscarStatusSolfabPorIdSolfabEIdEmpresa(), [
            'idsolfab' => $idsolfab,
        ])::exec();

        if($formalizacoes->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacoes->errorMessage());
            return [];
        } else {
             return $formalizacoes->data[0];
        }
    }

    public static function buscarPessoasLigadasSolfabAdjacente($idsolfab)
    { 
        $results = SQL::ini(SolfabDjQuery::buscarPessoasLigadasSolfabAdjacente(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
           
            $dados['sql'] = $results->sql();
            $dados['data'] = $results->data[0];
            $dados['numLinhas'] = $results->numRows();
            
            return $dados;
        }
    }

    public static function buscarDadosSolfabItem($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarDadosSolfabItem(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['sql'] = $results->sql();
            $dados['data'] = $results->data[0];
            $dados['numLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarAnexosPorTipoObjetoIdObjeto($tipoObjeto, $idObjeto)
    {
        $results = SQL::ini(ArquivoQuery::buscarAnexosPorTipoObjetoIdObjeto(),[
            'tipoobjeto'=> $tipoObjeto,
            'idobjeto'=> $idObjeto
        ])::exec();
        
            if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $dados['sql'] = $results->sql();
            $dados['data'] = $results->data;
            $dados['numLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarFormalizacaoPorIdLote($idlote)
    {
        return FormalizacaoController::buscarFormalizacaoPorIdLote($idlote);
    }

    public static function buscarNomePessoasLigadasSolfabAdjacente($idsolfab)
    {
        $results = SQL::ini(SolfabDjQuery::buscarNomePessoasLigadasSolfabAdjacente(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarLotesDeFormalizacaoPorIdSolfab($idsolfab)
    {
        $results = SQL::ini(LoteQuery::buscarLotesDeFormalizacaoPorIdSolfab(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarItensSolfabRelatorioStatusNotIN($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarItensSolfabRelatorioStatusNotIN(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function inserirSolfabItem($arraySolfabItem)
    {
        $results = SQL::ini(LoteQuery::buscarLotesDeFormalizacaoPorIdSolfab(), $arraySolfabItem)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } 
    }

    public static function atualizarAtualizarLotePorIdSolfab($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::atualizarAtualizarLotePorIdSolfab(), [
            "idsolfab" => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } 
    }

    public static function buscarLoteSolfabItem($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarLoteSolfabItem(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarFormalizacaoPorlote($idsolfab)
    {
        return FormalizacaoController::buscarFormalizacaoPorlote($idsolfab);
    }

    public static function buscarDadosSolfabRelatorio($idsolfab)
    {
        $results = SQL::ini(SolfabQuery::buscarDadosSolfabRelatorio(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data[0];
        }
    }

    public static function buscarItensSolfabRelatorio($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarItensSolfabRelatorio(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarCaminhoImagemTipoHeaderProduto($idempresa, $andWhere = false)
    {
        return EmpresaController::buscarCaminhoImagemTipoHeaderProduto($idempresa, $andWhere);
    }

    public static function buscarAmostras($idamostratra)
    {
        $results = SQL::ini(AmostraQuery::buscarAmostraAssinatura(), [
            'idamostratra' => $idamostratra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            // se nao for amostra tra agrupado e do novo modo hermesp 03-05-19
            if ($results->numRows() < 1) 
            { 
                $results = SQL::ini(AmostraQuery::buscarAmostraAssinaturaComRelatorio(), [
                    'idamostra' => $idamostratra
                ])::exec();
            }

            return $results->data;
        }
    }

    public static function buscarAgenteAmostras($idamostratra)
    {   
        $arrret = [];
        $results = SQL::ini(AmostraQuery::buscarAgentesAmostraPorIdAmostraTra(), [
            'idamostratra' => $idamostratra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            // se nao for amostra tra agrupado e do novo modo hermesp 03-05-19
            if ($results->numRows() < 1) 
            { 
                $results = SQL::ini(AmostraQuery::buscarAgentesAmostraPorIdAmostra(), [
                    'idamostra' => $idamostratra
                ])::exec();
            }

            foreach ($results->data as $value) 
            {
                foreach ($value as $col => $val) 
                {
                    //alterado para agrupar pelo resultado, para facilitar os loops
                    $arrret[$value["idresultado"]][$value["idlote"]][$col] = $val; 
                }
            }
            return $arrret;
        }
    }

    public static function buscarAmostrasVinculadasSolfab($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarAmostrasVinculadasSolfab(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarDatasAmostra($idamostra)
    {
        return AmostraController::buscarDatasAmostra($idamostra);
    }

    public static function buscarAmostraPorEnderecoEFinalidade($idamostra)
    {
        return AmostraController::buscarAmostraPorEnderecoEFinalidade($idamostra);
    }

    public static function buscarResultado($idamostra)
    {
        return AmostraController::buscarResultado($idamostra);
    }

    public static function buscarPessoaAssinaturaStatusAssinado($tipoobjeto, $idamostra)
    {
        $results = SQL::ini(CarimboQuery::buscarPessoaAssinaturaStatusAssinado(), [
            'tipoobjeto' => $tipoobjeto,
            'idamostra' => $idamostra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }

    public static function buscarLotePorResultado($idresultado)
    {
        return LoteController::buscarLotePorResultado($idresultado);
    }
    
    public static function buscarLotePorIdObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor)
    {
        return LoteController::buscarLotePorIdObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor);
    }

    public static function buscarResultadoIndividualPorIdresultado($idresultado)
    {
        return InclusaoResultadoController::buscarResultadoIndividualPorIdresultado($idresultado);
    }

    public static function buscarResultadosDeArquivoUploadEliza($idresultado)
    {
        return InclusaoResultadoController::buscarResultadosDeArquivoUploadEliza($idresultado);
    }

    public static function buscarStatusLotePorPartidaEExercicio($idunidade, $partida, $exercicio)
    {
        return LoteController::buscarStatusLotePorPartidaEExercicio($idunidade, $partida, $exercicio);
    }

    public static function buscarArquivoSolfabItem($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarArquivoSolfabItem(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    }
    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE -----
    public static function buscarSolfabPorIds($stridsf)
	{
        $results = SQL::ini(SolfabQuery::buscarSolfabPorIds(), [          
            "stridsf"=>$stridsf       
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idsolfab']] = $_valor['solfab'];                
            }
            return $lista;
        }
	}

    //funcao que retorna a solicitacoes de fabricacao do cliente que possuem as sementes da formula
    //hermesp 27-03-2019
    public static function listaSolfabCliente($idprodservformula, $inidpessoa, $idprodserv = NULL)
	{
        if(empty($idprodservformula) or empty($inidpessoa)){
            die("Solfab_controller - listaSolfabCliente faltam parametros básicos para consulta.");
        }

        $_listarProdservFormula = ProdservformulaController::buscarProdservDeFormulaEFormulaInsPorStatusEIdProdservFormula($idprodservformula);
        foreach($_listarProdservFormula as $prodservFormula)
        {
            $arrins[$prodservFormula['idprodserv']] = $prodservFormula['idprodserv'];//array com os insumos    
		    $idprodservArray[] = $prodservFormula['idprodserv'];
        }

        $idprodservArray = implode (", ", $idprodservArray);

        $_listarSolfab = self::buscarItensSolfabPorIdpessoEWhere($inidpessoa, $idprodserv);
        $arrsem = array();
        foreach($_listarSolfab as $solfab)
        {
            //array com as sementes da solicitacao de fabricacao do cliente 
            $arrsem[$solfab['idsolfab']][$solfab['idprodserv']] = $solfab['idprodserv']; 
        }

        foreach ($arrsem as $idsolfab => $arrsolfabitem) 
        {
            reset($arrins);
            foreach ($arrins as $semente)
            {
                if(!in_array($arrins[$semente], $arrsolfabitem)) 
                {
                    // se semente nao estiver nos itens da solicitacao de fabricacao
                    //retira a solicitacao de fabricacao do array
                    unset($arrsem[$idsolfab]);
                }
            }      
        }

        $arrRetAspas = array();
        reset($arrsem);
        // roda no array de solicitacao de fabricacao que sobraram no array
        foreach ($arrsem as $idsolfab => $arrsfi) 
        {
            $arrRetAspas[] = "'".$idsolfab."'";
        }

        return implode(",", $arrRetAspas);// retorna as solicitacoes
    }

    /*
    * As regras para recuperar Lotes que não possuem TRA:
    * - Lotes vinculados à amostra que não estão inclusos em nenhuma solicitacao de fabricação
    * - Lotes vinculados à outras amostras do mesmo cliente que não estão inclusos em nenhum TRA
    * - Lotes não esgotados/reprovados existentes em TRAs associados à outras amostras do mesmo cliente
    */
    public static function buscarLotesSolfab($inIdpessoa, $inIdProdServ, $inIdSolfab = null)
    {
        //Recupera a arvore atual de insumos 
        $arrInsumos = self::buscarJarvore($inIdProdServ, true);
        $arrr = [];
    
        if(count($arrInsumos) > 0)
        {
            if(empty($inIdSolfab))
            {
                $condicaoSemSolfabItem = "AND l2.status IN ('AUTORIZADA' , 'APROVADO') AND lf.qtd > 0";
                $condicaoSolfabItem = "";
            } else {
                $condicaoSemSolfabItem = "";
                $condicaoSolfabItem = "JOIN solfabitem sif ON (l2.idlote = sif.idobjeto AND sif.tipoobjeto = 'lote' AND sif.idsolfab = $inIdSolfab)";
            }
          
            //Primeiro nível: produto a ser produzido
            foreach($arrInsumos as $k=>$v)
            {
                if(count($v->insumos) <= 0)
                {
                    $oProd = getObjeto("prodserv", $inIdProdServ);
                    return "A configuração de insumos do Produto <a target='_blank' href='?_modulo=prodserv&_acao=u&idprodserv=".$inIdProdServ."'>".$oProd["descr"]."</a> está incorreta.";
                }

                //Insumos. ***************** Atenção: (OBJECT) ***************
                foreach($v->insumos as $insumoNivel_1)
                {     
                    $results = SQL::ini(ProdservFormulaQuery::buscarInsumos(), [
                        'solfabitem' => $condicaoSolfabItem,
                        'semsolfabitem' => $condicaoSemSolfabItem,
                        'idprodserv' => $insumoNivel_1->idprodserv,
                        'idpessoa' => $inIdpessoa
                    ])::exec();

                    if($results->numRows() > 0)
                    {
                        foreach($results->data AS $info_insumos)
                        {
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idamostra"] = $info_insumos["idamostra"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idunidade"] = $info_insumos["idunidade"];

                            //Agrupar por amostra$infoInsumosNivel2
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["idsolfab"] = $info_insumos["idsolfab"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["statussolfab"] = $info_insumos["statussolfab"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["idlotesolfab"] = $info_insumos["idlotesolfab"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["partida"] = $info_insumos["partida"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["statuslote"] = $info_insumos["statuslote"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["vencimento"] = $info_insumos["vencimento"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["corv"] = $info_insumos["corv"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["idregistro"] = $info_insumos["idregistro"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["exercicio"] = $info_insumos["exercicio"];
                            $arrr["amostras"][$info_insumos["idamostra"]][$info_insumos["idresultado"]][$info_insumos["idlote"]]["status"] = $info_insumos["status"];
                            //Agrupar por TRA
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["partida"] = $info_insumos["partida"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idsolfabitem"] = $info_insumos["idsolfabitem"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idregistro"] = $info_insumos["idregistro"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["exercicio"] = $info_insumos["exercicio"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["status"] = $info_insumos["status"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idamostra"] = $info_insumos["idamostra"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["idunidade"] = $info_insumos["idunidade"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["statuslote"] = $info_insumos["statuslote"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["vencimento"] = $info_insumos["vencimento"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["corv"] = $info_insumos["corv"];
                            $arrr["SF"][$info_insumos["idsolfab"]][$info_insumos["idlote"]]["ultimasolfab"] = $info_insumos["ultimasolfab"];
                        }
                    }//if(mysqli_num_rows($res)>0){);

                    if(!empty($insumoNivel_1->insumos))
                    {
                        foreach($insumoNivel_1->insumos as $insumoNivel_2)
                        {
                            $resultsNivel2 = SQL::ini(ProdservFormulaQuery::buscarInsumos(), [
                                'solfabitem' => $condicaoSolfabItem,
                                'semsolfabitem' => $condicaoSemSolfabItem,
                                'idprodserv' => $insumoNivel_2->idprodserv,
                                'idpessoa' => $inIdpessoa
                            ])::exec();

                            if($resultsNivel2->numRows() > 0)
                            {
                                foreach($resultsNivel2->data AS $infoInsumosNivel2)
                                {        
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idamostra"] = $infoInsumosNivel2["idamostra"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idunidade"] = $infoInsumosNivel2["idunidade"];

                                    //Agrupar por amostra
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["idsolfab"] = $infoInsumosNivel2["idsolfab"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["statussolfab"] = $infoInsumosNivel2["statussolfab"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["idlotesolfab"] = $infoInsumosNivel2["idlotesolfab"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["partida"] = $infoInsumosNivel2["partida"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["statuslote"] = $infoInsumosNivel2["statuslote"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["vencimento"] = $infoInsumosNivel2["vencimento"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["corv"] = $infoInsumosNivel2["corv"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["idregistro"] = $infoInsumosNivel2["idregistro"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["exercicio"] = $infoInsumosNivel2["exercicio"];
                                    $arrr["amostras"][$infoInsumosNivel2["idamostra"]][$infoInsumosNivel2["idresultado"]][$infoInsumosNivel2["idlote"]]["status"] = $infoInsumosNivel2["status"];
                                    //Agrupar por TRA
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["partida"] = $infoInsumosNivel2["partida"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idsolfabitem"] = $infoInsumosNivel2["idsolfabitem"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idregistro"] = $infoInsumosNivel2["idregistro"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["exercicio"] = $infoInsumosNivel2["exercicio"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["status"] = $infoInsumosNivel2["status"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idamostra"] = $infoInsumosNivel2["idamostra"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["idunidade"] = $infoInsumosNivel2["idunidade"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["statuslote"] = $infoInsumosNivel2["statuslote"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["vencimento"] = $infoInsumosNivel2["vencimento"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["corv"] = $infoInsumosNivel2["corv"];
                                    $arrr["SF"][$infoInsumosNivel2["idsolfab"]][$infoInsumosNivel2["idlote"]]["ultimasolfab"] = $infoInsumosNivel2["ultimasolfab"];
                                }
                            }//if(mysqli_num_rows($res)>0){);
                        }
                    }
                }//foreach($v->insumos as $idprodservformulains => $vi){//Insumos (OBJECT)
            }//foreach($arrInsumos as $k=>$v){
        }else{
            $oProd = getObjeto("prodserv", $inIdProdServ);
            return "getLotesSolfab: Nenhum insumo configurado para o produto <a target='_blank' href='?_modulo=prodserv&_acao=u&idprodserv=".$inIdProdServ."'>".$oProd["descr"]."</a>";
        }
        return $arrr;        
    }

    //Recupera a arvore atual de insumos em formato json ou array
    public static function buscarJarvore($inidprodserv, $inarray = false)
    {
        //Recupera a arvore atual de insumos em formato json
        $oProdserv = getObjeto("prodserv", $inidprodserv, "idprodserv");

        if($inarray){
            //Transforma o json em array
            if(empty($oProdserv["jarvore"])){
                echo("<div class='alert alert-warning'>getJArvore: Produto [<a href='?_modulo=prodserv&_acao=u&idprodserv=".$inidprodserv."' target=_blank>".$oProdserv["descr"]."</a>] não possui informação da árvore de insumos</div>");
                return false;
            }else{
                $arrtmp = json_decode($oProdserv["jarvore"]);
                return $arrtmp;
            }
        }else{
            return $oProdserv["jarvore"];
        }
    }

    public static function buscarRepresentantes($idpessoa)
	{
		$listarPlanteis = PlantelController::buscarPlantelObjeto('pessoa', $idpessoa);
		$rplanteis = $listarPlanteis['numLinhas'];

		if ($rplanteis["planteis"] == 0) {
			$and = "";
		} else {
			$and = " AND po.idplantel IN (".$listarPlanteis['data']["planteis"].")";
		}

		$lsitarRepresentante = PessoaController::buscarPessoasPorPlantel($and, 2);
		$arrret = [];
		foreach ($lsitarRepresentante as $representante) {
			//monta 2 estruturas json para finalidades (loops) diferentes
			$arrret[$representante["idpessoa"]]["nome"] = $representante["nome"];
		}
		return $arrret;
	}

    public static function buscarLotesComPessoasEPartidasNaoNulos()
    {
        return LoteController::buscarLotesComPessoasEPartidasNaoNulos();
    }
    //----- AUTOCOMPLETE -----

    // ----- Variáveis de apoio -----
    // ----- Variáveis de apoio -----

    public static function BuscarDescrProdservVacina($idlote)
    {
        
        $results = SQL::ini(CarimboQuery::BuscarDescrProdservVacina(), [
            'idlote' => $idlote
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data;
        }
    
    }

    
    public static function buscarLoteSolfab($idsolfab)
    {
        $results = SQL::ini(SolfabItemQuery::buscarLoteSolfab(), [
            'idsolfab' => $idsolfab
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data[0];
        }
    }

    public static function buscaInfRateioSemente($idlote)
    {
        $results = SQL::ini(SolfabItemQuery::buscaInfRateioSemente(), [
            'idlote' => $idlote
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data[0];
        }
    }

    public static function atualizaValorLote($idlote,$vlrlote,$vlrlotetotal)
    {
        $results = SQL::ini(SolfabItemQuery::atualizaValorLote(), [
            'idlote' => $idlote,
            'vlrlote'=>$vlrlote,
            'vlrlotetotal'=> $vlrlotetotal
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data[0];
        }
    }

    public static function atualizaRateioitemdest($idrateioitemdest,$idrateiocusto)
    {
        $results = SQL::ini(SolfabItemQuery::atualizaRateioitemdest(), [
            'idrateioitemdest' => $idrateioitemdest,
            'idrateiocusto'=>$idrateiocusto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
             return $results->data[0];
        }
    }


}

?>