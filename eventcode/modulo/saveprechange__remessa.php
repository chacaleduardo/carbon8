<?
if(array_key_exists("i", $_SESSION["arrpostbuffer"]["1"]) and
	!empty($_SESSION["arrpostbuffer"]["1"]["i"]["remessa"]["idagencia"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["i"]["remessa"]["idregistro"])){

        $inIdagencia=$_SESSION["arrpostbuffer"]["1"]["i"]["remessa"]["idagencia"];

       $idregistro =geraSegRemessa($inIdagencia);
       $_SESSION["arrpostbuffer"]["1"]["i"]["remessa"]["idregistro"]=$idregistro;
}

    
function geraSegRemessa($inIdagencia){
	### Inicializa a sequence para cada tipo de meio
	$sqlini = "SELECT count(*) as quant
				FROM sequence
				WHERE sequence = 'remessa'					
					and chave1 = ".$inIdagencia;

	$resini = d::b()->query($sqlini);
	if(!$resini){
		echo "1-Falha ao inicializar Sequence [".$tipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sqlini";
		die();
	}

	$rowini = mysqli_fetch_assoc($resini);
	### Caso nao exista a sequence inicializada para o tipo de meio
	if($rowini["quant"]==0){
		$sqlins = "insert into sequence  (`sequence`, `chave1`,`idempresa`,exercicio)
					values ('remessa',".$inIdagencia.",".$_SESSION["SESSAO"]["IDEMPRESA"].",year(current_date))";

		d::b()->query($sqlins) or die("2-Falha ao inicializar Sequence [".$tipo."] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);		
	}

	### Incrementa a sequence para o lote
	d::b()->query("update sequence set chave2 = (chave2 + 1) where sequence = 'remessa' and chave1 = ".$inIdagencia);

	$sql = "SELECT chave2,exercicio FROM sequence 
                    where sequence = 'remessa'						 
				    and chave1 = ".$inIdagencia;

	$res =d::b()->query($sql);

	if(!$res){
	    echo "1-Falha Sequence [remessa] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
		die();
	}

	$row = mysqli_fetch_assoc($res);

	### Caso nao retorne nenhuma linha ou retorne valor vazio
	if(empty($row["chave2"]) or $row["chave2"]==0){
		
			echo "2-Falha Pesquisando Sequence [remessa] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		
	}
	
	return $row["chave2"];

}//function geraSegRemessa($inIdagencia){

?>