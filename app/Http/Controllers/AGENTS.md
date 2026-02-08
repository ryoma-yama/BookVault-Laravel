# Controller Implementation Standards

When working in this directory, follow these mandatory standards for Laravel 12:

## Validation & Data Handling
- **Requirement**: Use **Form Request** classes for all validation. Do not use inline `$request->validate()`.
- **Constraint**: Prohibit manual array mapping in `store` and `update` methods. 
- **Action**: Pass `$request->validated()` directly to Model `create()` or `update()`.

## Navigation & Logic
- **Requirement**: Use the `to_route()` helper with named routes for all redirects.
- **Transaction**: Wrap any logic involving multiple database writes in a `DB::transaction` block.
- **Responsibility**: Keep controllers "thin." They should only handle HTTP-level logic.
