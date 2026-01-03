# Background Tasks Infrastructure

This document describes the infrastructure code for creating and managing background jobs/tasks (also called "processors") in the Bristolian application.

## Overview

Background tasks are long-running processes that execute periodically to perform maintenance, processing, or other automated work. Examples include:
- Processing email queues
- Running OCR on images
- Generating daily reports
- Checking for moon alerts

All background tasks follow a consistent infrastructure pattern that provides:
- Signal handling for graceful shutdown
- Run recording and tracking
- Enable/disable control
- Continuous execution with configurable timing

## Key Components

### ProcessType Enum

**Location**: `src/Bristolian/Repo/ProcessorRepo/ProcessType.php`

An enum that defines all available background task types. Each task type must have a unique value.

```php
enum ProcessType: string
{
    case email_send = "email_send";
    case meme_ocr = "meme_ocr";
    // ... other types
}
```

**When adding a new task type:**
1. Add a new case to the `ProcessType` enum
2. Add the processor to the admin processor list (see `src/Bristolian/AppController/Admin.php`)

### ProcessorRunRecordRepo

**Location**: `src/Bristolian/Repo/ProcessorRunRecordRepo/ProcessorRunRecordRepo.php`

Interface for recording processor runs. Tracks when processors start and finish, storing run history in the database.

Key methods:
- `startRun(ProcessType $process_type): string` - Records the start of a run, returns run ID
- `setRunFinished(string $id, string $debug_info): void` - Records completion with optional debug information
- `getLastRunDateTime(ProcessType $process_type): ?DateTimeInterface` - Gets the last run time for a processor
- `getRunRecords(ProcessType|null $processType): array` - Retrieves run history

### ProcessorRepo

**Location**: `src/Bristolian/Repo/ProcessorRepo/ProcessorRepo.php`

Manages processor enable/disable state. Allows administrators to control which processors are active.

Key methods:
- `getProcessorEnabled(ProcessType $processor): bool` - Check if a processor is enabled
- `setProcessorEnabled(ProcessType $processor, bool $enabled): void` - Enable or disable a processor
- `getProcessorsStates(): array` - Get the state of all processors

Processors can be enabled/disabled through the admin interface at `/admin/control_processors`.

### continuallyExecuteCallable Function

**Location**: `src/functions.php`

The core function that handles continuous execution with signal checking.

```php
continuallyExecuteCallable(
    callable $callable,
    int $secondsBetweenRuns,
    int $sleepTime,
    int $maxRunTime
): void
```

Parameters:
- `$callable` - The function/method to execute repeatedly
- `$secondsBetweenRuns` - Minimum time between executions (0 = run every loop)
- `$sleepTime` - Sleep duration between loop iterations (in seconds)
- `$maxRunTime` - Maximum total runtime before exiting (in seconds)

Features:
- Checks for system signals (SIGINT, SIGTERM, etc.) for graceful shutdown
- Respects timing constraints (minimum time between runs, maximum runtime)
- Handles sleep between iterations

## Creating a Background Task

### Step 1: Create the Controller Class

Create a class in `src/Bristolian/CliController/` that implements your background task logic.

### Step 2: Add Required Dependencies

Inject the required repositories:

```php
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;

class YourTask
{
    public function __construct(
        private ProcessorRunRecordRepo $processorRunRecordRepo,
        private ProcessorRepo $processorRepo,
        // ... other dependencies
    ) {
    }
}
```

### Step 3: Implement the process() Method

The `process()` method is the entry point that wraps your logic with `continuallyExecuteCallable`:

```php
public function process(): void
{
    $callable = function () {
        $this->runInternal();
    };

    continuallyExecuteCallable(
        $callable,
        $secondsBetweenRuns = 5,  // Minimum 5 seconds between runs
        $sleepTime = 1,            // Sleep 1 second between loop iterations
        $maxRunTime = 600          // Run for max 10 minutes
    );
}
```

### Step 4: Implement the runInternal() Method

The `runInternal()` method contains your actual task logic with infrastructure hooks:

