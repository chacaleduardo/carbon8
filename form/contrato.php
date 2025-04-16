<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

require_once("../inc/php/permissao.php");
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "contrato";
$pagvalcampos = array(
	"idcontrato" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contrato where idcontrato = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


function getClientesnf(){
global $_1_u_contrato_tipo,$_1_u_contrato_idplantel;

    if(!empty($_1_u_contrato_tipo) and !empty($_1_u_contrato_idplantel)){
    $sql= "
	SELECT 
		p.idpessoa, p.nome, p.cpfcnpj
	FROM
		pessoa p
	WHERE
		p.status = 'ATIVO'
			AND p.idtipopessoa IN (2 , 12)
			AND EXISTS( SELECT 
				1
			FROM
				plantelobjeto o force index(idobjeto_tipoobjeto)
			WHERE
				o.tipoobjeto = 'pessoa'
					AND o.idobjeto = p.idpessoa
					AND o.idplantel = '".$_1_u_contrato_idplantel."')
			AND NOT EXISTS( SELECT 
				1
			FROM
				contratopessoa f,
				contrato c
			WHERE
				p.idpessoa = f.idpessoa
					AND c.idcontrato = f.idcontrato
					AND c.tipo = '".$_1_u_contrato_tipo."'
					AND c.status = 'ATIVO')
		".getidempresa('p.idempresa','pessoa')."
	ORDER BY nome";
  
    }else{
 
        $sql= "SELECT p.idpessoa,p.nome,p.cpfcnpj
        FROM pessoa p			
        WHERE p.status = 'ATIVO'
        AND p.idtipopessoa in (2,12)
        ".getidempresa('p.idempresa','pessoa')."
        ORDER BY p.nome";
    }

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=htmlentities($r["nome"]);
        $arrret[$r["idpessoa"]]["cpfcnpj"]=htmlentities($r["cpfcnpj"]);
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getClientesnf();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);

if(!empty($_1_u_contrato_idcontrato) ){

	if(!empty($_1_u_contrato_idplantel)){
		function jsonProdutos(){    
			global $_1_u_contrato_idcontrato,$_1_u_contrato_tipo,$_1_u_contrato_idplantel,$_1_u_contrato_idempresa;
			if($_1_u_contrato_tipo=='P'){
				$str=" p.venda='Y'  and p.tipo='PRODUTO'"; }
			else{
				$str=" p.tipo='SERVICO'";
			}
			$sql = "select p.idprodserv,p.descrcurta,p.descr	
					from  prodserv p
					where ".$str."
					and p.venda='Y'
					AND p.idempresa = ".$_1_u_contrato_idempresa."
					AND EXISTS( SELECT 
									1
								FROM
									plantelobjeto o force index(idobjeto_tipoobjeto)
								WHERE
									o.tipoobjeto = 'prodserv'
										AND o.idobjeto = p.idprodserv
										AND o.idplantel = '".$_1_u_contrato_idplantel."')
					and not exists (select 1  from desconto d where d.idtipoteste=p.idprodserv and d.idcontrato=".$_1_u_contrato_idcontrato.")
					AND p.status='ATIVO' order by p.descr,p.descrcurta";
	
			$res = d::b()->query($sql);
	
			$arrtmp=array();
			$i=0;
			while ($r = mysqli_fetch_assoc($res)) {
				$arrtmp[$i]["value"]=$r["idprodserv"];
				if(!empty($r['descrcurta'])){
					$arrtmp[$i]["label"]= $r['descrcurta'];
				}else{
					$arrtmp[$i]["label"]= $r['descr'];
				}
				$i++;
			}
			return $arrtmp;
		}

	}else{
		function jsonProdutos(){    
			global $_1_u_contrato_idcontrato,$_1_u_contrato_tipo,$_1_u_contrato_idplantel,$_1_u_contrato_idempresa;
			if($_1_u_contrato_tipo=='P'){
				$str=" p.venda='Y'  and p.tipo='PRODUTO'"; }
			else{
				$str=" p.tipo='SERVICO'";
			}
			$sql = "select p.idprodserv,p.descrcurta,p.descr	
					from  prodserv p
					where ".$str."
					and p.venda='Y'
					AND p.idempresa = ".$_1_u_contrato_idempresa."
					and not exists (select 1  from desconto d where d.idtipoteste=p.idprodserv and d.idcontrato=".$_1_u_contrato_idcontrato.")
					AND p.status='ATIVO' order by p.descr,p.descrcurta";
	
			$res = d::b()->query($sql);
	
			$arrtmp=array();
			$i=0;
			while ($r = mysqli_fetch_assoc($res)) {
				$arrtmp[$i]["value"]=$r["idprodserv"];
				if(!empty($r['descrcurta'])){
					$arrtmp[$i]["label"]= $r['descrcurta'];
				}else{
					$arrtmp[$i]["label"]= $r['descr'];
				}
				$i++;
			}
			return $arrtmp;
		}

	}



	//Recupera os produtos a serem selecionados para uma nova Formalização
	$arrProd=jsonProdutos();

	//print_r($arrProd); die;
	$jsonProd=$JSON->encode($arrProd);
}else{//if(!empty($_1_u_contrato_idcontrato)){
	$jsonProd="null";
}

?>
<style>

select:required:invalid {
  color: green;
}
option[value=""][disabled] {
  display: none;
}
option {
  color: black;
}


a.dcontexto {
	position: relative;
	font: 12px arial, verdana, helvetica, sans-serif;
	padding: 0;
	color: #039;
	text-decoration: none;
	cursor: hand;
	z-index: 24;
}
a.dcontexto:hover {
	background: transparent;
	z-index: 25;
}
a.dcontexto div {
	display: none;
}
a.dcontexto:hover div {
	display: block;
	position: absolute;
	width: 230px;
	top: 0em;
	text-align: justify;
	left: 6em;
	font: 10px Verdana, arial, helvetica, sans-serif;
	padding: 5px 10px;
	border: 1px solid #999;
	background: #E8EBF2;
	color: #000;
}
.mostratab{
	display:none;
}

</style>


<style data-cke-temp="1" type="text/css" media="print">

.escondetab{
	display: none;
}
.mostratab{
	display:block;
}

</style>


    <div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Contrato <i title="Imprimir" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/contrato.php?idcontrato=<?=$_1_u_contrato_idcontrato?>')"></i></div>
        <div  class="panel-body">
            
<?
//Não permitir alterar o status para Ativo
if($_1_u_contrato_status == "INATIVO" ){
	$disabledStatus = "disabled='disabled' ";
}
if(!empty($_1_u_contrato_tipo)){
	$disabled="disabled='disabled' ";
}
?>
			
			<table>
			<tr> 
				<td>Contrato:</td>
				<td>
				<label class="alert-warning"><?=$_1_u_contrato_idcontrato?></label>
					<input name="_1_<?=$_acao?>_contrato_idcontrato"	type="hidden"	value="<?=$_1_u_contrato_idcontrato?>"	readonly='readonly'>
				</td> 
			</tr>
			
			
			<tr> 
				<td align="right">Título:</td> 
				<td colspan="5"><input	name="_1_<?=$_acao?>_contrato_titulo"	size="50"	type="text"	value="<?=$_1_u_contrato_titulo?>"	></td> 				
				<td align="right">Tipo:</td> 
				<td>
					<select <?=$disabled?> name="_1_<?=$_acao?>_contrato_tipo" vnulo>
						<option></option>
						<?fillselect("select 'S','Serviço' union select 'P','Produto'",$_1_u_contrato_tipo);?></select>
				</td> 
<?	
				if($_1_u_contrato_tipo=='P'){
?>
				<td align="right">Divisão:</td> 
				<td>
					<select name="_1_<?=$_acao?>_contrato_idplantel" vnulo>
						<option></option>
						<?fillselect("select p.idplantel,concat(e.sigla,' - ',p.plantel) as plantel
											from plantel p join empresa e on(e.idempresa=p.idempresa)
											where p.status='ATIVO' 
											 and p.idempresa=".cb::idempresa()." and p.prodserv='Y' order by plantel",$_1_u_contrato_idplantel);?></select>
				</td> 
<?
				}
?>				
			</tr> 
			<tr>
				<td align="right">N&ordm;.:</td> 
				<td><input name="_1_<?=$_acao?>_contrato_numero"  size="5"	type="text" 	value="<?=$_1_u_contrato_numero?>"></td> 
				<td align="right">Início:</td>
				<td>
					<input 
						name="_1_<?=$_acao?>_contrato_vigencia" 
						class="calendario"
                                                
						type="text" size="8"
						value="<?=$_1_u_contrato_vigencia?>" 
						vdata		>

				</td> 
				<td align="right">Fim:</td>
				<td>
					<input 
						name="_1_<?=$_acao?>_contrato_vigenciafim" 
						class="calendario"
						type="text" size="8"
						value="<?=$_1_u_contrato_vigenciafim?>" 
						vdata		>

								
				</td> 
				<td  align="right">Status:</td> 
				<td>
					<select <?=$disabledStatus?> name="_1_<?=$_acao?>_contrato_status">
						<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_contrato_status);?>		</select>
				</td> 
			</tr>
			</table>
        </div>
    </div>
    </div>
</div>


<?
			if(!empty($_1_u_contrato_idcontrato)){
			?>

<?
	$sqls ="SELECT c.idcontratopessoa,p.*
	FROM contratopessoa c,pessoa p
	where p.idpessoa = c.idpessoa				
	and c.idcontrato =".$_1_u_contrato_idcontrato." order by p.nome";
				

	$ress = d::b()->query($sqls) or die("A Consulta dos clientes falhou :".mysqli_error(d::b())."<br>Sql:".$sqls); 
	$qtdrows= mysqli_num_rows($ress);
	
?>			
			<div class="row">
                <div class="col-md-12">
                <div class="panel panel-default">  
                <div class="panel-heading">Cliente(s) do Contrato</div>
                <div class="panel-body"> 
                 	<table class="table table-striped planilha"> 			
			<?		
					$y=9999;
					while($rows = mysqli_fetch_array($ress)){	
						$y=$y+1;
?>						
						<tr  >
							<td align="left" class="col-md-10">
                            	<a class="pointer" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$rows['idpessoa'];?>')"><?=$rows["nome"]?></a>
							</td>
							<td  class="col-md-2">
								<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratopessoa',<?=$rows["idcontratopessoa"]?>)" alt="Excluir"></i>
							</td>							
						</tr>
<?
					}				
?>
				<tr>
					<td >Cliente:
						<input  type="text" placeholder="Adicionar Novo Cliente" name="contratopessoa_idpessoa"  cbvalue="" value="" style="width: 40em;">
					</td>
					<td></td>
				</tr>
			</table>
			</div>
			</div>
			</div>
	</div>
<?

	if($_1_u_contrato_tipo=='S'){
?>
<div class="row ">
		<div class="col-md-12" >
		<div class="panel panel-default" >
			<div class="panel-heading">Orçamento(s) Vinculado(s)</div>
			<div  class="panel-body">
				<?
				$sql = "select c.idorcamento,c.controle,c.status,c.cliente,c.dataorc,total,o.idobjetovinculo from objetovinculo o 
				join orcamento c on(c.idorcamento=o.idobjetovinc)
				where o.idobjeto=" . $_1_u_contrato_idcontrato . " 
				and o.tipoobjeto = 'contrato'
				and o.tipoobjetovinc ='orcamento' order by idorcamento";
		
							
				$res = d::b()->query($sql) or die("A Consulta dos orcamentos falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
				$qtd = mysqli_num_rows($res);
				
				if($qtd>0){
				?>
							
				<table class="table table-striped planilha">
				<tr class="header">
					<td>Orcamento</td>											
					<td >Cliente</td>	
					<td >Data</td>
					<td>Valor</td>
					<td></td>
				</tr>
				<?while ($row = mysqli_fetch_array($res)) {?>
					<tr class="res">
					<td><a class="pointer" onclick="janelamodal('?_modulo=orcamento&_acao=u&idorcamento=<?=$row["idorcamento"];?>')"> <?=$row['controle']?></a></td>											
					<td ><?=$row['cliente']?></td>	
					<td ><?=dma($row['dataorc'])?></td>
					<td> <?= number_format(tratanumero($row['total']), 2, ',', '.'); ?></td>
					<td><i class="fa fa-trash pointer hoververmelho cinza" onclick="excluir('objetovinculo',<?=$row['idobjetovinculo']?>)"></i></td>
				</tr>
					
				<?}?>
				</table>
			<hr>		
			<?}?>
			<table>
				<tr>
					<td>Novo Orçamento</td>
					<td>
						<select name="idorcamento" onchange="inserirorc(this)">
								<option></option>
								<?fillselect(" SELECT p.idorcamento,concat(p.controle,' - ',p.cliente) as orcamento
									FROM contratopessoa c,orcamento p
									where p.idpessoa = c.idpessoa
									and p.controle is not null
									and p.cliente is not null
									and p.status='APROVADO'
									and not exists (select 1 from objetovinculo o where o.idobjeto=c.idcontrato
															and o.tipoobjeto = 'contrato'
															and o.tipoobjetovinc ='orcamento' and o.idobjetovinc=p.idorcamento)
									and c.idcontrato =".$_1_u_contrato_idcontrato." order by orcamento");?>
						</select>
					</td> 				
					
				</tr>
			</table>
	</div>
</div>
</div>
</div> 



<?
	}

}//if(!empty($_1_u_contrato_idcontrato)){ clientes
?>


			<?
			if($_1_u_contrato_tipo=='S'){


				$sql = "
					SELECT
					  d.iddesconto,
					  d.idtipoteste,
					  concat(t.tipoteste,' ',ifnull(p.codigo,' ')) as tipoteste,
						t.valor,
						p.idportaria,
			      	  d.desconto,
					d.tipodesconto,
			      	  t.sigla
					FROM
					  desconto d,
					  vwtipoteste t left join portaria p on(t.idportaria=p.idportaria)
					where
					  d.idtipoteste = t.idtipoteste
					  AND d.idcontrato = " . $_1_u_contrato_idcontrato . "
					order by
					  t.tipoteste";
			
								
					$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
					$qtd = mysqli_num_rows($res);
					$i = 1;
				    while ($row = mysqli_fetch_array($res)) {
			
				?>
				<div class="row ">
                                <div class="col-md-12" >
                                <div class="panel panel-default" >
                                    <div class="panel-heading"><a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idtipoteste"];?>')"> <?=$row["sigla"]?> </a> - <?=$row["tipoteste"]?> </div>
                                    <div  class="panel-body">
									
									<div class="col-md-6" >	
				
										<table class="table table-striped planilha">
										<tr class="header">
											<td>Valor Tabela</td>											
											<td >Tipo desconto</td>										
											<?if($row["tipodesconto"]=='P'){?>
											<td >Desconto</td>
											<?}else{?>
												<td >Valor com desconto</td>
											<?
											}
											if($row["tipodesconto"]=='P'){?>
											<td>Valor Final</td>
											<?}?>
											<td></td>
										</tr>
				
											<?
											
												$i++;
											if(!empty($row['idportaria'])){
												$legportaria="S";
												$inidportaria=$inidportaria.$virg.$row['idportaria'];
												$virg=",";				
											}
											?>
											<tr class="respreto" >
											
												<td align="center">
												<input type="hidden" name="_<?=$i?>_u_desconto_iddesconto" value="<?=$row["iddesconto"]?>" readonly size="1">
													<?=$row["valor"]?></td>
												<td align="center"> 
																		<select name="_<?=$i?>_u_desconto_tipodesconto" id="select" style="background-color: #EFEFEE;font-weight:bold;font-size:12px;" vnulo>
												<?
													fillselect("SELECT 'P','%' union select 'V','R$'",$row["tipodesconto"]);
												?>
																		</select>				      	
																	</td>
																	<td align="center"><input type="text" class="size5" name="_<?=$i?>_u_desconto_desconto" value="<?=$row["desconto"]?>" size="4"></td>
																	<?if($row["tipodesconto"]=='P'){?>
																	<td align="center">
																		<?
																			$valor=$row["desconto"]/100*$row["valor"];
																			$valor = $row["valor"]-$valor;
																			$valor=number_format(tratanumero($valor), 2, ',', '.');
																			echo($valor);
																		?>
																	</td>
																	<?}?>
																	<td align="center">
																		<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('desconto',<?=$row["iddesconto"]?>)" alt="Excluir"></i>
																	</td>
											</tr>
											</table>
											</div>	
											<div class="col-md-6" >																			
										
										<table  class="table table-striped planilha">
									
										<?
										$sqlp="select ifnull(p.nomecurto,p.nome) as nome,c.* 
										from contratocomissao c join pessoa p on(p.idpessoa=c.idpessoa)
										where c.iddesconto = ".$row['iddesconto']." order by nome";
										$resp = d::b()->query($sqlp) or die("A Consulta das comissões falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
									
										$qtdresp=mysqli_num_rows($resp);
										if($qtdresp>0){
										?>
										<tr>
											<th class="col-md-6" >Nome</th>
											<th class="col-md-4" >Comissão</th>
											<th class="col-md-2"></th>
										</tr>
										<?
												while($rowp=mysqli_fetch_assoc($resp)){
												$i=$i+1;
												
												if(empty($rowp['comissao'])){
													$sqlc="select di.comissaogest 
														from divisao d 
														join divisaoplantel dp on(d.iddivisao=dp.iddivisao  ) 
														join divisaoitem di on (di.iddivisao=d.iddivisao and di.idprodserv =".$row["idtipoteste"]." and di.comissaogest >0 )
													where d.idpessoa =".$rowp['idpessoa']." 
													and d.status='ATIVO' 
													AND d.tipo='SERVICO' limit 1";
													
													$resc = d::b()->query($sqlc);
													$qtdc=mysqli_num_rows($resc);
													if($qtdc>0){
														$rowc=mysqli_fetch_assoc($resc);
														$rowp['comissao']=$rowc['comissaogest'];
													}									

												}
										?>
										<tr>
											<td><?=$rowp['nome']?></td>										
											<td >
													<input type="hidden" name="_<?=$i?>_u_contratocomissao_idcontratocomissao" value="<?=$rowp["idcontratocomissao"]?>" readonly size="1">
													<input class="size6" placeholder="0.00" type="text" style="text-align:right" name="_<?=$i?>_u_contratocomissao_comissao" value="<?=$rowp["comissao"]?>" size="7">
											</td>
											<td>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratocomissao',<?=$rowp['idcontratocomissao']?>)" title="Excluir"></i>
											</td>
										</tr>
										<?
											}//while($rowp=mysqli_fetch_assoc($resp)){
										?>
										
										<?
										}//if($qtdresp>0){
										?>
										<tr>
											<td colspan="3">
											<select   id="contratocomissao_idpessoa" class="size40" onchange="icontratocomissao(this,<?=$row['iddesconto']?>)"  >
													<option value="" disabled selected hidden>Selecione para adicionar uma comissão </option>
														<?fillselect("select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
																		from contratopessoa cp join pessoacontato c on (c.idpessoa = cp.idpessoa)
																		join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and status  ='ATIVO')
																		where cp.idcontrato =" . $_1_u_contrato_idcontrato . " 
																		
																		union
																		select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome
																		from contratopessoa cp 
																		join  plantelobjeto po on( po.idobjeto =cp.idpessoa and po.tipoobjeto='pessoa' )
																		join divisaoplantel dp on(dp.idplantel=po.idplantel)
																		join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='SERVICO' and d.status='ATIVO')   
																		join pessoa f on(f.idpessoa = d.idpessoa)
																		where cp.idcontrato =" . $_1_u_contrato_idcontrato . " 
																		
																		group by idpessoa order by nome");?>		
												</select>
>
											</td>
										</tr>
										</table>
											
										</div>
							
					
					

				       </div>
				</div>
				</div>
			</div>
			<?					  	
				}//while ($row = mysqli_fetch_array($res)) {
			?>
			<div class="row ">
					<div class="col-md-12" >
					<div class="panel panel-default" >
						<div class="panel-heading">Adicionar Produto</div>
						<div  class="panel-body">							
						<table>
							<tr>
								<td>Novo Desconto</td>
								<td>
									<input type="text" name="s_idtipoteste" style="width: 45em;" placeholder="Selecione um serviço para entrar no contrato."  cbvalue="" value="">
								</td>
								<td>
									<select name="s_tipodesconto" id="select" style="background-color: #EFEFEE;font-weight:bold;font-size:12px;" vnulo>
										<?
												fillselect("SELECT 'P','%' union select 'V','R$'");
										?>
									</select>
								</td>
								<td>
									<input name="s_desconto" size="7" type="text" value="">
								</td>
								<td><a class="fa fa-plus-circle verde pointer hoverazul" title="Inserir serviço!" onclick="inserir('servico',<?=$_1_u_contrato_idcontrato?>)"></a></td>
							</tr>
						</table>
						</div>
					</div>
					</div>
				</div> 
<?
				if($legportaria=="S"){
					    $sqlport="select idportaria,portaria,codigo,referencia,tipo from portaria where  idportaria in(".$inidportaria.")";
					    $resport= d::b()->query($sqlport) or die("Erro ao buscar portaria sql=".$sqlport);
					    $qtdport=mysqli_num_rows($resport);
					    if($qtdport>0){
					    ?>
					<div class="row">
					<div class="col-md-12" >
					<div class="panel panel-default" >
					    <div class="panel-heading">Legenda</div>
					<div  class="panel-body">
					    <table class="table table-striped planilha">
						    <?
						    while($rowport=mysqli_fetch_assoc($resport)){
							    if($troca=="S"){
								    $cortr = "#FFFFFF";
								    $troca="N";
							    }else{
								    $cortr = "#E8E8E8";
								    $troca="S";
							    }
			    ?>
						    <tr >
							    <th>
							    <?echo($rowport['codigo']." ".$rowport['tipo']." MAPA N&#186;. ".$rowport['portaria'].", de ".$rowport['referencia']);?>
							    </th>
						    </tr>	

			    <?
						    }
			    ?>
					    </table>
					    </div>
					</div>
					</div>
					</div>
			    <?
					    }
					}

		}elseif($_1_u_contrato_tipo=='P' && !empty($_1_u_contrato_idplantel)){
			?>
				
			
			<?
					$sql = "
					SELECT
						d.iddesconto,
						d.idtipoteste,
						d.idaliqicms,
						t.descrcurta,t.descr,
						d.desconto,	
						d.comissao,
						d.idprodservformula,
						t.vlrvenda as vlrvenda,
						t.comissao as comissaof,
						t.codprodserv,
						t.fabricado
					FROM
					  desconto d join prodserv t on( d.idtipoteste = t.idprodserv)
                                          
					where d.idtipoteste = t.idprodserv
					   AND d.idcontrato = " . $_1_u_contrato_idcontrato . "
					order by
					  t.descr";
			
					
			
					$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
					$qtd = mysqli_num_rows($res);
				?>
		
				
				
<?
				    $i = 1;
				    while ($row = mysqli_fetch_array($res)) {
                                        
				    	$i++;
?>
	<div class="row ">
                                <div class="col-md-12" >
                                <div class="panel panel-default" >
                                    <div class="panel-heading">
									<table>                                    
                                        <tr>
                                	    	<td align="left">
                                                <a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idtipoteste"];?>')">
                                                    <?if(empty($row["descrcurta"])){echo $row["descr"];}else{ echo $row["descrcurta"]; }?>
                                                </a>
                                            </td>
											
                                            <td align="center">
                                                <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('desconto',<?=$row["iddesconto"]?>)" title="Excluir"></i>
                                            </td>	
										</tr>
										
									</table>
									</div>
                                    <div  class="panel-body">
									<div class="row ">
									
									<div class="col-md-6" >
										
										<?if($row['fabricado']=='N'){?>	
											<table  class="table table-striped planilha">
											<tr>
												<th class="col-md-6">Produto</th>
												
												<th class="col-md-2">Valor</th>											
											</tr>
											<tr>
												<td>
												<?if(empty($row["descrcurta"])){echo $row["descr"];}else{ echo $row["descrcurta"]; }?>
												</td>							
												<td >
													<input type="hidden" name="_<?=$i?>_u_desconto_iddesconto" value="<?=$row["iddesconto"]?>" readonly size="1">

													<input class="size6" placeholder="0.00" type="text" style="text-align:right" name="_<?=$i?>_u_desconto_valor" value="<?=$row["valor"]?>" size="7">
												</td>
											</tr>
										</table>
										<?}else{?>
										
										<table  class="table table-striped planilha">
									
										<?
											/*$sqlp="select concat(p.plantel,' - ',f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,c.*
											from contratoprodservformula c join prodservformula f on(f.idprodservformula=c.idprodservformula)
											join plantel p on(p.idplantel=f.idplantel)
											where c.iddesconto=".$row['iddesconto']." order by rotulo";
											*/
											$sqlp="select f.idprodservformula as vidprodservformula,f.vlrvenda,f.comissao,concat(p.plantel,' - ',f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,c.*
													from prodservformula f  
													join plantel p on(p.idplantel=f.idplantel)
													left join contratoprodservformula c on(f.idprodservformula=c.idprodservformula and c.iddesconto=".$row['iddesconto'].")
													where f.idprodserv = ".$row["idtipoteste"]." 
													and f.idplantel=".$_1_u_contrato_idplantel." 
													and f.status='ATIVO' order by rotulo";
											$resp = d::b()->query($sqlp) or die("A Consulta das formulas falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
											$qtdresp=mysqli_num_rows($resp);
											if($qtdresp>0){
										?>
										<tr>
											<th class="col-md-6">Fórmula</th>											
											<th class="col-md-2">Valor</th>
											<th class="col-md-2"></th>
										</tr>
										<?
												while($rowp=mysqli_fetch_assoc($resp)){
												$i=$i+1;
												if(empty($rowp['idcontratoprodservformula'])){$nacao='i';}else{$nacao='u';}
												if(empty($rowp['valor']) and !empty($rowp['vlrvenda'])){$rowp['valor']=$rowp['vlrvenda'];}
										?>
										<tr>
											<td>
												<?=$rowp['rotulo']?>
												<input type="hidden" name="_<?=$i?>_<?=$nacao?>_contratoprodservformula_iddesconto" value="<?=$row["iddesconto"]?>" readonly size="1">
												<input type="hidden" name="_<?=$i?>_<?=$nacao?>_contratoprodservformula_idcontratoprodservformula" value="<?=$rowp["idcontratoprodservformula"]?>" readonly size="1">
												<input type="hidden" name="_<?=$i?>_<?=$nacao?>_contratoprodservformula_idprodservformula" value="<?=$rowp["vidprodservformula"]?>" readonly size="1">
											</td>
											<td>
												<input class="size6" placeholder="0.00" type="text" style="text-align:right" name="_<?=$i?>_<?=$nacao?>_contratoprodservformula_valor" value="<?=$rowp["valor"]?>" size="7">
											</td>
											<td>
											<?if(!empty($rowp['idcontratoprodservformula'])){?>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratoprodservformula',<?=$rowp['idcontratoprodservformula']?>)" title="Excluir"></i>
											<?}?>
											</td>
										</tr>
	
										<?
											}//while($rowp=mysqli_fetch_assoc($resp)){
										
										}//if($qtdresp>0){
										?>
										<!--tr>
											<td  colspan="4">
												<select  class="size40"  id="contratoprodservformula_idprodservformula" onchange="icontratoprodservformula(this,<?=$row['iddesconto']?>)"  >
												<option value="" disabled selected hidden>Selecione para adicionar uma fórmula</option>
												<?
															fillselect("select f.idprodservformula,concat(p.plantel,' - ',f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo
																		from prodservformula f join plantel p on(p.idplantel=f.idplantel)
																		where f.idprodserv = ".$row["idtipoteste"]." and f.status='ATIVO' order by rotulo");
														?>	
												</select>
											</td>
										</tr-->
										
										
										</table>
										<?}?>
											
										</div>
									
									<div class="col-md-6" >																			
										
										<table  class="table table-striped planilha">
									
										<?
										$sqlp="select ifnull(p.nomecurto,p.nome) as nome,c.* 
										from contratocomissao c join pessoa p on(p.idpessoa=c.idpessoa)
										where c.iddesconto = ".$row['iddesconto']." order by nome";
										$resp = d::b()->query($sqlp) or die("A Consulta das comissões falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
									
										$qtdresp=mysqli_num_rows($resp);
										if($qtdresp>0){
										?>
										<tr>
											<th class="col-md-6" >Nome</th>
											<th class="col-md-4" >Comissão</th>
											<th class="col-md-2"></th>
										</tr>
										<?
												while($rowp=mysqli_fetch_assoc($resp)){
												$i=$i+1;
												
												if(empty($rowp['comissao'])){
													$sqlc="select di.comissaogest 
														from divisao d 
														join divisaoplantel dp on(d.iddivisao=dp.iddivisao and dp.idplantel=".$_1_u_contrato_idplantel." ) 
														join divisaoitem di on (di.iddivisao=d.iddivisao and di.idprodserv =".$row["idtipoteste"]." and di.comissaogest >0 )
													where d.idpessoa =".$rowp['idpessoa']." 
													and d.status='ATIVO' 
													AND d.tipo='PRODUTO' limit 1";
													
													$resc = d::b()->query($sqlc);
													$qtdc=mysqli_num_rows($resc);
													if($qtdc>0){
														$rowc=mysqli_fetch_assoc($resc);
														$rowp['comissao']=$rowc['comissaogest'];
													}									

												}
										?>
										<tr>
											<td><?=$rowp['nome']?></td>										
											<td >
													<input type="hidden" name="_<?=$i?>_u_contratocomissao_idcontratocomissao" value="<?=$rowp["idcontratocomissao"]?>" readonly size="1">
													<input class="size6" placeholder="0.00" type="text" style="text-align:right" name="_<?=$i?>_u_contratocomissao_comissao" value="<?=$rowp["comissao"]?>" size="7">
											</td>
											<td>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratocomissao',<?=$rowp['idcontratocomissao']?>)" title="Excluir"></i>
											</td>
										</tr>
										<?
											}//while($rowp=mysqli_fetch_assoc($resp)){
										?>
										
										<?
										}//if($qtdresp>0){
										?>
										<tr>
											<td colspan="3">
											<select   id="contratocomissao_idpessoa" class="size40" onchange="icontratocomissao(this,<?=$row['iddesconto']?>)"  >
												<option value="" disabled selected hidden>Selecione para adicionar uma comissão </option>
														<?fillselect("select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
																		from contratopessoa cp join pessoacontato c on (c.idpessoa = cp.idpessoa)
																		join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and status  ='ATIVO')
																		-- join  plantelobjeto po on( po.idobjeto =p.idpessoa and po.tipoobjeto='pessoa' and po.idplantel=".$_1_u_contrato_idplantel.")
																		where cp.idcontrato =" . $_1_u_contrato_idcontrato . " 
																		
																		union
																		select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome
																		from contratopessoa cp 
																		join  plantelobjeto po on( po.idobjeto =cp.idpessoa and po.tipoobjeto='pessoa' and po.idplantel=".$_1_u_contrato_idplantel.")
																		join divisaoplantel dp on(dp.idplantel=po.idplantel)
																		join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='PRODUTO' and d.status='ATIVO')   
																		join pessoa f on(f.idpessoa = d.idpessoa)
																		where cp.idcontrato =" . $_1_u_contrato_idcontrato . " 
																		
																		group by idpessoa order by nome");?>		
												</select>
											</td>
										</tr>
										</table>
											
										</div>

										</div>
						</div>
						</div>
						</div>
                        </div>  
<?
                                    }// while ($row = mysqli_fetch_array($res)) {
?>
                  <div class="row ">
					<div class="col-md-12" >
					<div class="panel panel-default" >
						<div class="panel-heading">Adicionar Produto</div>
						<div  class="panel-body">
							<table>
								<tr>
									<td></td>
									<td ><input type="text" name="prodservformula_idprodservformula" style="width: 45em;" placeholder="Selecione um produto para entrar no contrato."  cbvalue="" value=""></td>

									<td></td>
								</tr>					
							</table>
						</div>
					</div>
					</div>
				</div>           
					
			<?}?>
			
			<?	if(empty($_1_u_contrato_idplantel) and $_1_u_contrato_tipo=='P' ){ ?>				
				<div class="row">
					<div class="col-md-12">
                		<div class="panel panel-default"> 
							<div class="panel-heading">
								<table class="blink vermelho">                                    
									<tr>
										<td>
											Selecione um Plantel para Adicionar Produtos
										</td>
									</tr>					
								</table>
							</div>
						</div>
					</div>
				</div>
		
			<?}?>

<?
	if(!empty($_1_u_contrato_idcontrato)){
?>
<div class="row">
	<div class="col-md-12">
                <div class="panel panel-default">  
                <div class="panel-heading">Análise Crítica do Contrato</div>
                <div class="panel-body"> 
		    <div class="col-md-6">
			<table>
			     <tr>
				<td nowrap><select  name="_1_u_contrato_espfis">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_espfis);?></select>
				</td>
				<td nowrap>(1)-	Estrutura das instalações.</td>
			    </tr>
			    <tr>
				<td nowrap><select  name="_1_u_contrato_captec">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_captec);?></select>
				</td>
				<td nowrap>(2)-Capacitação técnica.</td>
			    </tr>
			    <tr>
				<td nowrap><select  name="_1_u_contrato_capoper">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_capoper);?></select>
				</td>
				<td nowrap>(3)-Capacidade operacional.</td>
			    </tr>
			    <tr>
				<td nowrap><select  name="_1_u_contrato_ensaio">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_ensaio);?></select>
				</td>
				<td nowrap>(4)-Ensaio consta no escopo.</td>
			    </tr>
			    <tr>
				<td nowrap><select  name="_1_u_contrato_prazo">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_prazo);?></select>
				</td>
				<td nowrap>(5)-Cumprimento de prazo.</td>
			    </tr>			
			    <tr>
				<td nowrap><select  name="_1_u_contrato_ensaiosub">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_ensaiosub);?></select>
				</td>
				<td nowrap>(6)-Subcontratação do ensaio.</td>
			    </tr>
			    <tr>
				<td nowrap><select  name="_1_u_contrato_divergencia">
				    <option value=""></option>
				<?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_contrato_divergencia);?></select>
				</td>
				<td nowrap>(7)-Divergências entre a proposta e o contrato.</td>
			    </tr>		
			</table>
			<table>
			    <tr>
				<td>Responsável:</td>
				<td>
				    <select  name="_1_u_contrato_idpessoa">
				    <option value=""></option>
				    <?fillselect("select idpessoa,nome from pessoa where status = 'ATIVO' and idtipopessoa=1 ".getidempresa('idempresa','pessoa')." order by nome",$_1_u_contrato_idpessoa);?>
				    </select>
				</td>
			    </tr>
			</table>
		    </div>			
		    <div class="col-md-6">
			<table>
			    <td>Obs.:</td>
			    <td><textarea rows="10"  cols="60"  style=font-size:medium;  name="_1_u_contrato_obs" ><?=$_1_u_contrato_obs?></textarea></td>
			</table>
		   
		    </div>	
		</div>
		</div>
	</div>
