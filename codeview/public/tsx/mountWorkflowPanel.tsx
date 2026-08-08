import type { WorkflowPanelHandle, WorkflowPanelProps } from './codeviewTypes';
import { WorkflowPanelView } from './panels/WorkflowPanel';
import { createMountHandle } from './createMountHandle';

export function mountWorkflowPanel(
  root: HTMLElement,
  props: WorkflowPanelProps
): WorkflowPanelHandle {
  return createMountHandle(WorkflowPanelView, root, props);
}
