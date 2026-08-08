import type { CursorCommandsHandle, CursorCommandsProps } from './codeviewTypes';
import { CursorCommandsPanel } from './panels/CursorCommandsPanel';
import { createMountHandle } from './createMountHandle';

export function mountCursorCommands(
  root: HTMLElement,
  props: CursorCommandsProps
): CursorCommandsHandle {
  return createMountHandle(CursorCommandsPanel, root, props);
}
