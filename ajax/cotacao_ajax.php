<?
require_once(__DIR__ . "/../inc/php/functions.php");
require_once(__DIR__ . "/../form/controllers/nf_controller.php");
require_once(__DIR__ . "/../form/controllers/tipoprodserv_controller.php");
require_once(__DIR__ . "/../form/controllers/cotacao_controller.php");

$idnf = $_POST['idnf'];
$idnfitem = $_POST['idnfitem'];
$idcontaitem = $_POST['idcontaitem'];
$idcotacao = $_POST["idcotacao"];
$idgrupoes = $_POST["idgrupoes"];
$tipo = $_POST["tipo"];
$dadosbusca = $_POST["dadosbusca"];
$idprodservs = $_POST["idprodservs"];
$idempresa = $_POST['idempresa'];

if(!empty($_POST["comandoExecutar"])){
    $_comandoExecutar = $_POST["comandoExecutar"];

    switch($_comandoExecutar){
        case 'atualizarValoresNf':
            $retAjax = CotacaoAjax::atualizarValoresNf($idnf, $idnfitem);
        break;
        case 'atualizarContaItemProdservSemCadastro':
            $retAjax = CotacaoAjax::atualizarContaItemProdservSemCadastro($idcontaitem);
        break;
        case 'buscarContaItem':
            $retAjax = CotacaoAjax::buscarContaItem($idcotacao, $idgrupoes);
        break;
        case 'listarSugestaoTodos':
            $retAjax = CotacaoAjax::listarSugestaoTodos($idcontaitem, $idcotacao, $tipo, $dadosbusca, $idprodservs, $idempresa);
        break;
    }
    echo json_encode($retAjax);
}else{
    cbSetPostHeader("0", "Não foi enviado nenhum Comando a ser executado");
}

