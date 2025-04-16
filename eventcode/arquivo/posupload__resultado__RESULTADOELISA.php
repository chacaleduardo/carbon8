<?
$idresultado = $_POST['idobjeto'];
$inserelinhatextoelisa = true;

function insert_elisa_r($idresultado, $array, $column)
{

	$strsql = "insert into resultadoelisa(idempresa,idresultado,local,nome,".$column.")
	values (
	".$_SESSION["SESSAO"]["IDEMPRESA"].",
	$idresultado,
	'R',
	'".$array['nome']."',
	'".$array[$column]."')";

	if (!mysql_query($strsql)) {
		echo $strsql."<br>".mysql_error();
		die;
	}
}
function insert_elisa($idresultado, $array)
{

	$strsql = "insert into resultadoelisa(idempresa,idresultado,local,nome,well,OD,IE,result)
	values (
	".$_SESSION["SESSAO"]["IDEMPRESA"].",
	$idresultado,
	'C',
	'".$array['nome']."',
	'".$array['well']."',
	'".$array['OD']."',
	'".$array['IE']."',
	'".$array['result']."')";

	if (!mysql_query($strsql)) {
		echo $strsql."<br>".mysql_error();
		die;
	}
}
function remove_elisa($idresultado)
{
	$strsql = "update resultadoelisa set status = 'I' where idresultado = ".$idresultado." and status = 'A'";
	if (!mysql_query($strsql)) {
		echo $strsql."<br>".mysql_error();
		die;
	}
}

