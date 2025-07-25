<?php

// Conexão à base dados.
$con = mysqli_connect('127.0.0.1', 'root', '', 'app_receitas');

// Valida se a conexão foi concluída.
if ($con) {
    echo "Conexão com a base de dados concluída com sucesso.\n";
} else {
    echo "Erro na conexão com a base de dados.\n";
}

// Fase 2: Organização do projeto e repositórios GitHub.

// Fase 3: Criação da base de dados e exportação de dados de teste.

// Fase 4: Operações CRUD básicas para receitas em app.php.

$fim = false;

while (!$fim) {
    echo "Escolha uma opção:\n";
    echo "Criar nova receita -> 1\n";
    echo "Listar todas as receitas -> 2\n";
    echo "Atualizar receita existente -> 3\n";
    echo "Apagar receita -> 4\n";
    echo "Sair -> 0\n";

    $opcao = readline("Escolha uma opção: ");

    switch ($opcao) {
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
        case '0':
            $fim = true;
            echo "Encerrando...\n";
            break;
        default:
            echo "Opção inválida.\n";
    }
}

function criarReceita($con) {
    $nome = readline("Nome da receita: ");
    $descricao = readline("Descrição: ");
    $tempo = (int)readline("Tempo de preparo (min): ");
    $doses = (int)readline("Número de doses: ");

    $sql = "INSERT INTO receitas (nome, descricao, tempo_preparacao, doses)
            VALUES ('$nome', '$descricao', $tempo, $doses)";
    if (!mysqli_query($con, $sql)) {
        echo "Erro ao inserir receita: " . mysqli_error($con) . "\n";
        return;
    }

    $receita_id = mysqli_insert_id($con);
    echo "Receita criada com sucesso. ID: $receita_id\n";

    // Categoria
    echo "\nCategorias disponíveis:\n";
    $res = mysqli_query($con, "SELECT * FROM categorias");
    while ($cat = mysqli_fetch_assoc($res)) {
        echo "{$cat['id']} - {$cat['nome']}\n";
    }
    $categoria_id = (int)readline("Indique o ID da categoria: ");
    mysqli_query($con, "INSERT INTO receita_categoria (receita_id, categoria_id) VALUES ($receita_id, $categoria_id)");

    // Ingredientes
    echo "\nIngredientes disponíveis:\n";
    $res = mysqli_query($con, "SELECT * FROM ingredientes");
    while ($ing = mysqli_fetch_assoc($res)) {
        echo "{$ing['id']} - {$ing['nome']}\n";
    }

    echo "Informe os ingredientes (digite 0 para encerrar):\n";
    while (true) {
        $ing_id = (int)readline("ID do ingrediente (0 para sair): ");
        if ($ing_id === 0) break;

        // Verifica se esse ingrediente já foi adicionado para essa receita
        $verifica = mysqli_query($con, "
            SELECT * FROM receita_ingredientes 
            WHERE receita_id = $receita_id AND ingrediente_id = $ing_id
        ");

        if (mysqli_num_rows($verifica) > 0) {
            echo "Este ingrediente já foi adicionado.\n";
            continue;
        }

        $quantidade = readline("Quantidade: ");
        $unidade = readline("Unidade de medida (g ou ml): ");

        $sql = "INSERT INTO receita_ingredientes (receita_id, ingrediente_id, quantidade, unidade_medida)
                VALUES ($receita_id, $ing_id, '$quantidade', '$unidade')";
        mysqli_query($con, $sql);
    }

    echo "Receita salva com sucesso.\n";
}

function listarReceitas($con) {
    $sql = "SELECT r.id, r.nome, r.descricao, r.tempo_preparacao, r.doses, c.nome AS categoria
            FROM receitas r
            LEFT JOIN receita_categoria rc ON r.id = rc.receita_id
            LEFT JOIN categorias c ON rc.categoria_id = c.id";
    $res = mysqli_query($con, $sql);

    if (mysqli_num_rows($res) === 0) {
        echo "Nenhuma receita encontrada.\n";
        return;
    }

    while ($r = mysqli_fetch_assoc($res)) {
        echo "\n-----------------------\n";
        echo "ID: {$r['id']}\n";
        echo "Nome: {$r['nome']}\n";
        echo "Descrição: {$r['descricao']}\n";
        echo "Tempo: {$r['tempo_preparacao']} min\n";
        echo "Doses: {$r['doses']}\n";
        echo "Categoria: {$r['categoria']}\n";

        // Ingredientes da receita
        $resIng = mysqli_query($con, "
            SELECT i.nome, ri.quantidade, ri.unidade_medida
            FROM receita_ingredientes ri
            JOIN ingredientes i ON ri.ingrediente_id = i.id
            WHERE ri.receita_id = {$r['id']}
        ");
        echo "Ingredientes:\n";
        while ($i = mysqli_fetch_assoc($resIng)) {
            echo "- {$i['nome']}: {$i['quantidade']} {$i['unidade_medida']}\n";
        }
    }
}

function atualizarReceita($con) {
    $id = (int)readline("ID da receita que deseja atualizar: ");

    $res = mysqli_query($con, "SELECT nome, descricao, tempo_preparacao, doses FROM receitas WHERE id = $id");
    if (mysqli_num_rows($res) === 0) {
        echo "Receita não encontrada.\n";
        return;
    }

    $r = mysqli_fetch_assoc($res);
    $novoNome = readline("Novo nome ({$r['nome']}): ") ?: $r['nome'];
    $novaDesc = readline("Nova descrição ({$r['descricao']}): ") ?: $r['descricao'];
    $novoTempo = readline("Novo tempo ({$r['tempo_preparacao']}): ") ?: $r['tempo_preparacao'];
    $novasDoses = readline("Novas doses ({$r['doses']}): ") ?: $r['doses'];

    $sql = "UPDATE receitas SET 
        nome = '$novoNome', 
        descricao = '$novaDesc',
        tempo_preparacao = $novoTempo,
        doses = $novasDoses
        WHERE id = $id";
    mysqli_query($con, $sql);
    echo "Receita atualizada com sucesso.\n";
}

function apagarReceita($con) {
    $id = (int)readline("ID da receita que deseja excluir: ");

    $res = mysqli_query($con, "SELECT * FROM receitas WHERE id = $id");
    if (mysqli_num_rows($res) === 0) {
        echo "Receita não encontrada.\n";
        return;
    }

    mysqli_query($con, "DELETE FROM receita_ingredientes WHERE receita_id = $id");
    mysqli_query($con, "DELETE FROM receita_categoria WHERE receita_id = $id");
    mysqli_query($con, "DELETE FROM receitas WHERE id = $id");

    echo "Receita excluída com sucesso.\n";
}

mysqli_close($con);