<?// AQUI IRÁ VALIDAR SE O NOME DO ARQUIVO E VALIDO
//print_r($_POST);
//print_r($_FILES);
//echo($_FILES["file"]["name"]); die;


if(!empty($_POST['idobjeto'])){
    $idresultado=$_POST['idobjeto'];
   
    if($_POST["tipokit"]=='IDEXX' || $_POST["tipokit"]=='IDEXX-LAUDO'){
	    $ext=".RTF";
    }elseif($_POST["tipokit"]=='AFFINITECK' || $_POST["tipokit"]=='BIOCHEK'){
	    $ext=".txt";
    }
    
    $sqla="select concat(a.idregistro,p.codprodserv) as nomearqui,concat(a.idregistro,p.codprodserv,'".$ext."') as nomearquivortf
    from resultado r,amostra a,prodserv p
    where p.idprodserv = r.idtipoteste
    and  a.idamostra = r.idamostra
    and r.idresultado =".$idresultado;
    $resa=mysql_query($sqla) or die("Erro ao gerar o nome do arquivo");
    $rowa=mysql_fetch_assoc($resa);
    $nomearquivo = $rowa['nomearqui'];
    $nomearquivortf = $rowa['nomearquivortf'];

		
    IF($_FILES["file"]["name"]!=$nomearquivortf){
	echo("O nome do arquivo (".$_FILES["file"]["name"].") é diferente do nome padrão (".$nomearquivortf."). Favor verificar o nome do arquivo.");
	die("O nome do arquivo (".$_FILES["file"]["name"].") é diferente do nome padrão (".$nomearquivortf."). Favor verificar o nome do arquivo.");
    }
    
    
}else{
    die('idobjeto [idresultado] não informado');
}

