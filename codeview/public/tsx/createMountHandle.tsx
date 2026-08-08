import { h, render, ComponentType } from 'preact';
import type { MountHandle } from './codeviewTypes';

/**
 * Generic mount/update/unmount helper for host-owned roots.
 */
export function createMountHandle<P extends object>(
  Component: ComponentType<P>,
  root: HTMLElement,
  props: P
): MountHandle<P> {
  let current = props;
  render(h(Component, current), root);

  return {
    update(next: P) {
      current = next;
      render(h(Component, current), root);
    },
    unmount() {
      render(null, root);
    },
  };
}