</div>
<!--div class="row">
	 <div class="col-md-6">
                <div class="panel panel-default">  
                <div class="panel-heading">Anexo(s) do Contrato</div>
                <div class="panel-body"> 
		           
                            <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
                                    <i class="fa fa-cloud-upload fonte18"></i>
                            </div>
                     	
		</div>
		</div>
	</div>
</div-->

	<?				
		}//if(!empty($_1_u_contrato_idcontrato)){
	?>	
    
<?
if(!empty($_1_u_contrato_idcontrato)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_contrato_idcontrato; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "contrato"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
/*
	if(!empty($_1_u_contrato_idcontrato)){####INICIO IMPRESSAO
?>		
</div>
<div style="page-break-before: always;"></div>
 <div class="title mostratab" >CONTRATO <?=$_1_u_contrato_titulo?><?if($_1_u_contrato_numero){ echo(" - Nº.:")?> <?=$_1_u_contrato_numero?><?}?></div>
<br />
 			<?
				if($_1_u_contrato_tipo=='S'){
			?>
			
			
			<?
					$sql = "
					SELECT
					  d.iddesconto,
					  d.idtipoteste,
					  t.tipoteste,
						t.valor,
			      	  d.desconto,
					d.tipodesconto,
			      	  t.sigla
					FROM
					  desconto d,
					  vwtipoteste t
					where
					  d.idtipoteste = t.idtipoteste					 
					  AND d.idcontrato = " . $_1_u_contrato_idcontrato . "
					order by
					  t.tipoteste";
			
					$res = d::b()->query($sql) or die("A Consulta falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
					$qtd = mysqli_num_rows($res);
			
				?>
					
				 <table class="mostratab" width="100%" border="1">
				 <tr class="header1"> 
						<td colspan="6" align="center">Vigência: <?=$_1_u_contrato_vigencia?> á <?=$_1_u_contrato_vigenciafim?></td>
					</tr>
				<?if($qtd>0){?>
				  <tr class="header2">
				    <td>Sigla</td>
				    <td>Teste</td>
				    <td>Valor Tabela</td>
				    <td>Tipo desconto</td>
				    <td>Valor desconto</td>
				    <td>Valor Final</td>
				   
				  </tr>
				    <?
				    $i = 1;
				    while ($row = mysqli_fetch_array($res)) {
				    	$i++;

					?>
					  <tr class="res1"> 
					    <td align="center"><?=$row["sigla"]?></td>
					    <td align="center"><?=$row["tipoteste"]?></td>
					    <td align="center"><?=$row["valor"]?></td>
					    <td align="center"> 
				         <?
				          if($row["tipodesconto"]=="P"){echo("%");}else{echo("R$");}							
						  ?>
				        		      	
				      	 </td>
				      	  <td align="center"><?=$row["desconto"]?></td>
				      	  <td align="center">
						<?if($row["tipodesconto"]=='P'){
							$valor=$row["desconto"]/100*$row["valor"];
							$valor = $row["valor"]-$valor;
							$valor=number_format(tratanumero($valor), 2, ',', '.');
							echo($valor);
						}else{
							echo($row["desconto"]);
						}?>
						</td>
					   </tr>
					  <?
					  	}
					  }
					  ?>
					</table>
			<?}elseif($_1_u_contrato_tipo=='P'){


				
					$sqlp="select concat(p.plantel,' - ',f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,c.*
					from contratoprodservformula c join prodservformula f on(f.idprodservformula=c.idprodservformula)
					join plantel p on(p.idplantel=f.idplantel)
					where c.iddesconto=".$row['iddesconto']." order by rotulo";
					$resp = d::b()->query($sqlp) or die("A Consulta das formulas falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlp");
					$qtdresp=mysqli_num_rows($resp);
?>

					<table class="mostratab" width="100%" border="1">
					<tr class="header1"> 
						  <td colspan="5" align="center">Vigência: <?=$_1_u_contrato_vigencia?> á <?=$_1_u_contrato_vigenciafim?></td>
					  </tr>
				  <?
					if($qtdresp>0){
					?>
					<tr class="header2">
						<th >Formula</th>
						<th >Quantidade</th>
						<th >Valor</th>
					
					</tr>
					<?
						while($rowp=mysqli_fetch_assoc($resp)){
						$i=$i+1;
					?>
					<tr>
					<td><?=$rowp['rotulo']?></td>										
					<td align="right">
							<?=$rowp["qtd"]?>
					</td>
					<td align="right">
						<?=$rowp["valor"]?>
					</td>
				
					</tr>

					<?
					}//while($rowp=mysqli_fetch_assoc($resp)){
				}
			?>
			
						
					</table>				
			<?}?>
 
 
 <?
 }//fim impressão
 */
 ?>
<script>
jCli=<?=$jCli?>;// autocomplete cliente

//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id+"" ,"cpfcnpj":o.cpfcnpj}
});

