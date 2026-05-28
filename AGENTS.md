# Project Guidelines

## Runtime

- Use PHP `^8.3`.
- Manage dependencies with the local Composer phar: `php composer.phar ...`.
- Run tests with `php composer.phar test`.
- Check formatting with `php composer.phar format:test`.
- Apply formatting with `php composer.phar format`.
- More project documentation lives in `docs/`.

## Application Entry Points

- `index.php` is the single public entry point.
- Requests whose path starts with `/api` are handled by `App\Api\ApiKernel`.
- Other requests are handled by the legacy web router through `App\Route`.
- Do not start PHP sessions for API requests unless a future API feature explicitly requires it.

## Web Routing

- The existing web system uses the custom router in `App\Route`.
- Keep web routes and web controllers compatible with the current legacy flow.
- Do not migrate the web router while working on API-only changes.

## API Routing

- API routes are registered in `App/Routes/api.php`.
- API routes must use Symfony Routing's `RouteCollection` and `Route`.
- Do not register API routes in the database.
- Each API route must declare:
  - a unique route name;
  - a path starting with `/api`;
  - `_controller`;
  - `_action`;
  - allowed HTTP methods.
- Protected routes must set `_auth` to `true`. The current foundation only checks that a Bearer token exists; JWT validation is implemented in a later authentication step.

Example:

```php
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

## API Response Contract

All API responses must go through `App\Api\ApiResponse`.

Success responses use:

```json
{
  "data": {},
  "meta": {}
}
```

Error responses use:

```json
{
  "error": {
    "code": "not_found",
    "message": "Rota da API não encontrada.",
    "details": {}
  }
}
```

Paginated responses use:

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

## API Resources

- Use Resource classes for API output formatting, following the Laravel Resource idea.
- Resources must extend `App\Api\ApiResource`.
- Place concrete API resources under `App/Http/Resources`.
- Controllers should not expose raw DAO rows directly. Convert them through a Resource before returning.

Example:

```php
return ApiResponse::success(new AnimalResource($animal));
```

For lists:

```php
return ApiResponse::success(AnimalResource::collection($animals));
```

## Error Handling

- Use Symfony HTTP exceptions from `symfony/http-kernel` for API errors when possible.
- `App\Api\ApiKernel` converts Symfony HTTP exceptions into the API error envelope.
- Do not echo JSON manually from controllers.
- Do not return ad hoc error arrays from controllers.

## Tests

- Pest is the project test runner.
- API behavior must be covered with Pest tests under `tests/`.
- New API endpoints should include tests for success, not found or invalid method when applicable, validation/auth errors when applicable, and response envelope shape.
