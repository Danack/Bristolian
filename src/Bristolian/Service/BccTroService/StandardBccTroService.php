<?php

namespace Bristolian\Service\BccTroService;

use Bristolian\Model\Types\BccTro;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Service\BccTroFetcher\BccTroFetcher;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\RoomMessageService\RoomMessageService;



class StandardBccTroService implements BccTroService
{
    public function __construct(
        private BccTroFetcher $bccTroFetcher,
        private BccTroRepo $bccTroRepo,
        private RoomRepo $roomRepo,
        private RoomMessageService $roomMessageService,
        private CliOutput $cliOutput
    ) {
    }

    public function do_the_needful(): void
    {
        $tros = $this->bccTroFetcher->fetchTros();
        $save_id = $this->bccTroRepo->saveData($tros);
        $this->cliOutput->write("save id is $save_id\n");

        if (count($tros) === 0) {
            $tro_info = "There were no TROs found";
        }
        else {
            $tro_info = "There were " . count($tros) . " TROs found. The saved data has ID " . $save_id;
            $tro_info .= output_tro_list_to_output($tros);
        }

        $this->cliOutput->write($tro_info);

        $this->cliOutput->write("TROs retrieved.\n");

        $transport_room_name = "Transport";
        $rooms = $this->roomRepo->getRoomByName($transport_room_name);

        if (count($rooms) === 0) {
            $this->cliOutput->write("Failed to find '$transport_room_name'.");
            return;
        }

        $this->cliOutput->write("room found.\n");

        $markdown_text = renderBccTrosAsMarkdown($tros);

        $params = [
//            'text' => "BCC TRO has been updated: " . $tro_info,
            'text' => $markdown_text,
            'room_id' => ($rooms[0])->id,
        ];

        $messageParams = ChatMessageParam::createFromArray($params);
        $chat_message = $this->roomMessageService->sendRoomMessage($messageParams);

        $this->cliOutput->write("message sent to room.\n");

        $this->cliOutput->write("$markdown_text\n");

        $this->cliOutput->write("wat\n");
    }
}