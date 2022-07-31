<?php

declare(strict_types = 1);

namespace Bristolian\Data;

class ContentPolicyViolationReport
{

    /** The URI of the resource that was blocked from loading by the Content Security Policy. If the blocked URI is from a different origin than the document-uri, then the blocked URI is truncated to contain just the scheme, host, and port.
     * @var string
     */
    private string $blocked_uri;

    /**
     * The URI of the document in which the violation occurred.
     * @var string
     */
    private string $document_uri;

    /**
     * The referrer of the document in which the violation occurred.
     * @var string
     */
    private string $referrer;


    /**
     * The original policy as specified by the Content-Security-Policy HTTP header.
     * @var string
     */
    private string $original_policy;

    /**
     * The name of the policy section that was violated.
     * @var string
     */
    private string $violated_directive;


    // Optional parameters
    /** @var string */
    private string $disposition;

    /** @var string */
    private string $effective_directive;

    /** @var string */
    private string $line_number;

    /** @var string */
    private string $script_sample;

    /** @var string */
    private string $source_file;

    /** @var string */
    private string $status_code;

    public function __construct(
        string $document_uri,
        string $referrer,
        string $blocked_uri,
        string $violated_directive,
        string $original_policy,
        string $disposition,
        string $effective_directive,
        string $line_number,
        string $script_sample,
        string $source_file,
        string $status_code
    ) {
        $this->document_uri = $document_uri;
        $this->referrer = $referrer;
        $this->blocked_uri = $blocked_uri;
        $this->violated_directive = $violated_directive;
        $this->original_policy = $original_policy;

        $this->disposition = $disposition;
        $this->effective_directive = $effective_directive;
        $this->line_number = (string)$line_number;
        $this->script_sample = $script_sample;
        $this->source_file = $source_file;
        $this->status_code = $status_code;
    }

    /**
     * @param array<string, string|int> $report
     * @return self
     * @throws \Exception
     */
    public static function fromCSPPayload(array $report): self
    {
        if (array_key_exists("csp-report", $report) === false) {
            throw new \Exception("top element 'csp-report' not set, cannot decode.");
        }
        $data = $report['csp-report'];

        return new self(
            $data['document-uri'] ?? 'document-uri NOT SET',
            $data['referrer'] ?? 'referrer NOT SET',
            $data['blocked-uri'] ?? 'blocked-uri NOT SET',
            $data['violated-directive'] ?? 'violated-directive NOT SET',
            $data['original-policy'] ?? 'original-policy NOT SET',
            $data['disposition'] ?? 'disposition NOT SET',
            $data['effective-directive'] ?? 'effective_directive NOT SET',
            (string)($data['line-number'] ?? 'line_number NOT SET'),
            $data['script-sample'] ?? 'script_sample NOT SET',
            $data['source-file'] ?? 'source_file NOT SET',
            (string)($data['status-code'] ?? 'status_code NOT SET')
        );
    }

    /**
     * @return mixed
     */
    public function getDocumentUri()
    {
        return $this->document_uri;
    }

    /**
     * @return string
     */
    public function getReferrer(): string
    {
        return $this->referrer;
    }

    /**
     * @return string
     */
    public function getBlockedUri(): string
    {
        return $this->blocked_uri;
    }

    /**
     * @return string
     */
    public function getViolatedDirective(): string
    {
        return $this->violated_directive;
    }

    /**
     * @return string
     */
    public function getOriginalPolicy(): string
    {
        return $this->original_policy;
    }

    /**
     * @return string
     */
    public function getDisposition(): string
    {
        return $this->disposition;
    }

    /**
     * @return string
     */
    public function getEffectiveDirective(): string
    {
        return $this->effective_directive;
    }

    /**
     * @return string
     */
    public function getLineNumber(): string
    {
        return $this->line_number;
    }

    /**
     * @return string
     */
    public function getScriptSample(): string
    {
        return $this->script_sample;
    }

    /**
     * @return string
     */
    public function getSourceFile(): string
    {
        return $this->source_file;
    }

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->status_code;
    }

    /**
     * @param array<string, string> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['document-uri'],
            $data['referrer'],
            $data['blocked-uri'],
            $data['violated-directive'],
            $data['original-policy'],
            $data['disposition'] ?? 'disposition NOT SET',
            $data['effective-directive'] ?? 'effective_directive NOT SET',
            $data['line-number'] ?? 'line_number NOT SET',
            $data['script-sample'] ?? 'script_sample NOT SET',
            $data['source-file'] ?? 'source_file NOT SET',
            $data['status-code'] ?? 'status_code NOT SET'
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [
            'document-uri'        =>  $this->document_uri,
            'referrer'            =>  $this->referrer,
            'blocked-uri'         =>  $this->blocked_uri,
            'violated-directive'  =>  $this->violated_directive,
            'original-policy'     =>  $this->original_policy,

            'disposition'         => $this->disposition,
            'effective-directive' => $this->effective_directive,
            'line-number'         => $this->line_number,
            'script-sample'       => $this->script_sample,
            'source-file'         => $this->source_file,
            'status-code'         => $this->status_code,
        ];

        return $data;
    }
}
