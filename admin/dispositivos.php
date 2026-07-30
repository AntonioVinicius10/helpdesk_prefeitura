<?php
// 1. Corrige o fuso horário (com a barra)
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../config/conexao.php';

try {
    // 2. O MySQL calcula a diferença exata em segundos entre a telemetria e o horário atual
    $sql = "
        SELECT 
            d.id,
            d.hostname,
            d.cpu_modelo,
            d.gpu_modelo,
            d.ram_total_mb,
            d.ultimo_acesso,
            TIMESTAMPDIFF(SECOND, d.ultimo_acesso, NOW()) AS segundos_desde_ultimo_acesso,
            s.nome AS setor_nome,
            t.ram_livre_mb,
            t.disco_livre_gb,
            t.alertas,
            t.criado_em AS ultima_telemetria
        FROM dispositivos d
        LEFT JOIN secretarias_setores s ON d.setor_id = s.id
        LEFT JOIN dispositivos_telemetria t ON t.id = (
            SELECT MAX(id) 
            FROM dispositivos_telemetria 
            WHERE dispositivo_id = d.id
        )
        ORDER BY d.ultimo_acesso DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $dispositivosBanco = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao consultar dispositivos no banco de dados: " . $e->getMessage());
}

$dispositivos = [];

foreach ($dispositivosBanco as $d) {
    // Pega a diferença em segundos calculada pelo MySQL
    $diferencaSegundos = (int) ($d['segundos_desde_ultimo_acesso'] ?? 99999);

    // Decodifica o JSON de alertas do agente
    $alertasArray = !empty($d['alertas']) ? json_decode($d['alertas'], true) : [];

    // REGRA DE STATUS:
    // Se passou mais de 180 segundos (3 minutos) sem sinal -> Offline
    if ($diferencaSegundos > 180) {
        $status = 'offline';
    } elseif (!empty($alertasArray)) {
        $status = 'alerta';
    } else {
        $status = 'online';
    }

    // Porcentagem calculada de uso da memória RAM
    $ramTotal = $d['ram_total_mb'] ?: 1;
    $ramLivre = $d['ram_livre_mb'] ?? $ramTotal;
    $ramUsoPorcentagem = round((($ramTotal - $ramLivre) / $ramTotal) * 100);

    // Formatação de tempo de atualização
    if ($status === 'offline') {
        $uptimeTexto = 'Sem comunicação';
    } else {
        $minutosAtras = floor($diferencaSegundos / 60);
        $uptimeTexto = $minutosAtras <= 1 ? 'Ativo agora' : "Visto há {$minutosAtras} min";
    }

    $dispositivos[] = [
        'id'                => $d['id'],
        'hostname'          => $d['hostname'],
        'status'            => $status,
        'setor'             => $d['setor_nome'] ?? 'Setor Não Atribuído',
        'cpu_modelo'        => $d['cpu_modelo'] ?: 'Desconhecido',
        'ram_total'         => round($d['ram_total_mb'] / 1024, 1) . ' GB',
        'ram_uso'           => $ramUsoPorcentagem,
        'disco'             => ($d['disco_livre_gb'] !== null) ? $d['disco_livre_gb'] . ' GB Livres no C:' : 'Não informado',
        'uptime'            => $uptimeTexto,
        'ultima_manutencao' => date('d/m/Y H:i', strtotime($d['ultimo_acesso'])),
        'alertas'           => $alertasArray
    ];
}

// 3. Contadores para as caixas de resumo no topo da tela (Corrigindo as variáveis ausentes)
$total_pcs   = count($dispositivos);
$online_pcs  = count(array_filter($dispositivos, fn($d) => $d['status'] === 'online'));
$offline_pcs = count(array_filter($dispositivos, fn($d) => $d['status'] === 'offline'));
$alerta_pcs  = count(array_filter($dispositivos, fn($d) => $d['status'] === 'alerta'));
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Monitoramento - Helpdesk TI</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkbg: '#0f172a',
                        darkcard: '#1e293b',
                        darkborder: '#334155'
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-darkbg text-slate-100 min-h-screen font-sans antialiased pb-12">

    <?php 
    include __DIR__ . '/../includes/components/card_dispositivo.php';
?>

    <script>
        let filtroAtual = 'todos';

        function abrirModal(pc) {
            document.getElementById('modalHostname').innerText = pc.hostname;
            document.getElementById('modalStatus').innerText = 'Status: ' + pc.status.toUpperCase() + ' - ' + pc.uptime;
            document.getElementById('modalCpu').innerText = pc.cpu_modelo;
            document.getElementById('modalRam').innerText = pc.ram_total + ' (' + pc.ram_uso + '% em uso)';
            document.getElementById('modalDisco').innerText = pc.disco;
            document.getElementById('modalSetor').innerText = pc.setor;
            document.getElementById('modalManutencao').innerText = pc.ultima_manutencao;

            const alertasContainer = document.getElementById('modalAlertasContainer');
            const alertasTexto = document.getElementById('modalAlertasTexto');
            if (pc.alertas && pc.alertas.length > 0) {
                alertasTexto.innerText = pc.alertas.join(', ');
                alertasContainer.classList.remove('hidden');
            } else {
                alertasContainer.classList.add('hidden');
            }

            document.getElementById('modalDetalhes').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('modalDetalhes').classList.add('hidden');
        }

        function setFiltro(status) {
            filtroAtual = status;
            document.querySelectorAll('.btn-filtro').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-darkcard', 'text-slate-300');
            });

            const btnAtivo = document.getElementById('btn-' + status);
            btnAtivo.classList.remove('bg-darkcard', 'text-slate-300');
            btnAtivo.classList.add('bg-blue-600', 'text-white');

            filtrarDispositivos();
        }

        function filtrarDispositivos() {
            const busca = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.card-pc');

            cards.forEach(card => {
                const hostname = card.getAttribute('data-hostname');
                const status = card.getAttribute('data-status');

                const bateuBusca = hostname.includes(busca);
                const bateuFiltro = (filtroAtual === 'todos') || (status === filtroAtual);

                if (bateuBusca && bateuFiltro) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>