# SkillSwap - Plataforma de Troca de Habilidades

Uma plataforma completa para troca de conhecimentos e habilidades entre pessoas, desenvolvida com Laravel (backend) e Vue.js (frontend).

## 🚀 Como Rodar o Projeto

### Pré-requisitos

- **PHP 8.1+** com extensões SQLite, mbstring, openssl
- **Composer** (gerenciador de dependências PHP)
- **Node.js 18+** e **npm**
- **Git**

### 1. Configuração do Backend (Laravel)

```bash
# Navegue para o diretório backend
cd backend

# Instale as dependências
composer install

# Configure o ambiente (se não existir)
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate

# Execute as migrations
php artisan migrate

# Popule as categorias iniciais
php artisan db:seed --class=CategorySeeder

# Popule dados de demonstração
php artisan db:seed --class=DemoDataSeeder

# Inicie o servidor backend
php artisan serve
```

O backend estará disponível em: **http://localhost:8000**

### 2. Configuração do Frontend (Vue.js)

```bash
# Em um novo terminal, navegue para o frontend
cd frontend/frontend

# Instale as dependências
npm install

# Inicie o servidor de desenvolvimento
npm run dev
```

O frontend estará disponível em: **http://localhost:5173**

## 🎯 Como Testar a Integração

### 1. Verificar se os servidores estão rodando:
- **Backend:** http://localhost:8000/api/categories (deve retornar JSON com categorias)
- **Frontend:** http://localhost:5173 (deve carregar a página inicial)

### 2. Testar funcionalidades principais:

1. **Página Inicial**: Deve carregar e exibir as 10 categorias do backend
2. **Registro**: Crie uma nova conta em "Cadastrar"
3. **Login**: Entre com as credenciais criadas ou use uma conta de demonstração:
   - **Email:** joao@skillswap.com
   - **Senha:** password123

### 3. Contas de Demonstração Disponíveis:

| Nome | Email | Senha | Descrição |
|------|-------|-------|-----------|
| João Silva | joao@skillswap.com | password123 | Desenvolvedor Full Stack |
| Maria Santos | maria@skillswap.com | password123 | Designer UX/UI |
| Carlos Oliveira | carlos@skillswap.com | password123 | Professor de Inglês |

## 🎨 Funcionalidades Implementadas

### ✅ Backend (Laravel)
- ✅ API RESTful completa
- ✅ Autenticação com Laravel Sanctum
- ✅ Models e relacionamentos (User, Skill, Category, Exchange, Message)
- ✅ Controllers funcionais
- ✅ Migrations e seeders
- ✅ CORS configurado

### ✅ Frontend (Vue.js)
- ✅ Interface responsiva com Tailwind CSS
- ✅ Gerenciamento de estado com Pinia
- ✅ Autenticação integrada
- ✅ Páginas de Login e Registro funcionais
- ✅ Homepage com categorias dinâmicas
- ✅ Navegação e layout completos

## 📁 Estrutura do Projeto

```
SkillSwap/
├── backend/              # API Laravel
│   ├── app/
│   │   ├── Models/       # Models (User, Skill, Category, etc.)
│   │   └── Http/Controllers/API/  # Controllers da API
│   ├── database/
│   │   ├── migrations/   # Estrutura do banco
│   │   └── seeders/      # Dados iniciais
│   └── routes/api.php    # Rotas da API
│
├── frontend/frontend/    # Aplicação Vue.js
│   ├── src/
│   │   ├── views/        # Páginas (Home, Login, etc.)
│   │   ├── stores/       # Gerenciamento de estado
│   │   └── services/     # Integração com API
│   └── package.json
│
└── readme.md             # Este arquivo
```

## 🔧 Tecnologias Utilizadas

### Backend
- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticação de API
- **SQLite** - Banco de dados

### Frontend
- **Vue.js 3** - Framework JavaScript
- **Pinia** - Gerenciamento de estado
- **Vue Router** - Roteamento
- **Axios** - Cliente HTTP
- **Tailwind CSS** - Framework CSS

## 🌐 Endpoints da API

### Autenticação
- `POST /api/register` - Registrar usuário
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/user` - Dados do usuário

### Categorias
- `GET /api/categories` - Listar categorias

### Habilidades
- `GET /api/skills` - Listar habilidades
- `POST /api/skills` - Criar habilidade (autenticado)

## 🔧 Próximas Funcionalidades

- [ ] Página de listagem de habilidades
- [ ] Sistema de busca e filtros
- [ ] Dashboard do usuário
- [ ] Sistema de trocas
- [ ] Chat em tempo real
- [ ] Sistema de avaliações
- [ ] Upload de imagens
- [ ] Notificações

## 📝 Notas de Desenvolvimento

- O backend utiliza SQLite para facilidade de desenvolvimento
- CORS está configurado para permitir requests do frontend
- Dados de demonstração incluem 3 usuários e 8 habilidades
- Tokens de autenticação são armazenados no localStorage

## 🐛 Solução de Problemas

1. **Erro de CORS**: Certifique-se que o backend está rodando na porta 8000
2. **Erro de conexão**: Verifique se ambos servidores estão ativos
3. **Erro de autenticação**: Limpe o localStorage do navegador
4. **Erro de dependências**: Execute `composer install` e `npm install`

---

**Desenvolvido com ❤️ para demonstrar integração Laravel + Vue.js**
