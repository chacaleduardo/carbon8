<?
require_once("../inc/php/validaacesso.php");

$_token = $_GET["_token"]==''?$_GET["token"]:$_GET["_token"];

//verifica se foi enviado o _token de autenticação
if(!empty($_token)){	
    //desencripta o _token
    $str_token=des($_token);
    //verifica se deu certo a desencriptação
    if($str_token==false){	  
	    die("CB-ERROR: Falha #2 ao autenticar _token");
    }else{
	/*
	* Passa a string da variavel $str_token para o array $arr_token
	*/
       parse_str($str_token,$arr_token);
       //print_r($arr_token);
       /*
	* while list
	* o array contem chave mais valor 
	* O comando abaixo irá preencher os GETS com a chave = valor
	*/
       while(list($chave,$valor)=each($arr_token)){
	       //echo $chave;
	       $_GET[$chave]=$valor;
	       //echo $_GET[$chave];
       }
    }
}


$gravaarquivo=$_GET['gravaarquivo'];
ob_start();

$sqlf = "SELECT p.idpessoa,f.idempresa as idempresanf,p.nome,p.dddfixo,p.telfixo,p.email,p.cpfcnpj,p.inscrest,c.*,i.idformapagamento,i.formapagamento as pagamento,f.parcelas,f.aoscuidados as vendedor,f.idnf as orcamento,f.idnf as idsolicitacao,f.idtransportadora,f.intervalo,f.obs,f.diasentrada,f.frete,f.modfrete
FROM nf f,pessoa p,cotacao c, formapagamento i
where p.idpessoa=f.idpessoa 
and c.idcotacao = f.idobjetosolipor
and i.idformapagamento = f.idformapagamento
and f.tipoobjetosolipor='cotacao'
and f.idnf =".$_GET["idnf"];

$resf = d::b()->query($sqlf) or die("Erro ao buscar fornecedor pacote: ".$sqlf);

$rowf=mysqli_fetch_assoc($resf);

$sql = "select ifnull(p.descr,i.prodservdescr) as descr,pf.codforn,pf.unforn
,CASE i.un
WHEN NULL THEN p.un
WHEN '' THEN p.un
ELSE i.un
END AS unidade,i.*
from nfitem i join nf f on (i.idnf = f.idnf)
	left join prodserv p on (p.idprodserv = i.idprodserv)
    left join prodservforn pf 
		on (pf.idprodserv = p.idprodserv and pf.status='ATIVO' and pf.idpessoa = f.idpessoa and pf.idprodservforn = i.idprodservforn)
where  i.nfe = 'Y'
and f.idnf = ".$_GET["idnf"]." order by p.descr";


	
$res = d::b()->query($sql) or die("Erro ao buscar itens do pacote:".$sql);


?>
<html>
<head>
<title>Impressão</title>

<style>

hr{
    border: 0;
    border-bottom: 1px dashed #ccc;
    background: #999;
}
.rotulo{
font-weight: bold;
font-size: 10px;
}
.rotulotable{
font-weight: bold;
font-size: 9px;
}
.texto{
font-size: 10px;
}
.textoitem{
font-size: 9px;
}
.textoanexo{
font-size: 8px;
}



</style>
</head>
<body style="max-width:1100px;">
<?
	$_timbradoheader = 'HEADERSERVICO';
	require_once("timbrado.php");
?>
    <!-- <div class="bordadiv"> -->
