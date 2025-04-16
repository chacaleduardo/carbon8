<style>
   #tbOrificios input{
   width: 40px;
   }
   #tbOrificios span{
   float: right;
   vertical-align: middle;
   line-height: 30px;
   }
   #tbOrificios .row div div{
   background-color: #ccc;
   padding: 4px;
   }
   #tbOrificios .row div{
   padding: 3px 3px !important;
   }
   #diveditor{
   width: 98% !important;
   }
   .divdescritivo input{
   width: 40px !important;
   }
   .divdescritivo select{
   width: 80px !important;
   }
   .divdescritivo div{
   padding: 3px 3px;
   }
   .divdescritivo{
   margin-left: 0px; margin-right: 0px;
   }
   .interna{
   padding: 4px;
   }
   .interna3{
   padding: 4px;
   background-color: #bbb;
   margin: 3px;
   }
   .interna2{
   background-color: #ccc;
   padding: 4px;
   height: 45px;
   }
   input{
   text-align: right;
   }
   select{
   direction: rtl;
   }
   .diveditor {
   border: 1px solid gray;
   background-color: white;
   color: black;
   font-family: Arial,Verdana,sans-serif;
   font-size: 10pt;
   font-weight: normal;
   width: 800px;
   height: 256px;
   word-wrap: break-word;
   overflow: auto;
   padding: 5px;
   }
   .planilha tbody tr td, .planilha tbody tr th{
   padding:1px 4px;
   }
   .table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #f0f0f0;
   }
   .badge{
       font-weight: normal;
   }
   .itemestoque{
       font-weight: normal;
   }
