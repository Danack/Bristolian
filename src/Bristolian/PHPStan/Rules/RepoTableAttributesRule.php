<?php

declare(strict_types=1);

namespace Bristolian\PHPStan\Rules;

use Bristolian\Attribute\SkipTableAttributes;
use Bristolian\PHPStan\RepoTableResourceCollector;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
class RepoTableAttributesRule implements Rule
{
    private RepoTableResourceCollector $collector;

    /**
     * @param list<string> $enforcedDirectories Project-relative directory prefixes
     */
    public function __construct(
        private array $enforcedDirectories,
        private ReflectionProvider $reflectionProvider
    ) {
        $this->collector = new RepoTableResourceCollector();
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

        if (!$this->collector->isPathInEnforcedDirectories($fileName, $this->enforcedDirectories)) {
            return [];
        }

        if ($this->hasSkipAttribute($classReflection)) {
            return [];
        }

        if ($classReflection->isInterface()) {
            return [];
        }

        $shortName = $classReflection->getNativeReflection()->getShortName();
        if (str_starts_with($shortName, 'Fake') || str_starts_with($shortName, 'InMemory')) {
            return [];
        }

        $originalNode = $node->getOriginalNode();
        if (!$originalNode instanceof Class_) {
            return [];
        }

        $errors = [];

        foreach ($originalNode->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }

            $methodName = $classMethod->name->toString();
            if ($methodName === '__construct') {
                continue;
            }

            $interfaceMethodReflection = $this->findInterfaceMethodReflection(
                $classReflection,
                $methodName
            );
            if ($interfaceMethodReflection === null) {
                continue;
            }

            $interfaceDeclared = $this->collector->collectDeclaredFromMethodReflection(
                $interfaceMethodReflection
            );

            $implementationMethodReflection = $classReflection->getNativeReflection()->getMethod($methodName);
            $implementationDeclared = $this->collector->collectDeclaredFromMethodReflection(
                $implementationMethodReflection
            );

            foreach (array_merge(
                $interfaceDeclared['reads'],
                $interfaceDeclared['writes'],
                $implementationDeclared['reads'],
                $implementationDeclared['writes']
            ) as $tableHelperClass) {
                if (!$this->collector->isValidDatabaseTableHelperClass($tableHelperClass)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        '%s::%s: table attribute target %s must be a Bristolian\\Database\\* helper class.',
                        $classReflection->getName(),
                        $methodName,
                        $tableHelperClass
                    ))->identifier('bristolian.tableAttributes.invalidTarget')->build();
                }
            }

            $used = $this->collector->collectUsedFromMethodNode(
                $classMethod,
                static fn (Name $name): string => $scope->resolveName($name),
                fn (string $helperClass): bool => $this->reflectionProvider->hasClass($helperClass)
            );

            $implementationMethodLabel = sprintf('%s::%s', $classReflection->getName(), $methodName);
            $interfaceMethodLabel = sprintf(
                '%s::%s',
                $interfaceMethodReflection->getDeclaringClass()->getName(),
                $methodName
            );

            // Usage must match attributes on the implementation method (emphasis at the call site).
            foreach ($this->collector->diff(
                $implementationDeclared['reads'],
                $implementationDeclared['writes'],
                $used['reads'],
                $used['writes'],
                $implementationMethodLabel
            ) as $message) {
                $errors[] = RuleErrorBuilder::message($message)
                    ->identifier('bristolian.tableAttributes.mismatch')
                    ->line($classMethod->getStartLine())
                    ->build();
            }

            // Implementation method attributes must repeat the interface method attributes.
            foreach ($this->collector->diffImplementationAttributesAgainstInterface(
                $interfaceDeclared['reads'],
                $interfaceDeclared['writes'],
                $implementationDeclared['reads'],
                $implementationDeclared['writes'],
                $interfaceMethodLabel,
                $implementationMethodLabel
            ) as $message) {
                $errors[] = RuleErrorBuilder::message($message)
                    ->identifier('bristolian.tableAttributes.missingOnImplementation')
                    ->line($classMethod->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function hasSkipAttribute(ClassReflection $classReflection): bool
    {
        foreach ($classReflection->getAttributes() as $attributeReflection) {
            if ($attributeReflection->getName() === SkipTableAttributes::class) {
                return true;
            }
        }

        return false;
    }

    private function findInterfaceMethodReflection(
        ClassReflection $classReflection,
        string $methodName
    ): \ReflectionMethod|null {
        foreach ($classReflection->getImmediateInterfaces() as $interfaceReflection) {
            if ($this->hasSkipAttribute($interfaceReflection)) {
                continue;
            }

            $nativeInterface = $interfaceReflection->getNativeReflection();
            if ($nativeInterface->hasMethod($methodName) === false) {
                continue;
            }

            return $nativeInterface->getMethod($methodName);
        }

        return null;
    }
}
