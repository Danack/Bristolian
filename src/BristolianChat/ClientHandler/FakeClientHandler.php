<?php

declare(strict_types=1);

namespace BristolianChat\ClientHandler;

/**
 * Fake implementation that records broadcastText calls instead of sending them.
 * Use getRecordedCalls() in tests to assert on what was sent.
 */
class FakeClientHandler implements ClientHandler
{
    /** @var array<int, array{data: string, excludedClientIds: array<int|string>}> */
    private array $recordedCalls = [];

    /**
     * @param array<int|string> $excludedClientIds
     */
    public function broadcastText(string $data, array $excludedClientIds = []): void
    {
        $this->recordedCalls[] = [
            'data' => $data,
            'excludedClientIds' => $excludedClientIds,
        ];
    }

    /**
     * @return array<int, array{data: string, excludedClientIds: array<int|string>}>
     */
    public function getRecordedCalls(): array
    {
        return $this->recordedCalls;
    }
}
