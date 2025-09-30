export enum EventType {
    // Send a sound to be played to the server
    send_sound = "send_sound",

    // Receive a sound from the server
    received_sound = "received_sound",

    // Button was pressed,  trigger sound
    trigger_sound = "trigger_sound",
}

type EventData = {
    eventType: EventType, params: Object
}