//autocomplete de clientes
$("[name*=contratopessoa_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,select: function(event, ui){
          inserircliente(ui.item.value);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.cpfcnpj+"</span></a>").appendTo(ul);
        };
    }	
});
// FIM autocomplete cliente
    
if( $("[name=_1_u_contrato_idcontrato]").val() ){
    $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_contrato_idcontrato]").val()
            ,tipoObjeto: 'contrato'
			,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
    });
}

function inserircliente(inid){
    if(confirm("Deseja inserir este?")){		
        CB.post({
        objetos: "_x_i_contratopessoa_idcontrato="+$("[name=_1_u_contrato_idcontrato]").val()+"&_x_i_contratopessoa_idpessoa="+inid
        });
    }
    
}

function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
		objetos: "_x_d_"+tab+"_id"+tab+"="+inid
		,parcial:true
        });
    }
    
}

function inserir(tipo,idcontrato){

//if(tipo=='produto'){
 //   var str="_x_i_desconto_idcontrato="+idcontrato+"&_x_i_desconto_idtipoteste="+$("[name=p_idtipoteste]").val()+"&_x_i_desconto_idaliqicms="+$("[name=p_idaliqicms]").val()+"&_x_i_desconto_desconto="+$("[name=p_desconto]").val();
//}else{
    var str="_x_i_desconto_idcontrato="+idcontrato+"&_x_i_desconto_idtipoteste="+$("[name=s_idtipoteste]").attr('cbvalue')+"&_x_i_desconto_tipodesconto="+$("[name=s_tipodesconto]").val()+"&_x_i_desconto_desconto="+$("[name=s_desconto]").val();
//}

    CB.post({
      objetos: str
    });
}