</style>
<?
   require_once("../inc/php/validaacesso.php");
   
   if($_POST){
   	require_once("../inc/php/cbpost.php");
   }
   
   //Parà¢metros mandatà³rios para o carbon
   $pagvaltabela = "resultado";
   $pagvalcampos = array(
   	"idresultado" => "pk"
   );
   
   function listaTestes(){
   	
   	global $_1_u_resultado_idamostra, $_1_u_resultado_idresultado;
   	
   	$sqlt = "SELECT
			r.idresultado,
			r.idtipoteste,
			t.tipoteste,
			t.sigla,
			r.quantidade quant,
			r.status,
			t.tipogmt,
			r.criadopor,
			dmahms(r.criadoem),
			r.alteradopor,
			if(s.dia is null,'',concat(' - D',s.dia)) as rotulo,
			dmahms(r.alteradoem) alteradoem,
			se.nome as secretaria
		FROM
			resultado r join vwtipoteste t on (r.idtipoteste = t.idtipoteste)
			left join servicoensaio s on (r.idservicoensaio=s.idservicoensaio)
			left join pessoa se on (se.idpessoa = r.idsecretaria)
		WHERE
				r.idamostra = " . $_1_u_resultado_idamostra . "
			and r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
			and r.status !='OFFLINE'
		ORDER BY
			r.ord";
   //die($sqlt);
   	$rest = d::b()->query($sqlt);
   
   	if(!$rest) die("Falha consultando testes: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlt);
   
   	while ($r = mysqli_fetch_assoc($rest)) {
   		
   		$oficial = empty($r["secretaria"])?"naooficial":"oficial";
   		$testeativo = ($_1_u_resultado_idresultado == $r["idresultado"])?"ativo shadowRightGray":"inativo";
	?>
	    <div class="oTeste <?=$testeativo?>" cbstatus="<?=$r["status"]?>" onclick="CB.go('idresultado=<?=$r["idresultado"]?>')">
	       <table>
		  <tr>
		     <td class="sigla"><?=$r["sigla"]?></td>
		     <td class="quant"><span><?=$r["quant"]?></span></td>
		  </tr>
		  <tr class="testerotulo">
		     <td class="tipoteste"><?=$r["tipoteste"].$r["rotulo"]?></td>
		     <td><span class="<?=$oficial?>"><i class="fa fa-user-secret"></i></span></td>
		  </tr>
	       </table>
	    </div>
	    <div class="webui-popover-content">
	       <table>
		  <tr>
		     <td>Teste:</td>
		     <td class="nowrap"><?=$r["tipoteste"]?></td>
		  </tr>
		  <tr>
		     <td>Quant.:</td>
		     <td><?=$r["quant"]?></td>
		  </tr>
		  <?
		     if($r["secretaria"]){
		     ?>
		  <tr>
		     <td class="nowrap"><i class="fa fa-user-secret"></i>&nbsp;Oficial:</td>
		     <td class="nowrap"><?=$r["secretaria"]?></td>
		  </tr>
		  <?
		     }
		     ?>
	       </table>
	       <?
		  $sqla="select a.valor,a.criadopor,a.criadoem,p.assinateste from _auditoria a,pessoa p 
			     where objeto = 'resultado' and coluna ='status' and idobjeto =".$r["idresultado"]."
			     and a.criadopor = p.usuario";
		  $resa = d::b()->query($sqla);
		  $qtda= mysqli_num_rows($resa);
		  if($qtda>0){

		  ?>
	       <HR>
	       <table style="font-size: 10px">
		  <tr>
		     <td>STATUS</td>
		     <td>ALTERADO POR</td>
		     <td>ALTERADO EM</td>
		  </tr>
		  <?
		     while($ra = mysqli_fetch_assoc($resa)){
				if(($ra['valor']=='ASSINADO' AND $ra['assinateste']=='Y') or $ra['valor']!='ASSINADO' ){
		     ?>
		  <tr>
		     <td><?=$ra['valor']?></td>
		     <td><?=dmahms($ra['criadopor'])?></td>
		     <td><?=$ra['criadoem']?></td>
		  </tr>
		  <?
		     }
		     }
		     ?>
	       </table>
	       <?
		  }
		  ?>	
	    </div>
<?
	}
   }
   
   
   function confPressionamentoTeclas(){
   
   $sqlkp = "select * from gmtkeypress";
   
   $reskp = d::b()->query($sqlkp) or die("A Consulta de configuração de teclas falhou : " . mysql_error() . "<p>SQL: $sql");
   $row = mysqli_fetch_assoc($reskp);
   
   echo "arrKeyConf[".$row["x1"]."] = 1;\n";
   echo "arrKeyConf[".$row["x2"]."] = 2;\n";
   echo "arrKeyConf[".$row["x3"]."] = 3;\n";
   echo "arrKeyConf[".$row["x4"]."] = 4;\n";
   echo "arrKeyConf[".$row["x5"]."] = 5;\n";
   echo "arrKeyConf[".$row["x6"]."] = 6;\n";
   echo "arrKeyConf[".$row["x7"]."] = 7;\n";
   echo "arrKeyConf[".$row["x8"]."] = 8;\n";
   echo "arrKeyConf[".$row["x9"]."] = 9;\n";
   echo "arrKeyConf[".$row["x10"]."] = 10;\n";
   echo "arrKeyConf[".$row["x11"]."] = 11;\n";
   echo "arrKeyConf[".$row["x12"]."] = 12;\n";
   echo "arrKeyConf[".$row["x13"]."] = 13;\n";
   }
   
   /*
   * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
   */
   $pagsql = "SELECT
   		l.idresultado,
		l.versao,
   		l.alerta,
   		l.tipoalerta,
   		l.idamostra,
   		l.idtipoteste,
   		l.quantidade,
   		l.status,
   		l.criadopor,
   		l.criadoem,
   		l.alteradopor,
   		l.alteradoem,
   		l.descritivo,
   		l.q1,
   		l.q2,
   		l.q3,
   		l.q4,
   		l.q5,
   		l.q6,
   		l.q7,
   		l.q8,
   		l.q9,
   		l.q10,
   		l.q11,
   		l.q12,
   		l.q13,
   		l.idt,
   		l.gmt,
   		l.padrao,
   		l.var,
   		t.tipoteste,
   		t.sigla,
   		t.tipogmt,
   		t.tipobact,
   		l.positividade,
   		t.tipoespecial,
   		t.tiporelatorio,
   		l.idtecnico
   	FROM
   		resultado l
	JOIN
		vwtipoteste t ON l.idtipoteste = t.idtipoteste
   	WHERE
   		l.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
   		AND l.idresultado = #pkid
   	ORDER BY
   		t.tipoteste";
   
   /*
   * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
   */
   require_once("../inc/php/controlevariaveisgetpost.php");
   
   
   $sam = "select
   		a.idamostra,				
   		a.idregistro,
   		a.idunidade,
   		a.dataamostra,
   		ifnull(p.nomecurto, p.nome) as nome,
   		p.idpessoa,
   		sta.subtipoamostra,
   		a.idade,
   		a.tipoidade,
   		a.exercicio
   	from
   		amostra a,
   		pessoa p,
   		subtipoamostra sta
   	where
   		a.idpessoa = p.idpessoa
   		and a.idsubtipoamostra = sta.idsubtipoamostra
   		and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." 
   		and a.idamostra = " . $_1_u_resultado_idamostra;
   
   $resam = d::b()->query($sam) or die("Falha consultando amostra: " . mysqli_error() . "<p>SQL: ".$sam);
   $ram = mysqli_fetch_assoc($resam);
   
   $modamostra = getModuloAmostraPadrao($ram["idunidade"]);
   
   
   
   
   ?>
<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-body">
			<table id="amostra" style="width: 100%;">
			<tr>
				<td><strong>Registro:</strong></td>
				<td id="cabRegistro" cbidamostra="<?=$ram["idamostra"]?>">
					<label class='alert-warning'><?=$ram["idregistro"]?>
						<a title="Abrir Amostra" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=<?=$modamostra?>&idamostra=<?=$ram["idamostra"]?>" target="_blank"></a>
					</label>
				</td>
				<td><strong>ID Teste:</strong></td>
				<td >
					<label class='alert-warning'>
					    <?=$_1_u_resultado_idresultado?>.<?=$_1_u_resultado_versao?>
					</label>
				</td>
				<td style="width: 30px;">Cliente:</td>
				<td class="inputreadonly" nowrap style="padding:6px;"><?=$ram["nome"]?></td>
				<td>Amostra:</td>
				<td class="inputreadonly" style="padding:6px;"><?=$ram["subtipoamostra"]?></td>
                                <td>
                                    <a title="Imprimir Cliente Amostra." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/impclienteamostra.php?acao=u&idamostra=<?=$ram["idamostra"]?>')"></a>
                                </td>
			</tr>
			</table>
		</div>
	</div>
</div>
<div class="col-md-2">
    <?
      listaTestes();
    ?>
</div>
<div class="col-md-10">
   <div class="panel panel-default">
      <div class="panel-body">
         <style>
            .diveditor {
		border: 1px solid gray;
		background-color: white;
		color: black;
		font-family: Arial,Verdana,sans-serif;
		font-size: 10pt;
		font-weight: normal;
		width: 800px;
		height: 256px;
		word-wrap: break-word;
		overflow: auto;
		padding: 5px;
            }
         </style>
         <?
            //die($_1_u_resultado_idresultado);
            if(!empty($_1_u_resultado_idresultado)){
            	$sql = "SELECT
			    l.idresultado,
			    l.versao,
			    l.alerta,
			    l.tipoalerta,
			    l.idamostra,
			    l.idtipoteste,
			    l.quantidade,
			    l.status,
			    l.criadopor,
			    dmahms(l.criadoem) criadoem,
			    l.alteradopor,
			    dmahms(l.alteradoem) alteradoem,
			    l.descritivo,
			    l.q1,
			    l.q2,
			    l.q3,
			    l.q4,
			    l.q5,
			    l.q6,
			    l.q7,
			    l.q8,
			    l.q9,
			    l.q10,
			    l.q11,
			    l.q12,
			    l.q13,
			    l.idt,
			    l.gmt,
			    l.padrao,
			    l.var,
			    t.tipoteste,
			    t.sigla,
			    t.tipogmt,
			    t.tipobact,
			    l.positividade,
			    t.tipoespecial,
			    t.tiporelatorio,
			    l.idtecnico,
			    l.conformidade,
			    l.resultadocertanalise,
			    l.idservicoensaio,
			    p.modelo,
			    p.modo,
			    p.tipogmt,
			    l.tipokit
            			
            		FROM
			    resultado l
            		JOIN
			    vwtipoteste t ON l.idtipoteste = t.idtipoteste
            		LEFT JOIN
			    prodserv p ON l.idtipoteste = p.idprodserv
            			
            		WHERE
            			l.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            			AND l.idresultado = " . $_1_u_resultado_idresultado . "
            		ORDER BY
            			t.tipoteste";
            	//echo $sql;
            
            	$res = mysql_query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");
            	$row = mysql_fetch_array($res);
            	$_1_u_resultado_idresultado	= $row["idresultado"];
		$_1_u_resultado_versao		= $row["versao"];
            	$_1_u_resultado_alerta		= $row["alerta"];
            	$_1_u_resultado_tipoalerta	= $row["tipoalerta"];
            	$_1_u_resultado_descritivo	= $row["descritivo"];
            	$_1_u_resultado_q1		= $row["q1"];
            	$_1_u_resultado_q2		= $row["q2"];
            	$_1_u_resultado_q3		= $row["q3"];
            	$_1_u_resultado_q4		= $row["q4"];
            	$_1_u_resultado_q5		= $row["q5"];
            	$_1_u_resultado_q6		= $row["q6"];
            	$_1_u_resultado_q7		= $row["q7"];
            	$_1_u_resultado_q8		= $row["q8"];
            	$_1_u_resultado_q9		= $row["q9"];
            	$_1_u_resultado_q10		= $row["q10"];
            	$_1_u_resultado_q11		= $row["q11"];
            	$_1_u_resultado_q12		= $row["q12"];
            	$_1_u_resultado_q13		= $row["q13"];
            	$_1_u_resultado_gmt		= $row["gmt"];
            	$_1_u_resultado_idt		= $row["idt"];
            	$_1_u_resultado_var		= $row["var"];
            	$_1_u_resultado_tipoteste	= $row["tipoteste"];
            	$_1_u_resultado_tipogmt		= $row["tipogmt"];
            	$_1_u_resultado_tipobact	= $row["tipobact"];
            	$_1_u_resultado_criadopor	= $row["criadopor"];
            	$_1_u_resultado_criadoem	= $row["criadoem"];
            	$_1_u_resultado_alteradopor	= $row["alteradopor"];
            	$_1_u_resultado_alteradoem	= $row["alteradoem"];
            	$_1_u_resultado_positividade	= $row["positividade"];
            	$_1_u_resultado_tipoespecial	= $row["tipoespecial"];
            	$_1_u_resultado_quantidade	= $row["quantidade"];
            	$_1_u_resultado_tiporelatorio	= $row["tiporelatorio"];
            	$_1_u_resultado_idtipoteste	= $row["idtipoteste"];
            	$_1_u_resultado_idtecnico	= $row["idtecnico"];
            	$_1_u_resultado_conformidade	= $row["conformidade"];
		$_1_u_resultado_resultadocertanalise=$row["resultadocertanalise"];
            	$_1_u_resultado_idservicoensaio	= $row["idservicoensaio"];
            	$_1_u_resultado_modelo		= $row["modelo"];
		$_1_u_resultado_modo		= $row["modo"];
            	$_1_u_resultado_tipogmt		= $row["tipogmt"];
		$_1_u_resultado_tipokit		=$row["tipokit"];
		$tipogmt			= $row["titulo"];	
            	$qtx				= $row["quantidade"];
            	
            	$sql = "SELECT (valor*1) as valor FROM prodservtipoopcao where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." and idprodserv = '".$_1_u_resultado_idtipoteste."' order by valor*1";
            
            	$res = mysql_query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");
            	
            	$i = 1;
            	while($row=mysql_fetch_assoc($res)){
            	    $x[$i] = $row["valor"];
            	    $i++;
            	}
            	
            	$somaor= $_1_u_resultado_q1+$_1_u_resultado_q2+$_1_u_resultado_q3+$_1_u_resultado_q4+$_1_u_resultado_q5+$_1_u_resultado_q6+$_1_u_resultado_q7+$_1_u_resultado_q8+$_1_u_resultado_q9+$_1_u_resultado_q10+$_1_u_resultado_q11+$_1_u_resultado_q12+$_1_u_resultado_q13;
		
		
		/*****************************************/
		/*SE MODO FOR INDIVIDUAL [INICIO]
		/*****************************************/
		if ($_1_u_resultado_modo=="IND"){
		    
		    $sqlind="select * from resultadoindividual i where i.idresultado=".$_1_u_resultado_idresultado;
		    $resind=mysql_query($sqlind) or die (" Erro ao buscar od resultados individuais:".mysql_error." <p>SQL".$sqlind);
		    $qtdind=mysql_num_rows($resind);
		    if($qtdind==0 and $_1_u_resultado_quantidade > 0 ){//inserir quando não tiver orificio para aquele resultado
      	
			//se for de um teste do bioterio verifica se existe numeração
			if(!empty($_1_u_resultado_idservicoensaio)){
			    $sqlindx="select i.* from bioterioind i,servicoensaio s where i.idbioensaio=s.idobjeto and s.tipoobjeto='bioensaio' and s.idservicoensaio=".$_1_u_resultado_idservicoensaio." order by identificacao";
			    $resindx=mysql_query($sqlindx) or die (" Erro ao buscar identificacao:".mysql_error." <p>SQL".$sqlindx);
			    $qtdindx=mysql_num_rows($resindx);
			}else{
			    $qtdindx=0;
			}
			if($qtdindx>0){
			    while($rowindx=mysql_fetch_assoc($resindx)){
				$sqlin="insert into resultadoindividual (
									    idempresa,
									    idresultado,
									    identificacao,												
									    criadopor,
									    criadoem,
									    alteradopor,
									    alteradoem)
									    values(
									    ".$_SESSION["SESSAO"]["IDEMPRESA"]."
									    ,".$_1_u_resultado_idresultado."
									    ,".$rowindx['identificacao']."												
									    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
									    ,now()
									    ,'".$_SESSION["SESSAO"]["USUARIO"]."'
									    ,now()
									    )";
				mysql_query($sqlin) or die("A insercão dos individuos no resultado falhou : " . mysql_error() . "<p>SQL:".$sqlin);

			    }

			}else{

			    for ($z = 1; $z <= $_1_u_resultado_quantidade; $z++) {
				$sqlin="insert into resultadoindividual (
									    idempresa,
									    idresultado,								
									    criadopor,
									    criadoem,
									    alteradopor,
									    alteradoem) 
									    values(
									    ".$_SESSION["SESSAO"]["IDEMPRESA"]."
									    ,".$_1_u_resultado_idresultado."								
									    ,'".$_SESSION["SESSAO"]["USUARIO"]."' 
									    ,now()
									    ,'".$_SESSION["SESSAO"]["USUARIO"]."' 
									    ,now()
									    )";
				mysql_query($sqlin) or die("A insercão dos individuos no resultado falhou : " . mysql_error() . "<p>SQL:".$sqlin);

			    }
			}
		    }elseif($qtdind > $_1_u_resultado_quantidade){// excluir quando a quantidade de orificios for maior que a quantidade do resultado
      	
			$dif=$qtdind-$_1_u_resultado_quantidade;
			for ($d = 1; $d <= $dif; $d++) {
				$sqld="delete from resultadoindividual 
				where idresultado=".$_1_u_resultado_idresultado." order by idresultadoindividual desc limit 1";
				mysql_query($sqld) or die("Erro ao deletar orificios a mais: " . mysql_error() . "<p>SQL:".$sqld);
			}
      
		    }elseif($qtdind < $_1_u_resultado_quantidade){//inserir quando a quantidade de orificios for inferior a quantidade do resultado
      
			$dif=$_1_u_resultado_quantidade-$qtdind;
			for ($d = 1; $d <= $dif; $d++) {
			    $sqlin1="insert into resultadoindividual (
									idempresa,
									idresultado,
									criadopor,
									criadoem,
									alteradopor,
									alteradoem)
									values(
									".$_SESSION["SESSAO"]["IDEMPRESA"]."
									,".$_1_u_resultado_idresultado."
									,'".$_SESSION["SESSAO"]["USUARIO"]."'
									,now()
									,'".$_SESSION["SESSAO"]["USUARIO"]."'
									,now()
									)";
			    mysql_query($sqlin1) or die("A insercão dos individuos faltantes no resultado falhou : " . mysql_error() . "<p>SQL:".$sqlin1);
			}
      
		    }
		}
		
		/*****************************************/
		/*SE MODO FOR INDIVIDUAL [FIM]
		/*****************************************/
            	
            ?>
	    <h4 class='nowrap'><span class="cinza">Resultado para </span>
		<span class="negrito"><?=$_1_u_resultado_tipoteste?> 
		    <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?=$_1_u_resultado_idtipoteste?>" target="_blank"></a>
		</span>
	    </h4>
	    <hr>
	    <input type="hidden" name="_1_u_resultado_idresultado" value="<?=$_1_u_resultado_idresultado?>">
	    
	    <?
	    /*****************************************/
	    /*SE MODO FOR SELETIVO AGRUPADO [INICIO]
	    /*****************************************/
	    if($_1_u_resultado_modelo=="SELETIVO" && $_1_u_resultado_modo=="AGRUP"){
            //Se for tipo GMT
		$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
            ?>
	    <br>
	    <div class="row row-eq-height">
	       <div class="col-md-2">
		  <div class="row" style="margin: 3px;border: 1px solid #ccc;font-size: 10px; text-transform: uppercase; background-color: #ccc;">
		     <div class="col-md-12">
			<font class="graybold bold">Selecionar Ação:</font>
		     </div>
		     <div class="col-md-12">
			<font class="graybold"><input type="radio" value="+" name="xoper" class="tablehidden" onclick="setoper('+');" checked> Adicionar</font>
		     </div>
		     <div class="col-md-12">
			<font class="graybold"><input type="radio" value="-" name="xoper" class="tablehidden" onclick="setoper('-');"> Subtrair</font>
		     </div>
		  </div>
	       </div>
	       <div class="col-md-5">
		  <div id="tbOrificios">
		     <div class="row">
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q1" value="<?=$_1_u_resultado_q1?>" size="3" id="k_1" <?=$keyreadonly?>> x <span><?=$x[1]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q6" value="<?=$_1_u_resultado_q6?>" size="3" id="k_6" <?=$keyreadonly?>> x <span><?=$x[6]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q11" value="<?=$_1_u_resultado_q11?>" size="3" id="k_11" <?=$keyreadonly?>> x <span><?=$x[11]?></span>
			   </div>
			</div>
		     </div>
		     <div class="row">
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q2" value="<?=$_1_u_resultado_q2?>" size="3" id="k_2" <?=$keyreadonly?>> x <span><?=$x[2]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q7" value="<?=$_1_u_resultado_q7?>" size="3" id="k_7" <?=$keyreadonly?>> x <span><?=$x[7]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q12" value="<?=$_1_u_resultado_q12?>" size="3" id="k_12" <?=$keyreadonly?>> x <span><?=$x[12]?></span>
			   </div>
			</div>
		     </div>
		     <div class="row">
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q3" value="<?=$_1_u_resultado_q3?>" size="3" id="k_3" <?=$keyreadonly?>> x <span><?=$x[3]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q8" value="<?=$_1_u_resultado_q8?>" size="3" id="k_8" <?=$keyreadonly?>> x <span><?=$x[8]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q13" value="<?=$_1_u_resultado_q13?>" size="3" id="k_13" <?=$keyreadonly?>> x <span><?=$x[13]?></span>
			   </div>
			</div>
		     </div>
		     <div class="row">
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q4" value="<?=$_1_u_resultado_q4?>" size="3" id="k_4" <?=$keyreadonly?>> x <span><?=$x[4]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q9" value="<?=$_1_u_resultado_q9?>" size="3" id="k_9" <?=$keyreadonly?>> x <span><?=$x[9]?></span>
			   </div>
			</div>
		     </div>
		     <div class="row">
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q5" value="<?=$_1_u_resultado_q5?>" size="3" id="k_5" <?=$keyreadonly?>> x <span><?=$x[5]?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div>
			      <input type="text" name="_1_u_resultado_q10" value="<?=$_1_u_resultado_q10?>" size="3" id="k_10" <?=$keyreadonly?>> x <span><?=$x[10]?></span>
			   </div>
			</div>
			<div class="col-md-12">
			   <div style="padding:8px !important; background: #faebcc">
			      Total:  <span style="font-size: 12pt; color: red;" id="somaorificios"><?=$somaor?></span>
			   </div>
			</div>
		     </div>
		     <div class="row">
			<div class="col-sm-4">
			   <div style="padding:8px !important; background-color: #709ABE; ">
			      GTM: <span style="line-height: 20px;"><?=$_1_u_resultado_gmt?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div style="padding:8px !important;background-color: #709ABE">
			      IDT: <span  style="line-height: 20px;"><?=$_1_u_resultado_idt?></span>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div style="padding:8px !important; background-color: #709ABE">
			      CV: <span style="line-height: 20px;"><?=$_1_u_resultado_var?></span>
			   </div>
			</div>
		     </div>
		  </div>
	       </div>
	    </div>
      
      <?
	/*****************************************/
	/*SE MODO FOR SELETIVO AGRUPADO [FIM]
	/*****************************************/
        /*****************************************/
	/*SE MODO FOR SELETIVO INDIVIDUAL [INICIO]
	/*****************************************/
         }elseif($_1_u_resultado_modelo == "SELETIVO" and $_1_u_resultado_modo == "IND"){//Se for tipo GMT
   	$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
   /*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
   		$pesagem="Y";
   	}else{
   		$pesagem="N";
   	}	 
    */
   	?>
<br>	
<div align="center">
   <?
      
      	
      $sqlind="select * from resultadoindividual i where i.idresultado=".$_1_u_resultado_idresultado;
      $resind=mysql_query($sqlind) or die (" Erro ao buscar od resultados individuais 2:".mysql_error." <p>SQL".$sqlind);
      $x=0;
      $i=2;
      $tipoespecial= substr($_1_u_resultado_tipoespecial, 0, -4);
      $tab=0;
      	while($rowind=mysql_fetch_assoc($resind)){
      		$i=$i+1;
      		$tab=$tab+2;
      		
      		if(($x % 7) == 0){
      ?>
   <div class="col-sm-4">
      <div class="interna3">
         <div class="row">
            <div class="col-sm-4 text-right">
               ID
            </div>
            
            <div class="col-sm-4">
               Orif.
            </div>
            <div class="col-sm-4">
               Valor
            </div>
            
         </div>
      </div>
      <?
         }
         $x=$x+1;
         ?>
      <div class="row divdescritivo">
         <div class="col-sm-12">
            <div class="interna2 ">
               <div class="col-sm-1">
                  <span style="line-height: 30px;"><?=$x?></span>
               </div>
               <div class="col-sm-3">
                  <div class="interna">
                     <input type="hidden" name="_<?=$i?>_u_resultadoindividual_idresultadoindividual"  value="<?=$rowind['idresultadoindividual']?>" size="3" >
                     <input tabindex="<?=$tab?>" type="text" title="Identificação"  placeholder="ID" name="_<?=$i?>_u_resultadoindividual_identificacao" value="<?=$rowind['identificacao']?>" size="10" >
                  </div>
               </div>
               
               <div class="col-sm-3">
                  <div class="interna">
                     <input tabindex="<?=$tab+1?>" type="text" title="tecla" id="tecla<?=$i?>"  onchange="setresultadoind(<?=$i?>);" name="_<?=$i?>_u_resultadoindividual_valor" value="<?=$rowind['valor']?>" size="1" >
                     <input  type="hidden" title="tecla" id="resultado<?=$i?>"   name="_<?=$i?>_u_resultadoindividual_resultado" value="<?=$rowind['resultado']?>" size="1" >
                     <input  type="hidden" name="tipoespecial" value="<?=$_1_u_resultado_tipoespecial?>" size="1" >
                     <input  type="hidden" name="tipoteste" value="<?=$tipoespecial?>" size="1" >
                  </div>
               </div>
               <div class="col-sm-3">
                  <div class="interna">
                     <select class="seltit" id="rotulo<?=$i?>" title="Resultado" <?=$disabled2?>  name="resultado" vnulo disabled="disabled" style="background: #ddd;">
                        <option value=""></option>
                        <?fillselect("SELECT @i:=@i+1 AS num, (valor*1) as valor FROM prodservtipoopcao, (SELECT @i:=0) AS foo where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." and idprodserv = '".$_1_u_resultado_idtipoteste."' order by valor*1"
                           ,$rowind['resultado']);?>		
                     </select>
                  </div>
               </div>
               

            </div>
         </div>
      </div>
      <?
         if(($x % 7) == 0){
         ?>
   </div>
   <?
      }
      }
      if(($x % 7) != 0){
      ?>
</div>
</div>
<?
   }
   ?>
<?
   $stralerta = "";
   if( $_1_u_resultado_alerta=="Y"){
   	$stralerta ="checked";
   	$divdisplay="block";
   }else{
   	$divdisplay="none";
   }
   ?>
<div class="col-sm-4">
 <?if($_1_u_resultado_tipogmt!="N/A"){?>
   <div class="interna3" style="background-color:#709ABE;height: 45px;">
      <div class="row">
         <div class="col-sm-12">
            <div style="padding-left: 6px;padding-right: 6px;">
               GMT <span style="float:right"><?=$_1_u_resultado_gmt?></span>
            </div>
         </div>
      </div>
   </div>
   <?}?>
   <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
      <div class="row">
         <div class="col-sm-12">
            <div style="padding-left: 6px;padding-right: 6px;line-height: 30px">
               Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?=$stralerta?> onClick="alertateste(<?=$_1_u_resultado_idresultado?>);"></span>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?
    /*****************************************/
    /*SE MODO FOR SELETIVO INDIVIDUAL [FIM]
    /*****************************************/
    /*****************************************/
    /*SE MODO FOR DESCRITIVO AGRUPADO [INICIO]
    /*****************************************/
   }elseif($_1_u_resultado_modelo=="DESCRITIVO" and $_1_u_resultado_modo == "AGRUP"){
   	
   	if(!empty($_1_u_resultado_idtipoteste) and empty($_1_u_resultado_descritivo)){
   		$sqlt="select textoinclusaores from prodserv where idprodserv =".$_1_u_resultado_idtipoteste;
   		$rest=mysql_query($sqlt);
   		$rowt=mysql_fetch_assoc($rest);
   		$_1_u_resultado_descritivo = $rowt['textoinclusaores'];
   	}
   
   ?> 
<div align="center" style="padding: 0px 18px;">
   <div class="row">
      <div class="col-sm-8">
	   <div style="background-color: #ccc;padding: 4px;">
            <div class="row">
               <div class="col-sm-12">
         <label id="lbaviso" class="idbox" style="display: none;"></label>
         <div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left"><?=$_1_u_resultado_descritivo?></div>
         <textarea 
            style="display: none; text-align: left"
            name="_1_u_resultado_descritivo"><?=$_1_u_resultado_descritivo?></textarea>
      </div></div></div></div>
      <div class="col-sm-4">
         <div style="background-color: #ccc;padding: 4px;">
            <div class="row">
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
                  <div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="text" size="2" value="<?=$_1_u_resultado_positividade?>" style="float:left; width: 80px"></div>
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Alerta:</span></div>
                  <div class="col-sm-8"><input type="checkbox" id="chAlerta" <?=$stralerta?> onClick="alertateste(<?=$_1_u_resultado_idresultado?>);" style="float:left; "></div>
                  <?
                     $stralerta = "";
                     if( $_1_u_resultado_alerta=="Y"){
                     	$stralerta ="checked";
                     	$divdisplay="block";
                     }else{
                     	$divdisplay="none";
                     }
                     ?>		    
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?=$divdisplay?>;">Tipo Alerta:</span></div>
                  <div class="col-sm-8">
                     <div id="dTipoAlerta" style=" display: <?=$divdisplay?>;">
                        <select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?=$_1_u_resultado_idresultado?>);">
                           <option value=""></option>
                           <?fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);?>
                        </select>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?
            if($modamostra=='amostracqd' or $modamostra=='amostraprod'){//Controle de qualidade
            ?>			    
         <div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
            <div class="row">
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
                  <div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?=$_1_u_resultado_resultadocertanalise?>"></div>
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
                  <div class="col-sm-8">
                     <select name="_1_u_resultado_conformidade">
                        <option value=""></option>
                        <?fillselect(array('CONFORME'=>'Conforme'
                           ,'NAO CONFORME'=>'Não Conforme'
                           ,'NAO SE APLICA'=>'Não se Aplica'
                           ),$_1_u_resultado_conformidade);?>
                     </select>
                  </div>
               </div>
            </div>
         </div>
         <?
            }
            ?>			   
      </div>
   </div>
