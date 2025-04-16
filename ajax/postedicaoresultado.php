<?
require_once("../inc/php/functions.php");


$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

if(!$_POST){
    echo JSON_ENCODE([
        'error' => "Erro: Nenhum dado recebido."
    ]);
    die;
}else{
    $update = [];
    $_POST = JSON_DECODE(array_keys($_POST)[0], true);
    foreach($_POST as $key => $value){
        $id = explode("_", $key)[1];
        $tabela = explode("_", $key)[3];
        $campo = explode("_", $key)[4];
        $value = str_replace("_", " ", $value);

        $tabledata = retarraytabdef($tabela);

        if($tabledata[$campo]["type"] == "int"){
            $update[] = "UPDATE ".$tabela." set ".$campo." = ".$value." where ".$tabledata["#pkfld"]." = ".$id;
        }elseif($tabledata[$campo]["type"] == "date"){
            // $value = date("Y-m-d", strtotime($value));
            $value = DateTime::createFromFormat('d/m/Y', $value);
            if($value){
                $value = $value->format('Y-m-d');
                $update[] = "UPDATE ".$tabela." set ".$campo." = '".$value."' where ".$tabledata["#pkfld"]." = ".$id;
            }
        }elseif($tabledata[$campo]["type"] == "datetime"){
            // $value = date("Y-m-d H:i:s", strtotime($value));
            $value = DateTime::createFromFormat('d/m/Y H:i:s', $value);
            if($value){
                $value = $value->format('Y-m-d H:i:s');
                $update[] = "UPDATE ".$tabela." set ".$campo." = '".$value."' where ".$tabledata["#pkfld"]." = ".$id;
            }
        }else{
            $update[] = "UPDATE ".$tabela." set ".$campo." = '".strtoupper(upperCaseAcentos($value))."' where ".$tabledata["#pkfld"]." = ".$id;
        
        }
    }
    foreach($update as $sql){
        d::b()->query($sql);
    }
    echo JSON_ENCODE([
        'success' => "Dados atualizados com sucesso."
    ]);
}
