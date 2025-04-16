<?

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver seno executao via requisicao http
    include_once("../inc/php/functions.php");
}

$dataAtualizar = $_GET['dataAtualizar']; //Ano-Mes-dia
$mostrarLog = $_GET['mostrarLog'];

$sql="SELECT * from agencia where status = 'ATIVO' ";
$res = d::b()->query($sql) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sql);
while($row = $res->fetch_assoc()){
    $idagencia = $row['idagencia'];

    $Hoje = (empty($dataAtualizar)) ? date('Y-m-d') : $dataAtualizar;

    $sqlContapagar = "  WITH CTE AS (
                            SELECT 
                                idagencia,
                                saldo,
                                quitadoem,
                                quitadoemseg,
                                ROW_NUMBER() OVER (
                                    PARTITION BY idagencia, DATE(quitadoem) 
                                    ORDER BY quitadoem DESC, quitadoemseg DESC
                                ) AS ranked
                            FROM 
                                contapagar
                            WHERE 
                                quitadoem >= '$Hoje 00:00:00'
                        )
                        SELECT 
                            idagencia,
                            saldo,
                            quitadoem,
                            quitadoemseg
                        FROM 
                            CTE
                        WHERE 
                            ranked = 1
                            and idagencia = $idagencia
                        ORDER BY 
                            quitadoem ASC, idagencia;";
    $resContapagar = d::b()->query($sqlContapagar) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlContapagar);

    $arraySaldoDia = array();
    
    // Verifica se algo foi quitado hoje, se não, busca saldo do dia anterior
    if(mysqli_num_rows($resContapagar) == 0){
        // Busca saldo do dia de hoje
        $sqlSaldoHoje = "SELECT * from saldoagencia where idagencia = $idagencia and data = '$Hoje'";
        $resSaldoHoje = d::b()->query($sqlSaldoHoje) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlSaldoHoje);

        if($mostrarLog == 'Y'){ echo $sqlSaldoHoje.'<br />'; }

        // Se não tiver saldo para o dia de hoje, busca saldo do dia anterior
        if(mysqli_num_rows($resSaldoHoje) == 0){
            // Busca saldo do dia anterior
            $sqlSaldoOntem = "SELECT * from saldoagencia where idagencia = $idagencia and data = '".date('Y-m-d', strtotime('-1 day', strtotime($Hoje)))."'";
            $resSaldoOntem = d::b()->query($sqlSaldoOntem) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlSaldoOntem);

            if($mostrarLog == 'Y'){ echo $sqlSaldoOntem.'<br />'; }

            if(mysqli_num_rows($resSaldoOntem) == 0){
                $sql = "UPDATE saldoagencia set saldo = 0 where idagencia = $idagencia and data = '$Hoje'";
                d::b()->query($sql) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sql);
                
                if($mostrarLog == 'Y'){ echo $sql.'<br />'; }

            }else{
                $resSaldoOntemRow = $resSaldoOntem->fetch_assoc();
                $sql = "INSERT INTO saldoagencia (idagencia, data, saldo) VALUES ($idagencia, '$Hoje', ".$resSaldoOntemRow['saldo'].")";
                d::b()->query($sql) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sql);

                if($mostrarLog == 'Y'){ echo $resSaldoOntemRow.'<br />'; }
            }
        }
        
    }else{
        
        while($resContapagarRow = $resContapagar->fetch_assoc()){
            $diaQuitadoSemHora = explode(" ", $resContapagarRow['quitadoem'])[0];
            $valor = $resContapagarRow['saldo'];
            $arraySaldoDia[strtotime($diaQuitadoSemHora)] = $valor;
        }
        
        $novoSaldo = (!empty($dataAtualizar) && empty($arraySaldoDia[strtotime($Hoje)])) ? 0 : $arraySaldoDia[strtotime($Hoje)];

        // Verifica se tem saldo para o dia de hoje
        $sqlSaldoHoje = "SELECT * from saldoagencia where idagencia = $idagencia and data = '$Hoje'";
        $resSaldoHoje = d::b()->query($sqlSaldoHoje) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqlSaldoHoje);

        if($mostrarLog == 'Y'){ echo $sqlSaldoHoje.'<br />'; }
    
        if(mysqli_num_rows($resSaldoHoje) == 0){
            $sql = "INSERT INTO saldoagencia (idagencia, data, saldo) VALUES ($idagencia, '$Hoje', ".$novoSaldo.")";
            d::b()->query($sql) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sql);

            if($mostrarLog == 'Y'){ echo $sql.'<br />'; }

        }else{
            $sql = "UPDATE saldoagencia set saldo = ".$novoSaldo." where idagencia = $idagencia and data = '$Hoje'";
            d::b()->query($sql) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sql);

            if($mostrarLog == 'Y'){ echo $sql.'<br />'; }

        }
    }    
}

