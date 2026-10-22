# Answers: Performance, Security, and TypeScript

## 1. API Performance: `GET /api/tasks`

With a table containing 1,000,000 or more records, the endpoint should limit the amount of data read, optimize queries for how users filter and sort, and measure performance using production-like data.

1. **Use cursor/keyset pagination instead of deep offset pagination.** A large `OFFSET` forces the database to read and skip many rows. Keyset pagination continues from the previous page's sort key and is generally more stable and efficient when navigating deeper into a dataset. This project already uses `cursorPaginate(15)` and sorts by descending `id` in `TaskService`.
2. **Create indexes that match filter and sort conditions.** Review actual queries and use `EXPLAIN` to choose indexes. For example, a composite index on `assigned_to` and `id` may suit a user's task list; if status is frequently used as a filter, consider a composite index that matches `status` and the query's sort order. Composite index columns must be ordered to match the query conditions. Avoid creating too many indexes, as they increase storage and slow writes and updates.
3. **Eager-load relationships needed for display to avoid N+1 queries.** Load only the required columns. For example, this project calls `with('assignee:id,name,email')` when retrieving the task list. Do not query each task's relationship separately inside a loop.
4. **Select only the columns needed in the response.** Instead of retrieving every column with `*`, identify and explicitly select the fields the screen needs. This reduces I/O, PHP memory usage, and response size; retain any keys required for relationships and cursor pagination.
5. **Optimize search and filters.** A query like `LIKE '%term%'` generally cannot use a standard B-tree index. For large-scale search, consider MySQL `FULLTEXT` (if it meets the search requirements), a dedicated search service, or switching to prefix search. Validate the maximum search length and supported filters.
6. **Use Redis caching selectively.** Cache infrequently changing data or list results with keys that include the user/authorization scope, filters, sort order, and cursor. Set an appropriate TTL and invalidate or refresh the cache when a task is created, updated, or deleted. Do not use a shared cache that could expose one user's data to another. Measure first, since cursor-based lists and user-specific data can make caching less effective.
7. **Measure query and response performance.** Use `EXPLAIN`/`EXPLAIN ANALYZE`, Laravel query logging, or APM to identify full table scans, slow queries, and N+1 issues. Monitor response time, query count, and payload size; limit page size and test with large datasets.

## 2. Security: Laravel API + Vue SPA

- **SQL Injection:** Use Eloquent/Query Builder and parameter binding; never concatenate input directly into SQL. For column names or sort directions that cannot be bound, map input through an allowlist. Validate input with Form Requests and enforce authorization in the API.
- **XSS:** Do not render user content as arbitrary HTML. Vue escapes interpolated content by default; avoid `v-html`, or sanitize HTML with a trusted sanitizer if it is genuinely required. Consider a Content Security Policy and do not place untrusted data in scripts, URLs, or HTML attributes.
- **CSRF:** If authentication uses cookies/sessions, configure Sanctum's stateful domains appropriately, use CSRF cookies/tokens and `SameSite`, and verify CORS/origins precisely. Do not allow every origin when credentials are enabled. With bearer tokens alone, browsers do not automatically send the token like a cookie, so the CSRF threat is different; however, XSS can still steal the token.
- **Token leakage:** This project currently stores a Sanctum personal access token in `localStorage`; JavaScript running on the same origin can read it, so XSS could lead to session compromise. For a first-party SPA, consider using Sanctum cookie-based sessions with `HttpOnly`, `Secure`, and `SameSite` cookies and CSRF protection. If bearer tokens are retained, limit their lifetime and permissions, support revocation, never write tokens to logs or URLs, and protect the application against XSS.
- **Rate limiting and brute force:** Rate-limit login and abuse-prone APIs by IP/account; return an appropriate `429` response and monitor failed login attempts. For list endpoints, cap page size and keep search costs within reasonable limits.
- **HTTPS and deployment configuration:** Transmit credentials only over HTTPS; enable HSTS where appropriate, set cookies as `Secure`, and do not disable TLS verification. Manage secrets through environment variables or a secret store, disable debug mode in production, update dependencies, and restrict CORS to the frontend domain.
- **Authorization and input validation:** Always enforce permissions for each task on the backend (do not rely on hiding buttons in the frontend), validate data before saving, and return errors that do not disclose internal information. This project groups task routes under the `auth:sanctum` middleware; continue enforcing ownership/admin checks in policies or services for each operation.

## 3. TypeScript & Vue 3

### Benefits of TypeScript in a Vue 3 SPA

- Detects type errors and API contract mismatches early during development and builds.
- Clearly typed props, events, models, and API data improve IDE autocomplete and make refactoring safer.
- Makes it easier to share types across components, services, and stores, reducing confusion when data changes.
- Helps keep components readable and maintainable as the project and number of screens grow.

### Typed props example with `<script setup lang="ts">`

```vue
<script setup lang="ts">
interface Task {
  id: number
  title: string
  status: 'pending' | 'in_progress' | 'completed'
}

defineProps<{
  task: Task
  compact?: boolean
}>()
</script>

<template>
  <article :class="{ compact }">
    <h2>{{ task.title }}</h2>
    <span>{{ task.status }}</span>
  </article>
</template>
```

`task` is a required prop and is checked against the `Task` type; `compact` is optional (`boolean | undefined`). Vue infers the prop types in the template from the TypeScript declaration.
