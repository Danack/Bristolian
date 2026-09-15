<?php

declare(strict_types=1);

namespace Bristolian\PHPStan\Rules;

use Bristolian\PHPStan\TestClassCoversAttributeDetector;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Requires each *Test class under test/ to declare a class-level PHPUnit
 * coverage attribute (usually #[CoversNothing]) so coverage is not attributed
 * by accident.
 *
 * @implements Rule<InClassNode>
 */
class TestClassRequiresCoversAttributeRule implements Rule
{
    private TestClassCoversAttributeDetector $detector;

    public function __construct(?TestClassCoversAttributeDetector $detector = null)
    {
        $this->detector = $detector ?? new TestClassCoversAttributeDetector();
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();
        $fileName = $classReflection->getFileName();
        if ($fileName === null) {
            return [];
        }

        if ($this->detector->isPathUnderTestDirectory($fileName) === false) {
            return [];
        }

        $shortName = $classReflection->getNativeReflection()->getShortName();
        if ($this->detector->isTestClassShortName($shortName) === false) {
            return [];
        }

        if ($classReflection->isSubclassOf(\PHPUnit\Framework\TestCase::class) === false) {
            return [];
        }

        $originalNode = $node->getOriginalNode();
        if (!$originalNode instanceof Class_) {
            return [];
        }

        $attributeClassNames = [];
        foreach ($classReflection->getAttributes() as $attributeReflection) {
            $attributeClassNames[] = $attributeReflection->getName();
        }

        if ($this->detector->hasCoversAttribute($attributeClassNames)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Test class %s must declare a class-level PHPUnit coverage attribute '
                . '(usually #[CoversNothing]) so coverage is not attributed by accident.',
                $classReflection->getName()
            ))
                ->identifier('bristolian.test.missingCoversAttribute')
                ->build(),
        ];
    }
}
