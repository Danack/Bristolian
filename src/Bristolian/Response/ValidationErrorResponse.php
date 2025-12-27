<?php

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class ValidationErrorResponse implements StubResponse
{
    private string $body;

    /**
     * @param \DataType\ValidationProblem[] $validation_problems
     * @return ValidationErrorResponse
     */
    public static function fromProblems(array $validation_problems): self {

        $response = [
            'success' => false,
            'errors' => []
        ];

        foreach ($validation_problems as $validation_problem) {
            $response['errors'][] = $validation_problem->toString();
        }

        $instance = new self();

        $instance->body = json_encode_safe($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $instance;
    }

    public function getStatus(): int
    {
        return 400;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
