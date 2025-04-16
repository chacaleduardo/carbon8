<?

class ImMsgConfPlataformaQuery
{
    public static function buscarPlataformas()
    {
        return "SELECT idimmsgconfplataforma,
                        idplataforma
                from immsgconfplataforma
                where idimmsgconf = ?idimmsgconf?";
    }

    
}

?>