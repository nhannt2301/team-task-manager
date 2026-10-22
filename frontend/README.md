# Team Task Manager frontend

Vue 3, TypeScript, Vue Router, Vuetify 3, and Axios SPA for the Laravel API.

## Run locally

```sh
npm install
npm run dev
```

Set `VITE_API_BASE_URL` to the API root if it is not available at
`http://localhost:8000/api`.

## Routes

- `/login` — authenticate with the Laravel login API.
- `/tasks` — view your tasks, create tasks, and navigate through paginated results.
- `/tasks/:id` — view, update, or delete a task.

The login API returns a Sanctum personal access token. The frontend stores the
token and current user in local storage and sends the token as a bearer
authorization header on authenticated API requests. The Laravel API enforces
task ownership and admin permissions.

## Structure

- `src/router` — routes and authentication guards.
- `src/services` — typed API client and endpoint operations.
- `src/stores` — client authentication state.
- `src/types` — API models and payloads.
- `src/views` — login, task list, and task detail screens.
