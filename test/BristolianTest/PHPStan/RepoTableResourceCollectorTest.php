<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan;

use BristolianGenerated\Database\meme_tag;
use BristolianGenerated\Database\stored_meme;
use BristolianGenerated\Database\user;
use BristolianGenerated\Database\user_ownership;
use Bristolian\PHPStan\RepoTableResourceCollector;
use Bristolian\PHPStan\SqlTableReferenceExtractor;
use BristolianTest\BaseTestCase;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\PHPStan\RepoTableResourceCollector::class)]
class RepoTableResourceCollectorTest extends BaseTestCase
{
    private RepoTableResourceCollector $collector;

    public function setUp(): void
    {
        parent::setUp();
        $this->collector = new RepoTableResourceCollector(new SqlTableReferenceExtractor());
    }

    public function test_diff_reports_missing_reads_for_method(): void
    {
        $messages = $this->collector->diff(
            [],
            [meme_tag::class],
            [meme_tag::class, stored_meme::class],
            [meme_tag::class],
            'PdoMemeTagRepo::getUserTagsForMeme'
        );

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('PdoMemeTagRepo::getUserTagsForMeme', $messages[0]);
        $this->assertStringContainsString('missing #[ReadsTable', implode("\n", $messages));
    }

    public function test_diff_implementation_attributes_against_interface_requires_emphasis(): void
    {
        $messages = $this->collector->diffImplementationAttributesAgainstInterface(
            [meme_tag::class, stored_meme::class],
            [],
            [],
            [],
            'MemeTagRepo::getUserTagsForMeme',
            'PdoMemeTagRepo::getUserTagsForMeme'
        );

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('repeat on the implementation method', $messages[0]);
    }

    public function test_diff_accepts_clean_match(): void
    {
        $messages = $this->collector->diff(
            [user_ownership::class],
            [user::class, user_ownership::class],
            [user_ownership::class],
            [user::class, user_ownership::class],
            'UserRepo::ensureSystemUserExists'
        );

        $this->assertSame([], $messages);
    }

    public function test_collect_used_from_method_includes_sql_string_tables(): void
    {
        $code = <<<'PHP'
<?php
namespace Test;
class Example {
    public function getUserTagsForMeme(): array {
        $sql = <<< SQL
select mt.id from meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
SQL;
        return [];
    }
}
PHP;

        $classMethod = $this->parseFirstMethod($code);
        $used = $this->collector->collectUsedFromMethodNode(
            $classMethod,
            static fn (Name $name): string => $name->toString(),
            static fn (string $helperClass): bool => in_array(
                $helperClass,
                [meme_tag::class, stored_meme::class],
                true
            )
        );

        $this->assertSame([meme_tag::class, stored_meme::class], $used['reads']);
        $this->assertSame([], $used['writes']);
    }

    public function test_collect_this_method_call_names(): void
    {
        $code = <<<'PHP'
<?php
namespace Test;
class Example {
    public function saveDataIfNew(): void {
        $this->saveData();
        $other->saveData();
        $this->saveData();
    }
    public function saveData(): void {}
}
PHP;

        $classMethod = $this->parseFirstMethod($code);
        $this->assertSame(
            ['saveData'],
            $this->collector->collectThisMethodCallNames($classMethod)
        );
    }

    public function test_bubble_used_through_this_calls_includes_callee_writes(): void
    {
        $directUsedByMethod = [
            'saveDataIfNew' => [
                'reads' => [meme_tag::class],
                'writes' => [],
            ],
            'saveData' => [
                'reads' => [],
                'writes' => [stored_meme::class],
            ],
        ];
        $thisMethodCallsByMethod = [
            'saveDataIfNew' => ['saveData'],
            'saveData' => [],
        ];

        $bubbled = $this->collector->bubbleUsedThroughThisCalls(
            $directUsedByMethod,
            $thisMethodCallsByMethod
        );

        $this->assertSame([meme_tag::class], $bubbled['saveDataIfNew']['reads']);
        $this->assertSame([stored_meme::class], $bubbled['saveDataIfNew']['writes']);
        $this->assertSame([stored_meme::class], $bubbled['saveData']['writes']);
    }

    public function test_path_enforcement(): void
    {
        $this->assertTrue(
            $this->collector->isPathInEnforcedDirectories(
                '/project/src/Bristolian/Repo/MemeTagRepo/PdoMemeTagRepo.php',
                ['src/Bristolian/Repo']
            )
        );
        $this->assertFalse(
            $this->collector->isPathInEnforcedDirectories(
                '/project/src/Bristolian/CliController/Foo.php',
                ['src/Bristolian/Repo']
            )
        );
    }

    private function parseFirstMethod(string $code): ClassMethod
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $statements = $parser->parse($code);
        $this->assertNotNull($statements);

        foreach ($statements as $statement) {
            if (!$statement instanceof Namespace_) {
                continue;
            }
            foreach ($statement->stmts as $namespaceStatement) {
                if (!$namespaceStatement instanceof \PhpParser\Node\Stmt\Class_) {
                    continue;
                }
                foreach ($namespaceStatement->getMethods() as $method) {
                    return $method;
                }
            }
        }

        $this->fail('No class method found in fixture');
    }
}