</div>
<?
    /*****************************************/
    /*SE MODO FOR DESCRITIVO AGRUPADO [FIM]
    /*****************************************/
    /*****************************************/
    /*SE MODO FOR DESCRITIVO INDIVIDUAL [INICIO]
    /*****************************************/
   }elseif($_1_u_resultado_modelo == "DESCRITIVO" and $_1_u_resultado_modo == "IND"
		 
         	//$_1_u_resultado_tipoespecial=="BRONQUITE IND" or $_1_u_resultado_tipoespecial=="NEWCASTLE IND" or $_1_u_resultado_tipoespecial=="GUMBORO IND" 
         	//		or $_1_u_resultado_tipoespecial=="REOVIRUS IND" or $_1_u_resultado_tipoespecial=="PNEUMOVIRUS IND" or $_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"
         	//		or $_1_u_resultado_tipoespecial=="DESCRITIVO IND"
         			){//Se for tipo GMT
         	$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
         /*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
         		$pesagem="Y";
         	}else{
         		$pesagem="N";
         	}	 
          */
         	?>
     	
      <div align="center">
         <?
            
            	
            $sqlind="select * from resultadoindividual i where i.idresultado=".$_1_u_resultado_idresultado;
            $resind=mysql_query($sqlind) or die (" Erro ao buscar od resultados individuais 2:".mysql_error." <p>SQL".$sqlind);
            $x=0;
            $i=2;
            $tipoespecial= substr($_1_u_resultado_tipoespecial, 0, -4);
            $tab=0;
            	while($rowind=mysql_fetch_assoc($resind)){
            		$i=$i+1;
            		$tab=$tab+2;
            		
            		if(($x % 7) == 0){
            ?>
	  <div class="col-sm-4" style="padding: 0px;">
            <div class="interna3">
               <div class="row">
                  <div class="col-sm-4 text-right">
                     ID
                  </div>
                  
                  <div class="col-sm-7">
                     <?=(($_1_u_resultado_tipogmt=="N/A")?"RESULTADO":"VALOR")?>
                  </div>
                  
               </div>
            </div>
            <?
               }
               $x=$x+1;
               ?>
            <div class="row divdescritivo">
               <div class="col-sm-12">
                  <div class="interna2 ">
                     <div class="col-sm-1">
                        <span style="line-height: 30px;"><?=$x?></span>
                     </div>
                     <div class="col-sm-3">
                        <div class="interna">
                           <input type="hidden" name="_<?=$i?>_u_resultadoindividual_idresultadoindividual"  value="<?=$rowind['idresultadoindividual']?>" size="3" >
                           <input tabindex="<?=$tab?>" type="text" title="Identificação"  placeholder="ID" name="_<?=$i?>_u_resultadoindividual_identificacao" value="<?=$rowind['identificacao']?>" size="10" >
                        </div>
                     </div>
                  
                     <div class="col-sm-8">
                        <div class="interna">
                           <input  type="hidden" name="tipoespecial" value="<?=$_1_u_resultado_tipoespecial?>" size="1" >
                           <input style="width: 100% !important"   type="text" title="tecla" id="resultado<?=$i?>"   name="_<?=$i?>_u_resultadoindividual_resultado" value="<?=$rowind['resultado']?>" size="20"  <?=(($_1_u_resultado_tipogmt=="N/A")?" class='text-left' ":"vdecimal")?>>
                        </div>
                     </div>
                  
                  </div>
               </div>
            </div>
            <?
               if(($x % 7) == 0){
               ?>
         </div>
         <?
            }
            }
            if(($x % 7) != 0){
            ?>
      </div>
   </div>
   <?
      }
      ?>
   <?
      $stralerta = "";
      if( $_1_u_resultado_alerta=="Y"){
      	$stralerta ="checked";
      	$divdisplay="block";
      }else{
      	$divdisplay="none";
      }
      ?>
<div class="col-sm-4" style="padding:0px;">
      <?if($_1_u_resultado_tipogmt!="N/A"){?>
      <div class="interna3" style="background-color:#709ABE;height: 45px;">
         <div class="row">
            <div class="col-sm-12">
               <div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
                  GMT <span style="float:right"><?=$_1_u_resultado_gmt?></span>
               </div>
            </div>
         </div>
      </div>
      <?}?>
      <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
         <div class="row">
            <div class="col-sm-12">
               <div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
                  Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?=$stralerta?> onClick="alertateste(<?=$_1_u_resultado_idresultado?>);"></span>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?
    /*****************************************/
    /*SE MODO FOR DESCRITIVO INDIVIDUAL [FIM]
    /*****************************************/
    /*****************************************/
    /*SE MODO FOR DROP AGRUPADO [INICIO]
    /*****************************************/
   }elseif($_1_u_resultado_modelo=="DROP" and $_1_u_resultado_modo == "AGRUP"){

   ?> 
<div align="center" style="padding: 0px 16px;">

    
    
   <div class="row">
       
	   
     <div class="col-sm-8">
	 <div style="background-color: #ccc;padding: 4px;">
	     <div class="row">
               <div class="col-sm-12">
                  <div class="interna">
		      
		      <div class="row">
               <div class="col-sm-2">
		   
		   Resultado:
                    
		      
		      </div>
			  <div class="col-sm-10">
			       <select class="seltit" id="rotulo<?=$i?>" title="Resultado" <?=$disabled2?>  name="_<?=$i?>_1_u_resultado_descritivo" vnulo style="width:94% !important">
                        <option value=""></option>
                        <?fillselect("SELECT valor AS num, 1*valor as valor FROM prodservtipoopcao where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." and idprodserv = '".$_1_u_resultado_idtipoteste."' order by 1*valor"
                           ,$_1_u_resultado_descritivo);?>		
                     </select>
			  </div>
               </div>    
		 </div>
               </div>    
                     
                  </div>
               </div></div>
      <div class="col-sm-4">
         <div style="background-color: #ccc;padding: 4px;">
            <div class="row">
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
                  <div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="text" size="2" value="<?=$_1_u_resultado_positividade?>" style="float:left; width: 80px"></div>
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Alerta:</span></div>
                  <div class="col-sm-8"><input type="checkbox" id="chAlerta" <?=$stralerta?> onClick="alertateste(<?=$_1_u_resultado_idresultado?>);" style="float:left; "></div>
                  <?
                     $stralerta = "";
                     if( $_1_u_resultado_alerta=="Y"){
                     	$stralerta ="checked";
                     	$divdisplay="block";
                     }else{
                     	$divdisplay="none";
                     }
                     ?>		    
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?=$divdisplay?>;">Tipo Alerta:</span></div>
                  <div class="col-sm-8">
                     <div id="dTipoAlerta" style=" display: <?=$divdisplay?>;">
                        <select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?=$_1_u_resultado_idresultado?>);">
                           <option value=""></option>
                           <?fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);?>
                        </select>
                     </div>
                  </div>
               </div>
            </div>
         </div>
	  </div>
         <?
            if($modamostra=='amostracqd' or $modamostra=='amostraprod'){//Controle de qualidade
            ?>			    
         <div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
            <div class="row">
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
                  <div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?=$_1_u_resultado_resultadocertanalise?>"></div>
               </div>
               <div class="col-sm-12">
                  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
                  <div class="col-sm-8">
                     <select name="_1_u_resultado_conformidade">
                        <option value=""></option>
                        <?fillselect(array('CONFORME'=>'Conforme'
                           ,'NAO CONFORME'=>'Não Conforme'
                           ,'NAO SE APLICA'=>'Não se Aplica'
                           ),$_1_u_resultado_conformidade);?>
                     </select>
                  </div>
               </div>
            </div>
         </div>
         <?
            }
            ?>			   
      </div>
