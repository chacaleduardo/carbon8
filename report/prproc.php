<?
require_once("../inc/php/validaacesso.php");
if ($_GET['versao'] == 0) {
	$versao = true;
}else {
	
}

if (empty($_GET['idprproc']) or is_null($_GET['versao'])) {
	die('Parametros GET não fornecidos!');
 }else {
	 
 }


// Configuração de inputs visà­veis conforme combinação de tipo e subtipo de amostra
function jsonAtiv(){

	$sql= "select distinct trim(ativ) as ativ
			from prativ 
			where ativ>''
				and length(ativ)>2
                                ".getidempresa('idempresa','prativ')."
			order by trim(ativ)";

	$res = d::b()->query($sql) or die("Erro ao recuperar Hist de Ativ: ".mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($res)) {
		$arrtmp[$r["ativ"]]=$r["ativ"];
        $i++;
    }

	$json = new Services_JSON();
	return $json->encode($arrtmp);

}

$arrCores=["silver", "#cc0000", "#0000cc", "#00cc00", "#990000","#ff6600", "#fcd202", "#b0de09", "#0d8ecf",  "#cd0d74"];

$arrOpcoesInputManual=[""=>array("icone"=>"fa fa-eye-slash cinzaclaro"),"check"=>array("icone"=>"fa fa-check-square-o verde"), "linha"=>array("icone"=>"fa fa-window-minimize verde"), "text"=>array("icone"=>"fa fa-comment verde")];

//print_r($arrOpcoesInputManual);die;
$sqlproc="select * from objetojson where idobjeto=".$_GET['idprproc']." and tipoobjeto='prproc' and versaoobjeto=".$_GET['versao'];
$res = d::b()->query($sqlproc) or die("Erro ao recuperar json: ".mysqli_error(d::b()));
$row = mysqli_fetch_assoc($res);
$rc= unserialize(base64_decode($row["jobjeto"]));
$_1_u_prproc_idprproc = $rc['prproc']['res']['idprproc'];
$_1_u_prproc_proc = $rc['prproc']['res']['proc'];
$_1_u_prproc_tipo = $rc['prproc']['res']['tipo'];
$_1_u_prproc_subtipo = $rc['prproc']['res']['subtipo'];
$_1_u_prproc_versao = $rc['prproc']['res']['versao'];
$_1_u_prproc_status = $rc['prproc']['res']['status'];
?>
<title>Processo</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
<style>
.rotulo{
font-weight: bold;
font-size: 9px;
}
.texto{
font-size: 9px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}
.breakw {
	word-break: break-word;
}

@media print{
	#rodapengeraarquivo{
		position: fixed;
		bottom: 0;
	}
	.breakw {
	word-break: break-word;
	position:absolute; 
	top: 3cm;
}
}
</style>
<div style="width: 650px;">
<?
$_sqltimbrado="select * from empresaimagem where 1 ".getidempresa('idempresa','empresa')." and tipoimagem = 'HEADERPRODUTO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado=mysql_fetch_assoc($_restimbrado);
			$_timbradocabecalho = $_figtimbrado["caminho"];

if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}?>
            <br>
<div class="row">
	<div class="col-md-12">
	<div class="panel panel-default" >
		<div class="panel-heading">
                    <table style="width: 650px;">
                    <tr>
                        <td colspan="2" class="rotulo">
                            Processo: <?=$_1_u_prproc_proc?>
                        </td>
                        <td colspan="2" class="rotulo">
                            Tipo: <?=$_1_u_prproc_tipo?>
                        </td>
						<td colspan="2" class="rotulo">
                            Sub Tipo: <?=$_1_u_prproc_subtipo?>
                        </td>
						<td colspan="2" class="rotulo">
                        Status: <?=$_1_u_prproc_status?></td>
						</td>
						<td colspan="2" class="rotulo">Versão: <?=$_1_u_prproc_versao?>.0</td>
                    </tr>
                    </table>
		</div>
        <br>
		<div class="panel-body">

