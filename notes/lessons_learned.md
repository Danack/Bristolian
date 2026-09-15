# Lessons Learned - Common Mistakes to Avoid

This document captures mistakes and issues encountered during development sessions to help prevent them in the future.

## PHPStan Fixes - Common Pitfalls

### 1. Typed Properties and Traits That Use `newInstanceWithoutConstructor()`

**Issue:** When fixing PHPStan "missing type" errors, adding typed properties to classes that use the `FromArray` trait will break the code.

**What Happened:**
- Added `public string $name;` to `TestFromArrayClass` to fix PHPStan missing type error
- This broke the `FromArray` trait which uses `newInstanceWithoutConstructor()` and then iterates over properties
- Typed properties in PHP cannot be accessed before initialization, causing runtime errors

**Solution:**
- Use PHPDoc types (`/** @var string */`) instead of typed properties for classes that use `FromArray` trait
- The trait needs to be able to set properties without going through a constructor

**Example:**
```php
// ❌ BAD - Breaks FromArray trait
class TestFromArrayClass {
    use FromArray;
    public string $name;  // Cannot be accessed before initialization
}

// ✅ GOOD - Works with FromArray trait
class TestFromArrayClass {
    use FromArray;
    /** @var string */
    public $name;  // PHPDoc type satisfies PHPStan, works with trait
}
```

### 2. Accidental Brace Removal

**Issue:** When removing redundant assertions, be careful not to accidentally remove closing braces.

**What Happened:**
- Removed `assertTrue(true)` and the closing brace of the `if` block
- This caused syntax errors: "unexpected T_PUBLIC" and "unexpected EOF"

**Solution:**
- Always verify syntax after making edits
- Use search_replace with sufficient context to ensure unique matches
- Run PHPStan or syntax check after edits

**Prevention:**
- Include more context in search_replace operations
- Verify file structure before and after edits
- Run syntax checks immediately after making changes

### 3. Redundant Assertions - Understanding PHPStan's Type System

**Issue:** PHPStan knows types from method signatures and PHPDoc, so assertions like `assertIsString()` on values that are already known to be strings are redundant.

**What Happened:**
- PHPStan flagged many `assertIsString()`, `assertIsArray()`, `assertTrue(true)`, `assertNull(null)` calls as "will always evaluate to true"
- These assertions don't add value when PHPStan already knows the type

**Solution:**
- Remove redundant type assertions when PHPStan already knows the type
- Keep meaningful assertions like `assertNotEmpty()`, `assertSame()`, `assertContainsOnlyInstancesOf()`
- Only use type assertions when the type is not already known from method signatures/PHPDoc

**Example:**
```php
// ❌ BAD - PHPStan already knows $id is string
$id = $repo->storeMeme(...);  // Returns string
$this->assertIsString($id);  // Redundant
$this->assertNotEmpty($id);  // Meaningful

// ✅ GOOD - Only meaningful assertions
$id = $repo->storeMeme(...);  // Returns string
$this->assertNotEmpty($id);  // Meaningful assertion
```

### 4. Missing Return Types on Test Methods

**Issue:** Test methods should always have explicit return types, even if they return nothing.

**What Happened:**
- PHPStan flagged test methods without return types
- Many test methods were missing `: void` return type

**Solution:**
- Always add `: void` return type to test methods
- This helps PHPStan and makes code more explicit

**Example:**
```php
// ❌ BAD
public function testSomething() {
    // ...
}

// ✅ GOOD
public function testSomething(): void {
    // ...
}
```

### 5. Unnecessary Null Checks

**Issue:** Checking for null when PHPStan already knows a value cannot be null.

**What Happened:**
- Methods returning non-nullable types were being checked for null
- PHPStan flagged these as "always false" comparisons

**Solution:**
- Remove null checks when method signatures guarantee non-null return
- Trust the type system when it's explicit

**Example:**
```php
// ❌ BAD - getUserProfile() returns UserProfileWithDisplayName (non-nullable)
$profile = $repo->getUserProfile($user_id);
if ($profile === null) {  // Always false
    return new ErrorResponse();
}

// ✅ GOOD - Trust the type system
$profile = $repo->getUserProfile($user_id);
// Use $profile directly, no null check needed
```

## Best Practices Going Forward

1. **Always understand how traits work** before modifying classes that use them
2. **Verify syntax** after making edits, especially when removing code
3. **Trust PHPStan's type system** - if it knows a type, don't assert it redundantly
4. **Add return types** to all methods, especially test methods
5. **Remove redundant null checks** when types guarantee non-null values
6. **Run tests after PHPStan fixes** to ensure nothing broke

## Testing Checklist

After making PHPStan fixes, always:
- [ ] Run PHPStan again to verify errors are fixed
- [ ] Run unit tests to ensure nothing broke
- [ ] Check for syntax errors
- [ ] Verify the code still works as expected
