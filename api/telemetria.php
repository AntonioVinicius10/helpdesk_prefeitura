<?php
// api/telemetria.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexao.php';

date_default_timezone_set('America/Sao_Paulo');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensagem' => 'JSON inválido.']);
    exit;
}

$hostname     = trim($input['hostname'] ?? '');
$setorNome    = trim($input['setor_nome'] ?? '');
$cpuModelo    = trim($input['cpu_modelo'] ?? '');
$gpuModelo    = trim($input['gpu_modelo'] ?? '');
$ramTotalMb   = (int)($input['ram_total_mb'] ?? 0);
$ramLivreMb   = (int)($input['ram_livre_mb'] ?? 0);
$discoLivreGb = (int)($input['disco_livre_gb'] ?? 0);
$alertas      = json_encode($input['alertas'] ?? []);

if (empty($hostname)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'mensagem' => 'Hostname é obrigatório.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Tenta vincular ou cadastrar o Setor digitado na tabela secretarias_setores de forma segura
    $setorId = null;
    if (!empty($setorNome)) {
        $stmtSetor = $pdo->prepare("SELECT id FROM secretarias_setores WHERE LOWER(nome) = LOWER(:nome) LIMIT 1");
        $stmtSetor->execute([':nome' => $setorNome]);
        $setorId = $stmtSetor->fetchColumn();

        // Se o setor não existir, tenta criar de forma resiliente
        if (!$setorId) {
            try {
                $sigla = strtoupper(substr($setorNome, 0, 5));
                $stmtInsSetor = $pdo->prepare("INSERT INTO secretarias_setores (nome, sigla, ativo) VALUES (:nome, :sigla, 1)");
                $stmtInsSetor->execute([':nome' => $setorNome, ':sigla' => $sigla]);
                $setorId = $pdo->lastInsertId();
            } catch (\Throwable $e) {
                // Caso a tabela secretarias_setores exija outros campos obrigatorios, prossegue sem travar
                $setorId = null;
            }
        }
    }

    // 2. Insere ou Atualiza o Dispositivo
    $sqlDispositivo = "INSERT INTO dispositivos 
                        (hostname, setor_id, setor_nome, cpu_modelo, gpu_modelo, ram_total_mb, primeiro_acesso, ultimo_acesso) 
                       VALUES 
                        (:hostname, :setor_id, :setor_nome, :cpu_modelo, :gpu_modelo, :ram_total_mb, NOW(), NOW())
                       ON DUPLICATE KEY UPDATE 
                        hostname = :hostname_update,
                        setor_id = COALESCE(:setor_id_update, setor_id),
                        setor_nome = :setor_nome_update,
                        cpu_modelo = :cpu_modelo_update,
                        gpu_modelo = :gpu_modelo_update,
                        ram_total_mb = :ram_total_mb_update,
                        ultimo_acesso = NOW()";

    $stmt = $pdo->prepare($sqlDispositivo);
    
    // Values
    $stmt->bindValue(':hostname', $hostname);
    $stmt->bindValue(':setor_id', $setorId, $setorId ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':setor_nome', $setorNome);
    $stmt->bindValue(':cpu_modelo', $cpuModelo);
    $stmt->bindValue(':gpu_modelo', $gpuModelo);
    $stmt->bindValue(':ram_total_mb', $ramTotalMb);

    // Updates
    $stmt->bindValue(':hostname_update', $hostname);
    $stmt->bindValue(':setor_id_update', $setorId, $setorId ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':setor_nome_update', $setorNome);
    $stmt->bindValue(':cpu_modelo_update', $cpuModelo);
    $stmt->bindValue(':gpu_modelo_update', $gpuModelo);
    $stmt->bindValue(':ram_total_mb_update', $ramTotalMb);

    $stmt->execute();

    // 3. Pega o ID do dispositivo para a telemetria
    $stmtId = $pdo->prepare("SELECT id FROM dispositivos WHERE hostname = :hostname LIMIT 1");
    $stmtId->execute([':hostname' => $hostname]);
    $dispositivoId = $stmtId->fetchColumn();

    // 4. Grava/Atualiza a Telemetria (Apenas 1 registro por dispositivo)
    $stmtCheck = $pdo->prepare("SELECT id FROM dispositivos_telemetria WHERE dispositivo_id = :dispositivo_id LIMIT 1");
    $stmtCheck->execute([':dispositivo_id' => $dispositivoId]);
    $existeTelemetria = $stmtCheck->fetchColumn();

    if ($existeTelemetria) {
        // Se já existe, apenas ATUALIZA os dados mais recentes
        $stmtTel = $pdo->prepare("UPDATE dispositivos_telemetria SET 
                                    ram_livre_mb = :ram_livre_mb, 
                                    disco_livre_gb = :disco_livre_gb, 
                                    alertas = :alertas, 
                                    criado_em = NOW() 
                                  WHERE dispositivo_id = :dispositivo_id");
    } else {
        // Se é a primeira vez, CRIA o registro inicial
        $stmtTel = $pdo->prepare("INSERT INTO dispositivos_telemetria 
                                    (dispositivo_id, ram_livre_mb, disco_livre_gb, alertas, criado_em) 
                                  VALUES 
                                    (:dispositivo_id, :ram_livre_mb, :disco_livre_gb, :alertas, NOW())");
    }

    $stmtTel->execute([
        ':dispositivo_id' => $dispositivoId,
        ':ram_livre_mb'   => $ramLivreMb,
        ':disco_livre_gb' => $discoLivreGb,
        ':alertas'        => $alertas
    ]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'mensagem' => 'Dados atualizados com sucesso.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensagem' => $e->getMessage()]);
}