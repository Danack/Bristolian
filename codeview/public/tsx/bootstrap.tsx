import { mountCachedTools } from './mountCachedTools';
import { mountQualityTools } from './mountQualityTools';
import { mountCursorCommands } from './mountCursorCommands';
import { mountWorkflowPanel } from './mountWorkflowPanel';
import { mountModifiedFiles } from './mountModifiedFiles';
import type { CodeViewUIApi } from './codeviewTypes';

/**
 * CodeView webview entry. Assigns a global the extension loads via nonced <script src>.
 * Not the website bootstrap — no WidgetRegistry / initByClass / service worker.
 */
const CodeViewUI: CodeViewUIApi = {
  mountCachedTools,
  mountQualityTools,
  mountCursorCommands,
  mountWorkflowPanel,
  mountModifiedFiles,
};

// Side-effect: expose mount API for the extension host adapter.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
(window as any).CodeViewUI = CodeViewUI;
