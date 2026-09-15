<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Explanations;
use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use Bristolian\MarkdownRenderer\FakeMarkdownRenderer;
use Bristolian\MarkdownRenderer\MarkdownRenderer;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class ExplanationsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(MarkdownRenderer::class, CommonMarkRenderer::class);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::triangle_road
     */
    public function test_triangle_road(): void
    {
        $this->injector->alias(MarkdownRenderer::class, FakeMarkdownRenderer::class);
        $this->injector->share(new FakeMarkdownRenderer());

        $result = $this->injector->execute([Explanations::class, 'triangle_road']);
        $this->assertIsString($result);
        $this->assertStringContainsString('<hr/>', $result);
        $this->assertStringContainsString('Rendered content from triangle_road.md', $result);
        $this->assertMatchesRegularExpression('/share|qr|QR/', $result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::bristol_rovers
     */
    public function test_bristol_rovers(): void
    {
        $this->injector->alias(MarkdownRenderer::class, FakeMarkdownRenderer::class);
        $this->injector->share(new FakeMarkdownRenderer());

        $result = $this->injector->execute([Explanations::class, 'bristol_rovers']);
        $this->assertIsString($result);
        $this->assertStringContainsString('<hr/>', $result);
        $this->assertStringContainsString('Rendered content from bristol_rovers.md', $result);
        $this->assertMatchesRegularExpression('/share|qr|QR/', $result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::avon_crescent
     */
    public function test_avon_crescent(): void
    {
        $this->injector->alias(MarkdownRenderer::class, FakeMarkdownRenderer::class);
        $this->injector->share(new FakeMarkdownRenderer());

        $result = $this->injector->execute([Explanations::class, 'avon_crescent']);
        $this->assertIsString($result);
        $this->assertStringContainsString('<hr/>', $result);
        $this->assertStringContainsString('Rendered content from avon_crescent_spike_island.md', $result);
        $this->assertMatchesRegularExpression('/share|qr|QR/', $result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::advice_for_speaking_at_council
     */
    public function test_advice_for_speaking_at_council(): void
    {
        $result = $this->injector->execute([Explanations::class, 'advice_for_speaking_at_council']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::shenanigans_planning
     */
    public function test_shenanigans_planning(): void
    {
        $result = $this->injector->execute([Explanations::class, 'shenanigans_planning']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::monitoring_officer_notes
     */
    public function test_monitoring_officer_notes(): void
    {
        $result = $this->injector->execute([Explanations::class, 'monitoring_officer_notes']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Explanations::development_committee_rules
     */
    public function test_development_committee_rules(): void
    {
        $result = $this->injector->execute([Explanations::class, 'development_committee_rules']);
        $this->assertIsString($result);
    }
}
