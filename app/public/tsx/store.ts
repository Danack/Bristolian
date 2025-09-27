// store.ts
import { useState, useEffect } from "preact/hooks";

let _logged_in = false;
let listeners: ((logged_in: boolean) => void)[] = [];

export function set_logged_in(value: boolean) {
  _logged_in = value;
  listeners.forEach(cb => cb(_logged_in));
}

export function use_logged_in() {
  const [logged_in, setLoggedIn] = useState(_logged_in);
  useEffect(() => {
    const listener = (value: boolean) => setLoggedIn(value);
    listeners.push(listener);
    return () => {
      listeners = listeners.filter(l => l !== listener);
    };
  }, []);
  return logged_in;
}
