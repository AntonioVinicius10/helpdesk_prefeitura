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
├── account/                # Módulo de Autenticação
│   ├── login.php           # Tela de Login
│   └── logout.php          # Encerramento de Sessão
│
├── config/                 # Configurações do Sistema
│   └── conexao.php         # Conexão PDO com o Banco de Dados
│
├── includes/               # Componentes Reutilizáveis
│   ├── cards_admin.php     # Dashboard do Administrador
│   ├── cards_tecnico.php   # Dashboard do Técnico
│   └── cards_usuario.php   # Dashboard do Usuário
│
├── suporte/                # Módulo de Atendimento (Técnicos / Admin)
│   ├── fila_chamados.php   # Fila Geral de Chamados
│   └── ver_chamado.php    # Atendimento e Atualização do Chamado
│
├── usuario/                # Módulo do Solicitante
│   ├── abrir_chamado.php   # Formulário de Novo Chamado
│   ├── meus_chamados.php   # Listagem dos Chamados do Usuário
│   └── ver_chamado.php    # Acompanhamento e Respostas do Usuário
│
├── index.php               # Redirecionador Inicial
├── painel.php              # Painel Principal (Dashboard adaptativo por perfil)
├── perfil.php              # Edição de Perfil do Usuário Logado
└── README.md               # Documentação do Projeto