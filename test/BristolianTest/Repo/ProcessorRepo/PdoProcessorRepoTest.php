<?php

namespace BristolianTest\Repo\ProcessorRepo;

use Bristolian\Model\Types\ProcessorState;
use Bristolian\Repo\ProcessorRepo\PdoProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use BristolianTest\BaseTestCase;

/**
 * Tests for PdoProcessorRepo
 *
 * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
 */
class PdoProcessorRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_constructor(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);
        $this->assertInstanceOf(PdoProcessorRepo::class, $repo);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_setProcessorEnabled_creates_new_entry(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Set a processor to enabled
        $repo->setProcessorEnabled(ProcessType::email_send, true);

        // Verify it's enabled
        $enabled = $repo->getProcessorEnabled(ProcessType::email_send);
        $this->assertTrue($enabled);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_setProcessorEnabled_updates_existing_entry(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Set a processor to enabled
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::daily_system_info));

        // Update it to disabled
        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::daily_system_info));

        // Update it back to enabled
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::daily_system_info));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_getProcessorEnabled_with_disabled_processor(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Explicitly set a processor to disabled
        $repo->setProcessorEnabled(ProcessType::moon_alert, false);
        
        // Verify it returns false
        $enabled = $repo->getProcessorEnabled(ProcessType::moon_alert);
        $this->assertFalse($enabled);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_setProcessorEnabled_with_true(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        $repo->setProcessorEnabled(ProcessType::email_send, true);

        $enabled = $repo->getProcessorEnabled(ProcessType::email_send);
        $this->assertTrue($enabled);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_setProcessorEnabled_with_false(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);

        $enabled = $repo->getProcessorEnabled(ProcessType::daily_system_info);
        $this->assertFalse($enabled);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_getProcessorsStates_returns_array(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        $states = $repo->getProcessorsStates();

        $this->assertIsArray($states);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_getProcessorsStates_contains_processor_states(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Create some processor states
        $repo->setProcessorEnabled(ProcessType::email_send, true);
        $repo->setProcessorEnabled(ProcessType::moon_alert, false);

        $states = $repo->getProcessorsStates();

        // Verify they're in the results
        if (isset($states[ProcessType::email_send->value])) {
            $emailState = $states[ProcessType::email_send->value];
            $this->assertInstanceOf(ProcessorState::class, $emailState);
            $this->assertSame(ProcessType::email_send->value, $emailState->type);
            $this->assertTrue($emailState->enabled);
            $this->assertInstanceOf(\DateTimeInterface::class, $emailState->updated_at);
        }

        if (isset($states[ProcessType::moon_alert->value])) {
            $moonState = $states[ProcessType::moon_alert->value];
            $this->assertInstanceOf(ProcessorState::class, $moonState);
            $this->assertSame(ProcessType::moon_alert->value, $moonState->type);
            $this->assertFalse($moonState->enabled);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_getProcessorsStates_keyed_by_type(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Create processor states for all types
        $repo->setProcessorEnabled(ProcessType::email_send, true);
        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);
        $repo->setProcessorEnabled(ProcessType::moon_alert, true);

        $states = $repo->getProcessorsStates();

        // Verify the array is keyed by the processor type value
        $this->assertArrayHasKey(ProcessType::email_send->value, $states);
        $this->assertArrayHasKey(ProcessType::daily_system_info->value, $states);
        $this->assertArrayHasKey(ProcessType::moon_alert->value, $states);

        // Verify the keys match the type property
        foreach ($states as $key => $state) {
            $this->assertSame($key, $state->type);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_multiple_processors_independently(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Set different states for different processors
        $repo->setProcessorEnabled(ProcessType::email_send, true);
        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);
        $repo->setProcessorEnabled(ProcessType::moon_alert, true);

        // Verify each processor has the correct state
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::email_send));
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::daily_system_info));
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::moon_alert));

        // Change one processor
        $repo->setProcessorEnabled(ProcessType::email_send, false);

        // Verify only that processor changed
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::email_send));
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::daily_system_info));
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::moon_alert));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_processor_state_properties(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        $repo->setProcessorEnabled(ProcessType::email_send, true);

        $states = $repo->getProcessorsStates();

        if (isset($states[ProcessType::email_send->value])) {
            $state = $states[ProcessType::email_send->value];

            // Verify all properties exist and have correct types
            $this->assertIsString($state->id);
            $this->assertNotEmpty($state->id);
            $this->assertIsBool($state->enabled);
            $this->assertIsString($state->type);
            $this->assertSame(ProcessType::email_send->value, $state->type);
            $this->assertInstanceOf(\DateTimeInterface::class, $state->updated_at);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_all_process_types(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Test each enum value
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::daily_system_info));

        $repo->setProcessorEnabled(ProcessType::email_send, false);
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::email_send));

        $repo->setProcessorEnabled(ProcessType::moon_alert, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::moon_alert));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_toggle_processor_multiple_times(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Toggle the same processor multiple times
        $repo->setProcessorEnabled(ProcessType::email_send, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::email_send));

        $repo->setProcessorEnabled(ProcessType::email_send, false);
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::email_send));

        $repo->setProcessorEnabled(ProcessType::email_send, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::email_send));

        $repo->setProcessorEnabled(ProcessType::email_send, false);
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::email_send));
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_getProcessorsStates_empty_initially(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        $states = $repo->getProcessorsStates();

        // Initially might be empty or have default values
        $this->assertIsArray($states);
        
        // All values should be ProcessorState instances if present
        foreach ($states as $state) {
            $this->assertInstanceOf(ProcessorState::class, $state);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRepo\PdoProcessorRepo
     */
    public function test_setting_same_value_twice(): void
    {
        $repo = $this->injector->make(PdoProcessorRepo::class);

        // Set to true twice
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $repo->setProcessorEnabled(ProcessType::daily_system_info, true);
        $this->assertTrue($repo->getProcessorEnabled(ProcessType::daily_system_info));

        // Set to false twice
        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);
        $repo->setProcessorEnabled(ProcessType::daily_system_info, false);
        $this->assertFalse($repo->getProcessorEnabled(ProcessType::daily_system_info));
    }
}
