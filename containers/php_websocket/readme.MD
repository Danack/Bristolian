I R WEBSOCKETS SERVAL.

The container runs the chat server automatically with a file watcher. The watcher polls watched paths every 10 seconds and restarts the server when any file mtime changes (works reliably with Docker volume mounts, including on Mac).

Watched paths (restart on change):
- src/BristolianChat/
- chat/ (excluding vendor)
- src/functions.php, src/functions_chat.php, src/functions_common.php

Config files are not watched; config changes are handled by restarting the containers.

To run the server manually (e.g. for one-off debugging):
  cd /var/app/chat/src
  exec php index.php

(Dev/prod split for the watcher is to be done later; chat server is not currently running in production.)

Debugging the watcher:
- Container logs show "[chat-watch] Polling every 10s ..." at start with the list of paths, "[chat-watch] Starting server at ..." and "[chat-watch] Change detected, restarting at ..." when a change is seen. If restarts never appear after editing a watched file, ensure the file is under one of the watched paths and the container has been restarted so it runs the current entrypoint script.

