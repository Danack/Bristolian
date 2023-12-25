
// This needs to be in root, because of scope
// https://stackoverflow.com/a/58845198/778719
function handlePushEvent(event) {
    console.log('handlePushEvent');
    // Retrieve the textual payload from event.data (a PushMessageData object).
    // Other formats are supported (ArrayBuffer, Blob, JSON), check out the documentation
    // on https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData.
    const payload = event.data ? event.data.text() : 'no payload';

    let data = null;
    let title = 'A notification!';
    let options = {};
    const options_to_copy = [
        'body',
        'data',
        'tag',
        'renotify',
        'vibrate',
        'sound'
    ];

    try {
        data = JSON.parse(payload);
        for (const option of options_to_copy) {
            if (Object.hasOwn(data, option)) {
                options[option] = data[option];
            }
        }

        if (Object.hasOwn(data, "title")) {
            title = data["title"];
        }
    }
    catch (error) {
        body = payload;
    }

    // Keep the service worker alive until the notification is created.
    event.waitUntil(
      self.registration.showNotification(title, options)
    );
}




function handleNotificationClick(event) {
    // fix http://crbug.com/463146 allegedly
    event.notification.close();

    let url = event.notification?.data?.url;
    if (url == null) {
      return;
    }

    const urlToOpen = new URL(url, self.location.origin).href;

    const promiseChain = clients.matchAll({type: 'window', includeUncontrolled: true}).
      then((windowClients) => {
        let matchingClient = null;

        for (let i = 0; i < windowClients.length; i++) {
          const windowClient = windowClients[i];
          if (windowClient.url === urlToOpen) {
            matchingClient = windowClient;
            break;
          }
        }

        if (matchingClient) {
          return matchingClient.focus();
        } else {
          return clients.openWindow(urlToOpen);
        }
      });

    event.waitUntil(promiseChain);
}



// Register event listener for the 'push' event.
self.addEventListener('push', handlePushEvent);

self.addEventListener('install', (event) => {
    console.log('Inside the install handler:', event);
});

self.addEventListener('activate', (event) => {
    console.log('Inside the activate handler:', event);
});
self.addEventListener(fetch, (event) => {
    console.log('Inside the fetch handler:', event);
});
self.addEventListener('notificationclick', handleNotificationClick);

console.log("service worker is running.");
