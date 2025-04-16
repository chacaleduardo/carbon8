<?php

if(array_key_exists("i", $_SESSION["arrpostbuffer"]["1"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["exercicio"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idregistro"]) and 
	empty($_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["exercicio"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["idregistro"])){
		
	$idunidade = $_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idunidade"];
	
	if(empty($idunidade)){
		die("Não foi possivel identificar a Unidade para gerar o Registro!!!");
	}
	 
	d::b()->query("START TRANSACTION;");
	
	$status = $_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["status"];
	
	if($status=='PROVISORIO'){
		$exercicio = date('Y').'PROVISORIO';
	} else {
		$exercicio = date('Y');
	}
	
	### Tenta incrementar e recuperar o ID Atual do exercicio corrente
	//d::b()->query("LOCK TABLES seqamostra WRITE;");
	d::b()->query("update seqregistro set idregistro = (idregistro + 1) 
			where exercicio = '".$exercicio."'
				-- and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and idunidade = ".$idunidade
			) or die("Falha 1 atualizando IdRegistro : " . mysqli_error(d::b()) . "<p>SQL: $sql");

	$sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
			FROM seqregistro 
			where exercicio = '".$exercicio."'
			-- and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and idunidade = ".$idunidade;
	
	$resexercicio = d::b()->query($sql);
	
	if(!$resexercicio){
		//d::b()->query("UNLOCK TABLES;");
		echo "Falha Pesquisando Exercicio X IdRegistro : <p>SQL: ".$sql."<br>Erro:".mysqli_error(d::b());
		die();
	}
	
	$rowexercicio = mysqli_fetch_assoc($resexercicio);
	
	### Caso nao retorne nenhuma linha, sera necessario inicializar um novo ano de exercicio, com idamostra=1
	if(empty($rowexercicio["idregistro"])){
		$sqlatualizaexercicio =	"INSERT INTO seqregistro (idempresa,exercicio,idregistro,idunidade) 
						VALUES (".$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idempresa"].",'".$exercicio."',".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idunidade.");";

		$resexercicio = d::b()->query($sqlatualizaexercicio) or die("Falha 2 atualizando Exercicio : " . mysqli_error(d::b()) . "<p>SQL: $sqlatualizaexercicio");
	
		if(!$resexercicio){
			//d::b()->query("UNLOCK TABLES;");
			echo "Falha iniciando nova combinacao para Exercicio X IdAmostra : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	
		$sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
			FROM seqregistro where exercicio = '".$exercicio."'
			-- and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and idunidade = ".$idunidade;
		
		$resexercicio = d::b()->query($sql) or die("(3)Falha Pesquisando Exercicio X IdAmostra: " . mysqli_error(d::b()) . "<p>SQL: $sql");
	
		if(!$resexercicio){
			//d::b()->query("UNLOCK TABLES;");
			echo "Falha 4 Pesquisando Exercicio X IdAmostra : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
		$rowexercicio = mysqli_fetch_array($resexercicio);
	}
	
	//maf: O lock e unlcok estava causando commit. Verificar o que pode ser feito em caso de erros posteriores ao código de geração de exercicio
	//d::b()->query("UNLOCK TABLES;");
	
	//se o idnucleo vier vazio o valor do mesmo e informado como 0 (zero) para atender a questoes de relatorios(OUTROS) do site
	if(empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idnucleo"])){
		$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idnucleo"]=0;
	}
	$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["exercicio"] = $rowexercicio["exercicio"];
	$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idregistro"] = $rowexercicio["idregistro"];
	
	$_SESSION["post"]["_1_u_amostra_exercicio"] = $rowexercicio["exercicio"];
	$_SESSION["post"]["_1_u_amostra_idregistro"] = $rowexercicio["idregistro"];
}
//print_r($_SESSION["post"]); die("fim");


// SE ESTIVER ASSSINADA A AMOSTRA TRA ENQUANTO A TELA DO USUÁRIO ESTIVER ABERTA COM STATUS DEVOLVIDO NÃO SALVAR COM STATUS DEVOLVIDO E SIM ASSINADO;
$_status=$_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["status"];
$_idamostra=$_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["idamostra"];

if(!empty($_idamostra) and $_status=='DEVOLVIDO'){
    $sql="select * from carrimbo c where c.idobjeto = ".$_idamostra." and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and c.tipoobjeto='amostra' and c.status='ASSINADO'";
    $res = d::b()->query($sql);
    $qtdas= mysqli_num_rows($res);
    if($qtdas>0){
        $_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["status"]=="ASSINADO"; 
    }
}

//gerar os identificadores da amostra
if(!empty($_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["idobjeto"]) and ($_POST['qtdidentificador']>0)){
    $qtd=$_POST['qtdidentificador'];
    $idamostra=$_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["idobjeto"];
    $tipoobjeto=$_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["tipoobjeto"];
    
    for ($i = 2; $i <= $qtd; $i++) {
       // echo $i;
        $_SESSION["arrpostbuffer"][$i]["i"]["identificador"]["idobjeto"]=$idamostra;
        $_SESSION["arrpostbuffer"][$i]["i"]["identificador"]["tipoobjeto"]=$tipoobjeto;
    }
    montatabdef();
}

//print_r( $_SESSION["arrpostbuffer"]);
//die();
