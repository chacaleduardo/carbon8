<?
require_once("../inc/php/validaacesso.php");

//ini_set("display_errors",true);
//error_reporting(E_ALL);

$idpessoaAnt=$_GET["idpessoaant"];
$idEmpresaNovo=$_GET["idempresanovo"];

if(empty($idpessoaAnt) or empty($idEmpresaNovo)){
    http_response_code(400);
    die("Parâmetros incorretos");
}

$sqldominio = "select dominio from dominio where idempresa=".$idEmpresaNovo." order by iddominio asc limit 1";
$rdominio = d::b()->query($sqldominio) or die("Erro recuperando dominio");

$dominioNovo=mysql_fetch_assoc($rdominio)["dominio"];
$dominioAbreviadoNovo=explode(".",$dominioNovo)[0];

$usuario = '';
$senha = '';
$tipoauth = '';
$status = '';
$webmailpermissao = '';
$webmailusuario = '';
$webmailemail = '';
$criadoPor = 'duplicacao_automatica';
$senhaMd5Ant = '';

//Recupera os dados do usuario antigo (null esta sendo utilizado pra evitar erros no script)

$spa = "SELECT null as idpessoa,nome,nomecurto,idtipopessoa,uf,sexo,organograma,supervisor,idtipocliente,classificacao,idlp,idlporiginal,idsecretaria,idsgcargo,idunidade,motivoadmissao,motivodemissao,observacaodemissao,status,idfluxostatus,statuscrm,idempresa,idmatriz,usuario,senha,tipoauth,nasc,email,emailcopia,ramalfixo,perfil,url,emailxmlnfe,emailxmlnfecc,emailmat,demissao,contratacao,salario,insalubridade,unimedmens,emprestimo,parcela,parcelas,aliqinss,tipopagamento,escolaridade,formacao,dddfixo,telfixo,dddcel,telcel,dddass,telass,rotuloass,dddcom,telcom,cpfcnpj,rg,rgorgexpedidor,estcivil,ctrexp,ctrexp2,cipainicio,cipafim,titeleitor,zona,alistmil,cidnasc,nomepai,nomemae,emissaorg,cnh,emissaocnh,ctps,ctpsserie,pis,irrf,inscrest,indiedest,flgprodrural,razaosocial,contrato,centrocusto,vigencia,observacaore,observacaonf,observacaonfp,obspedido,obsvenda,obsinfnfe,contratoemp,experiencia,qualificacao,obs,obs1,obs2,emailresult,assinateste,msgqualidade,pedido,cotacao,estoque,redoma,msgamostra,visualizares,impresultado,resvincnf,receberes,receberesdata,receberestodos,participacaoserv,participacaoprod,confcalendario,idtransportadora,idagencia,idpreferencia,parcelavenda,formapagtovenda,prazopagtovenda,intervalovenda,flgsocio,qtddependente,vlrdependente,indfinal,decsimplesn,contaves,contagro,contsuinos,contbovinos,contkitdiag,contequinos,contovinos,contoutros,flagobrigatoriocontato,vendadireta,bancohoras,idempresagrupo,workspace_onlyoffice,regimetrib,idrheventofolha
,'duplicacao_automatica',now(),alteradopor,alteradoem,InscricaoMunicipalTomador,webmailpermissao,webmailemail,webmailusuario,jsonpreferencias,idarquivoavatar,criticidade,obsqualificacao,biodados,biodados_tempo,acesso,reppontoexterno,rqqualificacao,certanalise
FROM pessoa where idpessoa=".$idpessoaAnt;

$resspa=d::b()->query($spa);

$rspa = mysqli_fetch_assoc($resspa);

//Recupera o recordset e pega os dados do usuario antigo, abrindo "variaveis variaveis" para cada coluna
$vv=null;
while ($col = $resspa->fetch_field()) {
	$vv="_".$col->name;
	$$vv = $rspa[$col->name];
///	echo $col->name."\n";
}

d::b()->query("start transaction;") or die(mysqli_error(d::b()));

$supda = "update pessoa
set 
 usuario=null
    ,senha=null
    ,tipoauth=null
    ,webmailpermissao = 'N'
    ,webmailemail = concat(ifnull(webmailemail,idpessoa),'_')
    ,webmailusuario = concat(ifnull(webmailusuario,idpessoa),'_')
    ,alteradopor='$criadoPor'
    ,alteradoem=now()
where idpessoa=$idpessoaAnt";

d::b()->query($supda) or die("upd pessoa #1".mysqli_error(d::b()));

