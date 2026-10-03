<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Questions;
use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use Bristolian\MarkdownRenderer\FakeMarkdownRenderer;
use Bristolian\MarkdownRenderer\MarkdownRenderer;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\AppController\Questions::class)]

class QuestionsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(MarkdownRenderer::class, CommonMarkRenderer::class);
    }

    public function test_index(): void
    {
        $result = $this->injector->execute([Questions::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Questions for WECA', $result);
    }

    public function test_weca_question_active_travel(): void
    {
        $this->injector->alias(MarkdownRenderer::class, FakeMarkdownRenderer::class);
        $this->injector->share(new FakeMarkdownRenderer());

        $result = $this->injector->execute([Questions::class, 'weca_question_active_travel']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Rendered content from 1_active_travel_weca.md', $result);
    }

    public function test_weca_question_tram(): void
    {
        $this->injector->alias(MarkdownRenderer::class, FakeMarkdownRenderer::class);
        $this->injector->share(new FakeMarkdownRenderer());

        $result = $this->injector->execute([Questions::class, 'weca_question_tram']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Rendered content from 2_cumberland_basin_weca_road_feasilbity.md', $result);
    }
}
