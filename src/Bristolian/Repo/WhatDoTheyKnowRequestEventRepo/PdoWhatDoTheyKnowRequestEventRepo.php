<?php

declare(strict_types=1);

namespace Bristolian\Repo\WhatDoTheyKnowRequestEventRepo;

use Bristolian\Database\whatdotheyknow_request_event;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;

class PdoWhatDoTheyKnowRequestEventRepo implements WhatDoTheyKnowRequestEventRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }

    public function insertNewRequestEvent(
        int $wdtEventId,
        string $wdtEventPayloadJson,
        int $wdtInfoRequestId,
        string $wdtInfoRequestUrlTitle,
        int $wdtUserId,
        string $wdtUserUrlName,
        string $wdtUserDisplayName,
        int $wdtPublicBodyId,
        \DateTimeImmutable $wdtEventOccurredAtUtc
    ): bool {
        $params = [
            ':wdt_event_id' => $wdtEventId,
            ':wdt_info_request_id' => $wdtInfoRequestId,
            ':wdt_public_body_id' => $wdtPublicBodyId,
            ':wdt_user_id' => $wdtUserId,
            ':wdt_event_occurred_at' => $wdtEventOccurredAtUtc,
            ':wdt_event_payload' => $wdtEventPayloadJson,
            ':wdt_info_request_url_title' => $wdtInfoRequestUrlTitle,
            ':wdt_user_display_name' => $wdtUserDisplayName,
            ':wdt_user_url_name' => $wdtUserUrlName,
        ];

        try {
            $this->pdoSimple->insert(whatdotheyknow_request_event::INSERT, $params);
        } catch (PdoSimpleWithPreviousException $exception) {
            $pdoException = $exception->getPreviousPdoException();
            if ($pdoException->errorInfo[0] === '23000') {
                return false;
            }
            throw $exception;
        }

        return true;
    }
}