<div id="conteudo">
		<div style="float:right;vertical-align:top;">
			<table>
				<tr>
					<td colspan="2">	
						<div style="font-size: 11px;"><font style="font-weight: bold;"> Emissão: </font> <?=date("d/m/Y");?> </div>
					</td>
				</tr>
				<tr>
					<td>
						<div align="left" style="font-size: 11px;"><font style="font-weight: bold;"> N&#186; Pedido: </font> <?=$rowf["idcotacao"]?> </div>			
					</td>
					<td>
						<div style="font-size: 11px;"><font style="font-weight: bold;"> N&#186; Cotação: </font> <?=$rowf["idsolicitacao"]?> </div>
					</td>
				</tr>
			</table>
		</div><br>
	<?
	   $sqlfig="select * from empresa where  idempresa=".$rowf['idempresanf'];
		$resfig = mysql_query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
		$figrel=mysql_fetch_assoc($resfig);
	?>
			<div align='left' style='font-size: 12px; font-weight: bold; '>SOLICITANTE (Dados para faturamento, cobrança e entrega)</div>
			<hr>
			<table >
			<tr>
			    <td align="" class="rotulo">Razão Social:</td>
			    <td class="texto"><?=$figrel["razaosocial"]?></td>
			    <td align="right" class="rotulo">Telefone:</td>
			    <td class="texto">(<?=$figrel["DDDPrestador"]?>) <?=$figrel["TelefonePrestador"]?></td>
			</tr>	
			<tr>
			    <td align="" class="rotulo">CPF/CNPJ:</td>
				<td class="texto" nowrap><?=formatarCPF_CNPJ($figrel["cnpj"],true)?></td>
				<td align="right" class="rotulo"> I.E:</td> <td class="texto" nowrap><?=$figrel["inscestadual"]?></td>
			</tr>	
			<tr>
			    <td align="" class="rotulo">Endereço:</td>	
			    <td class="texto"><?=$figrel["xlgr"]?> - <?=$figrel["nro"]?></td>
			    <td align="right" class="rotulo">Bairro:</td>
			    <td class="texto"><?=$figrel["xbairro"]?></td>
			</tr>
			<tr>
			    <td align="" class="rotulo">Cidade:</td>
			    <td class="texto"><?=$figrel["xmun"]?>-<?=$figrel["uf"]?></td>
			    <td align="right" class="rotulo">CEP:</td>
			    <td class="texto"> <?=formatarCEP($figrel["cep"],true)?></td>
			</tr>
			<?if(!empty($figrel["refentrega"])){?>
			<tr>
			    <td align="" class="rotulo">Referência p/ entrega:</td>
			    <td  class="texto"> <?=$figrel["refentrega"]?></td>
			</tr>
			<?}?>
			<?if(!empty($figrel["emailnfe"])){?>
			    <tr>
					<td align="" class="rotulo">Email:</td>
					<td  class="texto"><?=$figrel["emailnfe"]?></td>
				</tr>
			<?}?>
		</table>
		<?if(!empty($figrel["horariorecebimento"])){?>
			<table>
			     <tr>	 
				<td class="rotulo">**HORÁRIO DE RECEBIMENTO DE MERCADORIAS**</td> 
			    </tr>
			    <tr>
				<td class="texto"><?=$figrel["horariorecebimento"]?></td>
			    </tr>
			</table>
		<?}?>
			<p>
		<div align='left' style='font-size: 12px; font-weight: bold; '>FORNECEDOR</div>
		<hr>
		<table>
		<tr>	
			<td align="right" class="rotulo">Razão Social:</td> 
			<td class="texto" nowrap><?=$rowf["nome"]?></td>
		<?if(!empty($rowf["dddfixo"])and !empty($rowf["telfixo"])){?>	 
			<td align="right" class="rotulo">Telefone:</td> 
			<td class="texto" nowrap><?=$rowf["dddfixo"]."-".$rowf["telfixo"]?></td> 
		<?}?>	
					
		</tr>
		<?if(!empty($rowf["cpfcnpj"]) and !empty($rowf["inscrest"])){?>
		<tr>
		    <?if(!empty($rowf["cpfcnpj"])){?>
			<td nowrap  class="rotulo">CPF/CNPJ:</td> 
			<td nowrap class="texto"><?=formatarCPF_CNPJ($rowf["cpfcnpj"]) ?></td> 
			<?
			}
			?>
			<?if(!empty($rowf["inscrest"])){?>
			<td nowrap align="right" class="rotulo">I.E:</td> 
			<td nowrap class="texto"><?=$rowf["inscrest"]?></td> 
			<?
			}
			?>
		</tr>
		<?}?>
		<?
		    if(!empty($rowf["idpessoa"])){

			$sqlend="select t.tipoendereco,c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf,e.obsentrega
			    from nfscidadesiaf c,endereco e,tipoendereco t
			    where c.codcidade = e.codcidade
			    and e.idtipoendereco = 2
			    and t.idtipoendereco = e.idtipoendereco
			    and e.idpessoa =".$rowf["idpessoa"];
			    $resend=d::b()->query($sqlend) or die("erro ao buscar informaçàµes do endereço sql=".$sqlend);
			    $qtdend= mysqli_num_rows($resend);
			    if($qtdend){
			    $rowend=mysqli_fetch_assoc($resend);
				    $cep=formatarCEP($rowend["cep"],true);

?>			    
		<tr>
		    <td align="" class="rotulo">Endereço:</td>	
		    <td class="texto"><?=$rowend["logradouro"]?> <?=$rowend["endereco"]?> N.: <?=$rowend["numero"]?> <?=$rowend["complemento"]?></td>
		    <td align="right" class="rotulo">Bairro:</td>
		    <td class="texto"><?=$rowend["bairro"]?></td>
		</tr>
		<tr>
		    <td align="" class="rotulo">Cidade:</td>
		    <td class="texto"><?=$rowend["cidade"]?>-<?=$rowend["uf"]?></td>
		    <td align="right" class="rotulo">CEP:</td>
		    <td class="texto"><?=$cep?></td>
		</tr>			   
		    <?
			    }

		    }
		    ?>
		<?if(!empty($rowf["email"])){?>
		<tr>
			<td  class="rotulo">Email:</td>
			<td class="texto" nowrap><?=$rowf["email"]?></td>		
		</tr>
		<?}?>
		<?if(!empty($rowf["vendedor"])){?>
		<tr>
			<td class="rotulo">AC:</td> 
			<td class="texto" nowrap><?=$rowf["vendedor"]?></td>			
		</tr>
		<?}?>
		</table>
		    
		<p></p>

