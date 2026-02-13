#!/usr/bin/env bash

set_title() {
  printf '\033]0;%s\007' "$1"
}

if [ -n "${ITERM_TITLE:-}" ]; then
  set_title "$ITERM_TITLE"
else
  set_title "bristolian php"
fi
trap 'set_title "iTerm – idle"' EXIT