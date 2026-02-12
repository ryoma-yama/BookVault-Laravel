# Testing Strategy & Implementation Standards

## 1. Core Philosophy
- **Anti-Bullshit Testing**: Prohibit tests that only verify framework internals (e.g., Eloquent's basic CRUD, Factory instantiation, or fillable attributes). These are "meaningless tests" that increase maintenance cost without adding value.
- **Behavior over Implementation**: Focus on "what" the system does (Public API/Use Cases) rather than "how" it's implemented internally.
- **Harmful Coverage**: Recognize that redundant tests are more harmful than no tests. Do not write tests solely to increase coverage percentages.

## 2. Test Prioritization (The Pyramid)
- **Primary Focus (Feature Tests)**:
  - Prioritize testing via Controller endpoints (Routes).
  - Verify the end-to-end flow: Request -> Controller -> Logic -> Response & DB State.
- **Secondary Focus (Unit Tests)**:
  - Only test complex business logic, custom data transformations (e.g., `toSearchableArray`), or intricate calculation algorithms.
  - Skip unit tests for simple getters/setters or standard Eloquent relationships.

## 3. Mandatory Testing Rules
- **No Framework Testing**: Do not use `expect($model)->toBeInstanceOf(Model::class)` or `expect(Model::count())->toBe(1)`.
- **Controller Test Requirements**:
  - Test validation rules by providing invalid data (Negative Testing).
  - Verify successful redirects using `to_route()` and named routes.
  - Assert DB state changes only for side effects of the Use Case.
- **Mocking**: Use `Mockery` or `Event::fake()` / `Mail::fake()` to isolate external dependencies, but keep DB interactions real (using `RefreshDatabase`) to ensure data integrity.

## 4. Pest Syntax & Architecture
- Use **Pest** functional syntax.
- Use `describe()` blocks to group logic by endpoint or specific business rules.
- (Optional) Use [Pest Architecture Testing](https://pestphp.com) to enforce "Thin Controllers" (e.g., ensuring no DB logic exists outside of Transactions or Models).