<? 
	$qtdrows= mysqli_num_rows($res);
	
	if($qtdrows>0){
	
?>

<div style="width:100%;">
	<table  align="" style="width:100%;">
		<tr style="background-color: #B5B5B5">
			<td align="center" class="rotulotable" style="width:5%;max-width:5%;"><font style="vertical-align: top;">QTD</font></td>
			<td align="center" class="rotulotable" style="width:5%;max-width:5%;"><font style="vertical-align: top;">UN</font></td>
			<td align="center" class="rotulotable" style="width:20%;max-width:30%;"><font style="vertical-align: top;">DESCRIÇÃO</font></td>
			<td align="center" class="rotulotable" style="width:10%;max-width:15%;"><font style="vertical-align: top;">ANEXOS</font></td>
			<td align="center" class="rotulotable" style="width:10%;" nowrap><font style="vertical-align: top;">VALOR UN <BR> (R$)</font></td>
			<td align="center" class="rotulotable" style="width:10%;" nowrap><font style="vertical-align: top;">DESC UN <BR> (R$)</font></td>
			<td align="center" class="rotulotable" style="width:10%;" nowrap><font style="vertical-align: top;">IPI <BR> (R$)</font></td>
			<td align="center" class="rotulotable" style="width:1%;" nowrap><font style="vertical-align: top;">TOTAL <BR> (R$)</font></td>
			<td align="center" class="rotulotable" style="width:1%;" nowrap><font style="vertical-align: top;">VALIDADE</font></td>
			<td align="center" class="rotulotable" style="width:1%;"><font style="vertical-align: top;">PREVISÃO ENTREGA</font></td>
			<td align="center" class="rotulotable"><font style="vertical-align: top;">OBS</font></td>
		</tr>

<?	
		$i=1;
		$total=0.00;
		$desconto=0.00;
		$totalsemdesc=0.00;
		$troca="S";
		$frete = $rowf["frete"];
		 if($rowf["modfrete"] == 1){
			$tipofrete = 'FOB';
		 }else{
			$tipofrete = 'CIF';
		 }
		while ($row = mysqli_fetch_assoc($res)){
		 $i = $i+1;
		 //$total= $total + $row["total"] + $row['valipi'] - $row['des'];
		 //$totalsemdesc += $row['total'] + $row['valipi'];
		 //$desconto += $row['des'];

		 $totalsemdesc += $row['total']+$row['valipi']+($row['des']*$row['qtd']);
		 $total=$total+$row['total']+$row['valipi'];
		 $desconto += $row['des']*$row['qtd'];

		//mudar a cor da linha
		 if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>
		<tr style="background-color: <?=$cortr?>">
			<td align="right" class="textoitem" ><?=$row["qtd"]?></td>
			<td align="center" class="textoitem" >
				<?
					echo($row["unidade"]);						
				?>
			</td>
			<td align="center" class="textoitem" >
			<?if(empty($row["codforn"])){
			 echo($row["descr"]);
			}else{
				echo($row["codforn"]);
			}
			?>			
			</td>
			<td align="center" class="textoanexo">
				<?if(!empty($row["idprodserv"])){?>
					<?$sqlarq = "SELECT nome, ifnull(nomeoriginal, nome) AS nomeoriginal from arquivo where idobjeto = ".$row["idprodserv"]." and tipoobjeto= 'arqCotacao';";
					$resarq = d::b()->query($sqlarq) or die("Erro ao buscar itens da nota:".mysqli_error($resarq));
					if(mysqli_num_rows($resarq) > 0){?>
						<table align="center">
							<?while($rowarq = mysqli_fetch_assoc($resarq)){?>
								<tr>
									<td>
									<?
										if (strlen($rowarq['nomeoriginal']) > 10){
											$str = substr($rowarq['nomeoriginal'], 0, 10)."...".substr($rowarq['nomeoriginal'],-4);
										}elseif(!empty($rowarq['nomeoriginal'])){
											$str = $rowarq['nomeoriginal'];
										}
										?> 
										<a href="<?="https://sislaudo.laudolab.com.br/upload/anexo_orcamento/".$rowarq['nome']?>" target="_blank"><?=$str?></a>
									</td>
								</tr>
							<?}?>
						</table>
					<?}?>
				<?}?>
			</td>	
			<td align="right" class="textoitem" ><?=number_format(tratanumero($row["vlritem"]), 2, ',', '.');?></td>
			<td align="right" class="textoitem" ><?=number_format(tratanumero($row["des"]), 2, ',', '.'); ?></td>
			<td align="right" class="textoitem" ><?=number_format(tratanumero($row["valipi"]), 2, ',', '.');?></td>
			<td align="right" class="textoitem" ><?=number_format(tratanumero($row["total"]), 2, ',', '.');?></td>
			<td align="center" class="textoitem" ><?=dma($row["validade"])?></td>
			<td align="center" class="textoitem" ><?=dma($row["previsaoentrega"])?></td>
			<td align="center" class="textoitem" ><?=$row["obs"]?></td>
		</tr>	

		 
		 
<? 	}	

		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>
		<tr style="background-color: <?=$cortr?>">
			<td class="rotulo" align="right" colspan="6">Frete <?=$tipofrete?>(R$):</td>
			<td class="rotulo" align="right"> <?=number_format(tratanumero($frete), 2, ',', '.');?></td>
		</tr>
		<?
		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>
		<tr style="background-color: <?=$cortr?>">
                    <td class="rotulo" align="right" colspan="6">Total sem Desconto(R$): </td>
                    <td class="rotulo" align="right"><?=number_format(tratanumero($totalsemdesc), 2, ',', '.'); ?> </td>
		</tr>
<?
		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
?>
		<tr style="background-color: <?=$cortr?>">
                    <td class="rotulo" align="right" colspan="6">Desconto (R$): </td>
                    <td class="rotulo" align="right"><?=number_format(tratanumero($desconto), 2, ',', '.'); ?> </td>
		</tr>

<? 	

		if($troca=="S"){
				$cortr = "#FFFFFF";
				$troca="N";
			}else{
				$cortr = "#E8E8E8";
				$troca="S";
			} 
			$vtotal=$total+$frete;
?>
		<tr style="background-color: <?=$cortr?>">
                    <td class="rotulo" align="right" colspan="6">Total com Desconto(R$): </td>
                    <td class="rotulo" align="right"><?=number_format(tratanumero($vtotal), 2, ',', '.'); ?> </td>
		</tr>
	</table>
</div>
<br>
<p>
	
<?		
	}
