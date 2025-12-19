
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

// Cache name for user profile data
const USER_PROFILE_CACHE = 'user-profiles-v1';

// Fetch event handler with caching for user profile requests
self.addEventListener('fetch', (event) => {
    // Check if this is a user profile API request
    if (event.request.url.includes('/api/users/') && event.request.method === 'GET') {
        event.respondWith(handleUserProfileRequest(event.request));
    }
});

async function handleUserProfileRequest(request) {
    const cache = await caches.open(USER_PROFILE_CACHE);

    try {
        // Try to get from cache first
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            // Check if cache is still fresh (less than 5 minutes old)
            const cacheDate = new Date(cachedResponse.headers.get('date') || 0);
            const now = new Date();
            const cacheAge = now.getTime() - cacheDate.getTime();
            const fiveMinutes = 5 * 60 * 1000;

            if (cacheAge < fiveMinutes) {
                return cachedResponse;
            }
        }

        // Fetch from network
        const networkResponse = await fetch(request);

        // Cache successful responses
        if (networkResponse.ok) {
            const responseToCache = networkResponse.clone();
            // Add cache timestamp
            const headers = new Headers(responseToCache.headers);
            headers.set('date', new Date().toISOString());

            const responseWithDate = new Response(responseToCache.body, {
                status: responseToCache.status,
                statusText: responseToCache.statusText,
                headers: headers
            });

            await cache.put(request, responseWithDate);
        }

        return networkResponse;
    } catch (error) {
        // If network fails, try to serve from cache even if stale
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        throw error;
    }
}
self.addEventListener('notificationclick', handleNotificationClick);

console.log("service worker is running.");