//Insert nova pessoa
$sinsp = "insert into pessoa (idpessoa,nome,nomecurto,idtipopessoa,uf,sexo,organograma,supervisor,idtipocliente,classificacao,idlp,idlporiginal,idsecretaria,idsgcargo,idunidade,motivoadmissao,motivodemissao,observacaodemissao,status,idfluxostatus,statuscrm,idempresa,idmatriz,usuario,senha,tipoauth,nasc,email,emailcopia,ramalfixo,perfil,url,emailxmlnfe,emailxmlnfecc,emailmat,demissao,contratacao,salario,insalubridade,unimedmens,emprestimo,parcela,parcelas,aliqinss,tipopagamento,escolaridade,formacao,dddfixo,telfixo,dddcel,telcel,dddass,telass,rotuloass,dddcom,telcom,cpfcnpj,rg,rgorgexpedidor,estcivil,ctrexp,ctrexp2,cipainicio,cipafim,titeleitor,zona,alistmil,cidnasc,nomepai,nomemae,emissaorg,cnh,emissaocnh,ctps,ctpsserie,pis,irrf,inscrest,indiedest,flgprodrural,razaosocial,contrato,centrocusto,vigencia,observacaore,observacaonf,observacaonfp,obspedido,obsvenda,obsinfnfe,contratoemp,experiencia,qualificacao,obs,obs1,obs2,emailresult,assinateste,msgqualidade,pedido,cotacao,estoque,redoma,msgamostra,visualizares,impresultado,resvincnf,receberes,receberesdata,receberestodos,participacaoserv,participacaoprod,confcalendario,idtransportadora,idagencia,idpreferencia,parcelavenda,formapagtovenda,prazopagtovenda,intervalovenda,flgsocio,qtddependente,vlrdependente,indfinal,decsimplesn,contaves,contagro,contsuinos,contbovinos,contkitdiag,contequinos,contovinos,contoutros,flagobrigatoriocontato,vendadireta,bancohoras,idempresagrupo,workspace_onlyoffice,regimetrib,idrheventofolha
,criadopor,criadoem,alteradopor,alteradoem,InscricaoMunicipalTomador,webmailpermissao,webmailemail,webmailusuario,jsonpreferencias,idarquivoavatar,criticidade,obsqualificacao,biodados,biodados_tempo,acesso,reppontoexterno,rqqualificacao,certanalise)
select null,'$_nome','$_nomecurto','$_idtipopessoa','$_uf','$_sexo','$_organograma','$_supervisor','$_idtipocliente','$_classificacao','$_idlp','$_idlporiginal','$_idsecretaria','$_idsgcargo','$_idunidade','$_motivoadmissao','$_motivodemissao','$_observacaodemissao','$_status','$_idfluxostatus','$_statuscrm',$idEmpresaNovo,'$_idmatriz',if(length('$_usuario')=0,null,'$_usuario'),'$_senha','$_tipoauth','$_nasc','$_email','$_emailcopia','$_ramalfixo','$_perfil','$_url','$_emailxmlnfe','$_emailxmlnfecc','$_emailmat','$_demissao','$_contratacao','$_salario','$_insalubridade','$_unimedmens','$_emprestimo','$_parcela','$_parcelas','$_aliqinss','$_tipopagamento','$_escolaridade','$_formacao','$_dddfixo','$_telfixo','$_dddcel','$_telcel','$_dddass','$_telass','$_rotuloass','$_dddcom','$_telcom','$_cpfcnpj','$_rg','$_rgorgexpedidor','$_estcivil','$_ctrexp','$_ctrexp2','$_cipainicio','$_cipafim','$_titeleitor','$_zona','$_alistmil','$_cidnasc','$_nomepai','$_nomemae','$_emissaorg','$_cnh','$_emissaocnh','$_ctps','$_ctpsserie','$_pis','$_irrf','$_inscrest','$_indiedest','$_flgprodrural','$_razaosocial','$_contrato','$_centrocusto','$_vigencia','$_observacaore','$_observacaonf','$_observacaonfp','$_obspedido','$_obsvenda','$_obsinfnfe','$_contratoemp','$_experiencia','$_qualificacao','$_obs','$_obs1','$_obs2','$_emailresult','$_assinateste','$_msgqualidade','$_pedido','$_cotacao','$_estoque','$_redoma','$_msgamostra','$_visualizares','$_impresultado','$_resvincnf','$_receberes','$_receberesdata','$_receberestodos','$_participacaoserv','$_participacaoprod','$_confcalendario','$_idtransportadora','$_idagencia','$_idpreferencia','$_parcelavenda','$_formapagtovenda','$_prazopagtovenda','$_intervalovenda','$_flgsocio','$_qtddependente','$_vlrdependente','$_indfinal','$_decsimplesn','$_contaves','$_contagro','$_contsuinos','$_contbovinos','$_contkitdiag','$_contequinos','$_contovinos','$_contoutros','$_flagobrigatoriocontato','$_vendadireta','$_bancohoras','$_idempresagrupo','$_workspace_onlyoffice','$_regimetrib','$_idrheventofolha','$criadoPor',now(),'$criadoPor',now(),'$_InscricaoMunicipalTomador','$_webmailpermissao',if(length('$_webmailemail')=0,null,concat(explode('$_webmailemail','@',1),'@','$dominioNovo')),if(length('$_usuario')=0,null,concat('$_usuario','_','$dominioAbreviadoNovo')),'$_jsonpreferencias','$_idarquivoavatar','$_criticidade','$_obsqualificacao','$_biodados','$_biodados_tempo','$_acesso','$_reppontoexterno','$_rqqualificacao','$_certanalise'";

