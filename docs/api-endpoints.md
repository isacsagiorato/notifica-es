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

### Animais

Animais tem endpoints proprios (`ApiAnimalController` + `ApiAnimalDAO`).
A leitura e publica; a escrita exige JWT (qualquer usuario autenticado ativo).

```http
GET    /api/animals            # publico, com filtros
GET    /api/animals/{id}       # publico, perfil do animal
POST   /api/animals            # protegido, cadastrar
PUT    /api/animals/{id}       # protegido, alterar
DELETE /api/animals/{id}       # protegido, excluir
```

Filtros do `GET /api/animals` (query string, combinaveis):

- `species`: filtra pela especie (ex.: `?species=Cachorro`);
- `status`: filtra pelo status (ex.: `?status=disponivel`).

Corpo de `POST`/`PUT` (chaves em ingles, iguais as da resposta):

```json
{
  "name": "Luna",
  "birth_date": "2025-02-01",
  "sex": "f",
  "species": "Gato",
  "size": "pequeno",
  "location": "Campinas",
  "photo": "luna.jpg",
  "status": "disponivel"
}
```

Regras de validacao (`422 validation_error` quando violadas):

- `name`: obrigatorio no cadastro;
- `sex`: `m` ou `f`;
- `size`: `pequeno`, `medio` ou `grande`;
- `status`: `disponivel`, `adotado`, `em_tratamento` ou `reservado`
  (padrao `disponivel` no cadastro);
- `birth_date`: formato `YYYY-MM-DD`.

O `PUT` aceita atualizacao parcial: os campos nao enviados mantem o valor atual.
`PUT`/`DELETE` em id inexistente retornam `404 not_found` (`Animal nao encontrado.`).
`DELETE` bem-sucedido responde `200` com `{"id": <id>, "deleted": true}`.

### Ranking de animais

Endpoint publico que retorna os animais ordenados por um criterio de ranking.

```http
GET /api/ranking
GET /api/ranking?order=adotados
```

Filtros (`order`):

- `adotados` (padrao): ordena por numero de solicitacoes de adocao do animal,
  do maior para o menor. Empates sao desempatados pelo `id` crescente.

Um `order` nao suportado retorna `400 bad_request`.

Cada item segue o `AnimalResource` e inclui o campo extra `adoption_requests`
com a contagem de solicitacoes de adocao:

```json
{
  "data": [
    {
      "id": 2,
      "name": "Mel",
      "birth_date": "2024-01-15",
      "sex": "f",
      "species": "Cachorro",
      "size": "pequeno",
      "location": "Sao Paulo",
      "photo": "mel.jpg",
      "status": "disponivel",
      "adoption_requests": 3
    }
  ],
  "meta": {}
}
```

> O filtro `visualizados` ainda nao esta disponivel: o schema nao possui coluna
> de visualizacoes. Para habilitar, adicione a coluna `visualizacoes` na tabela
> `animal`, inclua `visualizados` em `ApiRankingController::ORDERS` e adicione o
> metodo correspondente em `RankingDAO`.

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
