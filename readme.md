# SkillSwap

Plataforma para troca de conhecimentos e habilidades entre pessoas. Backend em Laravel (API + Reverb) e frontend em Vue.js.

## Como rodar com Docker

Requisitos: Docker e Docker Compose.

```bash
docker compose up --build
```

Servicos e portas:

| Servico | URL / porta |
|---------|-------------|
| Frontend | http://localhost:8080 |
| Backend (API) | http://localhost:8000 |
| Reverb (WebSocket) | ws://localhost:8081 |
| PostgreSQL | interno (`db:5432`, sem porta publicada) |

O que o `docker compose` sobe:

- `db` — PostgreSQL 16
- `backend` — API Laravel (`php artisan serve`)
- `reverb` — WebSockets em tempo real
- `frontend` — SPA Vue buildada e servida pelo nginx

Comportamento importante do backend no Docker:

1. Aguarda o Postgres ficar pronto
2. Executa `migrate:fresh --seed` a cada start (banco e dados sao recriados)
3. Sobe a API na porta 8000

Dados do Postgres **nao sao persistidos** (sem volume). Ao parar os containers, o banco e descartado.

Para encerrar:

```bash
docker compose down
```

Rebuild completo:

```bash
docker compose build --no-cache
docker compose up --build --force-recreate
```

### Contas de demonstracao

Disponiveis apos o seed automatico (senha: `password123`):

| Nome | Email | Observacao |
|------|-------|------------|
| Joao Silva | joao@skillswap.com | Admin (`is_admin`), Sao Paulo |
| Maria Santos | maria@skillswap.com | Designer UX/UI |
| Carlos Oliveira | carlos@skillswap.com | Professor de ingles |

Ha conversa pre-seedada entre Joao e Maria (troca pendente + mensagens). Joao tambem pode iniciar novas trocas a partir das habilidades no app.

### Identificadores (UUID)

Os campos publicos `id` de users, skills, categories, exchanges e messages sao **strings UUID**, nao inteiros incrementais. Atualize clientes, deep links (`/chat?user={uuid}`, `/users/{uuid}/profile`) e bodies (`receiver_id`, ids de skills) de acordo.

---

## Chat e mensagens (Reverb)

- Acesse http://localhost:8080/chat apos login.
- So e possivel **enviar** mensagens enquanto existir uma troca entre os dois usuarios com status `pending`, `accepted` ou `scheduled`. O historico permanece visivel apos o encerramento da troca.
- Ao solicitar uma troca (`POST /api/exchanges`), o texto do pedido vira a **primeira mensagem** da conversa.
- O frontend usa Laravel Echo + Reverb (`VITE_REVERB_*` no build Docker). Autenticacao do canal privado: `POST /broadcasting/auth` com Bearer token.

---

## Como rodar sem Docker

### Pre-requisitos

- PHP 8.2+ (extensoes: mbstring, openssl; SQLite ou pgsql)
- Composer
- Node.js 18+ e npm
- Git

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Por padrao o `.env.example` usa **SQLite**. O seed (`DatabaseSeeder`) popula categorias e dados de demo.

Para chat em tempo real localmente, configure Reverb no `.env` (`BROADCAST_CONNECTION=reverb` e variaveis `REVERB_*`) e em outro terminal:

```bash
php artisan reverb:start
```

API: http://localhost:8000

### Frontend

```bash
cd frontend/frontend
npm install
```

Crie um `.env` (ou exporte as variaveis) alinhado ao backend:

```env
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=skillswap-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8081
VITE_REVERB_SCHEME=http
```

```bash
npm run dev
```

Frontend (Vite): http://localhost:5173

---

## Funcionalidades

### Backend (Laravel 12)

- API REST com autenticacao Sanctum (Bearer token)
- OAuth via Socialite (Google e GitHub)
- Models: User, Skill, Category, Exchange, Message (UUID publico)
- CRUD de habilidades e categorias (categorias: apenas admin)
- Trocas (`exchanges`) com status: pending, accepted, rejected, scheduled, completed, cancelled
- Conversas e mensagens, com gate de envio ligado a troca ativa
- Broadcast em tempo real (Laravel Reverb)
- Estatisticas publicas e do usuario autenticado
- Matching de habilidades (`/api/skill-matches`)
- CORS liberado; `POST /broadcasting/auth` para canais privados
- Seeders: 20 categorias, 3 usuarios, 8 habilidades, trocas e mensagens de demo

### Frontend (Vue 3)

| Rota | Descricao | Auth |
|------|-----------|------|
| `/` | Home com categorias | Publica |
| `/login`, `/register` | Login e cadastro (email/senha + OAuth) | Somente visitante |
| `/auth/callback` | Callback OAuth | Publica |
| `/skills` | Listagem com busca e filtros | Publica |
| `/dashboard` | Stats, minhas skills, matches, trocas | Protegida |
| `/chat` | Chat em tempo real | Protegida |
| `/profile` | Perfil do usuario logado | Protegida |
| `/users/:userId/profile` | Perfil publico (UUID) | Publica |
| `/agenda` | Agenda (stub; integracao futura) | Protegida |
| `/about`, `/help-center`, `/faq`, `/privacy-policy`, `/terms-of-use`, `/contact` | Paginas estaticas | Publicas |

