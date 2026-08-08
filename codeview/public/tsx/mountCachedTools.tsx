import type { CachedToolsHandle, CachedToolsProps } from './codeviewTypes';
import { CachedToolsPanel } from './panels/CachedToolsPanel';
import { createMountHandle } from './createMountHandle';

export function mountCachedTools(
  root: HTMLElement,
  props: CachedToolsProps
): CachedToolsHandle {
  return createMountHandle(CachedToolsPanel, root, props);
}