</div>
<?
    /*****************************************/
    /*SE MODO FOR DROP AGRUPADO [FIM]
    /*****************************************/
    /*****************************************/
    /*SE MODO FOR DROP INDIVIDUAL [INICIO]
    /*****************************************/
   }elseif($_1_u_resultado_modelo == "DROP" and $_1_u_resultado_modo == "IND"
		 
         	//$_1_u_resultado_tipoespecial=="BRONQUITE IND" or $_1_u_resultado_tipoespecial=="NEWCASTLE IND" or $_1_u_resultado_tipoespecial=="GUMBORO IND" 
         	//		or $_1_u_resultado_tipoespecial=="REOVIRUS IND" or $_1_u_resultado_tipoespecial=="PNEUMOVIRUS IND" or $_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"
         	//		or $_1_u_resultado_tipoespecial=="DESCRITIVO IND"
         			){//Se for tipo GMT
         	$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
         /*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
         		$pesagem="Y";
         	}else{
         		$pesagem="N";
         	}	 
          */
         	?>
      <br>	
      <div align="center">
         <?
            
            	
            $sqlind="select * from resultadoindividual i where i.idresultado=".$_1_u_resultado_idresultado;
            $resind=mysql_query($sqlind) or die (" Erro ao buscar od resultados individuais 2:".mysql_error." <p>SQL".$sqlind);
            $x=0;
            $i=2;
            $tipoespecial= substr($_1_u_resultado_tipoespecial, 0, -4);
            $tab=0;
            	while($rowind=mysql_fetch_assoc($resind)){
            		$i=$i+1;
            		$tab=$tab+2;
            		
            		if(($x % 7) == 0){
            ?>
         <div class="col-sm-4">
            <div class="interna3">
               <div class="row">
                  <div class="col-sm-4 text-right">
                     ID
                  </div>
                  
                  <div class="col-sm-7">
                     <?=(($_1_u_resultado_tipogmt=="N/A")?"RESULTADO":"VALOR")?>
                  </div>
                  
               </div>
            </div>
            <?
               }
               $x=$x+1;
               ?>
            <div class="row divdescritivo">
               <div class="col-sm-12">
                  <div class="interna2 ">
                     <div class="col-sm-1">
                        <span style="line-height: 30px;"><?=$x?></span>
                     </div>
                     <div class="col-sm-3">
                        <div class="interna">
                           <input type="hidden" name="_<?=$i?>_u_resultadoindividual_idresultadoindividual"  value="<?=$rowind['idresultadoindividual']?>" size="3" >
                           <input tabindex="<?=$tab?>" type="text" title="Identificação"  placeholder="ID" name="_<?=$i?>_u_resultadoindividual_identificacao" value="<?=$rowind['identificacao']?>" size="10" >
                        </div>
                     </div>
                  
		<div class="col-sm-8">
                  <div class="interna">
                     <select class="seltit" id="rotulo<?=$i?>" title="Resultado" <?=$disabled2?>  name="_<?=$i?>_u_resultadoindividual_resultado" vnulo style="width:100% !important">
                        <option value=""></option>
                        <?fillselect("SELECT valor AS num, valor FROM prodservtipoopcao  where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." and idprodserv = '".$_1_u_resultado_idtipoteste."' order by valor*1"
                           ,$rowind['resultado']);?>		
                     </select>
		      
		      
		     
                     
                  </div>
               </div>
          
                  
                  </div>
               </div>
            </div>
            <?
               if(($x % 7) == 0){
               ?>
         </div>
         <?
            }
            }
            if(($x % 7) != 0){
            ?>
      </div>
   </div>
   <?
      }
      ?>
   <?
      $stralerta = "";
      if( $_1_u_resultado_alerta=="Y"){
      	$stralerta ="checked";
      	$divdisplay="block";
      }else{
      	$divdisplay="none";
      }
      ?>
   <div class="col-sm-4">
      <?if($_1_u_resultado_tipogmt!="N/A"){?>
      <div class="interna3" style="background-color:#709ABE;height: 45px;">
         <div class="row">
            <div class="col-sm-12">
               <div style="padding-left: 6px;padding-right: 6px; text-align: left">
                  GMT <span style="float:right"><?=$_1_u_resultado_gmt?></span>
               </div>
            </div>
         </div>
      </div>
      <?}?>
      <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
         <div class="row">
            <div class="col-sm-12">
               <div style="padding-left: 6px;padding-right: 6px;line-height: 30px; text-align: left">
                  Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?=$stralerta?> onClick="alertateste(<?=$_1_u_resultado_idresultado?>);"></span>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?
    /*****************************************/
    /*SE MODO FOR DROP INDIVIDUAL [FIM]
    /*****************************************/
    /*****************************************/
    /*SE MODO FOR UPLOAD [INICIO]
    /*****************************************/
   }elseif($_1_u_resultado_modelo=="UPLOAD"){
   	$strsql = "SELECT * FROM resultadoelisa where idresultado = ". $_1_u_resultado_idresultado . " and  idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and status = 'A' order by idresultadoelisa";
   	$result = d::b()->query($strsql) or die("A Consulta  dos resultados elisa falhou : " . mysqli_error() . "<p>SQL: $strsql");
   	$iresult  = mysqli_num_rows($result);
   	if ($iresult > 0){
   ?>
<div class="row" style="margin: 0px;">
   <div class="col-sm-6">
      <div class="row">
         <div class="interna2" style="background: #bbb">
            <div class="col-sm-6">
               <div class="col-sm-3 text-right">
                  &nbsp;
               </div>
               <div class="col-sm-3 text-right">
                  Wells
               </div>
               <div class="col-sm-3 text-right">
                  O.D.
               </div>
               <div class="col-sm-3 text-right">
                  S/P
               </div>
            </div>
            <div class="col-sm-6">
               <div class="col-sm-3 text-right">
                  S/N
               </div>
               <div class="col-sm-3 text-right">
                  Titer
               </div>
               <div class="col-sm-3 text-right">
                  Group
               </div>
               <div class="col-sm-3  text-right">
                  Result
               </div>
            </div>
         </div>
      </div>
      <?
         while ($row = mysqli_fetch_assoc($result)){
         ?>
      <div class="row">
         <div class="interna2" style="height:36px">
            <div class="col-sm-6">
               <div class="col-sm-3 text-right">
                  <?=$row['nome']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['well']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['OD']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['SP']?>
               </div>
            </div>
            <div class="col-sm-6">
               <div class="col-sm-3 text-right">
                  <?=$row['SN']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['titer']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['grupo']?>
               </div>
               <div class="col-sm-3 text-right">
                  <?=$row['result']?>
               </div>
            </div>
         </div>
      </div>
      <?
         }?>
   </div>
</div>
<br>
<?
   }
   $sql1="select concat(a.idregistro,p.codprodserv) as nomearqui,concat(a.idregistro,p.codprodserv) as nomearquivortf
       from resultado r,amostra a,prodserv p
       where p.idprodserv = r.idtipoteste
       and  a.idamostra = r.idamostra
       and r.idresultado =".$_1_u_resultado_idresultado;
   $res1=d::b()->query($sql1) or die("Erro ao buscar o nome do arquivo");
   $row1=mysqli_fetch_assoc($res1);
   ?>
<div class="row">
   <div class="col-md-2 nowrap">Nome Arquivo Padrão:</div>
   <div class="col-md-4">Nome Arquivo Padrão: "<?=$row1['nomearquivortf']?>.txt (AFFINITECK-BIOCHEK) ou RTF (IDEXX))" </div>
</div>
<div class="row">
   <div class="col-md-2 nowrap">Selecione o kit:</div>
   <div class="col-md-4"><select name="tipokit">			
      <?fillselect("select 'IDEXX','IDEXX' union select 'AFFINITECK','AFFINITECK' union select 'BIOCHEK','BIOCHEK'",$tipokit);?>		</select>
   </div>
</div>
<div class="cbupload" id="resultadoelisa" title="Clique ou arraste o arquivo Elisa para cá." style="width:50%;height:100%;">		    
   <i class="fa fa-cloud-upload fonte18"  ></i>
</div>

<?
   }
    /*****************************************/
    /*SE MODO FOR UPLOAD [FIM]
    /*****************************************/
   ?>
