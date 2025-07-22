<?php

// Criar uma conexão à base dados.
$con = mysqli_connect('127.0.0.1', 'root', '', 'app_receitas');

// Verificar se a conexão foi concluída
if ($con) {
    echo "Conexão com a base de dados concluída com sucesso!\n";
} else {
    echo "Erro na conexão com a base de dados\n";
}

// Fase 2: base de dados funcionando corretamente.

// Fase 3: dados inseridos na base de dados.

// Fase 4: operações CRUD

$fim = false;

while (!$fim) {
    echo "Selecione uma opção:\n";
    echo "Criar nova receita -> 1\n";
    echo "Listar todas as receitas -> 2\n";
    echo "Atualizar receita existente -> 3\n";
    echo "Apagar receita -> 4\n";
    echo "Sair -> 0\n";

    $menu = readline("Escolha uma opção: ");

    switch ($menu) {
        case '0':
            echo "Até breve\n";
            $fim = true;
            break;
        case '1':
            criarReceita($con);
            break;
        case '2':
            listarReceitas($con);
            break;
        case '3':
            atualizarReceita($con);
            break;
        case '4':
            apagarReceita($con);
            break;
        default:
            echo "Opção inválida!\n";
            break;
    }
}

// Função para criar receita
function criarReceita($con) {
    $nome = readline("Nome da receita: ");
    $descricao = readline("Descrição: ");
    $tempo = (int)readline("Tempo de preparo (min): ");
    $doses = (int)readline("Número de doses: ");

    // Insere nome na tabela receita
    $sql1 = "INSERT INTO receita (nome) VALUES ('$nome')";
    if (!mysqli_query($con, $sql1)) {
        echo "Erro ao inserir receita: " . mysqli_error($con) . "\n";
        return;
    }

    $id_receita = mysqli_insert_id($con);

    // Insere detalhes na tabela receita_info
    $sql2 = "INSERT INTO receita_info (receita_id, descricao, tempo_preparacao, doses) 
             VALUES ($id_receita, '$descricao', $tempo, $doses)";
    if (!mysqli_query($con, $sql2)) {
        echo "Erro ao inserir detalhes da receita: " . mysqli_error($con) . "\n";
        return;
    }

    echo "Receita criada com sucesso!\n";
}

// Função para listar receitas
function listarReceitas($con) {
    $sql = "SELECT r.id, r.nome, i.descricao, i.tempo_preparacao, i.doses
            FROM receita r
            LEFT JOIN receita_info i ON r.id = i.receita_id";
    $res = mysqli_query($con, $sql);

    if (mysqli_num_rows($res) == 0) {
        echo "Nenhuma receita localizada.\n";
        return;
    }

    while ($r = mysqli_fetch_assoc($res)) {
        echo "ID: " . $r['id'] . "\n";
        echo "Nome: " . $r['nome'] . "\n";
        echo "Descrição: " . $r['descricao'] . "\n";
        echo "Tempo de preparo: " . $r['tempo_preparacao'] . " min\n";
        echo "Doses: " . $r['doses'] . "\n";
    }
}

// Função para atualizar receita
function atualizarReceita($con) {
    $id = (int)readline("ID da receita que pretenda atualizar: ");

    $sql = "SELECT r.nome, i.descricao, i.tempo_preparacao, i.doses
            FROM receita r
            LEFT JOIN receita_info i ON r.id = i.receita_id
            WHERE r.id = $id";

    $res = mysqli_query($con, $sql);

    if (mysqli_num_rows($res) == 0) {
        echo "Receita não encontrada.\n";
        return;
    }

    $dados = mysqli_fetch_assoc($res);

    $nome = readline("Novo nome [{$dados['nome']}]: ");
    $descricao = readline("Nova descrição [{$dados['descricao']}]: ");
    $tempo = readline("Novo tempo (min) [{$dados['tempo_preparacao']}]: ");
    $doses = readline("Novas doses [{$dados['doses']}]: ");

    // Caso o utilizador selecionar enter, mantém valor antigo
    $nome = $nome ?: $dados['nome'];
    $descricao = $descricao ?: $dados['descricao'];
    $tempo = $tempo === "" ? $dados['tempo_preparacao'] : (int)$tempo;
    $doses = $doses === "" ? $dados['doses'] : (int)$doses;

    $sql1 = "UPDATE receita SET nome='$nome' WHERE id = $id";
    $sql2 = "UPDATE receita_info SET descricao='$descricao', tempo_preparacao=$tempo, doses=$doses WHERE receita_id = $id";

    if (mysqli_query($con, $sql1) && mysqli_query($con, $sql2)) {
        echo "Receita atualizada com sucesso!\n";
    } else {
        echo "Erro ao atualizar: " . mysqli_error($con) . "\n";
    }
}

// Função para apagar receita
function apagarReceita($con) {
    $id = (int)readline("ID da receita que deseja excluir: ");

    $res = mysqli_query($con, "SELECT * FROM receita WHERE id = $id");
    if (mysqli_num_rows($res) == 0) {
        echo "Receita não localizada.\n";
        return;
    }

    $confirma = readline("Deseja excluir? (s/n): ");
    if (strtolower($confirma) != 's') {
        echo "Operação anulada.\n";
        return;
    }

    mysqli_query($con, "DELETE FROM receita_info WHERE receita_id = $id");
    mysqli_query($con, "DELETE FROM receita_ingredientes WHERE receita_id = $id");
    mysqli_query($con, "DELETE FROM receita_categoria WHERE receita_id = $id");
    $del = mysqli_query($con, "DELETE FROM receita WHERE id = $id");

    if ($del) {
        echo "Receita excluida com sucesso.\n";
    } else {
        echo "Erro ao excluir: " . mysqli_error($con) . "\n";
    }
}

mysqli_close($con);