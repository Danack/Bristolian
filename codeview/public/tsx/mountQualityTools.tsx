import type { QualityToolsHandle, QualityToolsProps } from './codeviewTypes';
import { QualityToolsPanel } from './panels/QualityToolsPanel';
import { createMountHandle } from './createMountHandle';

export function mountQualityTools(
  root: HTMLElement,
  props: QualityToolsProps
): QualityToolsHandle {
  return createMountHandle(QualityToolsPanel, root, props);
}