<?
   if($modamostra=='amostraautogenas' and !empty($_1_u_resultado_idresultado)){//diag autogenas
   ?>

<div class="col-sm-4" >
     <div class="interna3" style="background-color:#ccc;height: 45px;">
         <div class="row">
            <div class="col-sm-12">
               <div style="padding-left: 6px;padding-right: 6px;">
   Agente:<i class="fa fa-plus-circle fa-1x verde btn-lg pointer" title="Criar um agente" onclick="inovolote(<?=$_1_u_resultado_idresultado?>)"></i>
   <?//buscar agentes gerados
      $sqll="select p.descr,l.* 
      		from lote l,prodserv p 
      		where p.idprodserv = l.idprodserv 
      		and l.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
      		and l.tipoobjetosolipor='resultado' 
      		and l.idobjetosolipor=".$_1_u_resultado_idresultado;
      //die($sqll);
      $resl = d::b()->query($sqll)or die(mysqli_error(d::b()));
      $qtdrowl=mysqli_num_rows($resl);
      if($qtdrowl>0){
      ?>
   <div id="resultadoagente<?=$_1_u_resultado_idresultado?>" style="display: none">
      <div id="cbModuloResultados" class="col-md-12 zeroauto panel panel-default">
         <table class="table table-hover table-striped table-condensed">
            <tr>
               <td>Lote</td>
               <td>Produto</td>
               <td>Criado por</td>
               <td>Criado em</td>
            </tr>
            <?while($rl=mysqli_fetch_assoc($resl)){?>
            <tr onclick="janelamodal('?_modulo=lote&_acao=u&idlote=<?=$rl['idlote']?>');" >
               <td><?=$rl['partida']?></td>
               <td><?=$rl['descr']?></td>
               <td><?=$rl['criadopor']?></td>
               <td><?=$rl['criadoem']?></td>
            </tr>
            <?}?>
         </table>
      </div>
   </div>
   <i class="fa fa-cubes fa-1x azul btn-lg pointer" title="Agente(s) isolados" onclick="listalote(<?=$_1_u_resultado_idresultado?>)"></i>
   <?}?>
</div></div>
		</div>
	</div>
    </div>

<?
   }
      
      
   ?>
   <div class="panel-body">
    <br>
    <?
    
    $sqlf1 = "select *
			from prodservformula
			where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and idprodserv=".$_1_u_resultado_idtipoteste."
			order by ordem,idprodservformula asc";

	$resf1 = d::b()->query($sqlf1) or die("E ao buscas as fazes do servico \n".mysqli_error(d::b())."\n".$sqlf1);
  
   ?>
    <style>
        .itemestoque{
	Xwidth:100%;
	width:auto;
	display: inline-block;
	text-align: right;
}
    </style>  
    <?
     
    while($rowf1=mysqli_fetch_assoc($resf1)){
        
        $sqlf="select p.descr,i.qtdi,qtdi_exp,i.idprodserv
                from prodserv p,prodservformula f, prodservformulains i 
                  where p.idprodserv = i.idprodserv
                and f.idprodserv = ".$_1_u_resultado_idtipoteste."
                and i.idprodservformula = f.idprodservformula
                and f.idprodservformula=".$rowf1["idprodservformula"]."
                and f.status = 'ATIVO'";           

        $resf =  d::b()->query($sqlf) or die("Erro ao buscar produtos  do teste:".mysqli_error(d::b())."sql=".$sqlf);    

        $qtdf= mysqli_num_rows($resf);

        if($qtdf>0){
           
            $sqlpf="select * from resultadoprodservformula where idresultado = ".$_1_u_resultado_idresultado;
            $respf=d::b()->query($sqlpf) or die("Erro 1 ao buscar resultado prodservformula  do teste:".mysqli_error(d::b())."sql=".$sqlpf);
            $qtdmostra1= mysqli_num_rows($respf);
        
            if($qtdmostra1<1 and ($_1_u_resultado_status!='ASSINADO' OR $_1_u_resultado_status!='FECHADO' )){
                $sqlin=" INSERT INTO resultadoprodservformula
                        (idempresa,idresultado,idprodservformula,criadopor,criadoem)
                        VALUES
                        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_1_u_resultado_idresultado.",".$rowf1["idprodservformula"].",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

                $resin =  d::b()->query($sqlin) or die("Erro ao a primeira fase do teste:".mysqli_error(d::b())."sql=".$sqlin);
            } 
    ?>
	<div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">Insumos do teste Fase:<?=$rowf1["ordem"]?>
                        <?
                        $sqlpf="select * from resultadoprodservformula where idresultado = ".$_1_u_resultado_idresultado." and status ='ATIVO' and idprodservformula =".$rowf1["idprodservformula"];
                        $respf=d::b()->query($sqlpf) or die("Erro 2 ao buscar resultado prodservformula  do teste:".mysqli_error(d::b())."sql=".$sqlpf);
                        $qtdmostra= mysqli_num_rows($respf);
                        
                        if($qtdmostra>0){
                         $rowresprod=mysqli_fetch_assoc($respf);   
                    ?>
                        <input title="Retirar fase" checked="checked" type="checkbox" name="retirarfase" onclick="dfase(<?=$rowresprod["idresultadoprodservformula"]?>);">
                    <?
                        }else{
                    ?>
                        <input title="Inserir fase" type="checkbox" name="inserirfase" onclick="ifase(<?=$_1_u_resultado_idresultado?>,<?=$rowf1["idprodservformula"]?>);">
                    <?
                        }
                    ?>
                    </div>
                    <div class="panel-body">	
                <?
                if($qtdmostra>0){
                ?>
                    <table class="table table-striped planilha" >
                    <tr>
                         <?if($_1_u_resultado_status!='ASSINADO'){?>
                        <th>Utilizar</th>
                         <?}?>
                        <th>Produto</th>
                        <th >Lotes</th>
                        <th >Utilizando</th>
                        <?if($_1_u_resultado_status!='ASSINADO'){?>
                        <th >Restante</th>
                        <?}?>
                    </tr>
<?
            $l=$_1_u_resultado_idresultado;
            while($rowf= mysqli_fetch_assoc($resf)){
                if($_1_u_resultado_status=='ASSINADO'){
                    
                    $sqlca="select l.partida,l.exercicio,l.idlote,l.qtddisp,l.qtddisp_exp,c.idlotecons,c.qtdd,c.qtdd_exp,l.status
                            from lote l join lotecons c on (c.idlote= l.idlote and c.tipoobjeto ='resultado' and c.idobjeto=".$_1_u_resultado_idresultado.")
                            where l.idprodserv =".$rowf["idprodserv"]." and l.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"];


                    $resca =  d::b()->query($sqlca) or die("Erro ao buscar atribuicoes dos lotes no resultado assinado:".mysqli_error(d::b())."sql=".$sqlca);
                    $qtdca= mysqli_num_rows($resca);
                    $qtdimput=$rowf['qtdi']*$_1_u_resultado_quantidade;
                    ?>
                     <tr>
                         
                        <td class='nowrap'><?=$rowf['descr']?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?=$rowf['idprodserv']?>" target="_blank"></a></td>
                        <?if($qtdca<1){?>
                        <td>Não foi encontrado lote disponivel!!!</td>
                        
                        <?}else{?>
                           <td >  
                        <? 
                        $utilizando=0;                   
                        while($rowca=mysqli_fetch_assoc($resca)){
                            $utilizando=$rowca['qtdd']+$utilizando;
                        ?>
                               <span class="label label-primary fonte10 itemestoque" qtddisp="<?=tratanumero($rowca['qtddisp'])?>" qtddispexp="" idlote="<?=$rowca['idlote']?>" data-toggle="tooltip" title="" data-original-title="<?=$rowca['partida']?>">
                                    <a class="branco hoverbranco" href="?_modulo=lote&_acao=u&idlote=<?=$rowca['idlote']?>" target="_blank"><?=$rowca['partida']?>/<?=$rowca['exercicio']?></a>
                                    <span class="badge pointer screen" idlote="<?=$rowca['idlote']?>" onclick="janelamodal('?_modulo=lote&_acao=u&idlote=<?=$rowca['idlote']?>')"><?=tratanumero($rowca['qtddisp'])?></span>
                                  <?if($rowca['status']!='ESGOTADO'){?>  
                                    <a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?=$rowca['idlote']?>)"></a>
                                  <?}?>
                                    <input type="text" name="<?=$act?>qtdd" value="<?=$rowca['qtdd']?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" readonly="readonly">
                                </span>                      
                        <?
                            }//
                        ?>
                               </td> 
                               <td align="right"><span class="badge"> <?=tratanumero($utilizando)?></span></td>
                                
                        <?}//if($qtdca<1){       
                 
                }else{// if($_1_u_resultado_status=='ASSINADO'){
                
                    $sqlc="select l.partida,l.exercicio,l.idlote,l.qtddisp,l.qtddisp_exp,c.idlotecons,c.qtdd,c.qtdd_exp 
                            from lote l left join lotecons c on (c.idlote= l.idlote and c.tipoobjeto ='resultado' and c.idobjeto=".$_1_u_resultado_idresultado.")
                            where l.idprodserv =".$rowf["idprodserv"]." 
                            and l.idunidade  in (1)
                            and l.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
                            and ((l.status ='APROVADO') or exists (select 1 from lotecons cc where cc.idlote = l.idlote and c.tipoobjeto ='resultado' and c.idobjeto=".$_1_u_resultado_idresultado."))";


                    $resc =  d::b()->query($sqlc) or die("Erro ao buscar atribuicoes dos lotes:".mysqli_error(d::b())."sql=".$sqlc);
                    $qtdc= mysqli_num_rows($resc);
                    $qtdutilizar=$rowf['qtdi']*$_1_u_resultado_quantidade;
                    $qtdimput=$rowf['qtdi']*$_1_u_resultado_quantidade;
?>
                    <tr class="trInsumo">
                        <td  align="right"><span class="badge sQtdpadrao"><?=tratanumero($qtdutilizar)?></span></td>
                        <td class='nowrap'><?=$rowf['descr']?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?=$rowf['idprodserv']?>" target="_blank"></a> </td>
                        <?if($qtdc<1){?>
                        <td>Não foi encontrado lote disponivel!!!</td>
                        
                        <?}else{?>
                           <td id="insumos<?=$rowf["idprodserv"]?>">  
                        <? 
                        
                        $qtdusando=0;
                        while($rowc=mysqli_fetch_assoc($resc)){
                            $l=$l+1;
                            $act=$l;
                            $novo='Y';
                            if(!empty($rowc['idlotecons'])){
                                $act='_cons'.$l.'_u_lotecons_';
                                $qtdimput=$qtdimput-$rowc['qtddisp'];
                                $novo='N';
                            }elseif($rowc['qtddisp']>0 and $qtdimput >0 and empty($rowc['qtdd'])){
                                if($rowc['qtddisp']<$qtdimput){
                                    $rowc['qtdd']=$rowc['qtddisp'];
                                    $qtdimput=$qtdimput-$rowc['qtddisp'];
                                    $act='_cons'.$l.'_i_lotecons_';
                                }else{                                      
                                    $rowc['qtdd']=$qtdimput;                                    
                                    $qtdimput=0;
                                   $act='_cons'.$l.'_i_lotecons_';
                                }
                                $novo='N';
                            }
                            if($rowc['qtddisp']<=0){
                                $readonlyzest="readonly='readonly'";
                            }else{
                                $readonlyzest="";
                            }
                            $qtdusando=$rowc['qtdd']+$qtdusando;
                        ?>
                       
                                
                                
                                <span class="label label-primary fonte10 itemestoque" qtddisp="<?=tratanumero($rowc['qtddisp'])?>" qtddispexp="" idlote="<?=$rowc['idlote']?>" data-toggle="tooltip" title="" data-original-title="<?=$rowc['partida']?>">
                                        <a class="branco hoverbranco" href="?_modulo=lote&_acao=u&idlote=<?=$rowc['idlote']?>" target="_blank"><?=$rowc['partida']?>/<?=$rowc['exercicio']?></a>
                                        <span class="badge pointer screen" idlote="<?=$rowc['idlote']?>" onclick="janelamodal('?_modulo=lote&_acao=u&idlote=<?=$rowc['idlote']?>')"><?=tratanumero($rowc['qtddisp'])?></span>
                                        <?if($rowca['status']!='ESGOTADO'){?> 
                                        <a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?=$rowc['idlote']?>)"></a>
                                        <?}?>
                                        <input <?=$readonlyzest?> type="text" name="<?=$act?>qtdd" id="vutilizar<?=$rowf["idprodserv"]?>" value="<?=$rowc['qtdd']?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" <?if($novo=='Y'){?> onchange="atualizainput(<?=$l?>)" <?}?>>
                                        <input type="hidden" name="<?=$act?>idlotecons" value="<?=$rowc['idlotecons']?>">
                                        <input type="hidden" name="<?=$act?>tipoobjeto" value="resultado">
                                        <input type="hidden" name="<?=$act?>idobjeto" value="<?=$_1_u_resultado_idresultado?>">
                                        <input type="hidden" name="<?=$act?>idlote" value="<?=$rowc['idlote']?>">
                                </span>
                        
                       
                        <?
                            }//
                            $restante=$qtdutilizar-$qtdusando;
                            if($restante>0){$fundo="fundolaranja";}else{$fundo="fundoverde";}
                        ?>
                            </td>
                            <td>
                                <span class="badge  sUtilizando <?=$fundo?>"><?=$qtdusando?></span>
                            </td>
                            <td>
                                <span class="badge sRestante <?=$fundo?>"><?=$restante?></span>
                            </td>       
                        <?  }//if($qtdc<1){
                        }//if($_1_u_resultado_status=='ASSINADO'){   
                        ?>
                        
                    </tr>
<?
                    }//while($rowf= mysqli_fetch_assoc($resf)){
?>
                </table>
<?
        }//if($qtdmostra>0){
