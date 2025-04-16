<?
    /*
	*JLAL - 14-08-20 
	**Inclusao de validação para verificar se o usuário tem permissão para acessar o sistema 
	**se nao tiver dado entrada no ponto na data corrente**
	*/
    if(
		// $row['pontoweb']!=='Y' 
	 	// 	and 
			(empty($row['acesso']) || $row['acesso'] == 'N'))
	{
		date_default_timezone_set('America/Sao_Paulo');
		if(date('H:i:s') < date('06:00') || date('H:i:s') > date('18:00'))
		{
			alarmeSet('Y','logponto','Login Ponto1',$inusr);
			unset($_SESSION["SESSAO"]["USUARIO"]);
			unset($_SESSION["SESSAO"]["SENHA"]);
			unset($_SESSION["SESSAO"]["SUPERUSUARIO"]);
			$_SESSION["SESSAO"]["LOGADO"] = false;
			die('Fora do horario de acesso! ( 06:00 - 18:00 )');
		}
    }elseif ($row['acesso'] == 'B') {
		alarmeSet('Y','logponto','Login Ponto1',$inusr);
		unset($_SESSION["SESSAO"]["USUARIO"]);
		unset($_SESSION["SESSAO"]["SENHA"]);
		unset($_SESSION["SESSAO"]["SUPERUSUARIO"]);
		$_SESSION["SESSAO"]["LOGADO"] = false;
		die('Acesso bloqueado temporariamente! Entre em contato com o RH para solicitar o desbloqueio!');
	}
?>