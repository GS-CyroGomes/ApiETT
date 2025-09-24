# API Temora ETT

Este projeto é uma API RESTful desenvolvida em PHP 5.6 com MySQL, containerizada usando Docker. A aplicação fornece endpoints para gerenciamento de dados, com foco em operações CRUD para entidades como pessoas.

## 📋 Visão Geral

A API é construída sobre uma arquitetura de contêineres Docker, utilizando:
- PHP 5.6 com Apache2
- MySQL 8.0.36
- PDO para acesso a banco de dados
- Xdebug para depuração

## 🚀 Estrutura do Projeto

```
.
├── .vscode/                     # Configurações do VS Code
├── logs/                        # Logs da aplicação e banco de dados
│   ├── apache2/                 # Logs do servidor Apache
│   ├── mysql/                   # Logs do MySQL
│   └── php56/                   # Logs do PHP
├── mysql/                       # Configurações do MySQL
│   ├── config/                  # Arquivos de configuração do MySQL
│   │   └── my.cnf               # Configuração personalizada do MySQL
│   ├── scripts/                 # Scripts SQL para inicialização do banco
│   │   └── ddl.sql              # Script de criação do banco de dados
│   └── Dockerfile               # Configuração da imagem Docker do MySQL
├── php56/                       # Aplicação PHP
│   ├── config/                  # Configurações do ambiente
│   │   ├── apache2.conf         # Configuração do Apache
│   │   ├── php.ini              # Configuração do PHP
│   │   └── xdebug.ini           # Configuração do Xdebug
│   ├── src/                     # Código-fonte da aplicação
│   │   └── apiTemoraETT/        # Raiz do projeto PHP
│   │       ├── app/             # Código da aplicação
│   │       │   ├── Controllers/ # Controladores da aplicação
│   │       │   ├── Core/        # Classes principais do framework
│   │       │   ├── Helpers/     # Funções auxiliares
│   │       │   ├── Middlewares/ # Middlewares da aplicação
│   │       │   └── Models/      # Modelos de dados
│   │       ├── config/          # Configurações da aplicação
│   │       │   └── Database.php # Configuração do banco de dados
│   │       ├── public/          # Pasta pública
│   │       │   └── index.php    # Ponto de entrada da aplicação
│   │       ├── vendor/          # Dependências do Composer
│   │       ├── .htaccess        # Configurações do Apache
│   │       ├── composer.json    # Definição das dependências PHP
│   │       └── composer.lock    # Versões travadas das dependências
│   └── Dockerfile               # Configuração da imagem Docker do PHP
├── .dockerignore                # Arquivos ignorados pelo Docker
├── .gitignore                   # Arquivos ignorados pelo Git
├── compose.yml                  # Configuração do Docker Compose
└── tools.sh                     # Scripts úteis para desenvolvimento
```

## 📦 Gerenciamento de Dependências (Composer)

O projeto utiliza o Composer para gerenciar as dependências PHP. As principais bibliotecas utilizadas são:

- **guzzlehttp/guzzle** (^6.3) - Cliente HTTP para fazer requisições a serviços externos
- **firebase/php-jwt** (^5.0) - Implementação de JSON Web Tokens (JWT) para autenticação
- **ramsey/uuid** (^3.8) - Geração de UUIDs (Identificadores Únicos Universais)
- **respect/validation** (^1.1) - Biblioteca de validação de dados
- **monolog/monolog** (^1.25) - Biblioteca de logging
- **doctrine/dbal** (2.5.*) - Camada de abstração de banco de dados
- **vlucas/phpdotenv** (^2.5) - Carregamento de variáveis de ambiente a partir de arquivos .env

### Comandos úteis do Composer

- Instalar dependências: `composer install`
- Atualizar dependências: `composer update`
- Ver dependências instaladas: `composer show`
- Atualizar o autoloader: `composer dump-autoload`

## Observação
.vscode/ é para configuração do vscode para debugar

## 🛠️ Pré-requisitos

- Docker
- Docker Compose
- Git (opcional)

## 🚀 Como Executar

1. Clone o repositório:
   ```bash
   git clone <repositório>
   cd MySQLphp5.6
   ```

2. Inicie os contêineres:
   ```bash
   docker-compose up -d --build --force-recreate
   ```

3. Acesse a API em:
   ```
   http://localhost:56109/apiTemoraETT/index.php?XDEBUG_TRIGGER
   ```

## 🐛 Configuração de Depuração

### Extensão do Navegador (Recomendado)
1. Instale a extensão **Xdebug Helper** no seu navegador:
   - [Chrome](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc)
   - [Firefox](https://addons.mozilla.org/pt-BR/firefox/addon/xdebug-helper-for-firefox/)

2. Configure a extensão:
   - Clique com o botão direito no ícone da extensão
   - Vá em "Opções"
   - Selecione "PHPStorm" como IDE Key

### Método Alternativo (Sem Extensão)
Adicione o parâmetro `?XDEBUG_TRIGGER` no final da URL da sua requisição:
```
http://localhost:56109/apiTemoraETT/index.php?XDEBUG_TRIGGER
```

### Configuração do VS Code
1. Instale a extensão **PHP Debug** criada pela equipe **xdebug** do seu editor
2. O arquivo `.vscode/launch.json` já está configurado para depuração
3. Pressione `F5` ou vá em "Run and Debug" para iniciar a sessão de depuração

### Configuração do Xdebug no Container
O Xdebug já está configurado no container com as seguintes configurações:
- Porta: 9000
- Remote Host: host.docker.internal
- Remote Port: 9000

O arquivo de configuração está em `php56/config/xdebug.ini`

## 🔧 Configuração

### Variáveis de Ambiente

As principais configurações podem ser ajustadas no arquivo `compose.yml`:

- **PHP/Apache**:
  - Porta: 56109
  - Diretório do código-fonte: `./php56/src`
  - Configurações do PHP: `./php56/config/php.ini`
  - Configurações do Apache: `./php56/config/apache2.conf`

- **MySQL**:
  - Porta: 13459
  - Usuário: web
  - Senha: web
  - Banco de dados: temora_coleta
  - Volume de dados: `./mysql-data`

### Banco de Dados

O banco de dados é inicializado automaticamente com os scripts SQL localizados em `./mysql/scripts/`.

## 🛠️ Desenvolvimento

### Estrutura de Arquivos



### Padrões de Código

- **Conexão com Banco de Dados**: Utiliza PDO com prepared statements para segurança contra injeção SQL.
- **Respostas**: Todas as respostas são retornadas em formato JSON.
- **Tratamento de Erros**: Utiliza códigos de status HTTP apropriados e mensagens de erro descritivas.

## 🔒 Segurança

- Todas as senhas são armazenadas usando criptografia de mão única.
- As conexões com o banco de dados utilizam usuários com privilégios mínimos necessários.
- As configurações sensíveis não devem ser versionadas no controle de origem.

## 📊 Monitoramento

Os logs estão disponíveis nos seguintes diretórios:
- Logs do Apache: `./logs/apache2/`
- Logs do MySQL: `./logs/mysql/`

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## ✉️ Contato

- Desenvolvedor: Cyro Gomes
- E-mail: [cyro.gomes@giusoft.com.br]
- Projeto: [Link do Repositório]
