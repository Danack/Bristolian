<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Pages;
use Bristolian\MarkdownRenderer\MarkdownRenderer;
use Bristolian\MarkdownRenderer\CommonMarkRenderer;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class PagesTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(MarkdownRenderer::class, CommonMarkRenderer::class);
    }

    /**
     * @covers \Bristolian\AppController\Pages::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Pages::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Absolute alpha', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::bcc_committee_meetings
     */
    public function test_bcc_committee_meetings(): void
    {
        $result = $this->injector->execute([Pages::class, 'bcc_committee_meetings']);
        $this->assertIsString($result);
        $this->assertStringContainsString('BCC committee meetings', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::floating_point_page
     */
    public function test_floating_point_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'floating_point_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('floating_point_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::floating_point_page_8
     */
    public function test_floating_point_page_8(): void
    {
        $result = $this->injector->execute([Pages::class, 'floating_point_page_8']);
        $this->assertIsString($result);
        $this->assertStringContainsString('floating_point_8_bit_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::timeline_page
     */
    public function test_timeline_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'timeline_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('time_line_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::teleprompter_page
     */
    public function test_teleprompter_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'teleprompter_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('teleprompter_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::email_link_generator_page
     */
    public function test_email_link_generator_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'email_link_generator_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('email_link_generator_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::qr_code_generator_page
     */
    public function test_qr_code_generator_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'qr_code_generator_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('qr_code_generator_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::notes_page
     */
    public function test_notes_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'notes_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('notes_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::twitter_splitter_page
     */
    public function test_twitter_splitter_page(): void
    {
        $result = $this->injector->execute([Pages::class, 'twitter_splitter_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('twitter_splitter_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::homepage
     */
    public function test_homepage(): void
    {
        $result = $this->injector->execute([Pages::class, 'homepage']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::about
     */
    public function test_about(): void
    {
        $result = $this->injector->execute([Pages::class, 'about']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::advice_for_speaking_at_council
     */
    public function test_advice_for_speaking_at_council(): void
    {
        $result = $this->injector->execute([Pages::class, 'advice_for_speaking_at_council']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::shenanigans_planning
     */
    public function test_shenanigans_planning(): void
    {
        $result = $this->injector->execute([Pages::class, 'shenanigans_planning']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::monitoring_officer_notes
     */
    public function test_monitoring_officer_notes(): void
    {
        $result = $this->injector->execute([Pages::class, 'monitoring_officer_notes']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::development_committee_rules
     */
    public function test_development_committee_rules(): void
    {
        $result = $this->injector->execute([Pages::class, 'development_committee_rules']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::questions
     */
    public function test_questions(): void
    {
        $result = $this->injector->execute([Pages::class, 'questions']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Questions for WECA', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::experimental
     */
    public function test_experimental(): void
    {
        $result = $this->injector->execute([Pages::class, 'experimental']);
        $this->assertIsString($result);
        $this->assertStringContainsString('notification_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Pages::experimental_debug_param
     */
    public function test_experimental_debug_param(): void
    {
        $params = \Bristolian\Parameters\DebugParams::createFromVarMap(
            new \VarMap\ArrayVarMap([
                'message' => 'test message',
                'detail' => 'some detail',
            ])
        );
        $this->injector->share($params);

        $result = $this->injector->execute([Pages::class, 'experimental_debug_param']);
        $this->assertIsString($result);
        $this->assertStringContainsString('test message', $result);
    }
}