?>
                    </div>
                </div>
            </div>
           
        </div>
<?
        }//if($qtdf>0){
    }//while($rowf1=mysqli_fetch_assoc($resf1)){
?>
    <br>
</div>
<br>
<div class="panel-body">
<div align="center" style="background: #ccc; height: 50px; line-height: 44px;">
   <table style="margin: 0px; padding: 0px;">
       <tr><td>** Alterar Status: </td>
         <td>
            
            <select style="dispdlay: none;" name="_1_u_resultado_status">
               <option value="ABERTO">ABERTO</option>
               <option value="PROCESSANDO">PROCESSANDO</option>
               <option value="FECHADO" selected>FECHADO</option>
            </select>
            <!--
               <select style="dispdlay: none;" name="_1_u_resultado_status">
               <?fillselect(array("ABERTO"=>"Aberto","PROCESSANDO"=>"Processando","FECHADO"=>"Fechado"),$_1_u_resultado_status)?>
               </select>
               -->
         </td>
      </tr>
   </table>
</div>
</div>
</fieldset>
<?
   }
   ?>		
</tr>
</table>
</div>
</div>
</div>
<p>
<div  class="panel-body">
   <div class="col-md-2"></div>
   <div class="col-md-10">
      <div class="cbupload" id="arquivoresultado" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
         <i class="fa fa-cloud-upload fonte18"></i>
      </div>
   </div>