Tambem: Pinia, Vue Router, Axios, Tailwind CSS, Laravel Echo + pusher-js.

### Ainda nao implementado / parcial

- Agenda: UI de placeholder (sem Google Calendar)
- Upload de imagens: campos `avatar` / `image` sao strings; sem pipeline de upload
- Notificacoes push/in-app
- Sistema completo de avaliacoes (ratings existem em usuarios/trocas; sem fluxo dedicado de reviews)

---

## Estrutura do projeto

```
SkillSwap/
├── docker-compose.yml
├── backend/                 # API Laravel
│   ├── app/
│   │   ├── Models/
│   │   ├── Http/Controllers/API/
│   │   ├── Events/          # MessageSent (broadcast)
│   │   └── Services/        # MessageService
│   ├── database/migrations/
│   ├── database/seeders/
│   ├── routes/api.php
│   ├── Dockerfile
│   └── docker-entrypoint.sh # migrate:fresh --seed + serve
├── frontend/frontend/       # SPA Vue.js
│   ├── src/views/
│   ├── src/stores/
│   ├── src/services/
│   ├── cypress/e2e/
│   ├── Dockerfile
│   └── nginx.conf
└── readme.md
```

---

## Tecnologias

| Camada | Stack |
|--------|-------|
| Backend | Laravel 12, PHP 8.2, Sanctum, Reverb, Socialite |
| Banco (Docker) | PostgreSQL 16 |
| Banco (local default) | SQLite |
| Frontend | Vue 3, Pinia, Vue Router, Axios, Tailwind CSS, Vite 7 |
| Tempo real | Laravel Echo + Reverb |
| Testes | PHPUnit 11 (backend), Cypress 15 (E2E frontend) |

---

## Endpoints da API

Base: `/api`. Autenticacao protegida: header `Authorization: Bearer {token}`.

### Publicos

| Metodo | Path | Descricao |
|--------|------|-----------|
| POST | `/register` | Registrar usuario |
| POST | `/login` | Login |
| GET | `/auth/{provider}/redirect` | URL OAuth (`google`, `github`) |
| GET | `/auth/{provider}/callback` | Callback OAuth |
| POST | `/auth/{provider}/token` | Troca de token OAuth |
| GET | `/categories` | Listar categorias |
| GET | `/skills` | Listar habilidades (filtros: `user_id`, `category_id`, `level`, `search`) |
| GET | `/skills/{skill}` | Detalhe de habilidade (UUID) |
| GET | `/stats` | Estatisticas publicas |

### Autenticados (`auth:sanctum`)

| Metodo | Path | Descricao |
|--------|------|-----------|
| POST | `/logout` | Logout |
| GET | `/user` | Usuario autenticado |
| PUT | `/profile` | Atualizar perfil |
| GET | `/users/{userId}/profile` | Perfil de outro usuario (UUID) |
| GET | `/my-skills` | Habilidades do usuario |
| GET | `/skill-matches` | Matches sugeridos |
| POST/PUT/DELETE | `/skills`, `/skills/{skill}` | CRUD de habilidades |
| POST/PUT/DELETE | `/categories`, `/categories/{category}` | CRUD de categorias (admin) |
| * | `/exchanges` | Resource de trocas |
| * | `/messages` | Resource de mensagens |
| GET | `/conversations` | Lista de conversas |
| GET | `/conversations/{userId}` | Conversa com um usuario (UUID) |
| GET | `/user-stats` | Stats do usuario |
| GET | `/weekly-stats` | Stats semanais |

Outros:

- `POST /broadcasting/auth` — autenticacao de canal privado (Sanctum)
- `GET /up` — health check

---

## Testes

### Backend (PHPUnit)

```bash
cd backend
php artisan test
```

Cobrem messaging, ownership/IDOR de exchanges, messages, skills/conversations e categorias admin.

### Frontend (Cypress E2E)

Com a stack Docker no ar (frontend em `:8080`, API em `:8000`):

```bash
cd frontend/frontend
npm run cy:run
# ou
npm run test:e2e
npm run cy:open
npm run test:e2e:messaging
npm run test:journey-gate
```

Specs principais: auth, dashboard, skills, exchanges, messaging, profile, static-pages.

O `baseUrl` do Cypress aponta para `http://localhost:8080` (frontend Docker).

---

## Solucao de problemas

1. **API nao responde / banco vazio apos restart** — no Docker o entrypoint roda `migrate:fresh --seed` a cada start; aguarde o backend ficar pronto.
2. **Chat sem tempo real** — confira se o servico `reverb` esta up e se `VITE_REVERB_*` bate com a porta 8081.
3. **Erro de autenticacao no frontend** — limpe o `localStorage` do navegador e faca login de novo.
4. **Cypress falha de conexao** — suba `docker compose up --build` antes; o E2E espera frontend em 8080 e API em 8000.
5. **Dependencias locais** — `composer install` no backend e `npm install` em `frontend/frontend`.
6. **UUIDs** — se links ou requests usarem IDs numericos, falharao; use os UUIDs retornados pela API.