jsonProd = <?=$jsonProd?>;//// autocomplete produto
    
//autocomplete de produto
$("[name*=prodservformula_idprodservformula]").autocomplete({
    source: jsonProd
    ,delay: 0
    ,select: function(event, ui){
        insereprodservformula(ui.item.value);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
         return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

//autocomplete de produto
$("[name*=s_idtipoteste]").autocomplete({
    source: jsonProd
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
         return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

function insereprodservformula(idprodserv){
    //alert(idprodservformula);
    
    
    CB.post({
        objetos: "_x_i_desconto_idcontrato="+$("[name=_1_u_contrato_idcontrato]").val()+"&_x_i_desconto_idtipoteste="+idprodserv
        ,parcial: true        
    })  
    
}

function icontratocomissao(vthis,iddesconto){
	CB.post({
        objetos: "_x_i_contratocomissao_iddesconto="+iddesconto+"&_x_i_contratocomissao_idpessoa="+$(vthis).val()
        ,parcial: true        
    })  

}

function icontratoprodservformula(vthis,iddesconto){
	CB.post({
        objetos: "_x_i_contratoprodservformula_iddesconto="+iddesconto+"&_x_i_contratoprodservformula_idprodservformula="+$(vthis).val()
        ,parcial: true        
    })  

}

function inserirorc(vthis){
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_i_objetovinculo_idobjeto":$("[name=_1_u_contrato_idcontrato]").val()
			,"_x_i_objetovinculo_tipoobjeto":'contrato'
			,"_x_i_objetovinculo_tipoobjetovinc":'orcamento'
            ,"_x_i_objetovinculo_idobjetovinc":strval
        }
        ,parcial: true
    });
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>