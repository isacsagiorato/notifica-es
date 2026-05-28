# API Endpoints

Este documento define o fluxo para criar endpoints REST na API mobile.

## 1. Registrar a rota

As rotas da API ficam em `App/Routes/api.php`.

```php
use App\Controller\ApiAnimalController;
use Symfony\Component\Routing\Route;

$routes->add('api.animals.index', new Route(
    path: '/api/animals',
    defaults: [
        '_controller' => ApiAnimalController::class,
        '_action' => 'index',
        '_auth' => true,
    ],
    methods: ['GET'],
));
```

Regras:

- o nome da rota deve ser unico;
- o path deve começar com `/api`;
- `_controller` aponta para a classe do controller;
- `_action` aponta para o metodo chamado no controller;
- `methods` define os metodos HTTP aceitos;
- use `_auth => true` para endpoints protegidos.

Endpoints publicos nao devem declarar `_auth`.

## 2. Criar o controller

Controllers da API ficam em `App/Controller`.

```php
namespace App\Controller;

use App\Api\ApiResponse;

class ApiAnimalController
{
    public function index(): array
    {
        return ApiResponse::success([
            // dados aqui
        ]);
    }
}
```

Todo endpoint deve retornar usando `App\Api\ApiResponse`.

Nao use `echo`, `json_encode`, `header`, nem arrays de erro soltos dentro dos controllers.

## 3. Criar Resource para saida da API

Quando o endpoint retornar dados do banco, formate a saida com Resources em `App/Http/Resources`.

```php
namespace App\Http\Resources;

use App\Api\ApiResource;

class AnimalResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['nome'],
        ];
    }
}
```

Uso com item unico:

```php
return ApiResponse::success(new AnimalResource($animal));
```

Uso com lista:

```php
return ApiResponse::success(AnimalResource::collection($animals));
```

Uso com paginacao:

```php
return ApiResponse::paginated(
    AnimalResource::collection($animals),
    page: 1,
    perPage: 15,
    total: 120
);
```

## 4. Contrato de resposta

Sucesso:

```json
{
  "data": {},
  "meta": {}
}
```

Erro:

```json
{
  "error": {
    "code": "not_found",
    "message": "Rota da API nao encontrada.",
    "details": {}
  }
}
```

Paginacao:

```json
{
  "data": [],
  "meta": {
    "pagination": {
      "page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1
    }
  }
}
```

## 5. Autenticacao

Endpoint protegido:

```php
'_auth' => true,
```

Rotas protegidas exigem JWT no header:

```http
Authorization: Bearer <token>
```

Variaveis de ambiente:

```env
JWT_SECRET=
JWT_TTL_SECONDS=3600
JWT_ISSUER=amigopet-api
```

`JWT_SECRET` deve ser uma chave longa o suficiente para HS256. Em desenvolvimento,
uma forma simples de gerar a chave e:

```bash
openssl rand -hex 32
```

### Login

```http
POST /api/auth/login
```

Payload:

```json
{
  "email": "usuario@email.com",
  "password": "senha"
}
```

Resposta:

```json
{
  "data": {
    "token": "...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "email": "usuario@email.com",
      "type": "adotante",
      "status": "a"
    }
  },
  "meta": {}
}
```

### Usuario autenticado

```http
GET /api/users/me
Authorization: Bearer <token>
```

Resposta:

```json
{
  "data": {
    "id": 1,
    "email": "usuario@email.com",
    "type": "adotante",
    "status": "a"
  },
  "meta": {}
}
```

## 6. Endpoints publicos de consulta

Endpoints publicos nao declaram `_auth => true` e podem ser consumidos sem
JWT. Eles devem expor apenas dados adequados para consulta publica.

Entidades publicas implementadas:

```http
GET /api/animals
GET /api/animals/{id}

GET /api/species
GET /api/species/{id}

GET /api/breeds
GET /api/breeds/{id}

GET /api/ongs
GET /api/ongs/{id}

GET /api/clinics
GET /api/clinics/{id}

GET /api/veterinarians
GET /api/veterinarians/{id}
```

Entidades como `login`, `adotante`, `administrador`, `rastreador`,
`solicitacao_adocao`, vinculos e historicos internos nao devem ser expostas
como endpoints publicos sem uma decisao explicita de produto e seguranca.

## 7. Testes

Todo comportamento novo deve ter teste automatizado.

Todo endpoint novo deve ter teste Pest em `tests/`. Nao considere um endpoint
completo enquanto o caminho de sucesso e os erros esperados nao estiverem
cobertos.

Cobrir, quando aplicavel:

- resposta de sucesso;
- formato do envelope `data` e `meta`;
- erros no envelope `error`;
- metodo HTTP invalido;
- rota protegida sem token;
- paginacao.

Comandos:

```bash
php composer.phar test
php composer.phar format:test
```

Observacao: `format:test` verifica o projeto todo e pode falhar em arquivos legados. Arquivos novos ou alterados devem seguir o Pint.
