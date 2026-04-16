<?php

declare(strict_types=1);

namespace Bristolian\CliController;

use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\WhatDoTheyKnowRequestEventRepo\WhatDoTheyKnowRequestEventRepo;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\RoomMessageService\RoomMessageService;
use Bristolian\Service\WhatDoTheyKnowFeedFetcher\WhatDoTheyKnowFeedFetcher;
use Bristolian\WhatDoTheyKnow\RequestEvent;

class WhatDoTheyKnowFeedCliController
{
    private const FOI_ADVICE_ROOM_NAME = 'FOI advice';

    private const SECONDS_BETWEEN_POLL_RUNS = 300; // 5 minutes

    private const CONTINUAL_SLEEP_SECONDS = 20;

    private const CONTINUAL_MAX_RUN_SECONDS = 36000; // 10 hours

    public function syncRequestedFromBristolOnce(
        WhatDoTheyKnowFeedFetcher $feedFetcher,
        WhatDoTheyKnowRequestEventRepo $whatDoTheyKnowRequestEventRepo,
        RoomRepo $roomRepo,
        RoomMessageService $roomMessageService,
        CliOutput $cliOutput
    ): void {
        $cliOutput->write("Fetching WhatDoTheyKnow requested-from-Bristol feed.\n");

        $json = $feedFetcher->fetchRequestedFromBristolCityCouncilJson();
        $items = json_decode_safe($json);

        if (array_is_list($items) === false) {
            throw new \InvalidArgumentException('WhatDoTheyKnow feed must be a JSON array.');
        }

        $rooms = $roomRepo->getRoomByName(self::FOI_ADVICE_ROOM_NAME);
        if (count($rooms) === 0) {
            $cliOutput->write("Failed to find room named '" . self::FOI_ADVICE_ROOM_NAME . "'.\n");
        }
        $roomId = count($rooms) > 0 ? $rooms[0]->id : null;

        $newCount = 0;
        foreach ($items as $index => $item) {
            if (is_array($item) === false) {
                throw new \InvalidArgumentException(
                    sprintf('Feed item at index %s must be an object.', (string)$index)
                );
            }
            /** @var array<string, mixed> $item */
            $payloadJson = json_encode_safe($item);
            $event = parseWhatDoTheyKnowRequestEventFromArray($item);
            $occurredAtUtc = whatDoTheyKnowWdtEventOccurredAtUtc($event->created_at);

            $inserted = $whatDoTheyKnowRequestEventRepo->insertNewRequestEvent(
                wdtEventId: $event->id,
                wdtEventPayloadJson: $payloadJson,
                wdtInfoRequestId: $event->info_request->id,
                wdtInfoRequestUrlTitle: $event->info_request->url_title,
                wdtUserId: $event->user->id,
                wdtUserUrlName: $event->user->url_name,
                wdtUserDisplayName: $event->user->name,
                wdtPublicBodyId: $event->public_body->id,
                wdtEventOccurredAtUtc: $occurredAtUtc
            );

            if ($inserted === false) {
                continue;
            }

            $newCount += 1;

            if ($roomId === null) {
                continue;
            }

            $requestUrl = whatDoTheyKnowRequestUrlFromUrlTitle($event->info_request->url_title);
            $messageText = buildNewEventRoomMessageText($event, $requestUrl);
            $messageParams = ChatMessageParam::createFromArray([
                'text' => $messageText,
                'room_id' => $roomId,
            ]);
            $roomMessageService->sendRoomMessage($messageParams);
        }

        $cliOutput->write(sprintf("Processed feed: %d new event(s) stored.\n", $newCount));
    }

    public function syncRequestedFromBristolContinual(
        WhatDoTheyKnowFeedFetcher $feedFetcher,
        WhatDoTheyKnowRequestEventRepo $whatDoTheyKnowRequestEventRepo,
        RoomRepo $roomRepo,
        RoomMessageService $roomMessageService,
        CliOutput $cliOutput,
        ?int $secondsBetweenPollRuns = null,
        ?int $continualSleepSeconds = null,
        ?int $continualMaxRunSeconds = null
    ): void {
        $secondsBetweenPollRuns ??= self::SECONDS_BETWEEN_POLL_RUNS;
        $continualSleepSeconds ??= self::CONTINUAL_SLEEP_SECONDS;
        $continualMaxRunSeconds ??= self::CONTINUAL_MAX_RUN_SECONDS;

        $callable = function () use (
            $feedFetcher,
            $whatDoTheyKnowRequestEventRepo,
            $roomRepo,
            $roomMessageService,
            $cliOutput
        ): void {
            try {
                $this->syncRequestedFromBristolOnce(
                    $feedFetcher,
                    $whatDoTheyKnowRequestEventRepo,
                    $roomRepo,
                    $roomMessageService,
                    $cliOutput
                );
            } catch (\Throwable $throwable) {
                $cliOutput->write(
                    'WhatDoTheyKnow sync error: ' . $throwable->getMessage() . "\n"
                );
            }
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenPollRuns,
            $continualSleepSeconds,
            $continualMaxRunSeconds
        );
    }
}
