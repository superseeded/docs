# Plan: Document New API Endpoints

I will document the new API endpoints by updating the OpenAPI specification and creating corresponding MDX files, following the existing project structure.

## Proposed Changes

### 1. Update `api-reference/openapi.json`
Add the new endpoints to the `paths` section and define necessary schemas in `components/schemas`.

- **`GET /`**: Root status endpoint.
- **`POST /match`**: Match pot sizes endpoint.
- **Schemas**:
    - `MatchRequest`: `{"strings": ["..."]}`
    - `MatchResponse`: `{"results": [{"original": "...", "hierarchy_id": "...", "note": "..."}]}`

### 2. Create New MDX Files
Create two new files in `api-reference/endpoint/` that reference the OpenAPI definitions.

- **`api-reference/endpoint/root.mdx`**
    ```mdx
    ---
    title: 'Root Status'
    openapi: 'GET /'
    ---
    ```
- **`api-reference/endpoint/match.mdx`**
    ```mdx
    ---
    title: 'Match Pot Sizes'
    openapi: 'POST /match'
    ---
    ```

### 3. Update `docs.json`
Add the new pages to the navigation under the "Endpoint examples" group (or a new group if preferred).

```json
{
  "group": "Endpoint examples",
  "pages": [
    "api-reference/endpoint/get",
    "api-reference/endpoint/create",
    "api-reference/endpoint/delete",
    "api-reference/endpoint/webhook",
    "api-reference/endpoint/root",
    "api-reference/endpoint/match"
  ]
}
```

## Implementation Steps

1.  **Modify `api-reference/openapi.json`**: Add `/` and `/match` paths and schemas.
2.  **Create `api-reference/endpoint/root.mdx`**: Basic MDX with `openapi` property.
3.  **Create `api-reference/endpoint/match.mdx`**: Basic MDX with `openapi` property.
4.  **Modify `docs.json`**: Update navigation to include the new files.
