<?
function verficavinculo($idsgdoc,$idpessoa){
	$sqlc='SELECT v.idsgdocvinc
			,d.idsgdoc
			,d.idregistro
			,d.titulo
			,d.versao
			,v.criadopor
			,v.criadoem
		FROM sgdocvinc v 
		JOIN sgdoc d ON d.idsgdoc=v.iddocvinc
		where v.idsgdoc = '.$idsgdoc;

	$rtc = d::b()->query($sqlc) or die("[saveprechange - Documentos vinculados]: ". mysqli_error(d::b()));
	if (mysqli_num_rows($rtc) > 0) {
		$arr = array();
		$_cmd= new cmd();
		$i=0;
		while ($rowv=mysqli_fetch_assoc($rtc)) {
			if ($rowv['versao'] == 0) {
				$rowv['versao'] = NULL;
			}
			$arr["_vfv".$i."_i_carrimbo_idpessoa"] = $idpessoa;
			$arr["_vfv".$i."_i_carrimbo_tipoobjeto"] = $_GET['_modulo'];
			$arr["_vfv".$i."_i_carrimbo_idobjeto"] = $rowv['idsgdoc'];
			$arr["_vfv".$i."_i_carrimbo_versao"] = $rowv["versao"];
	
	
			$sqve="select idobjeto from fluxostatuspessoa where modulo = '".$_GET['_modulo']."' and idmodulo = ".$rowv['idsgdoc']."  and idobjeto=".$idpessoa;
			$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
			if(mysqli_num_rows($rv) < 1){
				
				$arr["_vrf".$i."_i_fluxostatuspessoa_idmodulo"] = $rowv['idsgdoc'];
				$arr["_vrf".$i."_i_fluxostatuspessoa_modulo"] = $_GET['_modulo'];
				$arr["_vrf".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
				$arr["_vrf".$i."_i_fluxostatuspessoa_idobjeto"] = $idpessoa;
				$arr["_vrf".$i."_i_fluxostatuspessoa_tipoobjeto"] = 'pessoa';
				$arr["_vrf".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
				$arr["_vrf".$i."_i_fluxostatuspessoa_assinar"] = "Y";
			}
			$i++;
			verficavinculo($rowv['idsgdoc'],$idpessoa);
		}
		$res = $_cmd->save($arr);
		if(!$res){
			cbSetPostHeader("0","erro");
			die("Não foi possível solicitar a assinatura do participante nos documentos vinculados.");
		}

	}
}
$readonly = $_POST['_readonly_'];
$statusant=$_POST['statusant'];
//print_r( $_SESSION['arrpostbuffer']); die;
//abre variavel com a acao que veio da tela

// ATENÇÃO: caso queira atribuir um novo valor ao BUFFER, deve-se colocar a referência original do BUFFER.
// Ex:
//		$this->BUFFER["mykey"] = "myvalue";    // CORRETO

//		$arrpostbuffer = $this->BUFFER;
//		$arrpostbuffer["mykey"] = "myvalue";    // ERRADO

$iu = $this->BUFFER['1']['u']['sgdoc']['idsgdoc'] ? 'u' : 'i';

if($this->BUFFER['1'][$iu]['sgdoc']['idsgdoc'] and $_GET['idsgdoccp']){
	$this->BUFFER['1'][$iu]['sgdoc']['idsgdoc'] = "";
}

if($_GET['idsgdoccp']){
	foreach($this->BUFFER as $k => $v){
		if(array_key_exists('sgdocpag',$v[$iu])){
			unset($this->BUFFER[$k]);
		}
	}
}

if (!empty($this->BUFFER['removeContent']['u']['sgdoc']['idsgdoc'])) {
	$sql="DELETE from sgdocpag where idsgdoc=".$this->BUFFER['removeContent']['u']['sgdoc']['idsgdoc'];
	$rw = d::b()->query($sql) or die('Erro ao excluir conteudo. ->'.mysqli_error(d::b()). "<p>SQL: $sql");
}
if ($_POST['carrimbo_todos'] == "Y") {
	$sl="SELECT 1 from sgdoc where status <> 'OBSOLETO' and idsgdoc=".$this->BUFFER['1'][$iu]['sgdoc']['idsgdoc'];
	$rs = d::b()->query($sl) or die("A procura do status do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sl");
	if(mysqli_num_rows($rs) > 0){
		$_cmd= new cmd();
		$arr = array();
		$sql="SELECT fs.idobjeto as idpessoa
		from fluxostatuspessoa fs
		JOIN pessoa p on(p.idpessoa = fs.idobjeto and p.status='ATIVO')
		 where
		 fs.modulo='".$_GET['_modulo']."' and fs.tipoobjeto='pessoa' and  
		  fs.idmodulo=".$this->BUFFER['1'][$iu]['sgdoc']['idsgdoc']."  and 
		   fs.idobjetoext is not null and 
		   not exists(SELECT 1 from carrimbo c where c.idpessoa=fs.idobjeto and c.tipoobjeto='".$_GET['_modulo']."' and c.idobjeto=fs.idmodulo and c.versao=".$_POST['versao_doc'].") and
		   not exists(SELECT 1 from carrimbo c where c.idpessoa=fs.idobjeto and c.idobjeto=fs.idmodulo and c.tipoobjeto='".$_GET['_modulo']."' and c.versao<".$_POST['versao_doc']."  and c.status='PENDENTE')";
		$res = d::b()->query($sql) or die("A insercão de assinaturas falhou : " .mysqli_error(d::b()) . "<p>SQL: $sql");
		$i=0;
		if (mysqli_num_rows($res) > 0) {
			while ($row = mysqli_fetch_assoc($res)) {
				$arr['_x'.$i.'_i_carrimbo_idpessoa'] = $row['idpessoa'];
				$arr['_x'.$i.'_i_carrimbo_idobjeto'] =$this->BUFFER['1'][$iu]['sgdoc']['idsgdoc'];
				$arr['_x'.$i.'_i_carrimbo_tipoobjeto'] =$_GET['_modulo'];
				$arr['_x'.$i.'_i_carrimbo_versao'] =$_POST['versao_doc'];
				$arr['_x'.$i.'_i_carrimbo_status'] ="PENDENTE";
				$i++;
			}
			$rescmd = $_cmd->save($arr);
			if (!$rescmd) {
				cbSetPostHeader("0","erro");
				die("Não foi possível solicitar a assinatura dos participantes vinculados no documento.");
			}
		}
	}
	
}
$_tipo = $this->BUFFER['1']['i']['sgdoc']['idsgdoctipo'];

$grupo='1';

if(empty($_tipo) and !empty($this->BUFFER['x']['i']['sgdoc']['idsgdoctipo'])){

    $_tipo = $this->BUFFER['x']['i']['sgdoc']['idsgdoctipo'];
    $iu="i";
    $grupo='x';
}
if (!empty($readonly)) {
	die('Você não tem permissão para editar este documento!');
}
//se for um insert, o tipo de meio tiver sido informado e o lote estiver vazio
if($iu == "i"
	and (!empty($_tipo) 
	and empty($this->BUFFER["1"]["i"]["sgdoc"]["idregistro"]))){

	$_idregistro = geraRegistrosgdoc($_tipo);
	

	//Enviar o campo para a pagina de submit
	$this->BUFFER[$grupo]["i"]["sgdoc"]["idregistro"] = $_idregistro;

	//Selecionar o idstatus de acordo com o botão inserido como INICIAR
	$sql="SELECT mf.idstatus 
            FROM fluxo ms JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo  AND ms.modulo = '".$_GET['_modulo']."' AND ms.tipoobjeto = 'idsgdoctipo' AND ms.idobjeto = '".$_tipo."' AND ms.status = 'ATIVO'
			JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus AND s.statustipo = 'INICIO'
           WHERE 1 ".getidempresa('ms.idempresa', "fluxo"); 
    $res = d::b()->query($sql) or die("Erro ao buscar informções da configuração do status: ".mysqli_error(d::b()));    
    $row = mysqli_fetch_assoc($res);

	if ($row['idstatus']){
		$this->BUFFER[$grupo]["i"]["sgdoc"]["idstatus"] = $row['idstatus'];
	}
	
	//Atribuir o valor para retorno por session['post'] ah pagina anterior.
	$_SESSION["post"]["_".$grupo."_u_sgdoc_idregistro"] = $_idregistro;
	
	//d::b()->query("COMMIT") or die("prechange: Falha ao efetuar COMMIT [sequence]: ".mysqli_error());

}
if($this->BUFFER["1"]["u"]["sgdoc"]["status"] == 'REVISAO' AND ($statusant == 'AGUARDANDO' OR   $statusant == 'APROVADO' )){
	$this->BUFFER["1"]["u"]["sgdoc"]["acompversao"] = '';
}
if(($this->BUFFER["1"]["u"]["sgdoc"]["status"]=="APROVADO"  AND $statusant=="APROVADO") or ($this->BUFFER["1"]["u"]["sgdoc"]["status"]=="OBSOLETO"  AND $statusant=="OBSOLETO")){
    Die("O documento não poder ser salvo no status=".$this->BUFFER["1"]["u"]["sgdoc"]["status"]."<br> Caso queira salva-ló clique em Revisar para versionar o mesmo.");
}

if ($this->BUFFER["1"]["u"]["sgdoc"]["status"] == 'APROVADO' and empty($this->BUFFER["1"]["u"]["sgdoc"]["acompversao"]) ){

	$versao = traduzid('sgdoctipo', 'idsgdoctipo', 'flversao', $this->BUFFER['1']['i']['sgdoc']['idsgdoctipo']);
	if ($versao == 'Y') {
		die('O Campo Descrição do Histórico deve ser preenchido.');
	}
}

foreach ($this->BUFFER as $key => $value) {
	if(!empty($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"])){

		if ($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"] == 'pessoa') {
			unset($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["setor"]);
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["inseridomanualmente"] = 'S';
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["assinar"] = 'X';
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];

		}elseif($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"] == 'sgdepartamento'){
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["inseridomanualmente"] = 'S';
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["assinar"] = 'N';

			$arr = array();
			$i=0;
			$ii=0;
			$_CMD = new cmd();

			//Se for confirmado, irá inserir os Setores que estão no departamento
			if($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["setor"] == 'Y')
			{
				unset($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["setor"]);
				$sqlinsereitens = 'SELECT *	from sgsetor se
									where status="ATIVO" and se.idsgdepartamento = '.$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"]."
									and not exists(select 1 from fluxostatuspessoa fp where fp.idobjeto = se.idsgsetor and fp.tipoobjeto = 'sgsetor' and fp.idmodulo = ".$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"]." and fp.modulo like 'documento%')";
				$resa = d::b()->query($sqlinsereitens) or die("busca dos setores deste departamento falhou: " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");								

				if(mysqli_num_rows($resa) > 0){				
					// Acumula todos em um array e executar todos de uma vez
					while($r1 = mysqli_fetch_assoc($resa)){
						
						$arr["_xs".$ii."_i_fluxostatuspessoa_idobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"];
						$arr["_xs".$ii."_i_fluxostatuspessoa_tipoobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"];
						$arr["_xs".$ii."_i_fluxostatuspessoa_idobjeto"] = $r1['idsgsetor'];
						$arr["_xs".$ii."_i_fluxostatuspessoa_tipoobjeto"] = 'sgsetor';
						$arr["_xs".$ii."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
						$arr["_xs".$ii."_i_fluxostatuspessoa_inseridomanualmente"] = 'N';
						$arr["_xs".$ii."_i_fluxostatuspessoa_assinar"] = 'N';
						$arr["_xs".$ii."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"];
						$arr["_xs".$ii."_i_fluxostatuspessoa_modulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["modulo"];


						$sqlpessoas = 'select * from pessoaobjeto po where po.idobjeto = '.$r1['idsgsetor'].' and po.tipoobjeto ="sgsetor"';
						$resa1 = d::b()->query($sqlpessoas) or die("busca de pessoas falhou: " .mysqli_error(d::b()) . "<p>SQL: $sqlpessoas");
						while($r = mysqli_fetch_assoc($resa1)){

							$sqve="select idfluxostatuspessoa, idobjeto from fluxostatuspessoa where modulo = '".$_GET['_modulo']."' and idmodulo = ".$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"]."  and idobjeto=".$r['idpessoa'];
							$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
							if(mysqli_num_rows($rv) < 1){							
								$arr["_xf".$i."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"];
								$arr["_xf".$i."_i_fluxostatuspessoa_modulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["modulo"];
								$arr["_xf".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
								$arr["_xf".$i."_i_fluxostatuspessoa_idobjeto"] = $r["idpessoa"];
								$arr["_xf".$i."_i_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
								$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
								$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjeto"] = 'pessoa';
								$arr["_xf".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
								$arr["_xf".$i."_i_fluxostatuspessoa_assinar"] = "X";
								/*
								$arr["_xc".$i."_i_carrimbo_idpessoa"] = $r['idpessoa'];
								$arr["_xc".$i."_i_carrimbo_tipoobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"];
								$arr["_xc".$i."_i_carrimbo_idobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
								$arr["_xc".$i."_i_carrimbo_versao"] = $versao["versao"];
								*/
								$i++;
							}else {
								$rr = mysqli_fetch_assoc($rv);
								$arr["_xu".$i."_u_fluxostatuspessoa_idfluxostatuspessoa"] = $rr["idfluxostatuspessoa"];
								$arr["_xu".$i."_u_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
								$arr["_xu".$i."_u_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
								$arr["_xu".$i."_u_fluxostatuspessoa_inseridomanualmente"] = "S";
								$i++;
							}
						}
						$ii++;
					}
				}
			}else{
				unset($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["setor"]);
			}

			//Insere a Pessoa que está no Departamento.		
			$sqlinsereitens = 'select * from pessoaobjeto po where po.idobjeto = '.$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"].
				' and po.tipoobjeto ="'.$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"].'"';
			$resa = d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");
			while($r = mysqli_fetch_assoc($resa)){

				$sqve="select idfluxostatuspessoa, idobjeto from fluxostatuspessoa where modulo = '".$_GET['_modulo']."' and idmodulo = ".$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"]."  and idobjeto=".$r['idpessoa'];
				$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
				if(mysqli_num_rows($rv) < 1){							
					$arr["_xfd".$i."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_modulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["modulo"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_idobjeto"] = $r["idpessoa"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
					$arr["_xfd".$i."_i_fluxostatuspessoa_tipoobjeto"] = 'pessoa';
					$arr["_xfd".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
					$arr["_xfd".$i."_i_fluxostatuspessoa_assinar"] = "X";
					/*
					$arr["_xc".$i."_i_carrimbo_idpessoa"] = $r['idpessoa'];
					$arr["_xc".$i."_i_carrimbo_tipoobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"];
					$arr["_xc".$i."_i_carrimbo_idobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
					$arr["_xc".$i."_i_carrimbo_versao"] = $versao["versao"];
					*/
					$i++;
				}else {
					$rr = mysqli_fetch_assoc($rv);
					$arr["_xud".$i."_u_fluxostatuspessoa_idfluxostatuspessoa"] = $rr["idfluxostatuspessoa"];
					$arr["_xud".$i."_u_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
					$arr["_xud".$i."_u_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
					$arr["_xud".$i."_u_fluxostatuspessoa_inseridomanualmente"] = "S";
					$i++;
				}
			}

			if(!empty($arr)){
				$res = $_CMD->save($arr);
				if(!$res){
					unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
					die($_CMD->erro);
				}	
			}	
		}else {
			unset($this->BUFFER[$key]["i"]["fluxostatuspessoa"]["setor"]);
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjetoext"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["inseridomanualmente"] = 'S';
			$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["assinar"] = 'N';



			$sqlinsereitens = 'select * from pessoaobjeto po where po.idobjeto = '.$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idobjeto"].
			' and po.tipoobjeto ="'.$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["tipoobjeto"].'"';
			$resa = d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");								

			if(mysqli_num_rows($resa) > 0){
				$arr = array();
				$i=0;
				$_CMD = new cmd();

				
				// Acumula todos em um array e executar todos de uma vez
				while($r = mysqli_fetch_assoc($resa)){

					$sqve="select idfluxostatuspessoa, idobjeto from fluxostatuspessoa where modulo = '".$_GET['_modulo']."' and idmodulo = ".$this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"]."  and idobjeto=".$r['idpessoa'];
					$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
					if(mysqli_num_rows($rv) < 1){							
						$arr["_xf".$i."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["idmodulo"];
						$arr["_xf".$i."_i_fluxostatuspessoa_modulo"] = $this->BUFFER[$key]["i"]["fluxostatuspessoa"]["modulo"];
						$arr["_xf".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
						$arr["_xf".$i."_i_fluxostatuspessoa_idobjeto"] = $r["idpessoa"];
						$arr["_xf".$i."_i_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
						$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
						$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjeto"] = 'pessoa';
						$arr["_xf".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
						$arr["_xf".$i."_i_fluxostatuspessoa_assinar"] = "X";
						/*
						$arr["_xc".$i."_i_carrimbo_idpessoa"] = $r['idpessoa'];
						$arr["_xc".$i."_i_carrimbo_tipoobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"];
						$arr["_xc".$i."_i_carrimbo_idobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
						$arr["_xc".$i."_i_carrimbo_versao"] = $versao["versao"];
						*/
						$i++;
					}else {
						$rr = mysqli_fetch_assoc($rv);
						$arr["_xu".$i."_u_fluxostatuspessoa_idfluxostatuspessoa"] = $rr["idfluxostatuspessoa"];
						$arr["_xu".$i."_u_fluxostatuspessoa_idobjetoext"] = $r["idobjeto"];
						$arr["_xu".$i."_u_fluxostatuspessoa_tipoobjetoext"] = $r["tipoobjeto"];
						$arr["_xu".$i."_u_fluxostatuspessoa_inseridomanualmente"] = "S";
						$i++;
					}
				}

				$res = $_CMD->save($arr);
				if(!$res){
					unset($this->BUFFER[$key]["i"]["fluxostatuspessoa"]);
					die($_CMD->erro);
				}
				//unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
			}
		}
	}
}

if (!empty($this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"]) and !empty($this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"]) and !empty($this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idobjeto"]) and !empty($this->BUFFER["x"]["i"]["fluxostatuspessoa"]["tipoobjeto"]) ){
	//die(var_dump($this->BUFFER["x"]["i"]["fluxostatuspessoa"]));
	//$sqlv="SELECT versao from sgdoc where idsgdoc=".$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
	//$rv = d::b()->query($sqlv) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlv");	
	//$versao = mysqli_fetch_assoc($rv);
   

	

}

if (!empty($this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idfluxostatuspessoa"]) and !empty($this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idmodulo"]) and !empty($this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idobjetoext"])) {
	
	$sqlpfp='select * from fluxostatuspessoa where idobjetoext ='.$this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idobjetoext"].' and tipoobjetoext = "'.$_POST['_local_'].'" and inseridomanualmente="N"';
	$rpfp = d::b()->query($sqlpfp) or die("A Consulta de pessoas falhou :".mysql_error(d::b())."<br>Sql:".$sqlpfp);
	$npf= mysqli_num_rows($rpfp);
	$_CMD_2 = new cmd();
	$arrc = array();
	 $i= 0;
	while ($pfp = mysqli_fetch_assoc($rpfp)) {
		if($pfp['tipoobjeto'] == 'pessoa'){
			$sqlc='select c.* from carrimbo c join sgdoc sg on (sg.idsgdoc = c.idobjeto) where c.idpessoa='.$pfp['idobjeto'].' and c.idobjeto='.$pfp['idmodulo'].'';
			$rpc = d::b()->query($sqlc) or die("A Consulta de pessoas na carrimbo falhou :".mysql_error(d::b())."<br>Sql:".$sqlc);
			$npdc = mysqli_num_rows($rpc);
			if ($npdc == 0) {
				$arrc["_df".$i."_d_fluxostatuspessoa_idfluxostatuspessoa"] = $pfp['idfluxostatuspessoa'];
				$i++;
			}
		}

	}
	if (!empty($arrc)) {
		$res_1 = $_CMD_2->save($arrc);
		if(!$res_1){
			die($_CMD_2->erro);
		}
	 }
	//$delf="delete from fluxostatuspessoa where modulo='documento' and idmodulo=".$this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idmodulo"]." and idobjetoext=".$this->BUFFER["x"]["d"]["fluxostatuspessoa"]["idobjetoext"];
	//die($delf);
	//$exclui= d::b()->query($delf) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $delf");
	

}
if (empty($this->BUFFER["x"]["u"]["sgdoc"]["tipotreinamento"])  and !empty($this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"])) {

	$sql = 'select vc.iddocvinc,sg.* from sgdocvinc vc join sgdoc sg on(vc.iddocvinc = sg.idsgdoc) where  sg.idsgdoctipo = "treinamento" and vc.idsgdoc ='.$this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"];
	$resav = d::b()->query($sql) or die("SQL:".mysql_error(d::b())."<br>Sql:".$sql);
	$qtdd = mysqli_num_rows($resav);
	$_CMD = new cmd();
	if($qtdd > 0){		
		while ($r = mysqli_fetch_assoc($resav)) {
		$arr["_xf".$i."_u_sgdoc_idsgdoc"] = $r['iddocvinc'];
		$arr["_xf".$i."_u_sgdoc_idsgdoctipodocumento"] = '';
		$i++;
		}
		$res = $_CMD->save($arr);
		if(!$res){
			die($_CMD->erro);
		}		
	}
}elseif(!empty($this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"])){
	$sql = 'select vc.iddocvinc,sg.* from sgdocvinc vc join sgdoc sg on(vc.iddocvinc = sg.idsgdoc) where  sg.idsgdoctipo = "treinamento" and vc.idsgdoc ='.$this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"];
	$resav = d::b()->query($sql) or die("morre aqui :".mysql_error(d::b())."<br>Sql:".$sql);
	$qtdd = mysqli_num_rows($resav);
	$_CMD = new cmd();
	if($qtdd > 0){		
		while ($r = mysqli_fetch_assoc($resav)) {
		$arr["_xf".$i."_u_sgdoc_idsgdoc"] = $r['iddocvinc'];
		$arr["_xf".$i."_u_sgdoc_idsgdoctipodocumento"] = $this->BUFFER["x"]["u"]["sgdoc"]["tipotreinamento"];
		$i++;
		}
		$res = $_CMD->save($arr);
		if(!$res){
			die($_CMD->erro);
		}
	}
}

if (empty($this->BUFFER["x"]["u"]["sgdoc"]["tipoavaliacao"]) and !empty($this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"])) {
	$sql2 = 'select vc.iddocvinc,sg.* from sgdocvinc vc join sgdoc sg on(vc.iddocvinc = sg.idsgdoc) where  sg.idsgdoctipo = "avaliacao" and vc.idsgdoc ='.$this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"];
	$resav2 = d::b()->query($sql2) or die("A Consulta do relatário de versões falhou :".mysql_error(d::b())."<br>Sql:".$sql2);
	$qtdd2 = mysqli_num_rows($resav2);
	$arr2 = array();
	$_CMD2 = new cmd();
	$i1 = 0;
	if($qtdd2 > 0){		
		while ($r2 = mysqli_fetch_assoc($resav2)) {
		$arr2["_xf1".$i1."_u_sgdoc_idsgdoc"] = $r2['iddocvinc'];
		$arr2["_xf1".$i1."_u_sgdoc_idsgdoctipodocumento"] = '';
		$i1++;
		}
		$res = $_CMD2->save($arr2);
		if(!$res){
		die($_CMD2->erro);
		}		
	}

}elseif(!empty($this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"])){
	$sql2 = 'select vc.iddocvinc,sg.* from sgdocvinc vc join sgdoc sg on(vc.iddocvinc = sg.idsgdoc) where  sg.idsgdoctipo = "avaliacao" and vc.idsgdoc ='.$this->BUFFER["x"]["u"]["sgdoc"]["idsgdoc"];
	$resav2 = d::b()->query($sql2) or die("A Consulta do relatário de versões falhou :".mysql_error(d::b())."<br>Sql:".$sql2);
	$qtdd2 = mysqli_num_rows($resav2);
	$_CMD2 = new cmd();
	$arr2 = array();
	$i1 = 0;
	if($qtdd2 > 0){		
		while ($r2 = mysqli_fetch_assoc($resav2)) {
		$arr2["_xf1".$i1."_u_sgdoc_idsgdoc"] = $r2['iddocvinc'];
		$arr2["_xf1".$i1."_u_sgdoc_idsgdoctipodocumento"] = $this->BUFFER["x"]["u"]["sgdoc"]["tipoavaliacao"];
		$i1++;
		}
		$res = $_CMD2->save($arr2);
		if(!$res){
			die($_CMD2->erro);
		}
	}
}

if (!empty($this->BUFFER["x"]["i"]["vinculos"]["tipoobjetode"])
	and !empty($this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"])
	and !empty($this->BUFFER["x"]["i"]["vinculos"]["idobjetode"])
	and !empty($this->BUFFER["x"]["i"]["vinculos"]["tipoobjetopara"])) {
	 $sql = 'select * from pessoaobjeto po where po.idobjeto = '.$this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"].
	 ' and po.tipoobjeto ="'.$this->BUFFER["x"]["i"]["vinculos"]["tipoobjetopara"].'"
	 and not exists(select 1 from fluxostatuspessoa f where f.idobjeto = po.idpessoa and f.idmodulo='.$this->BUFFER["x"]["i"]["vinculos"]["idobjetode"].' and f.modulo="documento")';
	 $rps = d::b()->query($sql) or die("A Consulta do relatário de versões falhou :".mysql_error(d::b())."<br>Sql:".$sql);
	 $_CMD_1 = new cmd();
	 $array = array();
	 $i= 1;
	 $sqlv='select 1 from fluxostatuspessoa f where f.idobjeto = '.$this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"].' and f.idmodulo='.$this->BUFFER["x"]["i"]["vinculos"]["idobjetode"].' and f.modulo="documento"';
	 $rpv = d::b()->query($sqlv) or die("A Consulta do relatário de versões falhou :".mysql_error(d::b())."<br>Sql:".$sqlv);
	 if (mysqli_num_rows($rpv) == 0) {
		$array["_if0_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER["x"]["i"]["vinculos"]["idobjetode"];
		$array["_if0_i_fluxostatuspessoa_modulo"] = $_GET['_modulo'];
		$array["_if0_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
		$array["_if0_i_fluxostatuspessoa_idobjeto"] = $this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"];
		$array["_if0_i_fluxostatuspessoa_tipoobjeto"] = $this->BUFFER["x"]["i"]["vinculos"]["tipoobjetopara"];
		$array["_if0_i_fluxostatuspessoa_idobjetoext"] = $this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"];
		$array["_if0_i_fluxostatuspessoa_tipoobjetoext"] = $this->BUFFER["x"]["i"]["vinculos"]["tipoobjetopara"];
		$array["_if0_i_fluxostatuspessoa_inseridomanualmente"] = "S";
		$array["_if0_i_fluxostatuspessoa_assinar"] = "X";
	 }

	 while($rp = mysqli_fetch_assoc($rps)){
		$array["_if".$i."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER["x"]["i"]["vinculos"]["idobjetode"];
		$array["_if".$i."_i_fluxostatuspessoa_modulo"] = $_GET['_modulo'];
		$array["_if".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
		$array["_if".$i."_i_fluxostatuspessoa_idobjeto"] = $rp["idpessoa"];
		$array["_if".$i."_i_fluxostatuspessoa_tipoobjeto"] = 'pessoa';
		$array["_if".$i."_i_fluxostatuspessoa_idobjetoext"] = $this->BUFFER["x"]["i"]["vinculos"]["idobjetopara"];
		$array["_if".$i."_i_fluxostatuspessoa_tipoobjetoext"] = $this->BUFFER["x"]["i"]["vinculos"]["tipoobjetopara"];
		$array["_if".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
		$array["_if".$i."_i_fluxostatuspessoa_assinar"] = "X";
	 }
	 if (!empty($array)) {
		$res_1 = $_CMD_1->save($array);
		if(!$res_1){
			die($_CMD_1->erro);
		}
	 }
	 
}
if (!empty($this->BUFFER['1']['u']['sgdoc']['idsgdoc'])) {
	if ($this->BUFFER['1']['u']['sgdoc']['conteudo']) {
			$this->BUFFER['revisao']['u']['sgdoc']['revisao'] = $this->BUFFER['1']['u']['sgdoc']['revisao']+1;
			$this->BUFFER['revisao']['u']['sgdoc']['idsgdoc'] = $this->BUFFER['1']['u']['sgdoc']['idsgdoc'];
	}
}
?>