<div class="divbody">
<?
/*	
	$sql="select *
	from prativ pa
	where pa.idprproc =".$_1_u_prproc_idprproc."
	order by pa.loteimpressao, pa.ord";
	
	$sql="select pa.idprprocprativ,pa.loteimpressao,pa.dia,pa.ord as ordem,pa.idetapa, pa.idfluxostatus,a.*
  from prprocprativ pa join prativ a on(a.idprativ=pa.idprativ)
  where pa.idprproc=".$_1_u_prproc_idprproc."
	order by pa.loteimpressao, ordem";
	
	//die($sql);
		$res = d::b()->query($sql) or die("A Consulta das atividades falhou: " . mysql_error() . "<p>SQL: $sql");
	*/
	$i=1;
	$array=1;
	while ($row = $rc['prprocprativ']['res'][$array]){
?>

			<div class="row">   
				<div class="panel panel-default" >
                    <table border="1" style="width: 650px;" cellspacing='0' cellpading='0'>
							<tr>
                                <td>
					            <div class="panel-heading">
                                    <table>
                                        <tr>
                                            <td><i class="fa fa-print pointer cinzaclaro" style="color:<?=$arrCores[$row["loteimpressao"]]?>;" title="Alterar Lote de Impressão: #<?=$row["loteimpressao"]?>" idprprocprativ="<?=$row["idprprocprativ"]?>" loteimpressao="<?=$row["loteimpressao"]?>" onclick="alteraLoteImpressao(this)"></i></td>
                                            <td title="<?=$row["ativ"]?>"><label class="rotulo">
                                                        Atividade:
                                                </label>
                                                <?=$row["ativ"]?>
                                            </td>
                                            <td>
                                                <label class="rotulo">Status OP:</label>
                                                <?
                                                if (!empty($row["idfluxostatus"])) {
                                                    $sql = "SELECT mf.idfluxostatus, s.rotuloresp 
                                                    FROM fluxo ms JOIN fluxostatus mf ON  ms.idfluxo = mf.idfluxo
                                                    JOIN "._DBCARBON."._status s ON mf.idstatus = s.idstatus
                                                WHERE mf.idfluxostatus = ".$row["idfluxostatus"];
                                                    $resql= d::b()->query($sql) or die("Erro: " . mysql_error() . "<p>SQL:".$sql);
                                                    $qtdsql= mysqli_num_rows($resql);  
                                                    if ($qtdsql > 0) {
                                                        $rf = mysqli_fetch_assoc($resql);
                                                        echo $rf['rotuloresp'];
                                                    }
                                                }?>
                                            </td>
                                            <td>
                                                <label class="rotulo">Etapa:</label>
                                                <?
                                                if (!empty($row["idetapa"])) {
                                                    $sql = "SELECT idetapa, etapa 
                                                    FROM etapa 
                                                WHERE idetapa = ".$row["idetapa"];
                                                    $resql= d::b()->query($sql) or die("Erro: " . mysql_error() . "<p>SQL:".$sql);
                                                    $qtdsql= mysqli_num_rows($resql);  
                                                    if ($qtdsql > 0) {
                                                        $rf = mysqli_fetch_assoc($resql);
                                                        echo $rf['etapa'];
                                                    }
                                                }?>
                                            </td>																
                                            <td>
                                                <label class="rotulo">Sala:</label>
                                                <?
                                                    $sqlsal="select t.* from prativobj o,tagtipo t
                                                            where t.idtagclass=2 
                                                            and o.idobjeto=t.idtagtipo 
                                                            and o.tipoobjeto='tagtipo'
                                                            and o.idprativ=".$row['idprativ'];
                                                    $ressala= d::b()->query($sqlsal) or die("Erro ao buscar item sala : " . mysql_error() . "<p>SQL:".$sqlsal);
                                                    $qtdsala= mysqli_num_rows($ressala);
                                                    $sala=mysqli_fetch_assoc($ressala);
                                                    
                                                    echo $sala['tagtipo'];
                                                    ?>
                                            </td>
                                            <?
                                    $sqlc="select p.*,o.idprativobj 
                                            from prativopcao p left join prativobj o 
                                            on (o.tipoobjeto ='prativopcao' 
                                                and o.idobjeto = p.idprativopcao 
                                                and o.idprativ=".$row['idprativ'].") 
                                            where p.status='ATIVO' 
                                            and p.tipo='bioterio'
                                            order by p.ord";
                                    $resc= d::b()->query($sqlc) or die("A Consulta das prativopcao falhou : " . mysql_error() . "<p>SQL: $sqlc");
                                    
                                    while($rowc=mysqli_fetch_assoc($resc)){						
                                    ?>	
                                        <td ><label class="rotulo"><?echo($rowc["descr"].":");?></label>
                                        <?
                                        if(!empty($rowc["idprativobj"])){
                                        ?>
                                            Sim
                                        <?}else{?>	
                                            Não
                                        <?}
                                            
                                        ?>
                                            
                                        </td>
                                    <?
                                    }
                                    ?>
                                        <td class="nowrap"><label class="rotulo">Dia:</label>
                                            <?=$row['dia']?>
                                        </td>
                                            <td></td>
                                            <td></td>                  
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            <label class="rotulo">Campos</label>
                                        </td>
                                    </tr>
                                    
                                        <?
                                        $sqlc="select p.*,o.idprativobj 
                                        from  prativopcao p left join prativobj o 
                                        on (o.tipoobjeto ='prativopcao' 
                                        and o.idobjeto = p.idprativopcao 
                                        and o.idprativ=".$row['idprativ'].") 
                                        where p.status='ATIVO' 
                                        and p.tipo='camposconclusao'
                                        order by p.ord";
                                        $resc= d::b()->query($sqlc) or die("A Consulta das prativopcao falhou : " . mysql_error() . "<p>SQL: $sqlc");
                                        
                                        while($rowc=mysqli_fetch_assoc($resc)){						
                                            ?>	
                                        
                                            <?
                                            if(!empty($rowc["idprativobj"])){?>
                                            <tr>
                                                <td >
                                                <?
                                                    echo($rowc["descr"]);
                                                ?>
                                                </td>
                                            </tr>
                                            <?}?>
                                        <?} ?>
                                </table>
                            </td>
                        </tr>
	<?
				$sqlitem="select o.*,t.tagtipo 
						from prativobj o,tagtipo t
						where t.idtagclass=1
							and o.idobjeto=t.idtagtipo 
							and o.tipoobjeto='tagtipo'
							and o.idprativ=".$row['idprativ']." order by o.ord";
				$resitem = d::b()->query($sqlitem) or die("Erro ao buscar item : " . mysql_error() . "<p>SQL:".$sqlitem);
				$qtdrowitem= mysqli_num_rows($resitem);
				$i++;
				if($qtdrowitem>0){?>
                        <tr>
                            <td>
			<div class="col-md-3">
				<div class="panel panel-default" >
				<div class="panel-heading rotulo">Equipamentos</div>
					<div class="panel-body">					
					
					<table class="table table-striped planilha sortable" style="width: 100%; ">
					
		
					
			<?
					while($item=mysqli_fetch_assoc($resitem)){
					$i++;

					$ico=empty($arrOpcoesInputManual[$item["inputmanual"]]["icone"])?"fa fa-eye-slash cinzaclaro":$arrOpcoesInputManual[$item["inputmanual"]]["icone"];

			?>	
					<tr>
						<td>
							<?=$item['tagtipo']?>
						</td>						
					</tr>
			<?
					}//while($item1=mysqli_fetch_assoc($resitem)){
			?>
					
					</table>
					</div>
				</div>
			</div>
                </td>
            </tr>
	    <?}//if($qtdrowitem>0){?>
            <tr>
                <td>		
			<div class="col-md-3">
				<div class="panel panel-default" >
				<div class="panel-heading">
                                    <table>
                                        <tr>
                                            <td><label class="rotulo"> Teste:</label>
                                            <?
                                            if (!empty($row['idsubtipoamostra'])) {
                                                $sql = "SELECT idsubtipoamostra,subtipoamostra 
                                                from subtipoamostra  where idsubtipoamostra = ".$row["idsubtipoamostra"];
                                                $resql= d::b()->query($sql) or die("Erro: " . mysql_error() . "<p>SQL:".$sql);
                                                $qtdsql= mysqli_num_rows($resql);  
                                                if ($qtdsql > 0) {
                                                    $rf = mysqli_fetch_assoc($resql);
                                                    echo $rf['subtipoamostra'];
                                                }
                                            }?>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                    </table> 
                                </div>
	<?
				$sqlitem="select o.*,t.descr 
						from prativobj o,prodserv t
						where t.tipo='SERVICO'
						and o.idobjeto=t.idprodserv 
						and o.tipoobjeto='prodserv'
						and o.idprativ=".$row['idprativ']." order by ord";
				$resitem = d::b()->query($sqlitem) or die("Erro ao buscar item  prodserv servico: " . mysql_error() . "<p>SQL:".$sqlitem);
				$i++;
				$qtdrowitemteste= mysqli_num_rows($resitem);
				if($qtdrowitemteste>0){?>
					<div class="panel-body">					
					
					<table class="table table-striped planilha sortable" style="width: 100%; ">
					
            <?
					while($item=mysqli_fetch_assoc($resitem)){
					$i++;
			?>
					<tr>
						<td>
							<?=$item['descr']?>
						</td>						
					</tr>
			<?
					}//while($item1=mysqli_fetch_assoc($resitem)){
			?>
					
					</table>
					</div>
				</div>
			</div>		
	<?}//if($qtdrowitemteste>0){?>
                </td>
            </tr>
	<?
				$sqlitem="select o.*
						from prativobj o
						where o.tipoobjeto='ctrlproc'
						and o.idprativ=".$row['idprativ']."
						order by idprativobj";
				$resitem = d::b()->query($sqlitem) or die("Erro ao buscar item : " . mysql_error() . "<p>SQL:".$sqlitem);
				$qtdrowitem= mysqli_num_rows($resitem);
				$i++;
				if($qtdrowitem>0){?>
            <tr>
                <td>	
			<div class="col-md-3">
				<div class="panel panel-default" >
				<div class="panel-heading rotulo">Informações específicas</div>
					<div class="panel-body">		
					<table class="table table-striped planilha sortable" style="width: 100%;">
    <?
					while($item=mysqli_fetch_assoc($resitem)){
					$i++;
					$ico=empty($arrOpcoesInputManual[$item["inputmanual"]]["icone"])?"fa fa-eye-slash cinzaclaro":$arrOpcoesInputManual[$item["inputmanual"]]["icone"];

			?>	
					<tr>
						<td>
							<?=$item['descr']?>
						</td>						
					</tr>
			<?
					}//while($item1=mysqli_fetch_assoc($resitem)){
			?>
					</table>
					</div>
				</div>
			</div>
                </td>
            </tr>
	<?}//if($qtdrowitem>0){?>
            
	<?
				$sqlitem="select o.*
						from prativobj o
						where o.tipoobjeto='materiais'
						and o.idprativ=".$row['idprativ']." order by ord";
				$resitem = d::b()->query($sqlitem) or die("Erro ao buscar item : " . mysql_error() . "<p>SQL:".$sqlitem);
				$qtdrowitem= mysqli_num_rows($resitem);
				$i++;
				if($qtdrowitem>0){?>
                <tr>
                    <td>
			<div class="col-md-3">
				<div class="panel panel-default" >
				<div class="panel-heading rotulo">Materiais e Utensílios</div>
					<div class="panel-body">		
					<table class="table table-striped planilha sortable" style="width: 100%; ">
			<?
					while($item=mysqli_fetch_assoc($resitem)){
					$i++;
					$ico=empty($arrOpcoesInputManual[$item["inputmanual"]]["icone"])?"fa fa-eye-slash cinzaclaro":$arrOpcoesInputManual[$item["inputmanual"]]["icone"];
			?>	
					<tr>
						<td>
							<?=$item['descr']?>
						</td>
					</tr>
			<?
					}//while($item1=mysqli_fetch_assoc($resitem)){
			?>
					
					</table>
					</div>
				</div>
			</div>
                </td>
            </tr>
	<?}//if($qtdrowitem>0){?>					
            </table>
				</div>
				</div>	
            <br>

<?
$array++;
	}//while ($row = mysqli_fetch_assoc($res))
    ?>
    </div>