d::b()->query($sinsp) or die("ins pessoa #1".mysqli_error(d::b())." sql=".$sinsp);

// Abre a variavel para recuperar o ID inserido
$idpessoaNovo = mysqli_insert_id(d::b());

// Migrar eventos que a propria pessoa criou
$uflp = "update fluxostatuspessoa 
set idpessoa = $idpessoaNovo
where idpessoa = $idpessoaAnt;";
d::b()->query($uflp) or die("upd fluxostatuspessoa #1".mysqli_error(d::b()));

// Migrar eventos que a pessoa é participante: este script foi melhorado apos conversa com Pedro e Makswell
$insfsp = "INSERT INTO laudo.fluxostatuspessoa (idfluxostatuspessoa,idpessoa,idempresa,idmodulo,modulo,idobjeto,tipoobjeto,status,idfluxostatus,oculto,idobjetoext,tipoobjetoext,inseridomanualmente,visualizado,assinar,editar,criadopor,criadoem,alteradopor,alteradoem)
select null,idpessoa,idempresa,idmodulo,modulo,$idpessoaNovo,tipoobjeto,status,idfluxostatus,oculto,idobjetoext,tipoobjetoext,inseridomanualmente,visualizado,assinar,editar,'$criadoPor',now(),'$criadoPor',now()
from fluxostatuspessoa f
where f.idobjeto=$idpessoaAnt and f.tipoobjeto='pessoa'
and not exists(
	select 1 from fluxostatuspessoa f2 
    where f2.idmodulo=f.idmodulo
	and f2.modulo=f.modulo
	and f2.idobjeto=$idpessoaNovo
	and f2.tipoobjeto='pessoa'
)";

d::b()->query($insfsp) or die("ins fluxostatuspessoa #1".mysqli_error(d::b()));

// Inserir enderecos caso nao tenham sido migrados
$inse = "insert into endereco (
  select null,$idEmpresaNovo,$idpessoaNovo,logradouro,uf,cep,endereco,numero,complemento,bairro,cidade,codcidade,codcidadev,idcidadesiaf,tipoendereco,idtipoendereco,status,obsentrega,nomepropriedade,cnpjend,inscest,localizacao,lat,lon,'$criadoPor',now(),'$criadoPor',now()
  from endereco e
  where idpessoa = $idpessoaAnt
	and not exists(
		select 1 from endereco e2 where e2.idpessoa = $idpessoaNovo
    )
)";
d::b()->query($inse) or die("ins endereco #1".mysqli_error(d::b()));

// Inserir valores recorrentes mensais, caso nao tenham sido migrados
$insrep = "insert into rheventopessoa (
  select null,$idEmpresaNovo,$idpessoaNovo,idrhtipoevento,valor,status,'$criadoPor',now(),'$criadoPor',now()
  from rheventopessoa r
  where idpessoa = $idpessoaAnt
	and not exists(
		select 1 from rheventopessoa r2 where r2.idpessoa = $idpessoaNovo
    )
)";
d::b()->query($insrep) or die("ins endereco #1".mysqli_error(d::b()));

$inspa = "INSERT INTO pessoaagencia (
	select  null,$idEmpresaNovo,$idpessoaNovo,banco,agencia,conta,status,criadopor,criadoem,alteradopor,alteradoem 
	from pessoaagencia p
	where idpessoa = $idpessoaAnt
    and not exists(
		select 1 from pessoaagencia p2 where p2.idpessoa = $idpessoaNovo
    )
)";
d::b()->query($inspa) or die("ins pessoaagencia #1".mysqli_error(d::b()));