Class CotacaoAjax
{
	public static function atualizarValoresNf($idnf, $idnfitem)
	{
        if($idnf){
            $totalItens = NfController::buscarValoresNfitem($idnf); 
        } else {
            $totalItens = NfController::buscarValoresNfitemJoinNfitem($idnfitem); 
        }

		return $totalItens;
	}

    public static function atualizarContaItemProdservSemCadastro($idcontaitem)
	{   
        $option = "";
        $listarContaItemTipoProdservTipoProdServ = TipoProdServController::listarContaItemTipoProdservTipoProdServ($idcontaitem);
        foreach($listarContaItemTipoProdservTipoProdServ as $_dadosContaItemTipoProdservTipoProdServ){
            $option .= '<option value="'.$_dadosContaItemTipoProdservTipoProdServ['idtipoprodserv'].'>'.$_dadosContaItemTipoProdservTipoProdServ['tipoprodserv'].'</option>';
        }

	    return $option;
    }

    public static function buscarContaItem($idcotacao, $idgrupoes)
    {
        $option = "";
        $valuepicker = "";
        $stringpicker = "";
        $listarContaItem = TipoProdServController::buscarContaItem($idcotacao, $idgrupoes);
        $stringpicker = "<ul>";
        foreach ($listarContaItem as $_dadosContaItem) {
            if (!empty($_dadosContaItem['idobjetovinc'])) {
                $selected = 'selected';
                $valuepicker .= $_dadosContaItem['idtipoprodserv'] . ',';
                $stringpicker .= '<li>' . $_dadosContaItem['tipoprodserv'] . '</li>';
            } else {
                $selected = '';
            }

            $option .= '<option data-tokens="' . retira_acentos($_dadosContaItem['tipoprodserv']) . '" value="' . $_dadosContaItem['idtipoprodserv'] . '" ' . $selected . ' >' . $_dadosContaItem['tipoprodserv'] . '</option>';
        }
        $stringpicker .= "</ul>";

        $array['option'] = $option;
        $array['ids'] = substr($valuepicker, 0, -1);
        $array['strings'] = $stringpicker;

        return $array;
    }

    public static function listarSugestaoTodos($idcontaitem, $idcotacao, $tipo, $dadosbusca, $idprodservs, $idempresa)
	{
        $idprodservOld = "";
        $htmlTable = "";
        $htmlBody = "";
        if($tipo == 'getProdutoAlerta'){
            //Produto em Alerta
            $regraProdutoAlerta = " AND ((p.sugestaocompra2 > 0) AND (p.estmin > 0))";
            if($idcontaitem) {
                $listarSugestao = CotacaoController::listarSugestaoTodos($idcontaitem, $regraProdutoAlerta, $idcotacao, $idempresa);
                $qtdr = count($listarSugestao);
            } else {
                $qtdr = 0;
            }
            
            if($qtdr > 0){
                $qtdProd = 0;
                foreach($listarSugestao as $row){
                    if($idprodservOld != $row["idprodserv"] && $qtdProd > 0)
                    {
                        $htmlTable .= '</table>';
                        $htmlTable .= '</td>';
                        $htmlTable .= '</tr>';		
                    }

                    if($idprodservOld != $row["idprodserv"]){
                        $qtdProd++;
                        if(empty($row['idcotacao']) && $row['estmin'] > 0 && ($row["qtdest"] < $row['estmin'])){
                            $cortr = 'mistyrose';//vermelho
                        } else {
                            $cortr = '#ddd';//branco
                        }

                        $htmlTable .= '<tr style="background-color: '.$cortr.'; font-weight: bold;" data-text="'.$row['descr'].'">';				
                        $htmlTable .= '<td><input title="Produto Alerta" type="checkbox" class="itemalerta itemTodosProduto'.$row["idprodserv"].'" id="itemalerta" idprodservalerta="'.$row["idprodserv"].'"></td>'; //Input Selecionar				
                        $htmlTable .= '<td><input type="text" class="size7" name="itemalertaqtd'.$row["idprodserv"].'" id="itemalertaqtd'.$row["idprodserv"].'" value=""></td>'; //Digitar a Quantidade
                        $htmlTable .= '<td style="width: 5%;">'.$row['un'].'</td>'; // Unidade ProdServ	
                        $htmlTable .= '<td title="Código" style="width: 8%;">'.$row['codprodserv'].'
                                        <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="prodserv"></a>
                                        </td>'; // Código ProdServ - Sigla

                        //Descrição ou Link do Produto
                        $htmlTable .= '<td title="Descrição" style="width: 38%;">'.$row['descr'].'
                                            <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="calculosestoque"></a>';
                        if(empty($row['idprodservforn'])){
                            $htmlTable .= '<a title="Não possui Fornecedor" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer"></a>';
                        }
                        $htmlTable .= '</td>';
                        
                        //Estoque
                        $htmlTable .= '<td align="right" title="Estoque" style="width: 4%;">'.number_format(tratanumero($row["qtdest"]), 2, ',', '.').'</td>';//Estoque

                        //Sugestão de Compra 2
                        $htmlTable .= '<td align="right" title="Sugestão de Compra 2" style="width: 8%;">'.number_format(tratanumero($row['sugestaocompra2']), 2, ',', '.').'</td>';

                        //Estoque Mínimo
                        if(empty($row["estmin_exp"])){
                            $htmlTable .= '<td align="right" title="Estoque Mínimo" style="width: 6%;">'.number_format(tratanumero($row["estmin"]), 2, ',', '.').'</td>';
                        } else {
                            $htmlTable .= '<td align="right" title="Estoque Mínimo" style="width: 6%;">'.recuperaExpoente(tratanumero($row["estmin"]),$row["estmin_exp"]).'</td>';
                        }

                        //Estotque Mínimo Automático
                        $htmlTable .= '<td align="right" title="Estotque Mínimo Automático" style="width: 8%;">'.number_format(tratanumero($row["estminautomatico"]), 2, ',', '.').'</td>'; 

                        //Dias Estoque
                        $htmlTable .= '<td align="right" title="Dias Estoque" style="width: 8%;">'.number_format(tratanumero($row['destoque']), 2, ',', '.').'</td>'; 

                        //Orçamento
                        if(!empty($row["idnf"]))
                        {
                            $htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a target="_blank" href="./?_modulo=nfentrada&_acao=u&idnf='.$row["idnf"].'">'.$row["idcotacao"].'-'.$row['rotulo'].'</a></td>'; 
                        } elseif(!empty($row["idcotacao"])){
                            $htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a target="_blank" href="./?_modulo=cotacao&_acao=u&idcotacao='.$row["idcotacao"].'">'.$row["idcotacao"].'-'.$row['rotulo'].'</a></td>'; 
                        } else {
                            $htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a target="_blank" class="fa fa-plus-circle fa-1x verde pointer" onclick="addProdutoAlerta(\'false\', \'cotacao_sugestao\', \''.$row['idprodserv'].'\')"></a></td>'; 
                        }
                        
                        $htmlTable .= '<td><i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_fornecedores" title="Produto"  data-toggle="collapse" idprodserv="'.$row['idprodserv'].'" href="#prodservprodalerta'.$row['idprodserv'].'"></i></td>';
                        $htmlTable .= '</tr>';
                        $htmlTable .= '<tr class="prodservprodalerta'.$row['idprodserv'].' collapse" style="height:40px;" id="prodservprodalerta'.$row['idprodserv'].'" data-text="'.$row['descr'].'">';
                        $htmlTable .= '<td colspan="15">';
                        $htmlTable .= '<table class="table table-striped planilha" style="width: 100%;">';
                        $htmlTable .= '<tr data-text="'.$row['descr'].'">';
                        $htmlTable .= '<input name="itemalerta_forn_'.$row["idprodserv"].'" id="itemalerta_forn_'.$row["idprodserv"].'" type="hidden" value="">';
                        $htmlTable .= '<td style="width: 2%;"></td>';
                        $htmlTable .= '<td style="width: 3%;"></td>';
                        $htmlTable .= '<td style="width: 36%;">';
                        $htmlTable .= 'Nome  ';
                        $htmlTable .= '<a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="prodservfornecedor"></a>';
                        $htmlTable .= '</td>';
                        $htmlTable .= '<td style="width: 30%;">Descrição</td>';
                        $htmlTable .= '<td style="width: 10%; text-align: center;">Unidade Compra</td>';
                        $htmlTable .= '<td style="width: 7%; text-align: right;">Conversão</td>';
                        $htmlTable .= '<td style="width: 10%; text-align: center;">Unidade Padrão</td>';

                        $htmlTable .= '</tr>';
                    }

                    if(!empty($row['nome']))
                    {
                        $htmlTable .= '<tr data-text="'.$row['descr'].'">';
                        $htmlTable .= '<td></td>';
                        $htmlTable .= '<td><input type="checkbox" name="fornecedor" idprodservforn="'.$row["idprodservforn"].'" class="checkTodosProduto'.$row["idprodserv"].'" onclick="selecionaFornecedor('.$row["idprodserv"].', \'cotacao_sugestao\');"></td>';		
                        $htmlTable .= '<td>'.strtoupper($row['nome']).'</td>';
                        $htmlTable .= '<td>'.strtoupper($row['codforn']).'</td>';
                        $htmlTable .= '<td align="center">'.strtoupper($row['unidadedescr']).'</td>';
                        $htmlTable .= '<td align="right">'.$row['valconv'].'</td>';
                        $htmlTable .= '<td align="center">'.strtoupper($row['unidadeprod']).'</td>';
                        $htmlTable .= '</tr>';
                    } else {
                        $htmlTable .= '<tr data-text="'.$row['descr'].'">';
                        $htmlTable .= '<td></td>';
                        $htmlTable .= '<td></td>';		
                        $htmlTable .= '<td>-</td>';
                        $htmlTable .= '<td>-</td>';
                        $htmlTable .= '<td align="center">-</td>';
                        $htmlTable .= '<td align="right">-</td>';
                        $htmlTable .= '<td align="center">-</td>';
                        $htmlTable .= '</tr>';
                    }

                    $idprodservOld = $row["idprodserv"];
                }
            }

            $array['html'] = $htmlTable;
            $array['qtd'] = $qtdProd;
        }

        if($tipo == 'getTodosProdutos')
        {
            $where = "";
            if(!empty($dadosbusca))
            {
                $where .= " AND p.descr LIKE '%$dadosbusca%'";
            }

            if(!empty($idprodservs))
            {
                $where .= " AND p.idprodserv NOT IN ($idprodservs)";
            }

            if($idcontaitem) 
            {
                $listarSugestao = CotacaoController::listarSugestaoTodos($idcontaitem, $where, $idcotacao, $idempresa);
            }

            $qtdProd = 0;
            foreach($listarSugestao as $row)
            {	
                if($idprodservOld != $row["idprodserv"] && $qtdProd > 0)
                {
                    $htmlBody .= '</table>';
                    $htmlBody .= '</td>';
                    $htmlBody .= '</tr>';		
                }

                if($idprodservOld != $row["idprodserv"])
                {
                    $qtdProd++;	
                    if( $row['estmin'] > 0 &&  ($row["qtdest"] < $row['estmin'])){
                        $cortr = 'mistyrose';//vermelho
                    }else{
                        $cortr = '#ddd';
                    }

                    if($row['estmin'] <= 0)
                    {
                        $classMin = ' esconderMostrarEstoqueMinimo';
                    } else {
                        $classMin = "";
                    }

                    $htmlBody .= '<tr class="'.$classMin.'" style="background-color: '.$cortr.';" data-text="'.$row['descr'].'" id="prodservPrincipal'.$row["idprodserv"].'">';	
                    $htmlBody .= '<td text-align: center;"><input title="Todos Produtos" type="checkbox" class="itemalerta itemTodosProduto'.$row["idprodserv"].'" id="itemalerta" idprodservalerta="'.$row["idprodserv"].'"></td>'; //Input Selecionar		
                    $htmlBody .= '<td style="width: 3%;"><input type="text" class="size7" name="itemalertaqtd'.$row["idprodserv"].'" id="itemalertaqtd'.$row["idprodserv"].'" value=""></td>'; //Quantidade
                    $htmlBody .= '<td title="Unidade" style="width: 5%;">'.$row['un'].'</td>'; // Unidade ProdServ	
                    $htmlBody .= '<td title="Código" style="width: 8%;">'.$row['codprodserv'].' 
                                    <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="prodserv"></a>
                                    </td>'; // Código ProdServ

                    //Descrição do Produto
                    $htmlBody .= '<td title="Descrição" style="width: 38%;">'.$row['descr'].'
                                    <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="calculosestoque"></a>';
                    
                    if(empty($row["idprodservforn"])){
                        $htmlBody .= '<a title="Não possui Fornecedor" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer"></a>';
                    }
                                    
                    $htmlBody .= '</td>';

                    //Estoque
                    $htmlBody .= '<td align="right" title="Estoque" style="width: 4%;">'.number_format(tratanumero($row["qtdest"]), 2, ',', '.').'</td>'; 

                    //Sugestão de Compra	
                    $htmlBody .= '<td align="right" title="Sugestão de Compra 2" style="width: 8%;">'.number_format(tratanumero($row['sugestaocompra2']), 2, ',', '.').'</td>';

                    //Estoque Mínimo
                    $htmlBody .= '<td align="right" title="Estoque Mínimo" style="width: 6%;">'.$row['estmin'].'</td>';

                    //Estotque Mínimo Automático
                    $htmlBody .= '<td align="right" title="Estotque Mínimo Automático" style="width: 8%;">'.number_format(tratanumero($row["estminautomatico"]), 2, ',', '.').'</td>'; 

                    //Dias Estoque
                    $htmlBody .= '<td align="right" title="Dias Estoque" style="width: 8%;">'.number_format(tratanumero($row['destoque']), 2, ',', '.').'</td>'; 

                    //Orçamento
                    $htmlBody .= '<td title="Orçamento" style="width: 14%; text-align: center"><a target="_blank" href="?_modulo=cotacao&_acao=u&idcotacao='.$row['idcotacao'].'">'.$row['idcotacao'].' - '.$row['rotulo'].'</a></td>'; 

                    $htmlBody .= '<td><i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_fornecedores" title="Produto" style="padding: 0 10px 0 10px;"  data-toggle="collapse" idprodserv="'.$row['idprodserv'].'" href="#prodservtodos'.$row['idprodserv'].'"></i></td>';
                    $htmlBody .= '</tr>';
                    $htmlBody .= '<tr class="prodservtodos'.$row['idprodserv'].' collapse" style="height:40px;" id="prodservtodos'.$row['idprodserv'].'" data-text="'.$row['descr'].'">';
                    $htmlBody .= '<td colspan="15">';
                    $htmlBody .= '<table class="table table-striped planilha" style="width: 100%;">';
                    $htmlBody .= '<tr data-text="'.$row['descr'].'">';
                    $htmlBody .= '<input name="itemalerta_forn_'.$row["idprodserv"].'" id="itemalerta_forn_'.$row["idprodserv"].'" type="hidden" value="">';
                    $htmlBody .= '<td style="width: 2%;"></td>';
                    $htmlBody .= '<td style="width: 3%;"></td>';
                    $htmlBody .= '<td style="width: 36%;">';
                    $htmlBody .= 'Nome  ';
                    $htmlBody .= '<a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="'.$row['idprodserv'].'" modulo="prodservfornecedor"></a>';
                    $htmlBody .= '</td>';
                    $htmlBody .= '<td style="width: 30%;">Descrição</td>';
                    $htmlBody .= '<td style="width: 10%; text-align: center;">Unidade Compra</td>';
                    $htmlBody .= '<td style="width: 7%; text-align: right;">Conversão</td>';
                    $htmlBody .= '<td style="width: 10%; text-align: center;">Unidade Padrão</td>';
                    $htmlBody .= '</tr>';
                }
                
                $htmlBody .= '<tr data-text="'.$row['descr'].'">';
                $htmlBody .= '<td></td>';
                $htmlBody .= '<td><input type="checkbox" name="fornecedor" idprodservforn="'.$row["idprodservforn"].'" class="checkTodosProduto'.$row["idprodserv"].'" onclick="selecionaFornecedor('.$row["idprodserv"].', \'cotacao_todos\');"></td>';		
                $htmlBody .= '<td>'.strtoupper($row['nome']).'</td>';
                $htmlBody .= '<td>'.strtoupper($row['codforn']).'</td>';
                $htmlBody .= '<td align="center">'.strtoupper($row['unidadedescr']).'</td>';
                $htmlBody .= '<td align="right">'.$row['valconv'].'</td>';
                $htmlBody .= '<td align="center">'.strtoupper($row['unidadeprod']).'</td>';
                $htmlBody .= '</tr>';

                $idprodservOld = $row["idprodserv"];
            }

            $array['html'] = $htmlBody;
            $array['qtd'] = $qtdProd;
        }

        return $array;
    }
}

?>