</div>	


</div>
	</div>
</div>
<?
   $sql = " select p.idprodserv,p.descr
   from prodservprproc c  join prodserv p on ( p.idprodserv = c.idprodserv and p.status='ATIVO')
   where  c.idprproc  = ".$_1_u_prproc_idprproc." order by p.descr  ";
   
   $res = d::b()->query($sql) or die("A Consulta de PRODUTOS falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
   $existe = mysqli_num_rows($res);
   if($existe>0){
       ?>
<br>
<table border="1" style="width: 650px;" cellspacing='0' cellpading='0'>
<tr  class="header">
    <td colspan="2">
		<b>Produtos</b>
    </td>
</tr>		
<?			
        while($row = mysqli_fetch_assoc($res)){			
?>	
                <tr class="res">
                    <td nowrap><?=$row["descr"]?></td>
                </tr>				
<?							
        }
?>	
</table>
<br>
<?
        }//if($existe>0){
?>
<table border="1" style="width: 650px;" cellspacing='0' cellpading='0'>
<tr  class="header">
    <td colspan="2">
		<b>Histórico</b>
    </td>
</tr>
<tr>
    <td>
        <b>Versões</b>
    </td>
    <td>
        <b>Descrição</b>	
    </td>
        <?		$sql = 'SELECT * FROM objetojson where idobjeto ='.$_1_u_prproc_idprproc.' and versaoobjeto <= '.$_GET['versao'].' and tipoobjeto="prproc" order by versaoobjeto desc';
                $res = d::b()->query($sql) or die("A Consulta de versões falhou :".mysqli_error(d::b())."<br>Sql:".$sql);
                while($row1 = mysqli_fetch_assoc($res)){			
        ?>	
                        <tr class="res">
                            <td nowrap>Versão:  <?=$row1['versaoobjeto']?>.0</td>
                            <?
                            $rc1 = unserialize(base64_decode($row1["jobjeto"]));
                            ?>
                            <td style="line-height: 1.5;"><?=nl2br($rc1['prproc']['res']['descr'])?></td>
                        </tr>				
        <?							
                }
        ?>
</tr>
</table>
</div>