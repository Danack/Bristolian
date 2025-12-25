#!/usr/bin/env bash

set_title() {
  printf '\033]0;%s\007' "$1"
}

set_title "bristolian php"
trap 'set_title "iTerm – idle"' EXIT