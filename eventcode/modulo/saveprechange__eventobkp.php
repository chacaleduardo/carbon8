<?
	
    $iu                 = $_SESSION['arrpostbuffer']['1']['u']['evento']['idevento'] ? 'u' : 'i';

    $idevento           = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['idevento'];
    $prazo              = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['prazo'];
    $inicial            = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['inicio'];
    $inicio             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['inicio'];
    $fim                = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fim'];
    $iniciohms          = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['iniciohms'];
    $jsonConfig         = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['jsonconfig'];
    $fimhms             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['fimhms'];
    $repetirate         = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['repetirate'];
    $ideventotipo       = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['ideventotipo'];
    $evento             = $_SESSION['arrpostbuffer']['1'][$iu]['evento']['evento'];
    $_x_u_idevento      = $_SESSION['arrpostbuffer']['x']['u']['evento']['idevento'];
    $_idsgdoctipo       = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['idsgdoctipo'];
    $sgdocstatus        = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['status'];
    $titulo             = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['titulo'];
    $sgdoctipodocumento = $_SESSION['arrpostbuffer']['x']['i']['sgdoc']['idsgdoctipodocumento'];

	$ideventoresp       = $_SESSION['arrpostbuffer']['x']['d']['eventoresp']['ideventoresp'];

	$_eventoresp_idevento      = $_SESSION['arrpostbuffer']['x']['i']['eventoresp']['idevento'];







if (!empty($ideventoresp)){ 

	$sql = "SELECT idobjeto, e.idevento,tipoobjeto ,modulo, idmodulo from eventoresp r join evento e on e.idevento = r.idevento where ideventoresp = '".$ideventoresp."'";

	$res = d::b()->query($sql) or die("Erro ao carregar pessoas eventoresp: ".mysqli_error(d::b()));

	while ($r = mysqli_fetch_assoc($res)) {
		$idobjeto = $r['idobjeto'];
		$tipoobjeto = $r['tipoobjeto'];
		$idevento = $r['idevento'];
		
		if ($r["modulo"] and $r["idmodulo"])
			excluiAssinatura($r["idobjeto"], $r["modulo"], $r["idmodulo"]);
    
	}

	
	if ($tipoobjeto == 'imgrupo'){
		$sql = "SELECT ideventoresp from eventoresp where idobjetoext = '".$idobjeto."' and tipoobjetoext = 'imgrupo' and idevento = '".$idevento."'";

		$res = d::b()->query($sql) or die("Erro ao carregar pessoas imgrupo: ".mysqli_error(d::b()));
		$c = 10;
		while ($r = mysqli_fetch_assoc($res)) {
			$c++;
			$_SESSION['arrpostbuffer'][$c]['d']['eventoresp']['ideventoresp'] = $r['ideventoresp'];
		}

	}
}

    if (strlen($iniciohms) == 5) {
        $iniciohms .= ":12";
    }

    if (strlen($fimhms) == 5) {
        $fimhms .= ":12";
    }

    $verificaPrazo = json_decode($jsonConfig, true);
    if ($verificaPrazo["configprazo"]) {
        $fim = $prazo;
    }
    
    //valida prazo
    if (!empty($prazo) && $iu == 'i') {
        
        if ($iu === 'i') {

            $inicioDate = date("Ymd");
            $prazoDate  = DateTime::createFromFormat('d/m/Y H:i:s', $prazo.' '.$iniciohms)->format('Ymd');
   
            if ($inicioDate > $prazoDate) {

                die("Prazo não pode ser anterior a data inicial");
            }
        }
    }

    if (empty($_idsgdoctipo) && empty($idevento) &&  empty($ideventoresp) && empty($_eventoresp_idevento)) {

        if ($iu === 'u') {

            $sql = "SELECT ideventotipo, evento FROM evento WHERE idevento = ".$idevento;
        
            $res = d::b()->query($sql) or die("Erro carregar configuracao: ".mysqli_error(d::b()));
            
            $r = mysqli_fetch_assoc($res);
            
            if ($ideventotipo !== $r["ideventotipo"]) {
                die("Tipo do evento não pode ser alterado");
            }
        }
        
        if (empty($ideventotipo)) {
            die("Tipo evento deve ser selecionado");
        }

        if (empty($iniciohms) || empty($fimhms)) {

            $fimDate  = DateTime::createFromFormat('d/m/Y', $fim)->format('Ymd');
            $inicioDate = DateTime::createFromFormat('d/m/Y', $inicio)->format('Ymd');

        } else {

            if ($iniciohms === "00:00:12" || $fimhms === "00:00:12") {
                $fimDate  = DateTime::createFromFormat('d/m/Y', $fim)->format('Ymd');
                $inicioDate = DateTime::createFromFormat('d/m/Y', $inicio)->format('Ymd');
            } else {

                if (strlen($iniciohms) == 5) {
                    $fimDate    = DateTime::createFromFormat('d/m/Y H:i', $fim.' '.$fimhms)->format('YmdHi');
                    $inicioDate = DateTime::createFromFormat('d/m/Y H:i', $inicio.' '.$iniciohms)->format('YmdHi');
                } else {
                    $fimDate    = DateTime::createFromFormat('d/m/Y H:i:s', $fim.' '.$fimhms)->format('YmdHi');
                    $inicioDate = DateTime::createFromFormat('d/m/Y H:i:s', $inicio.' '.$iniciohms)->format('YmdHi');
                }
                
                $inicioYmd = DateTime::createFromFormat('d/m/Y', $inicio)->format('Ymd');
            }
        }
        
        if (empty($prazo) && $inicioDate > $fimDate) {
            die("A Data de início do evento não pode ser maior do que a data de fim do evento");
        }

        //se $repetirate for diferente de nulo, valida se é maior do que a data inicial do evento
        if (!empty($repetirate)) {
            $dateRepetirate = DateTime::createFromFormat('d/m/Y', $repetirate)->format('Ymd');
            //Inicio > Repetirate
            if (DateTime::createFromFormat('d/m/Y', $inicio)->format('Ymd')>=$dateRepetirate) {
                die("A Data de início do evento não pode ser maior ou igual a data final de Repetição");
            }
        }


    } elseif(!empty($_idsgdoctipo)) {
        
        if (empty($sgdocstatus) || empty($titulo) || empty($sgdoctipodocumento)) {
            die ("RNC inválida");
        }

        $_idregistro = geraRegistrosgdoc($_idsgdoctipo);        

        //Enviar o campo para a pagina de submit
        $_SESSION["arrpostbuffer"]["x"]["i"]["sgdoc"]["idregistro"] = $_idregistro;
        
        //Atribuir o valor para retorno por session['post'] ah pagina anterior.
        $_SESSION["post"]["_x_u_sgdoc_idregistro"] = $_idregistro;
    }
	

function excluiAssinatura($idPessoa, $modulo, $idmodulo) {
	
	$sql = "DELETE 
                FROM 
                    carrimbo 
                WHERE 
                    idpessoa    = ".$idPessoa."
					AND idobjeto    = ".$idmodulo."
					AND tipoobjeto  = '".$modulo."'
					AND status 		= 'PENDENTE';";

        $res = d::b()->query($sql) or die("Erro ao excluir assinatura: ".mysqli_error(d::b()));
}