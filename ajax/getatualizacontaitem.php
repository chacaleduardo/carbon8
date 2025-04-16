<?
require_once("../inc/php/functions.php");
require_once("../formcustom/prodserv.php");
require_once("../form/controllers/prodserv_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo "Erro: Não autorizado.";
    die;
}

if(empty($_GET["_idempresa"])){
	echo "Idempresa não informado";
    die;
}

//Chama a Classe prodserv
$prodservclass = new PRODSERV();

if(empty($_GET["idgrupoes"]) && $tipo == 'getContaItem'){
	die("Nenhum Grupo Selecionado.");
}

$tipo = $_GET["tipo"];
$idgrupoes = $_GET["idgrupoes"];
$idcotacao = $_GET["idcotacao"];
$idcontaitem = $_GET["idcontaitem"];
$dadosbusca = $_GET["dadosbusca"];
$idprodservs = $_GET["idprodservs"];

if($tipo == 'getContaItem')
{
	$sqlc = "SELECT e.idtipoprodserv, t.tipoprodserv, ov.idobjetovinc
			   FROM contaitemtipoprodserv e JOIN tipoprodserv t ON (t.idtipoprodserv = e.idtipoprodserv)
		  LEFT JOIN objetovinculo ov ON ov.idobjetovinc = e.idtipoprodserv AND ov.tipoobjetovinc = 'contaitemtipoprodserv' 
		  		AND ov.idobjeto = $idcotacao AND ov.tipoobjeto = 'cotacao'
			  WHERE e.idcontaitem IN ($idgrupoes) AND t.status = 'ATIVO' AND t.compra = 'Y'
		   GROUP BY e.idtipoprodserv
		   ORDER BY CASE WHEN idobjetovinc IS NOT NULL THEN 0 ELSE 1 END, t.tipoprodserv;";
	$res = d::b()->query($sqlc) or die("Erro ao recuperar Tipo Conta Item: ".mysqli_error(d::b()));

	while($rowContaItem = mysqli_fetch_assoc($res)) 
	{
		if(!empty($rowContaItem['idobjetovinc'])){
			$selected = 'selected';
			$valuepicker .= $rowContaItem['idtipoprodserv'].',';
		}else{
			$selected = '';
		}

		$option .= '<option data-tokens="'.retira_acentos($rowContaItem['tipoprodserv']).'" value="'.$rowContaItem['idtipoprodserv'].'" '.$selected.' >'.$rowContaItem['tipoprodserv'].'</option>';
	}

	$array['option'] = $option;
	$array['ids'] = substr($valuepicker, 0, -1);

	echo json_encode($array);
}

if($tipo == 'getProdutoAlerta')
{
	//Produto em Alerta
	$regraProdutoAlerta = " AND ((p.sugestaocompra2 > 0) AND (p.estmin > 0))";
	if($idcontaitem) 
	{
		$sql = getConsulta($regraProdutoAlerta);
		$res = d::b()->query($sql) or die("Erro ao buscar ProdutoAlerta: ".mysql_error()."<pre>SQL:".$sql);
		$qtdr = mysqli_num_rows($res);
	} else {
		$qtdr = 0;
	}
	
	if($qtdr > 0)
	{
		$qtdProd = 0;
		while ($row = mysqli_fetch_assoc($res))
		{
			if($idprodservOld != $row["idprodserv"] && $qtdProd > 0)
			{
				$htmlTable .= '</table>';
				$htmlTable .= '</td>';
				$htmlTable .= '</tr>';		
			}

			if($idprodservOld != $row["idprodserv"])
			{
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
				if(!empty($row["idnf"]) && $row["idcotacao"] != 'APROVADO')
				{
					$htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a title="'.$titulomodulo.'" target="_blank" href="./?_modulo=nfentrada&_acao=u&idnf='.$row["idnf"].'">'.$row["idcotacao"].'-'.$row['rotulo'].'</a></td>'; 
				} elseif(!empty($row["idcotacao"]) && $row["idcotacao"] != 'APROVADO'){
					$htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a title="'.$titulomodulo.'" target="_blank" href="./?_modulo=cotacao&_acao=u&idcotacao='.$row["idcotacao"].'">'.$row["idcotacao"].'-'.$row['rotulo'].'</a></td>'; 
				} else {
					$htmlTable .= '<td align="center" title="Orçamento" style="width: 14%; text-align: center"><a title="'.$titulo.'" target="_blank" class="fa fa-plus-circle fa-1x verde pointer" onclick="addProdutoAlerta(\'false\', \'cotacao_sugestao\', \''.$row['idprodserv'].'\')"></a></td>'; 
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

	echo json_encode($array);
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
		$sqlTodosProdutos = getConsulta($where);
		$resTodosProdutos = d::b()->query($sqlTodosProdutos) or die("Erro ao buscar Todos os Produtos ".mysql_error()."<pre>SQL:".$sqlTodosProdutos);
	}

	$qtdProd = 0;
	while ($row = mysqli_fetch_assoc($resTodosProdutos))
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

	echo json_encode($array);
}

function getConsulta($where = NULL)
{
	global $idcontaitem, $idcotacao;
	$sql = "SELECT p.idprodserv,
				p.codprodserv,
				p.descr,
				p.un,
				p.pedido_automatico,
				p.pedidoautomatico,
				p.idunidadeest,
				p.estmin,
				p.tempocompra,
				p.qtdest,
				p.estmin,
				p.destoque,
				p.mediadiaria,
				p.tempocompra,
				p.sugestaocompra,
				p.sugestaocompra2,
				ps.nome, 
				p.estminautomatico,
				p.tempocompra,
				uv2.descr AS unidadeprod,
				ps.idpessoa, 
				f.unforn, 
				f.valconv,
				f.idprodservforn,
				f.codforn,
				uv.descr AS unidadedescr,
				c.idcotacao,
				c.prazo,
				c.status AS statusorc,
				s.rotulo
			FROM vw8prodestoque p LEFT JOIN prodservforn f ON f.idprodserv = p.idprodserv AND f.status = 'ATIVO' AND f.multiempresa = 'N'
		LEFT JOIN unidadevolume uv ON uv.un = f.unforn
		LEFT JOIN unidadevolume uv2 ON uv2.un = p.un
		LEFT JOIN pessoa ps ON (ps.idpessoa = f.idpessoa)
		LEFT JOIN cotacao c ON c.idcotacao = p.ultimoorcamento 
		LEFT JOIN fluxostatus fs ON fs.idfluxostatus = c.idfluxostatus
		LEFT JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
			WHERE p.status = 'ATIVO' AND p.idtipoprodserv IN ($idcontaitem) AND p.comprado = 'Y' $where
			AND EXISTS (SELECT 1 FROM prodservcontaitem pi JOIN objetovinculo ov ON ov.idobjetovinc = pi.idcontaitem AND ov.idobjeto = $idcotacao AND ov.tipoobjeto = 'cotacao' AND ov.tipoobjetovinc = 'contaitem'
								WHERE pi.idprodserv = p.idprodserv)
				AND p.idempresa = ".$_GET["_idempresa"]."
		ORDER BY p.descr, ps.nome;";

	return $sql;
}
?>

