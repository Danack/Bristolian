---
description: "Point the machine spirit to the testing codex."
alwaysApply: true
---
- No enthusiastic confirmation phrases at the start of responses. Be direct.
- NEVER start responses with "Perfect!", "Excellent!", "Great!", or similar enthusiastic phrases. Be direct and factual.
- Never create mocks or expect mocks to be used in this project. Use real objects and dependencies instead of mock objects in tests.
- If you need to invoke a tool please read the files in @docs.
- If you are writing any code, or running any scripts, read the document @docs/developing/development_setup.md.
- If you are writing tests, read @docs/developing/testing_guidelines.md first.
- When I say "all tests" I am including PHP Unit, PhpStan, code sniffer and others. There should be a script to run them all.
- If you are doing any coding, read the document @docs/developing/testing_guidelines.md to learn how to run the tests. For running all code quality tools before finalising work, see the finalise_work command.
- **Avoid redundant type assertions in tests**: Do not use assertions like `assertIsString()`, `assertIsInt()`, `assertIsArray()`, `assertTrue(true)`, or `assertNull(null)` when PHPStan already knows the type. PHPStan will flag these as "will always evaluate to true" because the static analyzer already knows the type. Only use these assertions when they provide actual value (e.g., when the type is not already known from the method signature or PHPDoc). Instead, use meaningful assertions like `assertNotEmpty()`, `assertSame()`, `assertContainsOnlyInstancesOf()`, etc.
- **Always add return types to test methods**: All test methods must have explicit return types. Use `: void` for test methods that don't return a value. This helps PHPStan and makes the code more explicit.
- **Avoid unnecessary null checks**: Do not check for null when PHPStan already knows a value cannot be null (e.g., when a method returns a non-nullable type). Remove redundant `if ($value !== null)` checks when the type system guarantees the value is not null.
- **Put `else` on a new line**: When using `if`/`else`, put the `else` keyword on its own line after the closing `}` of the `if` block, not on the same line as the brace (e.g. use `}\n        else {`, not `} else {`).
- **Use union syntax for nullable types**: Prefer `string|null` (and similar union types) over `?string` for parameters, return types, and properties. Use the explicit union form consistently.
