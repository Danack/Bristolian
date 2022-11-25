type Message = {
  type: string;
  params: object;
};

interface MessageCallback {
  (params: object): void;
}

// Whether message processing is active. Events are not processed
// until startMessageProcessing is called, so that events that are
// triggered while widgets are being created, aren't dispatched
// until all widgets are loaded.
let messageProcessingActive: boolean = false;

// Record of whether event processing has ever been active
// If someone sends an event and processing has never been
// active, we give them an info message of 'you probably
// forgot to start processing'.
let messageProcessingEverActive: boolean = false;

// If an event is triggered, but event processes has never been started
// we create a warning timeout, that will show a debug message after x seconds
// to avoid people staring at their computer, wondering when their events
// aren't being processed.
let notStartedWarningTimeout: any = null;

const notStartedWarningTime = 5;

// A queue of events that have been stored, rather than dispatched
// immediately. This is typically used when an app is starting up.
let messageQueue: Message[] = [];

interface MessageListener {
  [key: string]: MessageCallback;
}

interface MessageListenerByType {
  [key: string]: MessageListener;
}

// The current listeners to events.
const messsageListeners: MessageListenerByType = {};

// let myStr: string = myArray[0];

interface MessageListenerType {
  [key: number]: string;
}

const messsageListenersTypes: MessageListenerType = {};

let next_id: number = 0;

/**
 * Register a listener for a particular type of event.
 *
 * @param messageType which event to listen for.
 * @param fn The thing to call when the event is dispatched.
 */
export const registerMessageListener = (messageType: string, fn: MessageCallback): number => {
  const id = next_id;
  next_id += 1;

  // @ts-ignore: any ...
  if (messsageListeners[messageType] === undefined) {
    // @ts-ignore: any ...
    messsageListeners[messageType] = {};
  }
  // @ts-ignore: any ...
  messsageListeners[messageType][id] = fn;
  messsageListenersTypes[id] = messageType;

  return id;
};

/**
 *
 * @param id
 */
export const unregisterListener = (id: number) => {
  let type = messsageListenersTypes[id];

  if (type === undefined) {
    console.log('Failed to unregisterListener, unknown id ' + id + ' in messsageListenersTypes');
  }

  delete messsageListeners[type][id];

  // check undefined
  delete messsageListenersTypes[id];
};

// Allow messages to be processed, and process any backlog of messages.
export const startMessageProcessing = () => {
  if (notStartedWarningTimeout !== null) {
    clearTimeout(notStartedWarningTimeout);
    notStartedWarningTimeout = null;
  }

  messageProcessingActive = true;
  messageProcessingEverActive = true;

  while (messageQueue.length > 0) {
    const messageData = messageQueue.pop();

    if (messageData === undefined) {
      // A foreach loop! A foreach loop! My kingdom
      // for a foreach loop!
      continue;
      // aka this will never happen.
    }

    triggerMessageInternal(messageData.type, messageData.params);
  }
};

// Stop messages from being processed immediately.
export const stopMessageProcessing = () => {
  messageProcessingActive = false;
};

export const timeoutDebugInfo = () => {
  // TODO - change to just no-console
  // @ts-ignore: console warning is fine here.
  console.warn(
    'You sent a message but message processing has never been activated. Call Message.startMessageProcessing if you want events to be dispatched.',
  );
};

/**
 * Actually process the event.
 *
 * @param eventType
 * @param params
 */
function triggerMessageInternal(eventType: string, params: object) {
  // @ts-ignore: any ...
  if (messsageListeners[eventType] === undefined) {
    // console.error('unknown event type ' + event);
    return;
  }
  // @ts-ignore: any ...
  const callbacks = messsageListeners[eventType];

  const keys = Object.keys(callbacks);
  for (const i in keys) {
    if (keys.hasOwnProperty(i) !== true) {
      continue;
    }

    const keyName = keys[i];
    const fn = callbacks[keyName];
    fn(params);
  }
}

export const sendMessage = (eventType: string, params: object) => {
  // if event processing is active, process it.
  if (messageProcessingActive === true) {
    return triggerMessageInternal(eventType, params);
  }

  // If not, store the data for later processing.
  messageQueue.push({ type: eventType, params });

  // If processing has ever been active, assume they know
  // what they're doing
  if (messageProcessingEverActive === true) {
    return;
  }

  // Otherwise create a timeout to remind them to call 'startMessageProcessing'
  notStartedWarningTimeout = setTimeout(timeoutDebugInfo, notStartedWarningTime * 1000);
};

/**
 * Clear the queued events. This should only have an effect
 * when the processing is disabled, as that is the only time
 * there should be queued events.
 */
export const clearMessages = () => {
  messageQueue = [];
};

/**
 * Get the queued events. This queue should only have entries
 * when the processing is disabled.
 */
export const getQueuedMessages = () => {
  // TODO - return a copy, because JS.
  return messageQueue;
};