?>	

	<?if(!empty($rowf['obs'])){?>

	<table style="font-size: 12px;  ">
		<tr>
			<td colspan="11"><font style="font-size:10"><b>Observação:</b></font></td>
		</tr>
		<tr>
			<td colspan="50" style="max-width:550px">

			    <?=nl2br($rowf['obs'])?></td>
		</tr>
	</table>
<br>
	<p>
			<?}?>
	
	
	<div align='left' style='font-size: 12px; font-weight: bold; '>CONDIÇÃO DE PAGAMENTO</div>
		<hr>
				<table>

				<?
					if(!empty($rowf["idtransportadora"])){
				?>
				<tr>	 
					<td align="right" class="rotulo">Transportadora:</td> 
					<td class="texto"><?=traduzid("pessoa","idpessoa","nome",$rowf["idtransportadora"])?></td>
				</tr>
				
				<?
					}
					if(!empty($rowf["pagamento"])){
						if(!empty($rowf["idformapagamento"]) and $rowf["pagamento"] == 'C.CREDITO'){
							$formapgto = traduzid("formapagamento","idformapagamento","ncartao",$rowf["idformapagamento"]);
							//$formapgto = " - (Cartão: ".substr($formapgto, 0,4).")";
						}else{
							$formapgto = "";
						}
				?>
				<tr>	 
					<td align="right" class="rotulo">Pagamento:</td> 
					<td class="texto"><?=$rowf["pagamento"].$formapgto?></td>
				</tr>
				<?
					}
					if(!empty($rowf["parcelas"])){
				?>				
				<tr> 	 
					<td align="right" class="rotulo">Parcelas:</td>
					<td class="texto"><?=$rowf["parcelas"]?></td>				
				</tr>
				<?
					}
					if(!empty($rowf["diasentrada"])){?>
						<tr> 	 
							<td align="right" class="rotulo">1º Vencimento:</td>
							<td class="texto"><?=$rowf["diasentrada"]?> dia(s)</td>				
						</tr>
					<?}
					if(!empty($rowf["intervalo"]) and !($rowf["parcelas"] == "1")){
				?>				
				<tr> 	 
					<td align="right" class="rotulo" nowrap>Intervalo:</td>
					<td class="texto"><?=$rowf["intervalo"]?> dia(s)</td>				
				</tr>
				<?
					}
				?>
				<tr>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr>
				</table>

