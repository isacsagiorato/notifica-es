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

Hoje a base apenas exige o header:

```http
Authorization: Bearer <token>
```

A validacao JWT real deve ser implementada no bloco de autenticacao.

## 6. Testes

Todo endpoint novo deve ter teste Pest em `tests/`.

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
