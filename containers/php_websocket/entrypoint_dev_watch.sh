#!/bin/sh
set -e

# Watch BristolianChat, chat (excluding vendor), and the three functions_*.php files.
# Poll file mtimes every POLL_INTERVAL seconds; restart the server when any watched file changes.
# (Polling is used because inotify often doesn't work over Docker volume mounts on Mac.)
WATCH_DIRS="/var/app/src/BristolianChat /var/app/chat/src"
WATCH_FILES="/var/app/chat/composer.json /var/app/src/functions.php /var/app/src/functions_chat.php /var/app/src/functions_common.php"
POLL_INTERVAL=10

echo "[chat-watch] Polling every ${POLL_INTERVAL}s (no inotify):"
for p in $WATCH_DIRS $WATCH_FILES; do echo "  $p"; done

# Output the latest mtime (epoch) among all watched files. Excludes chat/vendor by only scanning WATCH_DIRS + WATCH_FILES.
get_max_mtime() {
	m=$( { find $WATCH_DIRS -type f 2>/dev/null | xargs stat -c '%Y' 2>/dev/null; stat -c '%Y' $WATCH_FILES 2>/dev/null; } 2>/dev/null | sort -n | tail -1 )
	echo "${m:-0}"
}

SERVER_PID=""
trap 'if [ -n "$SERVER_PID" ]; then kill "$SERVER_PID" 2>/dev/null || true; fi; exit 0' TERM INT

last_mtime=$(get_max_mtime)
while true; do
	echo "[chat-watch] Starting server at $(date -Isec)"
	cd /var/app/chat/src && php index.php &
	SERVER_PID=$!

	# Poll until a watched file changes
	while true; do
		sleep "$POLL_INTERVAL"
		current_mtime=$(get_max_mtime)
		if [ -n "$current_mtime" ] && [ "$current_mtime" != "$last_mtime" ]; then
			echo "[chat-watch] Change detected, restarting at $(date -Isec)"
			kill "$SERVER_PID" 2>/dev/null || true
			wait "$SERVER_PID" 2>/dev/null || true
			last_mtime=$current_mtime
			break
		fi
		# Server may have exited (crash)
		if ! kill -0 "$SERVER_PID" 2>/dev/null; then
			wait "$SERVER_PID" 2>/dev/null || true
			echo "[chat-watch] Server exited, restarting at $(date -Isec)"
			last_mtime=$(get_max_mtime)
			break
		fi
	done
done
