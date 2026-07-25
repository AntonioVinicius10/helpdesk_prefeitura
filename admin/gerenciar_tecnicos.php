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

// 2. BUSCAR SETORES PARA O MODAL DE CADASTRO
try {
    $stmtSetores = $pdo->query("SELECT id, nome, sigla FROM secretarias_setores WHERE ativo = 1 ORDER BY nome ASC");
    $setores = $stmtSetores->fetchAll();
} catch (PDOException $e) {
    $setores = [];
}

// 3. PROCESSAR CADASTRO DE NOVO TÉCNICO VIA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_tecnico') {
    $nome           = trim($_POST['nome'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $setor_id       = $_POST['setor_id'] ?? '';
    $whatsapp       = trim($_POST['whatsapp'] ?? '');
    $senha          = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($setor_id) || empty($senha)) {
        $mensagemErro = "Preencha todos os campos obrigatórios (*).";
    } elseif ($senha !== $confirmarSenha) {
        $mensagemErro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $mensagemErro = "A senha deve ter no mínimo 6 caracteres.";
    } else {
        try {
            // Verifica se o e-mail já existe
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmtCheck->execute(['email' => $email]);

            if ($stmtCheck->fetch()) {
                $mensagemErro = "Este e-mail já está cadastrado no sistema.";
            } else {
                $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

                $sqlInsert = "INSERT INTO usuarios (nome, email, senha, telefone, setor_id, perfil, ativo) 
                              VALUES (:nome, :email, :senha, :telefone, :setor_id, 'tecnico', 1)";
                
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    'nome'     => $nome,
                    'email'    => $email,
                    'senha'    => $senhaHash,
                    'telefone' => $whatsapp,
                    'setor_id' => $setor_id
                ]);

                $mensagemSucesso = "Técnico cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao cadastrar técnico: " . $e->getMessage();
        }
    }
}

// 4. PROCESSAR ALTERAÇÃO DE STATUS (ATIVAR/DESATIVAR)
if (isset($_GET['alternar_status'])) {
    $tecnico_id = (int)$_GET['alternar_status'];
    try {
        $stmtStatus = $pdo->prepare("UPDATE usuarios SET ativo = NOT ativo WHERE id = :id AND perfil = 'tecnico'");
        $stmtStatus->execute(['id' => $tecnico_id]);
        header("Location: gerenciar_tecnicos.php");
        exit;
    } catch (PDOException $e) {
        $mensagemErro = "Erro ao alterar status do técnico.";
    }
}

// 5. LISTAR TODOS OS TÉCNICOS CADASTRADOS
try {
    $sqlTecnicos = "SELECT u.id, u.nome, u.email, u.telefone, u.ativo, u.criado_em, s.nome AS setor_nome, s.sigla AS setor_sigla 
                    FROM usuarios u 
                    LEFT JOIN secretarias_setores s ON u.setor_id = s.id 
                    WHERE u.perfil = 'tecnico' 
                    ORDER BY u.nome ASC";
    $stmtTecnicos = $pdo->query($sqlTecnicos);
    $tecnicos = $stmtTecnicos->fetchAll();
} catch (PDOException $e) {
    $tecnicos = [];
    $mensagemErro = "Erro ao listar técnicos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Técnicos - TI Prefeitura de Borborema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../account/painel.php">TI Prefeitura de Borborema</a>
            <div class="d-flex align-items-center text-white">
                <a href="../account/painel.php" class="btn btn-outline-light btn-sm me-2">Voltar ao Painel</a>
                <a href="/helpdesk_prefeitura/account/logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciamento de Técnicos TI</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoTecnico">
                + Novo Técnico
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
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Setor</th>
                                <th>WhatsApp</th>
                                <th>Status</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tecnicos) > 0): ?>
                                <?php foreach ($tecnicos as $tec): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($tec['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($tec['email']) ?></td>
                                        <td><?= htmlspecialchars($tec['setor_nome'] ?? 'Sem setor') ?> (<?= htmlspecialchars($tec['setor_sigla'] ?? '-') ?>)</td>
                                        <td><?= htmlspecialchars($tec['telefone'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($tec['ativo']): ?>
                                                <span class="badge bg-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inativo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="gerenciar_tecnicos.php?alternar_status=<?= $tec['id'] ?>" 
                                               class="btn btn-sm <?= $tec['ativo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                               onclick="return confirm('Deseja alterar o status deste técnico?')">
                                                <?= $tec['ativo'] ? 'Desativar' : 'Ativar' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhum técnico cadastrado até o momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalNovoTecnico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Cadastrar Novo Técnico de TI</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="gerenciar_tecnicos.php" method="POST">
                    <input type="hidden" name="acao" value="cadastrar_tecnico">
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" required placeholder="Ex: Carlos Oliveira">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email" class="form-control" required placeholder="carlos.ti@borborema.sp.gov.br">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Setor *</label>
                            <select name="setor_id" class="form-select" required>
                                <option value="" selected disabled>Selecione o setor...</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?> (<?= htmlspecialchars($setor['sigla']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" placeholder="(16) 99999-9999">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Senha Inicial *</label>
                                <input type="password" name="senha" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmar Senha *</label>
                                <input type="password" name="confirmar_senha" class="form-control" required minlength="6">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Salvar Técnico</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>