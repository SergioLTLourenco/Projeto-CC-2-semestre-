<?php
// Configuração do Banco de Dados
$host = 'localhost';
$dbname = 'gerenciador_tarefas';
$username = 'root';
$password = '';

// Conexão com o Banco de Dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Função para salvar template no banco de dados
if (isset($_POST['salvar-template'])) {
    $templateName = $_POST['template-name'];
    $templateCategory = $_POST['template-category'];
    $templateDescription = $_POST['template-description'];
    $numColumns = $_POST['num-columns'];

    try {
        // Inserir o template
        $stmt = $pdo->prepare("INSERT INTO Projetos (nome, descricao, categoria_id) VALUES (?, ?, ?)");
        $stmt->execute([$templateName, $templateDescription, $templateCategory]);
        $templateId = $pdo->lastInsertId();

        // Inserir colunas associadas ao template
        for ($i = 0; $i < $numColumns; $i++) {
            $columnName = $_POST["column-name-$i"];
            $stmt = $pdo->prepare("INSERT INTO Categorias (nome) VALUES (?)");
            $stmt->execute([$columnName]);
        }
        echo "<script>alert('Template e colunas salvos com sucesso!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao salvar template: " . $e->getMessage() . "');</script>";
    }
}

// Função para carregar templates salvos
function getTemplates($pdo) {
    $stmt = $pdo->query("SELECT id, nome FROM Projetos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para carregar colunas de um template
function getColumns($pdo, $templateId) {
    $stmt = $pdo->prepare("SELECT id, nome FROM Categorias WHERE id = ?");
    $stmt->execute([$templateId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para adicionar uma tarefa a uma coluna
if (isset($_POST['add-task'])) {
    $taskName = $_POST['task-name'];
    $responsible = $_POST['task-responsible'];
    $taskDate = $_POST['task-date'];
    $taskLocation = $_POST['task-location'];
    $taskProgress = $_POST['task-progress'];
    $taskTimeSpent = $_POST['task-time-spent'];
    $templateId = $_POST['template-id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Tarefas (nome, descricao, tempo_gasto, responsavel_id, projeto_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$taskName, $taskProgress, $taskTimeSpent, $responsible, $templateId]);
        echo "<script>alert('Tarefa adicionada com sucesso!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao adicionar tarefa: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCRUM BOARD</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- HTML para interface -->
    <header>
        <h1>SCRUM</h1>
        <div>
            <form method="post" action="index.php">
                <select name="template-id" id="template-select">
                    <option value="">Selecione um Template</option>
                    <?php foreach (getTemplates($pdo) as $template): ?>
                        <option value="<?= $template['id'] ?>"><?= $template['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Carregar</button>
            </form>
        </div>
        <button id="logout">Sair</button>
    </header>
    <main>
        <div id="welcome-screen">
            <h2>Bem-vindo ao Quadro Scrum</h2>
            <p>Selecione ou crie um template para começar!</p>
        </div>
        <div id="columns-container"></div>
    </main>

    <!-- Formulário para criação de templates -->
    <div id="create-template-modal" class="modal">
        <div class="modal-content">
            <span id="close-create-modal" class="close">&times;</span>
            <h3 id="template-modal-title">Criar Template</h3>
            <form method="post" id="create-template-form" action="index.php">
                <input type="text" name="template-name" id="template-name" placeholder="Nome do Template" required>
                <input type="number" name="template-category" id="template-category" min="1" max="5" placeholder="Categoria" required>
                <input type="number" name="num-columns" id="num-columns" min="1" max="5" placeholder="Número de Colunas" required>
                <textarea name="template-description" id="template-description" placeholder="Descrição" required></textarea>
                <button type="submit" name="salvar-template">Salvar</button>
            </form>
        </div>
    </div>

    <!-- Formulário para adicionar tarefa -->
    <div id="add-task-modal" class="modal">
        <div class="modal-content">
            <span id="close-task-modal" class="close">&times;</span>
            <h3 id="task-modal-title">Adicionar Tarefa</h3>
            <form method="post" id="add-task-form" action="index.php">
                <input type="hidden" name="template-id" value="<?= isset($_POST['template-id']) ? $_POST['template-id'] : '' ?>">
                <input type="text" name="task-name" id="task-name" placeholder="Nome da Tarefa" required>
                <input type="text" name="task-responsible" id="task-responsible" placeholder="Responsável" required>
                <input type="date" name="task-date" id="task-date" required>
                <input type="text" name="task-location" id="task-location" placeholder="Localização" required>
                <input type="text" name="task-progress" id="task-progress" placeholder="Status de Progresso" required>
                <input type="number" name="task-time-spent" id="task-time-spent" placeholder="Tempo Gasto" required>
                <button type="submit" name="add-task">Salvar</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