$handle = @fopen($arq_final, "r");
$grvtab1 = false;
$grvtab2 = false;
$grvtab3 = false;
if ($handle) {

	if ($_POST["tipokit"] == "IDEXX-LAUDO") {
		remove_elisa($idresultado);
		require_once(_CARBON_ROOT."/inc/rtfReader/rtfReader.php");

		$reader = new RtfReader();
		$rtf = file_get_contents($arq_final);
		$row = $reader->ParseOD($rtf);
		$formatter = new RtfHtml();
		$str = $formatter->Format($reader->root);
		$dom = new DOMDocument;
		$dom->loadHTML($str);
		$lines = $dom->getElementsByTagName('span');
		$count = -1;
		$amostra = -1;
		$placa = [];
		$array_well = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

		foreach ($lines as $line) {
			if ($line->nodeValue == "O.D." && $count < 8) {
				$count++;
				$amostra = 0;
				$placa[$count] = [];
			}
			if ($amostra > -1 && $amostra < 12 && $line->nodeValue != "O.D.") {
				array_push($placa[$count], floatval(str_replace(",", ".", $line->nodeValue)));
				$amostra++;
			}
		}

		$media_neg = ($placa[0][0] + $placa[1][0] + $placa[2][0] + $placa[3][0] + $placa[4][0]) / 5;
		$dp = sqrt(
			(pow(($placa[0][0] - $media_neg), 2) +
				pow(($placa[1][0] - $media_neg), 2) +
				pow(($placa[2][0] - $media_neg), 2) +
				pow(($placa[3][0] - $media_neg), 2) +
				pow(($placa[4][0] - $media_neg), 2)) / 4
		);
		$cut_of = $media_neg + (3 * $dp);
		$ie = $media_neg / $cut_of;
		#GMT OD
		#GMT IE
		$count = 1;
		$amostras = [];
		$controles = [];
		$ods = [];
		$ies = [];
		for ($x = 0; $x < 12; $x++) { //$row as $key_od=>$od
			for ($y = 0; $y < 8; $y++) { //$placa as $key_row=>$row
				//salvar as 5 primeiras amostras de controle, no inicio da fila
				$ie_amostra = number_format($placa[$y][$x] / $cut_of, 4);
				$result = $ie_amostra > 1.2 ? 'Pos!' : 'Neg';
				if ($x == 0 && $y < 5) {
					array_push($controles, [
						'nome' => null,
						'well' => 'NEG '.$array_well[$y].($x + 1),
						'OD' => $placa[$y][$x],
						'IE' => null,
						'result' => $result,
					]);
				} else {
					array_push($amostras, [
						'nome' => $count,
						'well' => $array_well[$y].($x + 1),
						'OD' => $placa[$y][$x],
						'IE' => $ie_amostra,
						'result' => $result,
					]);
					array_push($ods, $placa[$y][$x]);
					array_push($ies, $ie_amostra);
					$count++;
				}
			}
		}
		echo var_dump($amostras);
		foreach ($controles as $controle)
			insert_elisa($idresultado, $controle);
		foreach ($amostras as $amostra)
			insert_elisa($idresultado, $amostra);

		insert_elisa_r($idresultado, ['nome' => 'MED NEG', 'SP' => number_format($media_neg, 4)], 'SP');
		insert_elisa_r($idresultado, ['nome' => 'CUT OF', 'SP' => number_format($cut_of, 4)], 'SP');
		insert_elisa_r($idresultado, ['nome' => 'DP', 'SP' => number_format($dp, 4)], 'SP');
		insert_elisa_r($idresultado, ['nome' => 'IE', 'SP' => number_format($ie, 4)], 'SP');
		insert_elisa_r($idresultado, ['nome' => 'GMT OD', 'SP' => number_format(pow(array_product($ods), 1 / sizeOf($ods)), 5)], 'SP');
		insert_elisa_r($idresultado, ['nome' => 'GMT IE', 'SP' => number_format(pow(array_product($ies), 1 / sizeOf($ies)), 5)], 'SP');

		#echo var_dump($placa);
		fclose($handle);
	} else {
		$strsql = "update resultadoelisa set status = 'I' where idresultado = ".$idresultado." and status = 'A'";
		//echo $strsql."<BR>"; 
		$apagadados = true;

		mysql_query($strsql) or die("Erro ao inativar resultado anterior:".mysql_error());
		$iexec = 0;
		$maxilinha = 0;
		$tiporel = 0;
		$xnome = 0;
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if ($_POST["tipokit"] == "IDEXX") {


				if ($grvtab1) {

					/*
					* Estrutura em array a linha que sera analisada
					*/
					$vetlinha = array();
					$pos1 = stripos($buffer, chr(9)/*'	'*/) + 1;
					$pos2 = stripos(substr($buffer, $pos1, 200), '\par');
					$linha = substr($buffer, $pos1, $pos2);
					$vetlinha = explode('	', $linha);

					$ilinha = count($vetlinha);

					/*
					* Memoriza o tamanho do maior array para comparar se tipo de arquivo RTF [1] ou [2] ou [3]
					*/
					if ($ilinha > $maxilinha) {
						$maxilinha = $ilinha;
					}

					if ($ilinha >= 7) { //consider que qualquer excecao sera tratada como tipo 1
						/*
						* TIPO [1]: bronquite
						* nome-Well-O.D.-S/P-Titer-Group-Result
						*/
						$tiporel = 1;
					} elseif ($ilinha == 6) {
						/*
						* TIPO [2]
						* nome-Well-O.D.-Titer-Group-S/N
						*/
						$tiporel = 2;
					} elseif ($ilinha == 5) {
						/*
						* TIPO [2]
						* nome-Well-O.D.-S/P-Result
						*/
						$tiporel = 3;
					} elseif ($ilinha == 4) {
						//@498448 - INCLUSÃO DO RESULTADO ELISA INFLUENZA.
						/*
						* TIPO [4]
						* nome-Well-O.D.-Result
						*/
						$tiporel = 4;
					}
					echo ("\n <!-- tiporel:".$tiporel." -->");

					$possp = stripos($buffer, "S/P");
					$possn = stripos($buffer, "S/N");
					//echo $buffer."<BR>";

					if (($possp !== false or $possn !== false) and ($ilinha <= 3)) {
						$grvtab2 = true;
					} else {
						//print_r($vetlinha);echo count($vetlinha)."\n\n"; // INSPECIONAR PARA CASO DE LIXO
						$poswelltab2 = stripos($buffer, "Well");
						$posodtab2 = stripos($buffer, "O.D.");
						print_r($vetlinha);
						echo ("aqui");

						if (!empty($vetlinha[0]) and $vetlinha[0] !== 'Page' and count($vetlinha) >= 3 and !$poswelltab2 and !$posodtab2) { //Nao gravar lixo
							if ($grvtab2 === true) {
								if ($vetlinha[0] != "AMn:") {
									$strsql = "insert into resultadoelisa
										(idempresa,idresultado, local,nome,SP,titer)
										Values
										(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'R','".strtoupper(str_replace(":", "", $vetlinha[0]))."','".trim($vetlinha[1])."','".trim($vetlinha[2])."')";
									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							} else {

								if (
									($inserelinhatextoelisa == true
										and (strtoupper($vetlinha[0]) == "NEG"	or strtoupper($vetlinha[0]) == "POS"))
									or ((strtoupper($vetlinha[0]) != "NEG" and strtoupper($vetlinha[0]) != "POS"))
								) {

									echo "<!-- ".$rotulo." -->";

									if (strtoupper($vetlinha[0]) == "NEG"	or strtoupper($vetlinha[0]) == "POS") {
										$strsql = "insert into resultadoelisa
											(idempresa,idresultado,local,nome,well,OD)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[0])."','".trim($vetlinha[1])."','".trim($vetlinha[2])."')";
									} elseif ($tiporel == 1) {
										//maf: valida para evitar erros vindos do arquivo
										if (!is_numeric(trim($vetlinha[5]))) $errogruporesult = "O valor apresentado na coluna Group contém caracteres não numéricos: [<b>".trim($vetlinha[5])."</b>]";

										$strsql = "insert into resultadoelisa
											(idempresa,idresultado,local,nome,well,OD,SP,titer,grupo,result)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[0])."','".trim($vetlinha[1])."','".trim($vetlinha[2])."','".trim($vetlinha[3])."','".trim($vetlinha[4])."','".trim($vetlinha[5])."','".trim($vetlinha[6])."')";
									} elseif ($tiporel == 2) {
										//maf: valida para evitar erros vindos do arquivo
										if (!is_numeric(trim($vetlinha[5]))) $errogruporesult = "O valor apresentado na coluna Group contém caracteres não numéricos: [<b>".trim($vetlinha[5])."</b>]";

										$strsql = "insert into resultadoelisa
											(idempresa,idresultado,local,nome,well,OD,SN,titer,grupo)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[0])."','".trim($vetlinha[1])."','".trim($vetlinha[2])."','".trim($vetlinha[3])."','".trim($vetlinha[4])."','".trim($vetlinha[5])."')";
									} elseif ($tiporel == 3) {
										//maf: valida para evitar erros vindos do arquivo
										if (is_numeric(trim($vetlinha[4]))) $errogruporesult = "O valor apresentado na coluna Result contém caracteres numéricos: [<b>".trim($vetlinha[5])."</b>]";

										$strsql = "insert into resultadoelisa
											(idempresa,idresultado,local,nome,well,OD,SP,result)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[0])."','".trim($vetlinha[1])."','".trim($vetlinha[2])."','".trim($vetlinha[3])."','".trim($vetlinha[4])."')";
										//@498448 - INCLUSÃO DO RESULTADO ELISA INFLUENZA. Lucas Melo
									} elseif ($tiporel == 4) {
										//maf: valida para evitar erros vindos do arquivo
										if (is_numeric(trim($vetlinha[3]))) $errogruporesult = "O valor apresentado na coluna Result contém caracteres numéricos: [<b>".trim($vetlinha[3])."</b>]";

										$strsql = "insert into resultadoelisa
											(idempresa,idresultado,local,nome,well,OD,result)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[0])."','".trim($vetlinha[1])."','".trim($vetlinha[2])."','".trim($vetlinha[3])."')";
									}
									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							}
						}
					}
				} else {
					/*
					* maf050511: Tenta verificar se o usuario selecionou a opcao errada de extracao do arquivo para upload
					* A opção certa deve ser "Analize Case Report"
					*/
					$poscompare = stripos($buffer, "Compare Case Report");

					if ($poscompare !== false) {
					?>
						<script language="javascript">
							alert("Erro: Opcao do XCheck (Compare Case) selecionada incorretamente!");
						</script>
					<?
					}

					$poswell = stripos($buffer, "Well");
					$posod = stripos($buffer, "O.D.");
					echo ($buffer);

					if ($poswell !== false /*&& $posod !== false*/) {
						//Alcançou o ponto em que os dados iniciam no arquivo rtf
						//A partir deste ponto, para cada linha retornada, a lógica ira entrar no laco superior
						$grvtab1 = true;
						$apagadados = false;
					}
				} //$grvtab1

			} elseif ($_POST["tipokit"] == "AFFINITECK") { //$_POST["tipokit"]=="IDEXX" HERMESP - 20122013

				if ($grvtab2) {

					$vetlinha = array();
					$vetlinha = explode(' ', $buffer);
					########################
					$Statistical = stripos($buffer, "Stat");
					$Statisticalf = stripos($buffer, "tical");

					//	echo $buffer."<BR>";						

					if ($Statistical !== false or $Statisticalf !== false) {
						$grvtab3 = true;
					} else {
						//print_r($vetlinha);echo count($vetlinha)."\n\n"; // INSPECIONAR PARA CASO DE LIXO
						$poswelltab2 = stripos($buffer, "Layout");
						$posodtab2 = stripos($buffer, "OD");
						$pospage = stripos($buffer, "Pag");
						$posneg = stripos($buffer, "(Ne");
						$poscont = stripos($buffer, "(");
						$posstat = stripos($buffer, "Stati");
						$postiter = stripos($buffer, "Titer");
						$posPositives = stripos($buffer, ":");
						$posCase = stripos($buffer, "Case");
						$posComments = stripos($buffer, "Comm");
						//print_r($buffer);echo("<br>");

						if (
							!empty($vetlinha[0]) and $posPositives === false  and $posComments === false  and $pospage === false and $posneg === false and $poscont === false and $posCase === false
							and $postiter === false	and $posstat === false and count($vetlinha) >= 3 and !$poswelltab2 and !$posodtab2
						) { //Nao gravar lixo
							if ($grvtab3 === true) {
								$posAMean = stripos($buffer, "AM");
								if ($posAMean === false) {

									$find = array("ean", ":", "StDev");
									$replace = array("N", "", "SD");

									$strsql = "Insert Into resultadoelisa
										(idempresa,idresultado, local,nome,SP,titer)
										Values
										(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'R','".strtoupper(str_replace($find, $replace, $vetlinha[0]))."','".trim($vetlinha[1])."','".trim($vetlinha[3])."')";
									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							} else {
								if (
									($inserelinhatextoelisa == true
										and (strtoupper($vetlinha[0]) == "(NEG)"	or strtoupper($vetlinha[0]) == "(POS)"))
									or ((strtoupper($vetlinha[0]) != "(NEG)" and strtoupper($vetlinha[0]) != "(POS)"))
								) {

									// echo "<!-- ".print_r($vetlinha)." -->";
									//die("->".$tiporel);						
									echo "<!-- ".$rotulo." -->";
									//maf: valida para evitar erros vindos do arquivo
									//if(!is_numeric(trim($vetlinha[$titer])))$errogruporesult = "O valor apresentado na coluna Titer contém caracteres não numéricos: [<b>".trim($vetlinha[$titer])."</b>]";
									$xnome = $xnome + 1;
									$strsql = "Insert Into resultadoelisa
										(idempresa,idresultado,local,nome,well,OD,SP,titer,grupo,result)
											Values
											(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".$xnome."','".trim($vetlinha[$well])."','".trim($vetlinha[$od])."','".trim($vetlinha[$sp])."','".trim($vetlinha[$titer])."','".trim($vetlinha[$grupo])."','".substr(trim($vetlinha[$result]), 0, 3)."')";

									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							}
						}
					}
					###########################
				} else {
					/*
					* maf050511: Tenta verificar se o usuario selecionou a opcao errada de extracao do arquivo para upload
					* A opção certa deve ser "Analize Case Report"
					*/
					$poscompare = stripos($buffer, "Compare Case Report");

					if ($poscompare !== false) {
					?>
						<script language="javascript">
							alert("Erro: Opcao do XCheck (Compare Case) selecionada incorretamente!");
						</script>
					<?
					}

					$buffer = preg_replace("/[^a-zA-Z0-9\s]/", "", $buffer);


					$poswell = stripos($buffer, "Layout");
					$posod = stripos($buffer, "OD");




					if ($poswell !== false && $posod !== false) {
						//Alcançou o ponto em que os dados iniciam no arquivo TXT
						//A partir deste ponto, para cada linha retornada, a lógica ira entrar no laco superior
						$grvtab2 = true;
						$apagadados = false;

						### ATUALIZACAO DANIEL -hermesp
						//TROCAR OS VALORES DA LINHA POR VALORES DEFAUL
						$xvetlinha = array();
						$xsearch  = array('Layout', 'Corrected SP', 'ELISA Units', 'Titer grouping', 'Age', 'Result');
						$xreplace = array('well', 'SP', 'lixoelisa', 'grupo', 'lixoage', 'result');
						$xlinha = str_replace($xsearch, $xreplace, $buffer); //limpa a linha
						$xvetlinha = explode(' ', $xlinha);
						$xilinha = count($xvetlinha);
						//print_r($xvetlinha);
						//IDENTIFICAR OS CAMPOS PARA O INSERT DOS DADOS
						while (list($xkey, $xvalue) = each($xvetlinha)) {
							//$rotulo[$xkey]=strtolower($xvalue);								
							if (trim(strtolower($xvalue)) == "result") {
								$result = $xkey;
							} elseif (trim(strtolower($xvalue)) == "od") {
								$od = $xkey;
							} elseif (trim(strtolower($xvalue)) == "sp") {
								$sp = $xkey;
							} elseif (trim(strtolower($xvalue)) == "grupo") {
								$grupo = $xkey;
							} elseif (trim(strtolower($xvalue)) == "well") {
								$well = $xkey;
							} elseif (trim(strtolower($xvalue)) == "titer") {
								$titer = $xkey;
							} elseif (trim(strtolower($xvalue)) == "lixoage") {
								$lixo1 = $xkey;
							} elseif (trim(strtolower($xvalue)) == "lixoelisa") {
								$lixo2 = $xkey;
							} else {
								echo ($buffer);
								die("Erro, verificar configuração do arquivo campo[".trim(strtolower($xvalue))."] e ou entrar em contato com o administrador do sistema.");
							}
						}
						//print_r($rotulo);die;
						###FIM ATUALIZACAO DANIEL -hermesp						
					}
				} //$grvtab2	

			} elseif ($_POST["tipokit"] == "BIOCHEK") { //$_POST["tipokit"]=="BIOCHEK" HERMESP - 13022014
				//echo($buffer);

				if ($gravadados) {
					//troca algums valores
					$xsearch  = array(' 	', '	', 'GMT:', '%CV:');
					$xreplace = array(' ', ' ', 'GMN', 'CV');
					$buffer = str_replace($xsearch, $xreplace, $buffer); //limpa a linha

					$vetlinha = array();
					$vetlinha = explode(' ', $buffer);

					//retirar espaços
					$vetlinha = array_filter($vetlinha, 'strlen');
					$vetlinha = (array_slice($vetlinha, 0));

					$posTarget = stripos($buffer, "Target");
					$posInterpretation = stripos($buffer, "Interpretation");
					$posPositive = stripos($buffer, "Positive");
					$posPrinted = stripos($buffer, "Printed");
					//$posvpos = stripos($buffer, "+");

					if ($posTarget === false and $posInterpretation === false and $posPositive === false and $posPrinted === false) {
						//echo($buffer."<BR>");
						$posWell = stripos($buffer, "Well");

						if ($posWell == true) {
							$gravadados2 = true;

							//TROCAR OS VALORES DA LINHA POR VALORES DEFAUL
							$xvetlinha = array();
							$buffer = preg_replace("/[^a-zA-Z0-9\s]/", "", $buffer);
							$xsearch  = array('Sample ID', 'Well', 'SP Ratio', 'Titer Group', 'Raw OD', 'Result', 'Titer', ' 	', '	');
							$xreplace = array('nome', 'well', 'SP', 'grupo', 'od', 'result', 'titer', ' ', ' ');
							$xlinha = str_replace($xsearch, $xreplace, $buffer); //limpa a linha

							//	echo($xlinha);
							$xvetlinha = explode(' ', $xlinha);
							//retirar espaços
							$xvetlinha = array_filter($xvetlinha, 'strlen');
							$xvetlinha = (array_slice($xvetlinha, 0));

							$xilinha = count($xvetlinha);
							//print_r($xvetlinha);
							//IDENTIFICAR OS CAMPOS PARA O INSERT DOS DADOS
							while (list($xkey, $xvalue) = each($xvetlinha)) {
								//$rotulo[$xkey]=strtolower($xvalue);
								if (trim(strtolower($xvalue)) == "result") {
									$result = $xkey;
								} elseif (trim(strtolower($xvalue)) == "od") {
									$od = $xkey;
								} elseif (trim(strtolower($xvalue)) == "sp") {
									$sp = $xkey;
								} elseif (trim(strtolower($xvalue)) == "grupo") {
									$grupo = $xkey;
								} elseif (trim(strtolower($xvalue)) == "well") {
									$well = $xkey;
								} elseif (trim(strtolower($xvalue)) == "titer") {
									$titer = $xkey;
								} elseif (trim(strtolower($xvalue)) == "nome") {
									$nome = $xkey;
								} elseif (trim(strtolower($xvalue)) == "lixoelisa") {
									$lixo2 = $xkey;
								}
							}
							//print_r($xvetlinha);
							//fazer cadastro
						} else {

							if ($gravadados2 == false) {
								//print_r($vetlinha);
								// BUSCAR CAMPO MIN-MAX

								$titminmax = stripos($buffer, "Min-Max");

								//min e max estão na mesma linha
								if ($titminmax === false and !empty($vetlinha[1])) {

									//echo("OUTROS TITULOS ");
									//print_r($vetlinha);
									$strsql = "Insert Into resultadoelisa(idempresa,idresultado, local,nome,titer)
											Values(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'R','".strtoupper(str_replace($find, $replace, $vetlinha[0]))."','".trim($vetlinha[1])."')";
									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								} elseif (!empty($vetlinha[1])) {
									//echo("MAXIMO E MINIMO ");
									//print_r($vetlinha);
									$strsql = "Insert Into resultadoelisa(idempresa,idresultado, local,nome,titer)
											Values(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'R','MIN','".trim($vetlinha[2])."'),(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'R','MAX','".trim($vetlinha[4])."')";
									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							} else {

								//ODs
								//echo("ODs ");
								//	print_r($vetlinha);
								//	echo("nome(".$nome.") well(".$well.") od(".$od.") sp(".$sp.") titer(".$titer.") grupo(".$grupo.") result(".$result.")");
								//die("->".$tiporel);
								//insere os ODS

								if (!empty($vetlinha[4])) {

									$resultElisa = str_replace("[", "", (str_replace("]", "", $vetlinha[$result])));

									$strsql = "Insert Into resultadoelisa(idempresa,idresultado,local,nome,well,OD,SP,titer,grupo,result)
																										Values
																										(".$_SESSION["SESSAO"]["IDEMPRESA"].",$idresultado,'C','".trim($vetlinha[$nome])."','".trim($vetlinha[$well])."','".trim($vetlinha[$od])."','".trim($vetlinha[$sp])."','".trim($vetlinha[$titer])."','".trim($vetlinha[$grupo])."','".substr(trim($resultElisa), 0, 3)."')";

									if (!mysql_query($strsql)) {
										echo $strsql."<br>".mysql_error();
										die;
									}
								}
							}
						}
						//echo($buffer."<br>");
					}
				} else {

					$buffer = preg_replace("/[^a-zA-Z0-9\s]/", "", $buffer);
					$posMean = stripos($buffer, "Mean");

					if ($posMean !== false) {
						//echo("grava");
						//Alcançou o ponto em que os dados iniciam no arquivo TXT
						//A partir deste ponto, para cada linha retornada, a lógica ira entrar no laco superior
						$gravadados = true;
						$apagadados = false;
					}
				} //$gravadados									
			} //$_POST["tipokit"]=="BIOCHEK"	
		} //while (!feof($handle))
		//die('FIM');
		fclose($handle);
	}
} else {
	echo "Problema ao abrir arquivo!";
}