<br>

	<div align='left' style='font-size: 12px; font-weight: bold; '>OBSERVAÇÕES</div>
	<hr>
		<table>
		    <tr>	 
			<td  class="rotulo">ENTREGA DO PEDIDO DE COMPRA</td> 
		    </tr>
		    <tr>
			<td class="texto">Os números Pedido <?=$rowf["idcotacao"]?> e Cotação <?=$rowf["idsolicitacao"]?> devem constar nas informações adicionais da nota fiscal.<br>Horário de recebimento de mercadorias: <?=$figrel["horariorecebimento"]?><br> A(s) nota(s) fiscal(is) deste pedido deve(m) ser sua cópia fiel e é obrigatório indicar o número do pedido de compras no rodapé da mesma.
			    Enviar as notas fiscais eletrônicas, DANFE, XML e boletos para: <b><?=$figrel["emailnfe"]?></b>.
			</td>
		    </tr>		    
		    <tr>	 
			<td  class="rotulo">CERTIFICADO DE ANÁLISE</td> 
		    </tr>
		    <tr>
			<td class="texto">O certificado de análise do insumo/material ou certificado de calibração do equipamento, quando aplicável, deve ser disponibilizado eletronicamente (email ou site), ou de forma impressa. Caso seja via email, favor enviar os certificados para: <?=$figrel["emailcq"]?>.</td>
		    </tr>

<tr>	 
			<td class="rotulo">CONDIÇÕES GERAIS</td> 
		    </tr>
		    <tr>
			<td class="texto">
Autorizamos os fornecimentos dos materiais ou serviços constantes no pedido acima, dentro das condições técnicas, comerciais e físicas acordadas. Os itens e/ou condições constantes deste pedido de compras que estiverem em desacordo, estão sujeitos à  recusa do recebimento por parte da empresa <?=$figrel["nomefantasia"]?> e o custo da devolução será de responsabilidade do fornecedor. Seguem requisitos avaliados na entrega do serviço/produto:
		    <br> - A nota fiscal da entrega está de acordo com a ordem de compras da empresa <?=$figrel["nomefantasia"]?> (Itens, forma e prazo de pagamento, dados de faturamento, etc).
 <br> - Os itens entregues correspondem com a nota fiscal do fornecedor.
 <br> - A embalagem chegou integra (sem avarias) e está seguramente fechada.
 <br> - A identificação na embalagem é completa e está devidamente aderida.
 <br> - A entrega foi realizada na data prevista.
 <br> - Consta o certificado de análise do insumo/material ou certificado de calibração do equipamento (quando aplicável).
 <br> - Os itens entregues encontram-se dentro do prazo de validade necessário à  utilização dos mesmos (quando aplicável).
			</td>
		    </tr>
		</table>
	
</div>	
	
</body>
</html>
<?

//if($_GET['gerapdf']=='Y'){

	$html = ob_get_contents();
	//limpar o codigo html
	
	$html = preg_replace('/>\s+</', "><", $html);
	ob_end_clean();
	
	//Inclusão da biblioteca DOMPDF
	require_once "../inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";
	Dompdf\Autoloader::register();
	use Dompdf\Dompdf;

	// Instanciamos a classe
	$dompdf = new Dompdf();
	 
	// Passamos o conteúdo que será convertido para PDF
	$dompdf->loadHtml($html);
	 
	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->setPaper('A4', 'portrait');
	 
	// O arquivo é convertido
	$dompdf->render();
	
	if($gravaarquivo=='Y'){
	// Salvo no diretà³rio  do sistema
	    $output = $dompdf->output();
	    file_put_contents("/var/www/laudo/tmp/nfe/solicitacao_".$rowf["idcotacao"].".".$rowf["idsolicitacao"].".pdf",$output);
	    echo("OK");
	}else{   
	// Exibido para o usuário
		$dompdf->stream("solicitacao_".$rowf["idcotacao"].".".$rowf["idsolicitacao"].".pdf");
	}
//}
?>

