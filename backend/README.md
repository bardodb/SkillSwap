# SkillSwap Backend

Backend da plataforma SkillSwap desenvolvido em Laravel com API RESTful para troca de habilidades.

## Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação de API
- **SQLite** - Banco de dados (desenvolvimento)
- **PHP 8.1+** - Linguagem de programação

## Estrutura do Banco de Dados

### Principais Tabelas

- **users** - Usuários da plataforma
- **categories** - Categorias de habilidades
- **skills** - Habilidades oferecidas pelos usuários
- **exchanges** - Trocas de habilidades entre usuários
- **messages** - Sistema de mensagens

## Instalação e Configuração

### Pré-requisitos

- PHP 8.1 ou superior
- Composer
- SQLite (ou outro banco de dados compatível)

### Passos para Instalação

1. **Clone o repositório e navegue para o backend:**
   ```bash
   cd backend
   ```

2. **Instale as dependências:**
   ```bash
   composer install
   ```

3. **Configure o ambiente:**
   ```bash
   cp .env.example .env
   ```

4. **Gere a chave da aplicação:**
   ```bash
   php artisan key:generate
   ```

5. **Execute as migrations:**
   ```bash
   php artisan migrate
   ```

6. **Popule as categorias iniciais:**
   ```bash
   php artisan db:seed --class=CategorySeeder
   ```

7. **Inicie o servidor de desenvolvimento:**
   ```bash
   php artisan serve
   ```

A API estará disponível em `http://localhost:8000`

## Endpoints da API

### Autenticação

- `POST /api/register` - Registrar novo usuário
- `POST /api/login` - Login de usuário
- `POST /api/logout` - Logout (requer autenticação)
- `GET /api/user` - Dados do usuário logado
- `PUT /api/profile` - Atualizar perfil

### Categorias

- `GET /api/categories` - Listar categorias
- `POST /api/categories` - Criar categoria (requer autenticação)

### Habilidades

- `GET /api/skills` - Listar habilidades
- `GET /api/skills/{id}` - Detalhes de uma habilidade
- `POST /api/skills` - Criar habilidade (requer autenticação)
- `PUT /api/skills/{id}` - Atualizar habilidade
- `DELETE /api/skills/{id}` - Deletar habilidade

### Trocas

- `GET /api/exchanges` - Listar trocas do usuário
- `POST /api/exchanges` - Propor nova troca
- `PUT /api/exchanges/{id}` - Atualizar status da troca
- `GET /api/exchanges/{id}` - Detalhes de uma troca

### Mensagens

- `GET /api/conversations` - Listar conversas
- `GET /api/conversations/{userId}` - Mensagens com um usuário
- `POST /api/messages` - Enviar mensagem
- `GET /api/messages` - Listar mensagens

## Autenticação

A API utiliza Laravel Sanctum para autenticação via tokens. Para acessar endpoints protegidos:

1. Faça login via `/api/login` para obter o token
2. Inclua o token no header: `Authorization: Bearer {token}`

## Status de Desenvolvimento

✅ Models e Migrations criados
✅ Sistema de autenticação implementado
✅ Rotas da API configuradas
✅ Controllers básicos criados
✅ Seeder de categorias implementado
✅ Configuração CORS para frontend

## Próximos Passos

- [ ] Implementar controllers completos
- [ ] Adicionar validação de dados
- [ ] Implementar upload de imagens
- [ ] Adicionar sistema de notificações
- [ ] Implementar filtros e busca avançada
- [ ] Adicionar testes automatizados
