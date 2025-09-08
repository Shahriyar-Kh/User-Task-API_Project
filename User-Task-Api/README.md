# Laravel User & Task API (Stub)

This ZIP contains starter scaffold files for a Laravel-based User & Task Management API:
- JWT authentication (tymon/jwt-auth)
- Role-based access (admin/user)
- Task CRUD with validation
- Rate limiting hints and middleware

**How to use**
1. Create a fresh Laravel project: `composer create-project laravel/laravel user-task-api`
2. Copy the files from this ZIP into the corresponding paths in your Laravel project.
3. Run `composer require tymon/jwt-auth`
4. Publish and set up JWT secret: `php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"` then `php artisan jwt:secret`
5. Update `.env` (see `.env.example`) and run `php artisan migrate`.
6. Start server: `php artisan serve`

**Included files**
- app/Models/User.php
- app/Models/Task.php
- app/Http/Controllers/AuthController.php
- app/Http/Controllers/UserController.php
- app/Http/Controllers/TaskController.php
- app/Http/Middleware/RoleMiddleware.php
- app/Http/Requests/StoreTaskRequest.php
- database/migrations/*_create_users_table.php
- database/migrations/*_create_tasks_table.php
- routes/api.php
- .env.example

This is a stub — copy into a Laravel project and adjust namespaces, imports and composer packages as needed.
