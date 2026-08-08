import type { ModifiedFilesHandle, ModifiedFilesProps } from './codeviewTypes';
import { ModifiedFilesPanel } from './panels/ModifiedFilesPanel';
import { createMountHandle } from './createMountHandle';

export function mountModifiedFiles(
  root: HTMLElement,
  props: ModifiedFilesProps
): ModifiedFilesHandle {
  return createMountHandle(ModifiedFilesPanel, root, props);
}