</div>
<p>
<div class="row">
   <?$tabaud = "resultado";?>
   <div class="panel panel-default">
      <div class="panel-body">
         <div class="row col-md-12">
            <div class="col-md-1">Criado Por:</div>
            <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
            <div class="col-md-1">Criado Em:</div>
            <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>
         </div>
         <div class="row col-md-12">
            <div class="col-md-1">Alterado Por:</div>
            <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
            <div class="col-md-1">Alterado Em:</div>
            <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>
         </div>
      </div>
   </div>
</div>
<div id="novolote" style="display: none"> 
<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">    
   <div class="row">
      <div class="col-md-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <table>
                  <tr>
                     <td align="right"><strong>Agente:</strong></td>
                     <td >
                        <select  id="idprodservlote" name="">
                           <option></option>
                           <?fillselect("select p.idprodserv,p.descr 
                              from prodserv p join  unidadeobjeto u on( u.idunidade = 6 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                              where p.tipo = 'PRODUTO'
                              and p.status = 'ATIVO' 
                              and p.especial='Y'
                              and p.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." order by p.descr");?>		
                        </select>
                        <input	id="idlotelote" name="" type="hidden"	value=""	readonly='readonly'>
                        <input	id="statuslote" name="" type="hidden"	value="PENDENTE" readonly='readonly'>
                        <input	id="idunidadegplote" name="" type="hidden"	value="2" readonly='readonly'>
                        <input  id="exerciciolote" name=""  type="hidden"	value="<?=date("Y")?>"	readonly='readonly'>
                        <input  id="tipoobjetolote" name=""  type="hidden"	value="resultado"	readonly='readonly'>
                        <input  id="idobjetolote" name=""  type="hidden"	value=""	readonly='readonly'>
                     </td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
    </div>
<script>
   var idResultado = Number(<?=$_1_u_resultado_idresultado?>);
   var pageStateChanged = false; //Teste se a pagina sofreu alteracoes
   var vModelo = "<?=$_1_u_resultado_modelo?>";
   var vModo = "<?=$_1_u_resultado_modo?>";
   
   //Variáveis para soro
   var arrKeyConf = new Array();
   var arrKeyConf = new Array();
   
   var qtx = parseInt(<?=$qtx?>);//A soma dos orificios deve ser <= a este valor
   var xoper = "+";
   
   //Colocar alerta nos testes
   function alertateste(inIdresultado,inChk){
   
   	sTipoalerta=$("[name=_1_u_resultado_tipoalerta]").val();
   
   	if($("#chAlerta").is(':checked')){
   		CB.post({"objetos":"_x_u_resultado_idresultado="+inIdresultado+"&_x_u_resultado_alerta=Y&_x_u_resultado_tipoalerta="+sTipoalerta});
   	}else{
   		CB.post({"objetos":"_x_u_resultado_idresultado="+inIdresultado+"&_x_u_resultado_alerta=N&_x_u_resultado_tipoalerta="+sTipoalerta});
   	}
   }
   
   
   function retsomax(){
   
   		//soma as quantidades dos inputs (orificios)
   		var total = 0;
   		$("#tbOrificios input[id^=k_]").each( function(){
   			total += Number($(this).val());
   		});
   
   		//Verifica se é maior que a quantidade de testes estipulada
   		if((total+1) <= qtx){
   			$("#somaorificios").html(total+1);			
   			return true;
   		}else{
   			return false;
   		}
   }
   
   function capkey(e){
   	
   	teclaPressionada = retkey(e);
   
   	iInput = arrKeyConf[teclaPressionada];
   	
   	if(iInput){
   		pageStateChanged = true;
   
   		//alert("tecla: "+teclaPressionada+"\n codigo:" + i);
   		$objx = $("#k_" + iInput);
   
   		if($objx.length==0){
   			console.log('Objeto ['+idobjx+'] não encontrado');
   			return;
   		}
   
   		if(xoper == "+"){
   			if(retsomax()){//Verifica se a quant maxima foi atingida
   				$objx.val(parseInt($objx.val()) + 1);
   				return false;
   			}else{
   				alertAtencao("A Quantidade total de ["+qtx+"] testes  foi atingida!",null,"3000");
   				return false;
   			}
   		}else if(xoper == "-"){
   			if(parseInt($objx.val()) > 0){//Verifica se a quant maxima foi atingida
   				$objx.val(parseInt($objx.val()) - 1);
   				$("#somaorificios").html(Number($("#somaorificios").html())-1);
   				return false;
   			}else{
   				window.status = "Limite inferior [0] atingido...";
   				return false;
   			}
   		}else{
   			alert("Valor para Operação (+ ou -) não ajustado!\n Impossà­vel calcular orifà­cios.");
   		}
   	}
   }
   
   function setoper(inoper){
   	xoper = inoper;
   	console.log("Operação de cálculo alterada para ["+inoper+"]");
   }
   
   //Conforme o tipo do teste prepara a tela para reagir a funçàµes/comandos especà­ficos
   if(vModelo=="DESCRITIVO"){
   	sSeletor = '#diveditor';
   	oDescritivo = $("[name=_1_"+CB.acao+"_resultado_descritivo]");
   
   	//Atribuir MCE somente apà³s método loadUrl
   	//CB.posLoadUrl = function(){
   		//Inicializa Editor
   		if(tinyMCE.editors["diveditor"]){
   		    tinyMCE.editors["diveditor"].remove();
   		}
   		tinyMCE.init({
   			selector: sSeletor
   			/*,height : 300
   			,min_height: 300 */
   			,inline: true /* não usar iframe */
   			,toolbar: 'bold | subscript superscript | bullist numlist | table'
   			,menubar: false
   			,plugins: ['table','autoresize']
   			,setup: function (editor) {
   				editor.on('init', function (e) {
   					this.setContent(oDescritivo.val());
   				});
   			}
   			,entity_encoding: 'raw'
   		});
   
   	 //}
   
   	//Antes de salvar atualiza o textarea
   	CB.prePost = function(){
   		if(tinyMCE.get('diveditor')){
   			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
   			oDescritivo.val( tinyMCE.get('diveditor').getContent().toUpperCase());
   		}
   	}
   }else if(vModelo =="SELETIVO" && vModo =="AGRUP"){
   	document.onkeypress = capkey;
   <?
      confPressionamentoTeclas();
      ?>
   }else if(vModelo=="UPLOAD"){    
       $("#resultadoelisa").dropzone({
   	    idObjeto: idResultado
   	    ,tipoObjeto: 'resultado'
   	    ,tipoArquivo: 'RESULTADOELISA'
   	    ,tipoKit: $("[name=tipokit]").val()
   	    ,sending: function(file, xhr, formData){
   		//Ajusta parametros antes de enviar via post
   		formData.append("tipokit", this.options.tipoKit);
   	    }
   	});
   	
   }
   
   CB.preLoadUrl = function(){
   	//Como o carregamento é via ajax, os popups ficavam aparecendo apà³s o load
   	$(".webui-popover").remove();
   }
   
   $(".oTeste").webuiPopover({
   	trigger: "hover"
   	,placement: "right"
   	,delay: {
           show: 300,
           hide: 0
       }
   });
   
   
   
   window.onbeforeunload = testPageState;
   function testPageState(){
   	if((typeof(pageStateChanged) != "undefined")&&(pageStateChanged)){
   		mess = "***********************************************************\n\nAS INFORMAààES NàO FORAM SALVAS AINDA!\n DESEJA REALMENTE SAIR SEM SALVAR?\n\n***********************************************************";
   		return mess;
   	}
   }
   
   
   
   function inovolote(inidresultado){
   		var strCabecalho = "</strong>NOVO AGENTE <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='criaragente();'><i class='fa fa-circle'></i>Salvar</button></strong>";
   		$("#cbModalTitulo").html((strCabecalho));
   		
   		var  htmloriginal =$("#novolote").html();
   		var objfrm= $(htmloriginal);
   		
   		objfrm.find("#idlotelote").attr("name", "_999_i_lote_idlote");
   		objfrm.find("#idprodservlote").attr("name", "_999_i_lote_idprodserv");
   			
   		objfrm.find("#exerciciolote").attr("name", "_999_i_lote_exercicio");
   		objfrm.find("#statuslote").attr("name", "_999_i_lote_status");
   		objfrm.find("#idunidadegplote").attr("name", "_999_i_lote_idunidadegp");
   					
   		objfrm.find("#idobjetolote").attr("name", "_999_i_lote_idobjetosolipor");
   		objfrm.find("#idobjetolote").attr("value", inidresultado);
   		
   		objfrm.find("#tipoobjetolote").attr("name", "_999_i_lote_tipoobjetosolipor");
   		
   		$("#cbModalCorpo").html(objfrm.html());
   		$('#cbModal').modal('show');
   		
   }
   
   function listalote(inidresultado){
   	var strCabecalho = "</strong>AGENTE(S)</strong>";
   		$("#cbModalTitulo").html((strCabecalho));
   		
   		var  htmloriginal =$("#resultadoagente"+inidresultado).html();
   		var objfrm= $(htmloriginal);
   		$("#cbModalCorpo").html(objfrm.html());
   		$('#cbModal').modal('show');
   	
   }
   
   function criaragente(){
    
     var str="_x_i_lote_idprodserv="+$("[name=_999_i_lote_idprodserv]").val()+
             "&_x_i_lote_status=ABERTO&_x_i_lote_exercicio="+$("[name=_999_i_lote_exercicio]").val()+
             "&_x_i_lote_tipoobjetosolipor="+$("[name=_999_i_lote_tipoobjetosolipor]").val()+
             "&_x_i_lote_idobjetosolipor="+$("[name=_999_i_lote_idobjetosolipor]").val();
      
       CB.post({
               objetos: str
               ,parcial:true
               ,posPost: function(resp,status,ajax){
                   if(status="success"){
                       $("#cbModalCorpo").html("");
                       $('#cbModal').modal('hide');
                   }else{
                       alert(resp);
                   }
               }
           });
   }
   
   if( $("[name=_1_u_resultado_idresultado]").val() ){
   $("#arquivoresultado").dropzone({
   		idObjeto: $("[name=_1_u_resultado_idresultado]").val()
   		,tipoObjeto: 'resultado'
   	});
   }
   
   
   function setresultadoind(vid){
   
   
   
   	var tecla=parseInt($('#tecla'+vid).val());	
   
      var valor = tecla +1;
   
    // alert(valor);
   
   	document.getElementById('resultado'+vid).value=valor; 
   	document.getElementById('rotulo'+vid).value=valor; 
   	
   	
   }
   /*
   function atualizainput(vthis,vact,vidlote){
       
       CB.post({
   	    "objetos":"_x_i_lotecons_idlote="+vidlote+"&_x_i_lotecons_tipoobjeto=resultado&_x_i_lotecons_idobjeto="+$("input[name*='"+vact+"idobjeto']" ).val()+"&_x_i_lotecons_qtdd="+$(vthis).val()
   	    ,parcial:true
               ,refresh:false
       });
   }
   */
   function atualizainput(inlinha){
       
       $("[name="+inlinha+"idlote]").attr('name', '_'+inlinha+'_i_lotecons_idlote');
       $("[name="+inlinha+"tipoobjeto]").attr('name', '_'+inlinha+'_i_lotecons_tipoobjeto');
       $("[name="+inlinha+"idobjeto]").attr('name', '_'+inlinha+'_i_lotecons_idobjeto');
       $("[name="+inlinha+"qtdd]").attr('name', '_'+inlinha+'_i_lotecons_qtdd');
       
   }
   
   function esgotarlote(inIdlote){
       if(confirm("Deseja realmente esgotar o lote?")){
   	CB.post({
   	    "objetos":"_x_u_lote_idlote="+inIdlote+"&_x_u_lote_status=ESGOTADO&_x_u_lote_qtddisp=0&&_x_u_lote_qtddisp_exp=0"
   	    ,parcial:true
          });
       }   
   }
   
   function mostraConsumo(inOConsumo){
     $oc = $(inOConsumo);
   	
   	$tbInsumo=$oc.closest("table");	
   	//$oajustecalc=$tbInsumo.find("[class=ajuste_calc]");
   	
   	
   	$trInsumo=$oc.closest("tr.trInsumo");
   	$sQtdpadrao=$trInsumo.find(".sQtdpadrao");
   	$sUtilizando=$trInsumo.find(".sUtilizando");
   	$sRestante=$trInsumo.find(".sRestante");
           somaUtilizacao=0;
   	$oConsumos=$trInsumo.find("[name*=_qtdd]");
           
           
           $.each($oConsumos, function(isc,osc){
   		var $o=$(osc);
   		if($o.val()){
   
   			if($o.attr("cbqtddispexp")!="" && ($o.val().toLowerCase().indexOf("e")<=0 && $o.val().toLowerCase().indexOf("d")<=0)){
   				alertAtencao("Valor inválido. <br> Inserir e ou d.");
   				return false;
   			}
   
   			valor=$o.val().replace(/,/g, '.');
   			valor=normalizaQtd(valor);
   
   			somaUtilizacao+=valor;
   		}
   	})
           
           	qtdPadrao=normalizaQtd($sQtdpadrao.html());
   
   	//somaUtilizacao=recuperaExpoente(somaUtilizacao,qtdPadrao);
   	
   	if(somaUtilizacao>=qtdPadrao){
   		sclass="fundoverde";
   	}else{
   		sclass="fundolaranja";
   	}
   	
   	if(somaUtilizacao>0){
   		//Formata o badge de 'utilizando'
   		$sUtilizando
   			.html(somaUtilizacao)
   			.removeClass("fundoverde")
   			.removeClass("fundolaranja")
   			.addClass(sclass)
   			.attr("title",(somaUtilizacao/qtdPadrao)*100+"%");
   	}else{//zero ou vazio
   		//Formata o badge de 'utilizando'
   		$sUtilizando
   			.html(somaUtilizacao)
   			.removeClass("fundoverde")
   			.removeClass("fundolaranja")
   			.attr("title",(somaUtilizacao/qtdPadrao)*100+"%");	    
   	}
   	
   	$sRestante
   		//.html(((qtdPadrao-somaUtilizacao)/$vajustecalc))
   		.html(qtdPadrao-somaUtilizacao)
   		.removeClass("fundoverde")
   		.removeClass("fundolaranja")
   		.addClass(sclass);
   	
   }
   
   function normalizaQtd(inValor){
   	var sVlr=""+inValor;
   	var $arrExp;
   	var fVlr;
   	if(sVlr.toLowerCase().indexOf("d")>-1){
   		$arrExp=sVlr.toLowerCase().split('d');
   		fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
   		fVlr = parseFloat(fVlr);
   	}else if(sVlr.toLowerCase().indexOf("e")>-1){
   		$arrExp=sVlr.toLowerCase().split('e');
   		fVlr = $arrExp[0]*Math.pow(10,$arrExp[1]);
   	}else{
   		fVlr=parseFloat(sVlr).toFixed(2);
   	}
   	
   	return parseFloat(fVlr);
   }
   
   //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>