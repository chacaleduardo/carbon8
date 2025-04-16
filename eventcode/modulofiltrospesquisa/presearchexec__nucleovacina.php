<?
if(gethostname()!=="sislaudo"){
	//Maf: Alterar a conexao para o servidor MASTER. A partir deste ponto todos os comandos serão executados no servidor remoto
	d::b("sislaudo.laudolab.com.br","3307","vultr","VultRremoto_2019","laudo");

	//Confirma se o db foi alterado
	$resh=d::b()->query("select @@hostname as 'hostname'");
	$rh = mysqli_fetch_assoc($resh);

	if($rh["hostname"]!=="sislaudo"){
		die("Servidor remoto indisponível.\nTente novamente mais tarde.");
	}
}
