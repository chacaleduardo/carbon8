<?
require_once("../inc/php/functions.php");

$cargo= $_GET['cargo']; 

if(empty($cargo)){
	die("Cargo NAO ENVIADO");
}

    $s = "
		select 
			scf.status,
			scf.idsgcargofuncao,
			sf.funcao
			
		from 
			sgcargofuncao scf
		join 
			sgfuncao sf on sf.idsgfuncao = scf.idsgfuncao
		where
			scf.status = 'ATIVO' and
			scf.idsgcargo = ".$cargo."
		order by
			sf.funcao;";

	$rts = d::b()->query($s) or die("Erro ao buscar Cargo: ". mysqli_error(d::b()));

	while ($r = mysqli_fetch_assoc($rts)) {
            echo $r["funcao"]."<br>";
        }
          
?>
