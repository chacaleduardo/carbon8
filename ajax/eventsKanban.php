<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/evento_controller.php");
require_once("../form/controllers/eventotipo_controller.php");

$jwt = validaTokenReduzido();

if ($jwt["sucesso"] !== true) {
	echo JSON_ENCODE([
		'error' => "Erro: Não autorizado."
	]);
	die;
}

if($_POST){
    if(!$_POST['action']){
        die('Action não foi enviada!');
    }
    if($_POST['action'] == 'buscarEventosKaban'){
        $filtro = "";
        if($_POST['idempresa'] !== null){
            $arrUrgencia = explode(",", $_POST['idempresa']);
            $filtro .= " AND e.cliente in (";
            foreach($arrUrgencia as $urgencia){
                $filtro .= "'".$urgencia."',";
            }
            $filtro = substr($filtro, 0, -1);
            $filtro .= ")";
        }
    
        if($_POST['setor'] !== null){
            $arrSetor = explode(",", $_POST['setor']);
            $filtro .= " AND e.setor in (";
            foreach($arrSetor as $setor){
                $filtro .= "'".$setor."',";
            }
            $filtro = substr($filtro, 0, -1);
            $filtro .= ")";
        }
    
        if($_POST['status']){
            $filtro .= " AND e.idfluxostatus in (".$_POST['status'].")";
        }
    
        if($_POST['urgencia'] !== null){
            $arrUrgencia = explode(",", $_POST['urgencia']);
            $filtro .= " AND e.urgencia in (";
            foreach($arrUrgencia as $urgencia){
                $filtro .= "'".$urgencia."',";
            }
            $filtro = substr($filtro, 0, -1);
            $filtro .= ")";
        }
        
        if($_POST['criadopor']){
            $filtro .= " AND e.criadopor like '%".$_POST['criadopor']."%'";
        }
        
        if($_POST['iniciodatafim'] && $_POST['fimdatafim']){
            $filtro .= " AND e.datafim BETWEEN '".$_POST['iniciodatafim']."' AND '".$_POST['fimdatafim']."'";
        }

        if($_POST['responsavel'] !== null){
            $arrResp = explode(",", $_POST['responsavel']);
            $filtro .= " AND e.responsavel in (";
            foreach($arrResp as $resp){
                $filtro .= "'".$resp."',";
            }
            $filtro = substr($filtro, 0, -1);
            $filtro .= ")";
        }

        if($_POST['sustentacao'] !== null){
            $arrSust = explode(",", $_POST['sustentacao']);
            $filtro .= " AND e.bonificado in (";
            foreach($arrSust as $sust){
                $filtro .= "'".$sust."',";
            }
            $filtro = substr($filtro, 0, -1);
            $filtro .= ")";
        }

        if($_POST["evento"]){
            $filtro .= " AND e.evento like '%".$_POST["evento"]."%'";
        }

        $options = EventoTipoController::buscarCampoPorIdEventoTipoCampo(28,'textocurto5');
        if ($options["codedeletado"]){
            $options["code"] = $options["code"]. " UNION ".$options["codedeletado"];
        }
        $execCode = d::b()->query($options['code']) or die("Erro ao buscar cliente: " . mysqli_error(d::b()) . "<br>" . $options['code']);
        while($row = mysqli_fetch_array($execCode,MYSQLI_NUM)){
            $clientes[$row[0]] = $row[1];
        }
    
    
        $evento = EventoController::buscarEventosPorIdEventoTipoParaKanban($filtro);
        if(($_POST["lido"] == "0" || $_POST["lido"] == "N") && $evento){
            foreach($evento as $key => $value){
                foreach($value as $key2 => $value2){
                    if($value2["viu"] == $_POST["lido"]){
                        $eventoFormatado[$key][] = $value2;
                    }
                }
            }
        }else{
            $eventoFormatado = $evento;
        }
        foreach ($eventoFormatado as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if($value2["cliente"]){
                    $eventoFormatado[$key][$key2]["siglacliente"] = explode(" - ",$clientes[$value2["cliente"]])[0];
                }else{
                    $eventoFormatado[$key][$key2]["siglacliente"] = "";
                }
            }
        }
        echo JSON_ENCODE($eventoFormatado);
    }else if($_POST['action'] == 'sortEvento'){
        foreach($_POST as $key => $value){
            if ($key == 'action') {
                continue;
            }
            EventoController::updateEventoOrder($key, $value);
        }
        // $evento = EventoController::updateEventoOrder($_POST['id'], $_POST['order']);
        // echo JSON_ENCODE($evento);
    }
    
}else{
    die('TipoEvento não foi enviado!');
}