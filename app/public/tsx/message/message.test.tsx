var clearMessages = require('danack-message').clearMessages;
var getQueuedMessages = require('danack-message').getQueuedMessages;
var registerMessageListener = require('danack-message').registerMessageListener;
var startMessageProcessing = require('danack-message').startMessageProcessing;
var stopMessageProcessing = require('danack-message').stopMessageProcessing;
var sendMessage = require('danack-message').sendMessage;
var unregisterListener = require('danack-message').unregisterListener;

const message_foo = 'foo';
const message_unknown = 'unknown';

describe('widgety', function () {
  it('Message queue management', function () {
    stopMessageProcessing();
    clearMessages();
    expect(getQueuedMessages()).toHaveLength(0);
    sendMessage('foo', {});
    expect(getQueuedMessages()).toHaveLength(1);
    clearMessages();
    expect(getQueuedMessages()).toHaveLength(0);
  });

  it('dispatches okay', function () {
    clearMessages();
    startMessageProcessing();
    sendMessage(message_foo, {});

    var calledParams = [];
    const fn = (params) => {
      calledParams.push(params);
    };

    const values = { zok: true, fot: false, pik: 3 };

    // Check an unknown message doesn't reach our callback
    sendMessage(message_unknown, values);
    expect(calledParams).toHaveLength(0);

    // Check foo message does reach our callback.
    let id = registerMessageListener(message_foo, fn);
    sendMessage(message_foo, values);
    expect(calledParams).toHaveLength(1);
    expect(calledParams[0]).toEqual(values);

    unregisterListener(id);
  });

  it('stopping starting processing works', function () {
    clearMessages();

    const values = { zok: true, fot: false, pik: 3 };

    var calledParams = [];
    const fn = (params) => {
      calledParams.push(params);
    };

    clearMessages();
    stopMessageProcessing();

    // Check foo message does reach our callback.
    let id = registerMessageListener(message_foo, fn);

    // Check an message doesn't reach our callback when processing
    // isn't running
    sendMessage(message_foo, values);
    expect(calledParams).toHaveLength(0);

    // Start message processing
    startMessageProcessing();
    // check the message was received.
    expect(calledParams).toHaveLength(1);

    // send another message
    sendMessage(message_foo, values);
    // Check the callback was called
    expect(calledParams).toHaveLength(2);

    stopMessageProcessing();

    // send another message
    sendMessage(message_foo, values);
    // Check the callback was not called.
    expect(calledParams).toHaveLength(2);

    unregisterListener(id);
  });

  // TODO test debug timeout.
});
