<?
//Maf: a senha, por conta de sistemas legados, deve ser mantida num repo com md5
$si="insert into extauth (idpessoa, senha)
	values (".$_SESSION["SESSAO"]["IDPESSOA"].",md5('".$_POST["senhanova"]."'))";

$res=d::b()->query($si);

if(!$res){
	if(d::b()->errno==1062){

		$su="update extauth
			set senha=md5('".$_POST["senhanova"]."')
			where idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];

		$resu=d::b()->query($su);

		if(!$resu){
			die("Erro atualizando senha retrocompatível");
		}
	}else{
		die("Erro gerando senha retrocompatível: ".d::b()->error);
	}
}