```php
public function runInternal(): void
{
    // Optional: Check if processor is enabled
    if ($this->processorRepo->getProcessorEnabled(ProcessType::your_task) !== true) {
        echo "ProcessType::your_task is not enabled.\n";
        return;
    }

    // Record the start of the run
    $run_id = $this->processorRunRecordRepo->startRun(ProcessType::your_task);

    // Your task logic here
    try {
        // Do work...
        $debug_info = "Task completed successfully";
    }
    catch (\Exception $exception) {
        echo "Task failed: " . $exception->getMessage() . "\n";
        $debug_info = "Task failed: " . $exception->getMessage();
        goto finish;
    }

finish:
    // Always record completion, even on error
    $this->processorRunRecordRepo->setRunFinished($run_id, $debug_info);
}
```

### Step 5: Add ProcessType Enum Value

Add your task type to the `ProcessType` enum:

```php
enum ProcessType: string
{
    // ... existing cases
    case your_task = "your_task";
}
```

### Step 6: Add CLI Command

Register your command in `cli/cli_commands.php`:

```php
$command = new Command(
    'process:your_task',
    'Bristolian\CliController\YourTask::process'
);
$command->setDescription("Process your background task");
$console->add($command);
```

### Step 7: Add to Admin Interface (Optional)

If you want administrators to be able to enable/disable your processor, add it to the processor list in `src/Bristolian/AppController/Admin.php`:

```php
protected static $processors = [
    // ... existing processors
    ProcessType::your_task->value => "Your Task Description",
];
```

## Best Practices

### Error Handling

Always wrap your task logic in try-catch blocks and use `goto finish` to ensure `setRunFinished()` is always called:

```php
public function runInternal(): void
{
    $run_id = $this->processorRunRecordRepo->startRun(ProcessType::your_task);
    
    try {
        // Your logic
        $debug_info = "Success";
    }
    catch (\Exception $exception) {
        echo "Error: " . $exception->getMessage() . "\n";
        $debug_info = "Error: " . $exception->getMessage();
        goto finish;
    }

finish:
    $this->processorRunRecordRepo->setRunFinished($run_id, $debug_info);
}
```

### Processor Enable/Disable Check

The enable/disable check is optional but recommended. It should occur **before** `startRun()` to avoid recording runs when the processor is disabled:

```php
if ($this->processorRepo->getProcessorEnabled(ProcessType::your_task) !== true) {
    echo "ProcessType::your_task is not enabled.\n";
    return;  // Return early, don't record a run
}
```

### Debug Information

Always provide meaningful `$debug_info` when calling `setRunFinished()`. This information is stored in the database and can be useful for debugging:

- Success: Describe what was accomplished
- Failure: Include error messages and context
- No work: Explain why no work was done (e.g., "No items to process")

### Timing Configuration

Choose appropriate timing parameters for your task:

- **`$secondsBetweenRuns`**: How frequently the task should run
  - Fast tasks (email queue): 5 seconds
  - Medium tasks (OCR): 5-10 seconds
  - Slow tasks (daily reports): 30+ seconds

- **`$sleepTime`**: How long to sleep between loop iterations
  - Usually 1 second is sufficient
  - Longer sleep times reduce CPU usage but increase latency

- **`$maxRunTime`**: Maximum runtime before the process exits
  - Short tasks: 600 seconds (10 minutes)
  - Long-running tasks: 3600+ seconds (1+ hour)

## Running Background Tasks

Background tasks are typically run via supervisord or similar process managers. They can also be run manually via CLI:

```bash
docker exec bristolian-php_fpm-1 bash -c "php cli.php process:your_task"
```

## Monitoring

Run records are stored in the `processor_run_record` database table and can be viewed through the admin interface. Each record includes:
- Start time
- End time
- Status (initial, finished)
- Debug information

## Examples

See the following files for complete examples:
- `src/Bristolian/CliController/Email.php` - Email queue processor
- `src/Bristolian/CliController/MemeOcr.php` - OCR processor with full infrastructure
- `src/Bristolian/CliController/SystemInfo.php` - Daily system info processor
- `src/Bristolian/CliController/MoonInfo.php` - Moon alert processor

