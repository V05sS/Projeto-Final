<?php

// Criar uma conexão à base dados.
$con = mysqli_connect('127.0.0.1', 'root', '', 'app_receitas');

// Verificar se a conexão foi concluída
if ($con) {
    echo "Conexão com a base de dados concluída!\n";
} else {
    echo "Erro na conexão com a base de dados\n";
}

// Fechar conexão.
mysqli_close($con);

// Fase 2: base de dados funcionando corretamente.

// Fase 3: dados inseridos na base de dados.

?>