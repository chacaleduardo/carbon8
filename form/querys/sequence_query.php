<?
    class SequenceQuery
    {
        public static function inserir()
        {
            return "INSERT INTO sequence (
                        `sequence`, idempresa, chave1, chave2, chave3,
                        exercicio, descricao
                    )
                    VALUES (
                        '?sequence?', ?idempresa?, ?chave1?, ?chave2?, ?chave3?,
                        ?exercicio?, '?descricao?'
                    )";
        }

        public static function atualizarChavePorIdEmpresa()
        {
            return "UPDATE sequence SET chave1 = (chave1 + 1) WHERE sequence = 'tag' AND idempresa = ?idempresa?";
        }

        public static function atualizarChavePorIdEmpresaExercicio()
        {
            return "UPDATE sequence SET chave1 = (chave1 + 1) WHERE sequence = '?sequence?' AND idempresa = ?idempresa? AND exercicio = ?exercicio?";
        }

        public static function verificaSeExisteSequence()
        {
            return "SELECT count(*) as quant FROM sequence where sequence = '?sequence?' and  exercicio = year(current_date) ";
        }

        public static function buscarSequenceDoAno()
        {
            return "SELECT chave1,exercicio FROM sequence where sequence = '?sequence?'  and  exercicio = year(current_date)";
        }

        public static function verificarSeTagSequenceExiste()
        {
            return "SELECT 1 FROM sequence where sequence = '?sequence?' and idempresa = ?idempresa?";
        }
    }
?>