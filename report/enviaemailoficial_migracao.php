<?
//die("Em manutençãoo. Aguarde. Entrar em contato com Hermes Pedro ou Marcelos");

ini_set('memory_limit', '-1');
require_once("../inc/php/validaacesso.php");

if (   $_SESSION["SESSAO"]["USUARIO"]=="marcelo" 
	|| $_SESSION["SESSAO"]["USUARIO"]=="marcelocunha" 
	|| $_SESSION["SESSAO"]["USUARIO"]=="Fábio"
	|| $_SESSION["SESSAO"]["USUARIO"]=="josesousa"
	|| $_SESSION["SESSAO"]["USUARIO"]=="fabio"
	|| $_SESSION["SESSAO"]["USUARIO"]=="daniel"
	|| $_SESSION["SESSAO"]["USUARIO"]=="gabrieltiburcio"
	|| $_SESSION["SESSAO"]["USUARIO"]=="lidianemelo") {

?>
<html>
<head>
	<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
	<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css">
	<link href="../inc/css/carbon.css" media="all" rel="stylesheet" type="text/css">

	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>

	<style>
		body {
			padding: 20px;
		}
		.tdemail a{
			padding: 1px;
			padding-right: 6px;
			display: inline-block;
		}
	</style>
</head>
<body>
<?

$lacre  = $_GET["lacre"];
$nucleoamostra  = $_GET["nucleoamostra"];
$exercicio  = $_GET["exercicio"];
$idsecretaria   = $_GET["idsecretaria"];
$idpessoa   = $_GET["idpessoa"];
$status= $_GET['status'];
$dataamostra_1  = $_GET["dataamostra_1"];
$dataamostra_2  = $_GET["dataamostra_2"];
$idcomunicacaoext = $_GET["idcomunicacaoext"];

$visualizacao = $_GET["visualizacao"]; 

if (!empty($dataamostra_1) or !empty($dataamostra_2)){
  $dataini = validadate($dataamostra_1);
  $datafim = validadate($dataamostra_2);

  if ($dataini and $datafim){
    $clausulad .= " and (r.alteradoem  BETWEEN '" . $dataini ."' and '" .$datafim ." 23:59:59') ";
  }else{
    die ("Datas n&atilde;o V&aacute;lidas!");
  }
}

if(!empty($idregistro_1) and !empty($idregistro_2)){
  $clausulad .=" and a.idregistro BETWEEN ".$idregistro_1." AND ".$idregistro_2;


}

if(!empty($exercicio)){
  $clausulad .=" and a.exercicio ='".$exercicio."'";
}

if(!empty($nucleoamostra)){
  $clausulad .=" and a.nucleoamostra like('%".$nucleoamostra."%') ";
}

if(!empty($lacre)){
  $clausulad .=" and a.lacre like('%".$lacre."%') ";
}

if(!empty($tc)){
  $clausulad .=" and a.tc like('%".$tc."%') ";
}

if(!empty($idsecretaria)){
  $clausulad .=" and r.idsecretaria = ".$idsecretaria." ";
}

if(!empty($idpessoa)){
  $clausulad .=" and a.idpessoa = ".$idpessoa." ";
}

if(!empty($idcomunicacaoext)){
	$clauscomext = " and c.idcomunicacaoext=".$idcomunicacaoext;
}

if($status=="ENVIADO" or $status=="NAO ENVIADO" or $status=="ADIADO" or $status=="EM FILA"){
  $clausuladexiste =" exists ";
  $auxstatus = "= '".$status."'";
}else{
  $clausuladexiste =" not exists ";
  $auxstatus = "!= 'PENDENTE'";
}

/*
 * colocar condição para executar select
 */
if(!empty($clausulad) or !empty($clauscomext)){
  
  /*maf260819: - analisar o bloco: not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial...
		 	   - analisar a necessidade de (select count(*) from resultado rr where rr.idresultado = r.idresultado): sempre trará [1] porque não existe left join e o idresultado é único
			   - realizado um group concat nos resultados e amostras, para explicitar ao usuário o que irá (~teoricamente) estar contido
	*/
  //resultados oficiais todos
  $sql="select sb.tipores, 
     sb.dataamostra,sb.idpessoa,sb.nome,sb.idsecretaria,sb.secretaria,sb.emailresult,sb.tememail,sb.idnucleo,sb.nucleoamostra,sb.lote,sb.exercicio,sb.idresultado, group_concat(concat(sb.idresultado,'-',sb.idregistro) separator '#') as lresultados, group_concat(sb.idamostra separator '#') as lamostras, sum(sb.qtdresultado) as qtdresultado
      
    from 
    (
    select 'TODOS' AS tipores,
       a.idregistro, a.idamostra, a.dataamostra,a.idpessoa,p.nome,s.idpessoa as idsecretaria,s.nome as secretaria,concat(s.emailcopia,',resultados@laudolab.com.br') as emailresult,IF(s.emailcopia like('%@%'),'S','N') as tememail,a.idnucleo,a.nucleoamostra,a.lote,a.exercicio,r.idresultado,(select count(*) from resultado rr where rr.idresultado = r.idresultado) as qtdresultado
      from    
      (amostra a
      ,resultado r 
      ,pessoa p
      ,pessoa s
      )           
      where p.idpessoa = a.idpessoa 
        and s.idpessoa = r.idsecretaria
        and ".$clausuladexiste." (
          select 1
		  from comunicacaoext c
			join mailfila m on (m.idobjeto = c.idcomunicacaoext)
			join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
          where c.tipo = 'EMAILOFICIAL'
          and m.status ".$auxstatus."
          and i.tipoobjeto = 'resultado'
          and i.idobjeto = r.idresultado
          and m.tipoobjeto = 'comunicacaoext'
          and m.remover = 'N'
	  ".$clauscomext."
        )
        -- MAF280819: de acordo com reunião com Jr./Fabio/Hermes/Cunha, optou-se por retirar a restrição de envio de emails parcialmente conforme a regra abaixo. Isto porque essa regra não era aplicada na emissãoresultadogerapdf.php no sistema antigo 
        -- and not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra and rr.idsecretaria != '' and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
        and r.status = 'ASSINADO'
        and r.idamostra = a.idamostra 
        and r.idsecretaria != ''
        and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
        ".$clausulad."
    ) as sb
    
      group by sb.idnucleo,sb.idsecretaria ";
  
  
  
  $sql.=" union all ";
  
  
  //resultados oficiais positivos
  $sql.="select sb1.tipores, 
     sb1.dataamostra,sb1.idpessoa,sb1.nome,sb1.idsecretaria,sb1.secretaria,sb1.emailresult,sb1.tememail,sb1.idnucleo,sb1.nucleoamostra,sb1.lote,sb1.exercicio,sb1.idresultado, group_concat(concat(sb1.idresultado,'-',sb1.idregistro) separator '#') as lresultados, group_concat(sb1.idamostra separator '#') as lamostras, sum(sb1.qtdresultado) as qtdresultado      
      from 
      (   
        select 'POS' as tipores,
         a.idregistro, a.idamostra, a.dataamostra,a.idpessoa,p.nome,s.idpessoa as idsecretaria,s.nome as secretaria,concat(s.emailresult,',resultados@laudolab.com.br') as emailresult,IF(s.emailresult like('%@%'),'S','N') as tememail,a.idnucleo,a.nucleoamostra,a.lote,a.exercicio,r.idresultado,(select count(*) from resultado rr where rr.idresultado = r.idresultado) as qtdresultado 
        from
        (amostra a
        ,resultado r
        ,pessoa p
        ,pessoa s
        )
        where p.idpessoa = a.idpessoa
          and s.idpessoa = r.idsecretaria
          and ".$clausuladexiste." (
                  select 1 
				  from comunicacaoext c
				  join mailfila m on (m.idobjeto = c.idcomunicacaoext)
				  join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
                  where c.tipo = 'EMAILOFICIALPOS'
                  and m.status ".$auxstatus."
                  and i.tipoobjeto = 'resultado'
                  and i.idobjeto = r.idresultado
                  and m.tipoobjeto = 'comunicacaoext'
                  and m.remover = 'N'
		  ".$clauscomext."
                  )         
          -- MAF280819: de acordo com reunião com Jr./Fabio/Hermes/Cunha, optou-se por retirar a restrição de envio de emails parcialmente conforme a regra abaixo. Isto porque essa regra não era aplicada na emissãoresultadogerapdf.php no sistema antigo 
          -- and not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra and rr.idsecretaria != '' and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
          and r.status = 'ASSINADO'
          and r.idamostra = a.idamostra             
          and r.alerta = 'Y'
          and r.idsecretaria != ''          
          and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
          ".$clausulad." 
      ) as sb1
    
      group by sb1.idnucleo,sb1.idsecretaria";

  
  $sql.="  order by  dataamostra,nome ";


echo "<!-- \n".$sql."\n -->";
//die;

if (!empty($sql)){
    $res = mysql_query($sql) or die("Falha ao pesquisar resultados: " . mysql_error() . "<p>SQL: $sql");
    $ires = mysql_num_rows($res);
    
  }
}
?>

<fieldset>
<legend>Filtros para Listagem</legend>
<div class="col-sm-12">
	<div class="col-sm-6">
		<form action="<?=$_SERVER["PHP_SELF"] ?>" method="get">
		  <table>
			<!-- tr>
			  <td class="rotulo">Exercício:</td>
			  <td><input name="exercicio" value="<?=$exercicio?>" size="3"></td>
			</tr -->
			<tr>
				<td class="rotulo">Data da última alteração entre</td>
				<td>
					<input name="dataamostra_1" id="dataamostra_1" value="<?=$dataamostra_1?>" autocomplete="off" type="text" placeholder="dd/mm/aaaa">
				</td>
				<td>&nbsp;e&nbsp;</td>
				<td>
					<input name="dataamostra_2" id="dataamostra_2" value="<?=$dataamostra_2?>" autocomplete="off" type="text" onfocus="fill_2(this)" placeholder="dd/mm/aaaa">
				</td>
			</tr>
			<tr>
			  <td class="rotulo">ID ComunicaçãoExt:</td>
			  <td><input name="idcomunicacaoext" value="<?=$idcomunicacaoext?>" size="20"></td>
			</tr>
			<!-- tr>
			  <td class="rotulo">Lacre:</td>
			  <td><input name="lacre" value="<?=$lacre?>" size="20"></td>
			</tr>
			<tr>
			  <td class="rotulo">Nucleo Amostra:</td>
			  <td><input name="nucleoamostra" value="<?=$nucleoamostra?>" size="20"></td>
			</tr>
			<tr>
			  <td class="rotulo">Secretaria:</td> 
			  <td colspan="10">
					<select name="idsecretaria"  id="idsecretaria" vnulo>
						  <option value=""></option>
					  <?//fillselect("select idpessoa,nome from pessoa where idtipopessoa = 10 and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and status ='ATIVO' order by nome",$idsecretaria);?>
				 </select>  
			  </td>
			</tr>
			<tr>
			  <td class="rotulo">Cliente:</td> 
			  <td colspan="10">
					<select name="idpessoa"  id="idpessoa" vnulo>
					<option value=""></option>
					  <?//fillselect("select idpessoa,nome from pessoa where idtipopessoa = 2 and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and status ='ATIVO' order by nome",$idpessoa);?>
				 </select>  
			  </td>
			</tr -->
			<tr>
			  <td class="rotulo">Visualizar:</td> 
			  <td colspan="10">
				 <select name="visualizacao" id="visualizacao" vnulo>
					 <?fillselect(array('SOMENTESECRETARIAS'=>'Somente secretarias com email configurado','TUDO'=>'Mostrar tudo'),$visualizacao);?>
				 </select>
			  </td>
			</tr>
			<tr>
			  <td class="rotulo">Status:</td> 
			  <td colspan="10">
				 <select name="status"  id="status" vnulo>
					 <?fillselect(array('PENDENTE'=>'Pendente', 'EM FILA'=>'Em Fila', 'ADIADO'=>'Adiado', 'NAO ENVIADO'=>'Não Enviado', 'ENVIADO'=>'Enviado'),$status);?>
				 </select>
			  </td>
			</tr>

			<tr>
			  <td></td>
			  <td><button type="submit" class="btn btn-primary">Pesquisar</button></td>
			</tr>
		  </table>  
		</form>
	</div>
	<div class="col-sm-6">
		<table>
			<tr>
				<td>Remetente:</td>
			</tr>
			<?$sqlempresaemail = "SELECT * FROM empresaemails WHERE tipoenvio = 'RESULTADOOFICIAL' and idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
			$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar empresaemails sql=".$sqlempresaemail);
			$qtdempresaemail=mysqli_num_rows($resempresaemail);
			if($qtdempresaemail == 1){
				$nemails = 1;
			}else{
				if($qtdempresaemail > 1){
					$nemails = 2;
				}else{
					$nemails = 0;
				}
			}
			
			if($nemails == 1){?>
				<tr>
					<td>
					<?
						$sqldominio = "SELECT em.idemailvirtualconf,em.idempresa,ev.email_original AS dominio 
                          FROM empresaemails em 
                          JOIN emailvirtualconf ev ON(em.idemailvirtualconf = ev.idemailvirtualconf)
                          WHERE em.tipoenvio = 'RESULTADOOFICIAL'
                          AND ev.status = 'ATIVO'
                          AND em.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
						$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
						$rowdominio=mysqli_fetch_assoc($resdominio)?>
						<input id="emailunico" title="Email Remetente" type="hidden" value="<?=$rowdominio["idemailvirtualconf"]?>">
						<label class="alert-warning"><?=$rowdominio["dominio"]?></label>
					</td>
				</tr>
			<?}else{
				if($nemails > 1){
					$sqldominio = "SELECT em.idemailvirtualconf,ev.email_original AS dominio,em.idempresa 
                        FROM empresaemails em 
                        JOIN emailvirtualconf ev ON(em.idemailvirtualconf = ev.idemailvirtualconf)
                        WHERE em.tipoenvio = 'RESULTADOOFICIAL'
                        AND ev.status = 'ATIVO'
                        AND em.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
							
					$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
					$qtddominio=mysqli_num_rows($resdominio);
					if($qtddominio>0){
						while($rowdominio=mysqli_fetch_assoc($resdominio)){?>
							<tr>
								<td>
									<input name="emailremetente" title="Email Remetente" type="radio" value="<?=$rowdominio["idemailvirtualconf"]?>">
									<label class="alert-warning" ><?=$rowdominio["dominio"]?> </label>
								</td>
							</tr>
						<?}
					}
				}else{?>
					<tr>
						<td><label class="alert alert-danger">Não há email configurado para essa empresa</label></td>
					<tr>
				<?}
			}?>
		</table>
	</div>
</div>
</fieldset>
<?
if(!empty($clausulad) or !empty($clauscomext)){
	
	$irows=mysqli_num_rows($res);
	
?>
<fieldset><legend>Resultados para envio do email: <!-- strong><?=$irows?></strong --></legend>

<table class="table table-striped table-condensed" id="inftable">
	<thead>
	<tr>
		<th >Qtd. Res.</th>
		<th >Nucleo</th>
		<th >Lote</th>
		<th >Cliente</th> 
		<th >Secretaria</th>
		<th >Email</th>
    <?if($status == "PENDENTE"){?>
	  <th >Status</th>
    <?}?>
		<th >Controles</th>
	</tr>
	</thead>
<?while($row=mysqli_fetch_assoc($res)){
/*
  $sqls = "select concat(s.emailcopia,',resultados@laudolab.com.br') as emailresult
  from pessoa s
  where s.status = 'ATIVO'
  and s.idpessoa = ".$row['idsecretaria'];
  $ress=mysql_query($sqls) or die("Erro ao buscar email da secretaria sql".$sqls);
  $rows=mysql_fetch_assoc($ress);
  */
  
  $getdata="&alterado_1=".$dataini."&alterado_2=".$datafim;
  
  if($row['tipores']=="POS"){
    
      $cortr="background-color:#ff6464;";
      //$fnenvio="emissaogerapdf('".$row['idnucleo']."',".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'Y')";
      //$fnenvio="$(this).addClass('verde').closest('tr').addClass('fade'); window.open('enviaemailoficial_emissaogerapdf.php?idnucleo=".$row['idnucleo']."&exercicio=".$row['exercicio']."&idsecretaria=".$row['idsecretaria']."&idpessoa=".$row['idpessoa']."&alerta=Y".$getdata."')";
      $strsucesso="EMAILOFICIALPOS";
	  
		// GVT - 20/05/2020 Verifica a falta de informações essenciais para o envio do email.
		if(empty($row['idnucleo']) or $row['idnucleo'] == 0){
			$fnenvio="erroinfos('Núcleo está vazio!')";
		}else if(empty($row['exercicio'])){
			$fnenvio="erroinfos('Exercício está vazio!')";
		}else if(empty($row['idsecretaria'])){
			$fnenvio="erroinfos('Secretaria está vazia!')";
		}else if(empty($row['idpessoa'])){
			$fnenvio="erroinfos('Cliente está vazio!')";
		}else{
			$fnenvio="enviaremailoficial(this,'Y',".$row['idnucleo'].",".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'".$getdata."',".$nemails.")";
		}
	  
		//$fnenvio="enviaremailoficial(this,'Y',".$row['idnucleo'].",".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'".$getdata."',".$nemails.")";
	  
		$sqlcont="select email,receberes  
            from pessoa p,pessoacontato c 
            where p.status='ATIVO'
            and receberes is not null and receberes !=''
            and p.idpessoa = c.idcontato
            and c.idpessoa= ".$row['idsecretaria'];
      
    
  }else{
      $cortr="";
      //$fnenvio="emissaogerapdf(this,'".$row['idnucleo']."',".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'N')";
    
      //$fnenvio="$(this).addClass('verde').closest('tr').addClass('fade');window.open('enviaemailoficial_emissaogerapdf.php?idnucleo=".$row['idnucleo']."&exercicio=".$row['exercicio']."&idsecretaria=".$row['idsecretaria']."&idpessoa=".$row['idpessoa']."&alerta=N".$getdata."')";
      
      $strsucesso="EMAILOFICIAL";
	  
		// GVT - 20/05/2020 Verifica a falta de informações essenciais para o envio do email.
		if(empty($row['idnucleo']) or $row['idnucleo'] == 0){
			$fnenvio="erroinfos('Núcleo está vazio!')";
		}else if(empty($row['exercicio'])){
			$fnenvio="erroinfos('Exercício está vazio!')";
		}else if(empty($row['idsecretaria'])){
			$fnenvio="erroinfos('Secretaria está vazia!')";
		}else if(empty($row['idpessoa'])){
			$fnenvio="erroinfos('Cliente está vazio!')";
		}else{
			$fnenvio="enviaremailoficial(this,'N',".$row['idnucleo'].",".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'".$getdata."',".$nemails.")";
		}
      
		//$fnenvio="enviaremailoficial(this,'N',".$row['idnucleo'].",".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'".$getdata."',".$nemails.")";
	  
		$sqlcont="select email,receberestodos as receberes
            from pessoa p,pessoacontato c 
            where p.status='ATIVO'
            and receberestodos is not null and receberestodos !=''
            and p.idpessoa = c.idcontato
            and c.idpessoa =".$row['idsecretaria'];
    
  }
    
    $rescont=mysql_query($sqlcont) or die("Erro ao buscar informações do contato da secretaria sql=".$sqlcont);
    $qtdreceberes=mysql_num_rows($rescont);
  
  
  //resultados para email todos cujo campo email todos da secretaria esteja VAZIO
  if($row['tipores']=="TODOS" and $qtdreceberes<1){
    if($status!="ENVIADO"){

    //Buscar os resultado para inserir no log da comunicacaoext
    $sqlf=" select  r.idresultado
        from resultado r,amostra a,pessoa p,pessoa s
        where p.idpessoa = a.idpessoa
          and s.idpessoa = r.idsecretaria
          and not exists(select 1 from resultado rr USE INDEX (idamostra_status) where rr.idamostra = a.idamostra  and rr.idsecretaria != '' and r.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
          and r.status = 'ASSINADO'
          and r.idamostra = a.idamostra
          and r.idsecretaria=".$row['idsecretaria']."
          and a.idpessoa = ".$row['idpessoa']."
          and a.idnucleo ='".$row['idnucleo']."'
          and a.exercicio = '".$row['exercicio']."'
          and not exists(
                  select 1 from comunicacaoext c,comunicacaoextitem i
                  where c.tipo = 'EMAILOFICIAL'
                  and c.status ='SUCESSO'
                  and c.idcomunicacaoext = i.idcomunicacaoext
                  and i.tipoobjeto = 'resultado'
                  and i.idobjeto = r.idresultado
                  )
          and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
    $resf=mysql_query($sqlf) or die("Erro ao buscar resultados enviados sql=".$sqlf);
    $qtdf=mysql_num_rows($resf);
    echo "<!--";
    echo $sqlf;
    echo "-->";
      if($qtdf>0){
      //Inserir notificação de email vazio com status SUCESSO
        $sql1 = "insert into comunicacaoext (idempresa,tipo,`from`,`to`,idobjeto,tipoobjeto,status,criadoem,criadopor)
                        values (".$_SESSION["SESSAO"]["IDEMPRESA"].",'EMAILOFICIAL','SISLAUDO','A OPÇÃO DE EMAIL TODOS NÃO MARCADA PARA NENHUM CONTATO',".$row['idnucleo'].",'nucleo','GERANDOLOG',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."')";
          
        mysql_query($sql1) or die("erro ao inserir Log na tabela de comunicação de EMAIL TODOS VAZIO [".mysql_error()."] ".$sql1);
        $newidcomunicacao = mysql_insert_id();
        
        
        while ($rowf=mysql_fetch_assoc($resf)){
          $sqlu="INSERT INTO `comunicacaoextitem` (idempresa,idcomunicacaoext,idobjeto,tipoobjeto,debug)
              values
              (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$newidcomunicacao.",".$rowf['idresultado'].",'resultado','inslista')";
        
          mysql_query($sqlu) or die("erro ao vincular comunicação ao resultado erro [".mysql_error()."] ".$sqlu);
        }
        
        $sqlu1="update comunicacaoext set status = 'SUCESSO',conteudo='A OPÇÃO DE EMAIL TODOS NÃO MARCADA PARA NENHUM CONTATO' where idcomunicacaoext = ".$newidcomunicacao;
        mysql_query($sqlu1) or die("erro ao inserir Log de SUCESSO [".mysql_error()."] ".$sql1);
      
      }
    }
  }else{
    if($status == 'PENDENTE'){
      $virg="";
      $stremail="";
      while($rowrescont=mysql_fetch_assoc($rescont)){
        $stremail.=$virg.$rowrescont['email'];
        $virg=", ";
      }
    }else{
      $_sqls="select c.idcomunicacaoext from comunicacaoext c,comunicacaoextitem i, mailfila m
                where c.tipo = '".$strsucesso."'
                and c.idcomunicacaoext = i.idcomunicacaoext
                and i.tipoobjeto = 'resultado'
                and m.idobjeto = c.idcomunicacaoext
                and m.tipoobjeto = 'comunicacaoext'
                and i.idobjeto = ".$row['idresultado']."
                group by idcomunicacaoext";
      $_ress=mysql_query($_sqls) or die("Erro ao buscar log da comunicação externa. sql=".$_sqls);
      $rows6=mysql_fetch_assoc($_ress);

      $_rsql2 = "select * from mailfila where idobjeto = ".$rows6["idcomunicacaoext"]." and tipoobjeto = 'comunicacaoext' and remover = 'N'";
      $_rres2 = d::b()->query($_rsql2) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql2);

      $estrutura = "<div>";
      while($_rrow2 = mysqli_fetch_assoc($_rres2)){
		if($status == $_rrow2["status"]){
			switch($_rrow2["status"]){
			  case 'EM FILA': $cor = 'style="padding: 2px;background-color: gray;color:white;margin-left:5px;border-radius: 5px;"';break;
			  case 'ADIADO': $cor = 'style="padding: 2px;background-color: yellow;margin-left:5px;border-radius: 5px;"';break;
			  case 'NAO ENVIADO': $cor = 'style="padding: 2px;background-color: red;color:white;margin-left:5px;border-radius: 5px;"';break;
			  case 'ENVIADO': $cor = 'style="padding: 2px;background-color: green;color:white;margin-left:5px;border-radius: 5px;"';break;
			  default: $cor = "";break;
			}
			
			$estrutura.= "<div style='padding: 3px;'><span>";
			$estrutura.= $_rrow2["destinatario"];
			$estrutura.= "</span><span ".$cor.">";
			$estrutura.= $_rrow2["status"];
			$estrutura.= "</span></div>";
		}
      }
      $estrutura .= "</div>";
    }
	
	//maf: esconder quando existir somente o email oficial@laudolab
	if($visualizacao!="TUDO" && trim($stremail)=="oficial@laudolab.com.br"){
		$clnone="display:none;";
		$visi = "";
	}else{
		$clnone="";
		$visi = "visivel = 's'";
	}
	
?>  
  <tr style="<?=$cortr?><?=$clnone?>" <?=$visi?> value="<?=$row['idresultado']?>">
    <td >
      <?=$row['qtdresultado']?>
    
    </td> 
    <td ><?=$row['nucleoamostra']?></td>
    <td ><?=$row['lote']?></td> 
    <td ><?=$row['nome']?></td> 
    <td ><?=$row['secretaria']?></td>
    <td class="tdemail">
    <?
    if($status == "PENDENTE"){
      echo(trim($stremail));
    }else{
      echo $estrutura;
    }
     
     //echo "<hr>";
     echo "<div style='margin-top:6px;'><span class='cinzaclaro fonte9' >Resultados: </span>";

     //maf: Remonta listas com os resultados e amostras
 	foreach (explode("#",$row['lresultados']) as $sresult) {
 		$aresult=explode('-',$sresult);
 		echo "<a class='fade hoverazul' title='Registro: ".$aresult[1]."' target='_blank' href='/?_modulo=resultaves&_acao=u&idresultado=".$aresult[0]."'>".$aresult[0]."</a>";
 	}
 	echo "</div>";
     
     
    ?>
    </td> 
    <?if($status == "PENDENTE"){?>
    <td ><?=$status?></td>
    <?}?>
    <td >
<?
if($status == "PENDENTE"){
?>
      <div id="<?=$strsucesso?><?=$row['idnucleo']?>">
      	<i class="fa fa-paper-plane fa-2x pointer btn hoververde cinza" title="Enviar Email" onclick="<?=$fnenvio?>"></i>
      </div>
<?
}else{
      $sqls="select c.idcomunicacaoext from comunicacaoext c,comunicacaoextitem i, mailfila m
                where c.tipo = '".$strsucesso."'
                and c.idcomunicacaoext = i.idcomunicacaoext
                and i.tipoobjeto = 'resultado'
                and m.idobjeto = c.idcomunicacaoext
                and m.tipoobjeto = 'comunicacaoext'
                and i.idobjeto = ".$row['idresultado']."
                group by idcomunicacaoext";
      $ress=mysql_query($sqls) or die("Erro ao buscar log da comunicação externa. sql=".$sqls);
      $rows=mysql_fetch_assoc($ress);

      ?>

	<a title="Ver emails enviados" target='_blank' href="../reenvioemail.php?tipoobjeto=comunicacaoext&idobjeto=<?=$rows['idcomunicacaoext']?>">
		<span class="fa-stack">
    <i class="fa fa-envelope fa-stack-2x" style="color:silver;"></i>
			<i class="fa fa-info-circle pointer hoverazul fa-stack-1x" style="color:blue;"></i>
		</span>
	</a>
	<div class="" style=""><?=$rows['idcomunicacaoext']?></div>

<?
}
?>    
    </td> 
    
  </tr>

<?
  }
}?>           
</table>
</fieldset>
<?
}//if($_GET){
else{
	die("[Nenhum parâmetro informado]");
}
?>

<script language="javascript">

processando=false;
//para gerar o arquivo e a comunicao externa
function emissaogerapdf(vidnucleo,vexercicio,vidsecretaria,vidpessoa,valerta){
  document.body.style.cursor = 'wait';
  if(processando==true){
    alert('Aguarde Processando!!!');
  }else{
    processando=true;
      if(valerta==='Y'){
        $('#EMAILOFICIALPOS'+vidnucleo).hide();              
      }else{
        $('#EMAILOFICIAL'+vidnucleo).hide();
      }
      $.get("../report/enviaemailoficial_emissaogerapdf.php",
        {idnucleo : vidnucleo,exercicio:vexercicio,idsecretaria:vidsecretaria,idpessoa:vidpessoa,alerta:valerta}, 
        function(vidcomunicacaoext){
          if(parseInt(vidcomunicacaoext)){
            //enviaemail(vidcomunicacaoext,vidnucleo,vexercicio,vidsecretaria,vidpessoa,valerta); 
          }else{
            alert(vidcomunicacaoext);
          }
        }
    );
  }
  document.body.style.cursor = '';
}

// GVT - 20/05/2020 - Função para alertar a falta de informações para envio de email
function erroinfos(texto){
	alert("Erro: Não foi possível enviar pois "+texto);
}

//para email de todos e positivo
function enviaemail(vidcomunicacaoext,vidnucleo,vexercicio,vidsecretaria,vidpessoa,valerta){
  document.body.style.cursor = 'wait';
  
      $.get("../ajax/enviaemailsecretaria.php", 
        { idcomunicacaoext:vidcomunicacaoext,idnucleo : vidnucleo,exercicio:vexercicio,idsecretaria:vidsecretaria,idpessoa:vidpessoa}, 
        function(resposta){
            alert(resposta);
          processando=false;  
          if(valerta==='Y'){
            $('#EMAILOFICIALPOS'+vidnucleo).hide();              
          }else{
            $('#EMAILOFICIAL'+vidnucleo).hide();
          }
        }
    );
  document.body.style.cursor = '';    
}
//$(this).addClass('verde').closest('tr').addClass('fade'); window.open('enviaemailoficial_emissaogerapdf.php?idnucleo=".$row['idnucleo']."&exercicio=".$row['exercicio']."&idsecretaria=".$row['idsecretaria']."&idpessoa=".$row['idpessoa']."&alerta=Y".$getdata."')
//$(this).addClass('verde').closest('tr').addClass('fade'); window.open('enviaemailoficial_emissaogerapdf.php?idnucleo=".$row['idnucleo']."&exercicio=".$row['exercicio']."&idsecretaria=".$row['idsecretaria']."&idpessoa=".$row['idpessoa']."&alerta=N".$getdata."')
function enviaremailoficial(vthis,alerta,idnucleo,exercicio,idsecretaria,idpessoa,data,flag){

	if(flag == 1){
		var aux = $("#emailunico").val();
		$(vthis).addClass('verde').closest('tr').addClass('fade');
		window.open('enviaemailoficial_emissaogerapdf.php?idnucleo='+idnucleo+'&exercicio='+exercicio+'&idsecretaria='+idsecretaria+'&idpessoa='+idpessoa+'&idemailvirtualconf='+aux+'&alerta='+alerta+data);
	}else{
		if(flag == 2){
			if (! $("input[type='radio'][name='emailremetente']").is(':checked') ){
			  alert("É necessário selecionar um remetente");
			}else{
				var aux = $("input[name='emailremetente']:checked").val();
				$(vthis).addClass('verde').closest('tr').addClass('fade');
				window.open('enviaemailoficial_emissaogerapdf.php?idnucleo='+idnucleo+'&exercicio='+exercicio+'&idsecretaria='+idsecretaria+'&idpessoa='+idpessoa+'&idemailvirtualconf='+aux+'&alerta='+alerta+data);
			}
		}
	}
}
</script>

</body>
</html>
<? }else{
	
		echo 'Você não possui acesso à este módulo';
	} ?>
