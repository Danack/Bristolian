1. How to evolve scenarios without brittleness

The mistake is to let scenarios become procedural scripts instead of capabilities.

❌ Brittle scenario
public function setupPaidUserWithOrder(): self
{
$this->userExists('u1');
$this->orderExists('o1', 'u1');
$this->invoiceExists('i1', 'o1');
$this->paymentExists('p1', 'i1');
return $this;
}


This breaks when anything changes.

✅ Stable scenario: introduce capabilities
public function paidUser(string $userId): self
{
$this->ensureUser($userId);
$this->ensureOrderForUser($userId);
$this->ensurePaidInvoiceForUser($userId);
return $this;
}


Each step is idempotent and hides structure.

private function ensurePaidInvoiceForUser(string $userId): void
{
$order = $this->world->db()->findOrderForUser($userId)
?? $this->createOrder($userId);

    $invoice = $this->world->db()->findInvoiceForOrder($order->id())
        ?? $this->createInvoice($order->id());

    $this->world->db()->markInvoicePaid($invoice->id());
}


Now tests depend on outcomes, not structure.

Rule:

Scenario methods should describe facts, not steps.

2. How to split worlds per bounded context

Large systems naturally have multiple models.
You do not want one giant InMemoryDatabase.

Instead:

final class BillingWorld
{
private BillingDatabase $db;
private array $services = [];

    public function __construct()
    {
        $this->db = new BillingDatabase();
    }

    public function repo(string $class)
    {
        return $this->services[$class]
            ??= new $class($this->db);
    }
}

final class UserWorld
{
private UserDatabase $db;
private array $services = [];

    public function __construct()
    {
        $this->db = new UserDatabase();
    }
}


Each bounded context has:

its own world

its own fake DB

its own scenarios

its own language

its own invariants

If contexts must interact (common case)

You use ports, not shared DBs.

interface UserLookup
{
public function find(UserId $id): ?User;
}


Billing fake:

final class InMemoryUserLookup implements UserLookup
{
public function __construct(
private UserWorld $users
) {}

    public function find(UserId $id): ?User
    {
        return $this->users->repo(UserRepository::class)->getById($id);
    }
}


Now contexts are coupled only at the boundary.

This is how you stop test infrastructure becoming a monolith.

3. How to avoid scenario explosion

Scenario explosion happens when you create one scenario per test shape:

PaidUserScenario
UnpaidUserScenario
RefundedUserScenario
TrialUserScenario
ExpiredUserScenario


This does not scale.

The fix: orthogonal traits

Scenarios should be composable, not enumerated.

BillingScenario::new()
->userExists('u1')
->withPaidInvoice()
->withRefund()
->withOverdueBalance()
->whenBillingRuns();


Each modifier adds one axis of variation.

Example modifier
public function withPaidInvoice(): self
{
$this->ensureInvoice();
$this->world->db()->markInvoicePaid($this->invoiceId);
return $this;
}

If modifiers conflict (important)

Make conflicts explicit:

public function withRefund(): self
{
if ($this->hasPaidInvoice === false) {
throw new LogicException('Refund requires a paid invoice');
}

    // ...
}


Failing fast is better than silent weirdness.

4. The unifying principle (this matters)

All three problems are solved by the same idea:

Scenarios describe facts about the world, not how the world got there.

Once you enforce that, everything becomes stable.

5. What this looks like in a very large codebase

Realistically:

tests/
Support/
Billing/
BillingWorld.php
BillingScenario.php
BillingDatabase.php
Users/
UserWorld.php
UserScenario.php
UserDatabase.php


Each directory is a bounded context.
Each world is small.
Each scenario has 10–30 methods, not 200.
Tests read like prose.

6. Final sanity check

If you can read a test without knowing the schema, you have succeeded.
If you must know table structure, you have leaked infrastructure.

If you want, I can show a before/after refactor of a real messy test, or a rule-of-thumb checklist for reviewing scenarios in PRs.