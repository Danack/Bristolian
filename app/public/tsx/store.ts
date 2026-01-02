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

// Non-hook version for use in class components
// Note: This will return the current value but won't trigger re-renders when it changes
// Class components using this should re-render via other means (e.g., state changes)
export function get_logged_in(): boolean {
  return _logged_in;
}

// Subscribe to login state changes (for class components)
// Returns an unsubscribe function
export function subscribe_logged_in(callback: (logged_in: boolean) => void): () => void {
  listeners.push(callback);
  return () => {
    listeners = listeners.filter(l => l !== callback);
  };
}

// User info store
interface UserInfo {
  user_id: string | null;
  avatar_image_id: string | null;
}

let _user_info: UserInfo = {
  user_id: null,
  avatar_image_id: null
};
let user_info_listeners: ((user_info: UserInfo) => void)[] = [];

export function set_user_info(value: UserInfo) {
  _user_info = value;
  user_info_listeners.forEach(cb => cb(_user_info));
}

export function use_user_info() {
  const [user_info, setUserInfo] = useState(_user_info);
  useEffect(() => {
    const listener = (value: UserInfo) => setUserInfo(value);
    user_info_listeners.push(listener);
    return () => {
      user_info_listeners = user_info_listeners.filter(l => l !== listener);
    };
  }, []);
  return user_info;
}
