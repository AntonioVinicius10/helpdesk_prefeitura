# 🛠️ Help Desk Borborema - Sistema de Gestão de Chamados de TI

Sistema web para gerenciamento e atendimento de chamados de suporte técnico da Prefeitura de Borborema. O projeto permite a comunicação eficiente entre os servidores municipais (usuários solicitantes), técnicos da TI e administradores do sistema.

---

## 🚀 Funcionalidades Principais

### 👤 Perfil Usuário (Solicitante)
- **Abertura de Chamados:** Formulação de solicitações com título, categoria, prioridade e descrição detalhada.
- **Gerador de Protocolos:** Criação automática de protocolo único (`YYYYMMDD-XXXX`) por chamado.
- **Meus Chamados:** Acompanhamento em tempo real do status dos chamados abertos (`Novo`, `Em Andamento`, `Fechado`).
- **Interação / Réplica:** Leitura de respostas do suporte e envio de mensagens adicionais.
- **Meu Perfil:** Atualização de dados cadastrais (nome, e-mail, telefone/WhatsApp) e alteração de senha.

### 🛠️ Perfil Técnico
- **Fila de Atendimento:** Visualização de todos os chamados da prefeitura com ordenação inteligente (`Novo` sempre no topo, seguido por `Em Andamento` e `Fechado`).
- **Gestão de Chamados:** Mudar status da solicitação e visualizar dados completos do solicitante e do seu setor.
- **Respostas:** Envio de orientações, pareceres e histórico por escrito.
- **Meu Perfil:** Gestão dos dados do próprio técnico.

### 👑 Perfil Administrador
- Acesso a todas as funcionalidades técnicas e de usuário.
- Visão geral e controle completo da fila de atendimento do Help Desk.
- Gerenciamento de perfil e configurações.

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** PHP 7.4+
- **Banco de Dados:** MySQL / MariaDB (com `PDO` para conexões seguras)
- **Front-end:** HTML5, CSS3, Bootstrap 5.3
- **Servidor Web Local:** Apache (XAMPP / WAMP / Laragon)

---

## 📂 Estrutura de Pastas do Projeto

```text
helpdesk_prefeitura/
│
├── account/                    # Módulo de Autenticação e Entrada
│   ├── autocadastro.php        # Cadastro de Novos Usuários
│   ├── esqueci_senha.php        # Recuperação de Senha por Telefone
│   ├── login.php               # Tela de Login Principal
│   ├── logout.php              # Encerramento de Sessão
│   └── painel.php              # Painel Principal (Dashboard Adaptativo)
│
├── admin/                      # Módulo Exclusivo do Administrador
│   ├── gerenciar_setores.php   # Gestão de Secretarias e Setores
│   └── gerenciar_tecnicos.php  # Gestão de Técnicos e Permissões
│
├── assets/                     # Arquivos Estáticos e Mídia
│   └── img/
│       └── brasao.png          # Logomarca / Brasão Oficial da Prefeitura
│
├── config/                     # Configurações de Infraestrutura
│   └── conexao.php             # Conexão PDO com Banco de Dados MySQL
│
├── includes/                   # Componentes Reutilizáveis (Cards/Widgets)
│   ├── cards_admin.php         # Indicadores do Administrador
│   ├── cards_tecnico.php       # Indicadores do Técnico
│   └── cards_usuario.php       # Indicadores do Solicitante
│
├── suporte/                    # Módulo de Atendimento (Técnicos / Admin)
│   ├── fila_chamados.php       # Fila Geral com Ordenação de Chamados
│   └── ver_chamado.php        # Atendimento, Respostas e Troca de Status
│
├── usuario/                    # Módulo do Solicitante (Servidor/Munícipe)
│   ├── abrir_chamado.php       # Formulário de Abertura de Chamado
│   ├── meus_chamados.php       # Listagem e Status dos Chamados Próprios
│   └── ver_chamado.php        # Acompanhamento e Envio de Réplica
│
├── estrutura.txt               # Mapeamento Gerado da Estrutura de Pastas
├── gerar_admin.php             # Script Utilitário para Criar Conta Admin
├── helpdesk_prefeitura.sql     # Script do Banco de Dados MySQL
├── Help_TI_Borborema.vbs       # Script para Criar Atalho na Área de Trabalho
├── index.php                   # Redirecionador Inicial da Aplicação
├── perfil.php                  # Edição de Dados do Usuário Logado
└── README.md                   # Documentação Completa do Projeto