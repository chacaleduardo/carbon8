<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
}

function novolog($idobjeto,$tipoobjeto,$modulo,$mensagem,$status){
    $vinmsg=str_replace("'"," ",$mensagem);
    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
            values (".$idobjeto.",'".$tipoobjeto."','".$modulo."','".$vinmsg."','".$status."',sysdate())";
    $res=d::b()->query($sql); 				
}

$sql="select sb.tipores,sb.idempresa,sb.idpessoa,sb.idsecretaria,sb.idnucleo,sb.exercicio,sb.lacre,sb.tc			
            from 
            (select 'TODOS' AS tipores,a.idempresa,a.idpessoa,r.idsecretaria ,a.idnucleo,a.exercicio,a.lacre,a.tc
                from (amostra a,resultado r ) 					
                where not exists  (
                                select 1 from controleemissao c,controleemissaoitem i
                                where c.alerta = 'N'
                                and c.idcontroleemissao = i.idcontroleemissao
                                and i.tipoobjeto = 'resultado'
                                and i.idobjeto = r.idresultado
                            )				
                    and not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra and rr.idsecretaria != '' and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
                    and r.status = 'ASSINADO'
                    and r.idamostra = a.idamostra 
                    and r.idsecretaria != ''
                    and a.idempresa = 1
                    and a.dataamostra between DATE_SUB(date_format(now(),'%Y-%m-%d'), INTERVAL 30 DAY) and date_format(now(),'%Y-%m-%d')
            ) as sb
            group by sb.idnucleo,sb.idsecretaria  
    union all                         
    select sb1.tipores,sb1.idempresa,sb1.idpessoa,sb1.idsecretaria,sb1.idnucleo,sb1.exercicio,sb1.lacre,sb1.tc	
            from 
            (select 'POS' as tipores,a.idempresa,a.idpessoa,r.idsecretaria ,a.idnucleo,a.exercicio,a.lacre,a.tc
                from (amostra a,resultado r)
                where not exists  (
                                    select 1 from controleemissao c,controleemissaoitem i
                                    where c.alerta = 'Y'
                                    and c.idcontroleemissao = i.idcontroleemissao
                                    and i.tipoobjeto = 'resultado'
                                    and i.idobjeto = r.idresultado
                                    )					
                    and not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra and rr.idsecretaria != '' and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
                    and r.status = 'ASSINADO'
                    and r.idamostra = a.idamostra 						
                    and r.alerta = 'Y'
                    and r.idsecretaria != ''					
                    and a.idempresa = 1
                    and a.dataamostra between DATE_SUB(date_format(now(),'%Y-%m-%d'), INTERVAL 30 DAY) and date_format(now(),'%Y-%m-%d')
            ) as sb1		
            group by sb1.idnucleo,sb1.idsecretaria";
$res=d::b()->query($sql);

if(!$res){
    echo("erro ao agrupar resultados para controleemissao");
    novolog(1,'controleemissao','cron_controleemissao',"Erro ao agrupar resultados para controleemissao","ERRO");
}

while($row=mysqli_fetch_assoc($res)){
    
    if($row['tipores']=='POS'){
        $sqlalerta=" and r.alerta = 'Y' ";
	$sqlintipo="Y";
    }else{
        $sqlalerta="  ";
	$sqlintipo="N";
    }
    
    //insert controleemissao
    $sqli="INSERT INTO controleemissao
            (idempresa,idnucleo,exercicio,idsecretaria,idpessoa,alerta,tc,lacre,tipo,criadopor,criadoem,alteradopor,alteradoem)
            VALUES
            (".$row['idempresa'].",".$row['idnucleo'].",".$row['exercicio'].",".$row['idsecretaria'].",".$row['idpessoa'].",'".$sqlintipo."','".$row['tc']."','".$row['lacre']."','OFICIAL','crontab',sysdate(),'crontab',sysdate())";
    $resi=d::b()->query($sqli);
    if(!$resi){
        echo("Erro ao inserir na controleemissao ".$sqli);
        novolog($row['idnucleo'],'controleemissao','cron_controleemissao',"Erro ao inserir na controleemissao ".$sqli,"ERRO");
    }else{
         $idcontroleemissao = mysqli_insert_id(d::b());
    }
        
    $sqlf=" select  r.idempresa,r.idresultado
                from resultado r,amostra a
                where 	not exists(select 1 from resultado rr USE INDEX (idamostra),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra  and rr.idsecretaria != '' and r.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
                        and r.status = 'ASSINADO'
                        and r.idamostra = a.idamostra
                        ".$sqlalerta."
                        and r.idsecretaria=".$row['idsecretaria']."
                        and a.idpessoa = ".$row['idpessoa']."
                        and a.idnucleo ='".$row['idnucleo']."'
                        and a.exercicio = '".$row['exercicio']."'
                        and not exists(
                                        select 1 from controleemissao c,controleemissaoitem i
                                        where c.alerta = '".$sqlintipo."'
                                        and c.idcontroleemissao = i.idcontroleemissao
                                        and i.tipoobjeto = 'resultado'
                                        and i.idobjeto = r.idresultado
                                        )
                        and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
    $resf=d::b()->query($sqlf);
    if(!$resf){
        echo("Erro ao buscar resultados para  controleemissao ".$sqlf);
        novolog($row['idnucleo'],'controleemissao','cron_controleemissao',"Erro ao buscar resultados para  controleemissao ".$sqlf,"ERRO");
    }
    while($rowf=mysqli_fetch_assoc($resf)){
        $sqlit="INSERT INTO controleemissaoitem
                (idempresa,idcontroleemissao,idobjeto,tipoobjeto,criadopor,criadoem,alteradopor,alteradoem)
                VALUES
                (".$rowf['idempresa'].",".$idcontroleemissao.",".$rowf['idresultado'].",'resultado','crontab',sysdate(),'crontab',sysdate())";
        $resit=d::b()->query($sqlit);
        if(!$resit){
            echo("Erro ao inserir na controleemissaoitem ".$sqlit);
            novolog($row['idnucleo'],'controleemissao','cron_controleemissao',"Erro ao inserir na controleemissaoitem ".$sqlit,"ERRO");
        }
    }//while($rowf=mysqli_fetch_assoc($resf)){    
}//while($row=mysqli_fetch_assoc($res)){

$sqlu="update controleemissao c set c.status = case when (select 1 from comunicacaoextitem i 
			where i.status='SUCESSO' 
			and i.idobjeto = c.idcontroleemissao 
			and tipoobjeto ='controleemissao') >1 then 'ENVIADO' else 'PENDENTE' end            
where c.status = 'PENDENTE';";
$resu=d::b()->query($sqlu);
if(!$resu){
    echo("Erro ao atualizar a controleemissao".$sqlu);
    novolog(1,'controleemissao','cron_controleemissao',"Erro ao atualizar a controleemissao ".$sqlu,"ERRO");
}

echo ("FIM");