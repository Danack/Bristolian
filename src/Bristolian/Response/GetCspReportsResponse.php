<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class GetCspReportsResponse implements StubResponse
{
    private string $body;

    /**
     * @param array<int, mixed> $reports
     */
    public function __construct(int $count, array $reports)
    {
        $converted_reports = \convertToValueSafe($reports);

        $response_ok = [
            'result' => 'success',
            'data' => [
                'count' => $count,
                'reports' => $converted_reports,
            ],
        ];

        $this->body = json_encode_safe($response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
