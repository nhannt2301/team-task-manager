# Team Task Manager

Team Task Manager is a development-focused task management application built as a monorepo.

- **Backend:** Laravel 11 REST API, PHP 8.3, Laravel Sanctum
- **Frontend:** Vue 3 SPA, TypeScript, Vuetify 3, Vite
- **Database:** MySQL 8
- **Development environment:** Docker Compose

## Prerequisites

- Docker and Docker Compose
- Git, if cloning the repository

## Setup and run with Docker Compose

Run the following commands from the repository root.

### 1. Configure the backend

Create the Laravel environment file:

```sh
cp backend/.env.example backend/.env
```

Set the database values in `backend/.env` to match the MySQL service in
`docker-compose.yml`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=root_password
```

These are local development credentials; do not use them in a production
environment.

### 2. Start the services

```sh
docker compose up -d --build
```

The frontend container installs its npm dependencies when it starts. The
backend and MySQL services are available at the following addresses:

| Service | URL |
| --- | --- |
| Frontend | <http://localhost:5173> |
| Backend API | <http://localhost:8000/api> |
| MySQL | `localhost:3306` from the host; `mysql:3306` from containers |

### 3. Install backend dependencies and initialize Laravel

```sh
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
```

Wait until MySQL is ready before running the migrations

```sh
docker compose exec backend php artisan migrate --seed
```

This creates the database tables and loads the demo accounts and sample data.
The seed creates 100 additional generated users and 1,000,000 sample tasks.

### 4. Open and sign in

Open <http://localhost:5173/login> and use one of the accounts in
[Test accounts](#test-accounts).

The frontend development server is already running in Docker. To run it
directly on your machine instead, keep the backend and database running, then
run:

```sh
cd frontend
npm install
npm run dev
```

The local frontend uses `http://localhost:8000/api` by default. Override this
with `VITE_API_BASE_URL` if your backend API is at a different address.

## Test accounts

The database seeder creates these accounts:

| Role | Email | Password | Access |
| --- | --- | --- | --- |
| Admin | `admin@example.com` | `password` | Can access all tasks and administer task assignments. |
| User | `user@example.com` | `password` | Can access tasks assigned to that user. |

The generated sample tasks are assigned to the 100 additional generated users,
not to `user@example.com`. Sign in as the admin to see the seeded tasks, or
create a task while signed in as the standard user to try the user workflow.
All generated users use the password `password`.

## Assumptions and simplifications

- This setup is intended for local development and demonstration, not
  production. Docker Compose uses development-oriented settings and default
  MySQL credentials.
- There is no public sign-up or password-reset flow; accounts are provided by
  the database seeder.
- Authentication uses Laravel Sanctum personal access tokens. The frontend
  stores the token in browser local storage.
- The seeder provides generated demonstration data rather than a curated or
  production dataset.
- The application has two roles: `admin` and `user`. Regular users are limited
  to their own assigned tasks; administrators can access all tasks.

## Common commands

Run migrations after pulling changes that add new migrations:

```sh
docker compose exec backend php artisan migrate
```

Stop the services while preserving the MySQL data volume:

```sh
docker compose down
```

**Warning:** `migrate:fresh` drops all tables and deletes their data. Use it
only when you intend to reset the local database; then reseed it with:

```sh
docker compose exec backend php artisan migrate:fresh --seed
```

The MySQL data is stored in the `dbdata` Docker volume. Removing that volume
also deletes the database contents.
