<?php

// Conexão à base dados.
$con = mysqli_connect('127.0.0.1', 'root', '', 'app_receitas');

// Valida se a conexão foi concluída.
if ($con) {
    echo "Conexão com a base de dados concluída com sucesso.\n";
} else {
    echo "Erro na conexão com a base de dados.\n";
    exit; // Para evitar continuar se não conectar
}

// Fase 2: Organização do projeto e repositórios GitHub.

// Fase 3: Criação da base de dados e exportação de dados de teste.

// Fase 4: Operações CRUD básicas para receitas em app.php.

// Fase 5: Gestão de categorias e associação receita_categoria.

// Fase 6: Gestão de ingredientes e composição das receitas (pendente de avaliação do Guilherme).

$fim = false;

while (!$fim) {
    echo "\nEscolha uma opção:\n";
    echo "Criar nova receita -> 1\n";
    echo "Listar todas as receitas -> 2\n";
    echo "Atualizar receita existente -> 3\n";
    echo "Apagar receita -> 4\n";
    echo "Criar nova categoria -> 5\n";
    echo "Listar categorias -> 6\n";
    echo "Associar receita a categoria -> 7\n";
    echo "Desassociar receita de categoria -> 8\n";
    echo "Listar receitas por categoria -> 9\n";
    echo "Criar novo ingrediente -> 10\n";
    echo "Listar ingredientes -> 11\n";
    echo "Atualizar ingrediente de receita -> 12\n";
    echo "Remover ingrediente de receita -> 13\n";
    echo "Ver detalhes completos da receita -> 14\n";
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
        case '5':
            criarCategoria($con);
            break;
        case '6':
            listarCategorias($con);
            break;
        case '7':
            associarCategoria($con);
            break;
        case '8':
            desassociarCategoria($con);
            break;
        case '9':
            listarReceitasPorCategoria($con);
            break;
        case '10':
            criarIngrediente($con);
            break;
        case '11':
            listarIngredientes($con);
            break;
        case '12':
            atualizarIngredienteReceita($con);
            break;
        case '13':
            removerIngredienteReceita($con);
            break;
        case '14':
            detalhesCompletosReceita($con);
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

// Funções adicionais (fase 5)

function criarCategoria($con) {
    $nome = readline("Nome da nova categoria: ");
    $sql = "INSERT INTO categorias (nome) VALUES ('$nome')";
    if (mysqli_query($con, $sql)) {
        echo "Categoria criada com sucesso.\n";
    } else {
        echo "Erro ao criar categoria: " . mysqli_error($con) . "\n";
    }
}

function listarCategorias($con) {
    $res = mysqli_query($con, "SELECT * FROM categorias");
    if (mysqli_num_rows($res) === 0) {
        echo "Nenhuma categoria encontrada.\n";
        return;
    }
    echo "Categorias:\n";
    while ($cat = mysqli_fetch_assoc($res)) {
        echo "{$cat['id']} - {$cat['nome']}\n";
    }
}

function associarCategoria($con) {
    listarReceitas($con);
    $idReceita = (int)readline("ID da receita para associar categoria: ");
    
    listarCategorias($con);
    $idCategoria = (int)readline("ID da categoria para associar: ");

    $check = mysqli_query($con, "SELECT * FROM receita_categoria WHERE receita_id = $idReceita AND categoria_id = $idCategoria");
    if (mysqli_num_rows($check) > 0) {
        echo "Essa associação já existe.\n";
        return;
    }

    $sql = "INSERT INTO receita_categoria (receita_id, categoria_id) VALUES ($idReceita, $idCategoria)";
    if (mysqli_query($con, $sql)) {
        echo "Categoria associada com sucesso.\n";
    } else {
        echo "Erro na associação: " . mysqli_error($con) . "\n";
    }
}

function desassociarCategoria($con) {
    listarReceitas($con);
    $idReceita = (int)readline("ID da receita para desassociar categoria: ");

    $res = mysqli_query($con, "
        SELECT c.id, c.nome FROM categorias c
        JOIN receita_categoria rc ON c.id = rc.categoria_id
        WHERE rc.receita_id = $idReceita
    ");

    if (mysqli_num_rows($res) === 0) {
        echo "Nenhuma categoria associada a essa receita.\n";
        return;
    }

    echo "Categorias associadas:\n";
    while ($cat = mysqli_fetch_assoc($res)) {
        echo "{$cat['id']} - {$cat['nome']}\n";
    }

    $idCategoria = (int)readline("ID da categoria para desassociar: ");
    $sql = "DELETE FROM receita_categoria WHERE receita_id = $idReceita AND categoria_id = $idCategoria";
    if (mysqli_query($con, $sql)) {
        echo "Categoria desassociada com sucesso.\n";
    } else {
        echo "Erro na desassociação: " . mysqli_error($con) . "\n";
    }
}

function listarReceitasPorCategoria($con) {
    listarCategorias($con);
    $idCategoria = (int)readline("Digite o ID da categoria para filtrar receitas: ");

    $sql = "SELECT r.id, r.nome, r.descricao, r.tempo_preparacao, r.doses
            FROM receitas r
            JOIN receita_categoria rc ON r.id = rc.receita_id
            WHERE rc.categoria_id = $idCategoria";

    $res = mysqli_query($con, $sql);
    if (mysqli_num_rows($res) === 0) {
        echo "Nenhuma receita encontrada para essa categoria.\n";
        return;
    }

    while ($r = mysqli_fetch_assoc($res)) {
        echo "ID: {$r['id']}\n";
        echo "Nome: {$r['nome']}\n";
        echo "Descrição: {$r['descricao']}\n";
        echo "Tempo: {$r['tempo_preparacao']} min\n";
        echo "Doses: {$r['doses']}\n";
    }
}

// Funções adicionais (fase 6)

function criarIngrediente($con) {
    $nome = readline("Nome do ingrediente: ");
    $sql = "INSERT INTO ingredientes (nome) VALUES ('$nome')";
    if (mysqli_query($con, $sql)) {
        echo "Ingrediente criado com sucesso.\n";
    } else {
        echo "Erro ao criar ingrediente: " . mysqli_error($con) . "\n";
    }
}

function listarIngredientes($con) {
    $res = mysqli_query($con, "SELECT * FROM ingredientes");
    if (mysqli_num_rows($res) === 0) {
        echo "Nenhum ingrediente encontrado.\n";
        return;
    }

    echo "Ingredientes:\n";
    while ($i = mysqli_fetch_assoc($res)) {
        echo "{$i['id']} - {$i['nome']}\n";
    }
}

function atualizarIngredienteReceita($con) {
    $idReceita = (int)readline("ID da receita: ");
    
    $res = mysqli_query($con, "
        SELECT i.id, i.nome, ri.quantidade, ri.unidade_medida
        FROM receita_ingredientes ri
        JOIN ingredientes i ON ri.ingrediente_id = i.id
        WHERE ri.receita_id = $idReceita
    ");

    if (mysqli_num_rows($res) === 0) {
        echo "Nenhum ingrediente encontrado para essa receita.\n";
        return;
    }

    echo "Ingredientes associados:\n";
    while ($i = mysqli_fetch_assoc($res)) {
        echo "{$i['id']} - {$i['nome']} ({$i['quantidade']} {$i['unidade_medida']})\n";
    }

    $idIng = (int)readline("ID do ingrediente a atualizar: ");
    $quantidade = readline("Nova quantidade: ");
    $unidade = readline("Nova unidade (g/ml): ");

    $sql = "UPDATE receita_ingredientes 
            SET quantidade = '$quantidade', unidade_medida = '$unidade'
            WHERE receita_id = $idReceita AND ingrediente_id = $idIng";
    if (mysqli_query($con, $sql)) {
        echo "Ingrediente atualizado com sucesso.\n";
    } else {
        echo "Erro ao atualizar: " . mysqli_error($con) . "\n";
    }
}

function removerIngredienteReceita($con) {
    $idReceita = (int)readline("ID da receita: ");
    
    $res = mysqli_query($con, "
        SELECT i.id, i.nome FROM receita_ingredientes ri
        JOIN ingredientes i ON ri.ingrediente_id = i.id
        WHERE ri.receita_id = $idReceita
    ");

    if (mysqli_num_rows($res) === 0) {
        echo "Nenhum ingrediente encontrado para essa receita.\n";
        return;
    }

    echo "Ingredientes associados:\n";
    while ($i = mysqli_fetch_assoc($res)) {
        echo "{$i['id']} - {$i['nome']}\n";
    }

    $idIng = (int)readline("ID do ingrediente a remover: ");

    $sql = "DELETE FROM receita_ingredientes 
            WHERE receita_id = $idReceita AND ingrediente_id = $idIng";
    if (mysqli_query($con, $sql)) {
        echo "Ingrediente removido com sucesso.\n";
    } else {
        echo "Erro ao remover: " . mysqli_error($con) . "\n";
    }
}

function detalhesCompletosReceita($con) {
    $id = (int)readline("ID da receita: ");

    $sql = "SELECT r.id, r.nome, r.descricao, r.tempo_preparacao, r.doses, c.nome AS categoria
            FROM receitas r
            LEFT JOIN receita_categoria rc ON r.id = rc.receita_id
            LEFT JOIN categorias c ON rc.categoria_id = c.id
            WHERE r.id = $id";

    $res = mysqli_query($con, $sql);
    if (mysqli_num_rows($res) === 0) {
        echo "Receita não encontrada.\n";
        return;
    }

    $r = mysqli_fetch_assoc($res);
    echo "\n=== Detalhes da Receita ===\n";
    echo "ID: {$r['id']}\n";
    echo "Nome: {$r['nome']}\n";
    echo "Descrição: {$r['descricao']}\n";
    echo "Tempo: {$r['tempo_preparacao']} min\n";
    echo "Doses: {$r['doses']}\n";
    echo "Categoria: {$r['categoria']}\n";

    echo "Ingredientes:\n";
    $resIng = mysqli_query($con, "
        SELECT i.nome, ri.quantidade, ri.unidade_medida
        FROM receita_ingredientes ri
        JOIN ingredientes i ON ri.ingrediente_id = i.id
        WHERE ri.receita_id = $id
    ");
    if (mysqli_num_rows($resIng) === 0) {
        echo "- Nenhum ingrediente associado.\n";
    } else {
        while ($i = mysqli_fetch_assoc($resIng)) {
            echo "- {$i['nome']}: {$i['quantidade']} {$i['unidade_medida']}\n";
        }
    }
}

mysqli_close($con);