$insph = "INSERT INTO pessoahorario
(
	select null,$idEmpresaNovo,$idpessoaNovo,horaini,horafim,periodo,'$criadopor',now(),'$criadoPor',now()
	from pessoahorario h
	where idpessoa = $idpessoaAnt
    and not exists(
		select 1 from pessoahorario h2 where h2.idpessoa = $idpessoaNovo
    )
)";
d::b()->query($insph) or die("ins pessoahorario #1".mysqli_error(d::b()));

$inspc = "insert into pessoacontato (
	select null, $idEmpresaNovo, idcontato, $idpessoaNovo, emailresultado,emailresultadodata,viagem,emailnfsecc,emailxmlnfe,emailxmlnfecc,emailmaterial,somenteoficial,participacaoserv,participacaoprod,receberes,receberestodos,criadoem,criadopor,alteradoem,alteradopor
	from pessoacontato c
	where idpessoa = $idpessoaAnt
		and not exists(
			select 1 from pessoacontato c2 where c2.idpessoa = $idpessoaNovo
		)
)";
d::b()->query($inspc) or die("ins pessoacontato #1".mysqli_error(d::b()));

$inspo = "insert into plantelobjeto (
	select null, $idEmpresaNovo,idplantel,$idpessoaNovo,tipoobjeto,criadopor,criadoem,alteradopor,alteradoem 
	from plantelobjeto o
	where tipoobjeto = 'pessoa'
		and idobjeto = $idpessoaAnt
		and not exists(
			select 1 from plantelobjeto o2 where tipoobjeto = 'pessoa' and idobjeto = $idpessoaNovo
		)
)";
d::b()->query($inspo) or die("ins plantelobjeto #1".mysqli_error(d::b()));

// Migrar determinados tipos de "lancamentos" de folha de pagamento abertos
$ure = "update rhevento
set idempresa = $idEmpresaNovo
	,idpessoa = $idpessoaNovo
where idrhtipoevento=6
    and status='PENDENTE'
    and situacao='P'
    and valor!=0
	and idpessoa = $idpessoaAnt";
d::b()->query($ure) or die("upd rhevento #1".mysqli_error(d::b()));

$insoe = "insert into objempresa
(idempresa, idobjeto, objeto, empresa, criadopor, criadoem, alteradoem)
values 
($idEmpresaNovo,$idpessoaNovo,'pessoa',$idEmpresaNovo,'$criadoPor',now(),now())";
d::b()->query($insoe) or die("ins objempresa #1".mysqli_error(d::b()));

$insar = "insert into arquivo(idempresa,tipoarquivo,jperm,nomeoriginal,nome,caminho,caminhothumb,mime,tamanho,tamanhobytes,imagempadrao,idpessoa,idobjeto,tipoobjeto,obs,criadoem)
select $idEmpresaNovo,tipoarquivo,jperm,nomeoriginal,nome,caminho,caminhothumb,mime,tamanho,tamanhobytes,imagempadrao,idpessoa,$idpessoaNovo,tipoobjeto,obs,now() from arquivo where tipoarquivo='AVATAR' and idobjeto=$idpessoaAnt and tipoobjeto='pessoa' limit 1";
d::b()->query($insar) or die("ins arquivo #1".mysqli_error(d::b()));

// Transfere a autenticacao de email

$ssant = "select senha from extauth where idpessoa = $idpessoaAnt";
$ressant=d::b()->query($ssant);
$rssant = mysqli_fetch_assoc($ressant);

$senhaMd5Ant=$rssant["senha"];

d::b()->query("delete from extauth where idpessoa = $idpessoaAnt") or die(mysqli_error(d::b()));
d::b()->query("delete from extauth where idpessoa = $idpessoaNovo") or die(mysqli_error(d::b()));
d::b()->query("insert into extauth (idpessoa, senha, alteradoem) values($idpessoaNovo,'$senhaMd5Ant',now())") or die(mysqli_error(d::b()));


d::b()->query("commit");
d::b()->query("CALL "._DBCARBON."._ftsByHostnameAtualizarDb(true, 'laudo' , 'vwcadfuncionario','manual');");

echo "/arquivos/scripts/moveMail.sh ".$_webmailusuario." ".$_usuario."_".$dominioAbreviadoNovo;
