<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// 1. SEGURANÇA: Somente administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'admin') {
    header("Location: /helpdesk_prefeitura/painel.php");
    exit;
}

$mensagemSucesso = '';
$mensagemErro = '';

// 2. PROCESSAR CADASTRO DE NOVO SETOR VIA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_setor') {
    $nome  = trim($_POST['nome'] ?? '');
    $sigla = strtoupper(trim($_POST['sigla'] ?? ''));

    if (empty($nome) || empty($sigla)) {
        $mensagemErro = "Preencha todos os campos obrigatórios (*).";
    } else {
        try {
            // Verifica se a sigla ou nome já existem
            $stmtCheck = $pdo->prepare("SELECT id FROM secretarias_setores WHERE sigla = :sigla OR nome = :nome");
            $stmtCheck->execute(['sigla' => $sigla, 'nome' => $nome]);

            if ($stmtCheck->fetch()) {
                $mensagemErro = "Já existe um setor/secretaria com este Nome ou Sigla.";
            } else {
                $sqlInsert = "INSERT INTO secretarias_setores (nome, sigla, ativo) VALUES (:nome, :sigla, 1)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    'nome'  => $nome,
                    'sigla' => $sigla
                ]);

                $mensagemSucesso = "Setor/Secretaria cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao cadastrar setor: " . $e->getMessage();
        }
    }
}

// 3. PROCESSAR ALTERAÇÃO DE STATUS (ATIVAR/DESATIVAR)
if (isset($_GET['alternar_status'])) {
    $setor_id = (int)$_GET['alternar_status'];
    try {
        $stmtStatus = $pdo->prepare("UPDATE secretarias_setores SET ativo = NOT ativo WHERE id = :id");
        $stmtStatus->execute(['id' => $setor_id]);
        header("Location: gerenciar_setores.php");
        exit;
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao alterar status do setor.";
    }
}

// 4. LISTAR TODOS OS SETORES CADASTRADOS
try {
    $sqlSetores = "SELECT * FROM secretarias_setores ORDER BY nome ASC";
    $stmtSetores = $pdo->query($sqlSetores);
    $setores = $stmtSetores->fetchAll();
} catch (PDOException $e) {
    $setores = [];
    $mensagemErro = "Erro ao listar setores.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Setores -  TI Prefeitura de Borborema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../account/painel.php"> TI Prefeitura de Borborema</a>
            <div class="d-flex align-items-center text-white">
                <a href="../account/painel.php" class="btn btn-outline-light btn-sm me-2">Voltar ao Painel</a>
                <a href="/helpdesk_prefeitura/account/logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciamento de Setores e Secretarias</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoSetor">
                + Novo Setor / Secretaria
            </button>
        </div>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($mensagemSucesso) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th># ID</th>
                                <th>Nome do Setor / Secretaria</th>
                                <th>Sigla</th>
                                <th>Status</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($setores) > 0): ?>
                                <?php foreach ($setores as $setor): ?>
                                    <tr>
                                        <td><strong>#<?= $setor['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($setor['nome']) ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($setor['sigla']) ?></span></td>
                                        <td>
                                            <?php if ($setor['ativo']): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="gerenciar_setores.php?alternar_status=<?= $setor['id'] ?>" 
                                               class="btn btn-sm <?= $setor['ativo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                               onclick="return confirm('Deseja alterar o status deste setor?')">
                                                <?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Nenhum setor cadastrado até o momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalNovoSetor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Cadastrar Novo Setor ou Secretaria</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="gerenciar_setores.php" method="POST">
                    <input type="hidden" name="acao" value="cadastrar_setor">
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome Completo do Setor / Secretaria *</label>
                            <input type="text" name="nome" class="form-control" required placeholder="Ex: Secretaria de Meio Ambiente">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sigla *</label>
                            <input type="text" name="sigla" class="form-control" required placeholder="Ex: SEMA" maxlength="20">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Salvar Setor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>