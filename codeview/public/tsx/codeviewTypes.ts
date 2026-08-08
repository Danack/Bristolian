/** Shared host hover-detail payload. */
export interface HoverDetail {
  title: string;
  lines: string[];
}

/* ---- Cached tools (existing) ---- */

/** One entry from codeview-data.json cached_tools[] (config). */
export interface CachedToolConfig {
  id: string;
  label: string;
  tool_path: string;
  command: string;
  container_name: string;
  globs: string[];
}

export interface CoverageMetric {
  covered: number | null;
  uncovered: number | null;
  total: number | null;
  percent: number | null;
}

export interface CachedToolGapFile {
  path: string;
  uncovered: number;
  statements: number;
}

/** Per-tool status from host (cachedToolBootstrap.tools[]). */
export interface CachedToolStatus {
  id: string;
  label: string;
  command: string;
  present: boolean;
  stale: boolean;
  percent: number | null;
  covered: number | null;
  uncovered: number | null;
  totalStatements: number | null;
  filesWithGaps: number | null;
  filesAnalysed: number | null;
  methods: CoverageMetric | null;
  classes: CoverageMetric | null;
  source: string | null;
  generatedAt: string | null;
  topFiles: CachedToolGapFile[];
}

/** Session / run overlay merged by the host adapter before update(). */
export interface CachedToolRunOverlay {
  running: boolean;
  runExitCode: number | null;
  runError: string | null;
}

export type CachedToolsHoverDetail = HoverDetail;

export interface CachedToolsProps {
  tools: CachedToolConfig[];
  loading: boolean;
  dockerReady: boolean;
  dockerUnavailable: boolean;
  dockerGateDismissed: boolean;
  containerName: string;
  statusById: Record<string, CachedToolStatus>;
  runById: Record<string, CachedToolRunOverlay>;
  onRun: (toolId: string) => void;
  onProceedAnyway: () => void;
  onHoverDetail: (detail: HoverDetail | null) => void;
}

export interface MountHandle<P> {
  update(next: P): void;
  unmount(): void;
}

export type CachedToolsHandle = MountHandle<CachedToolsProps>;

/* ---- Quality control ---- */

export type QualityToolStatus = 'unlit' | 'green' | 'red' | 'running';

export interface QualityToolRow {
  id: string;
  label: string;
  command: string;
  description?: string;
  /** Precomputed title/hover from host. */
  hoverText: string;
  status: QualityToolStatus;
  disabled: boolean;
}

export interface QualityToolsProps {
  tools: QualityToolRow[];
  onRun: (toolId: string) => void;
}

export type QualityToolsHandle = MountHandle<QualityToolsProps>;

/* ---- Cursor commands ---- */

export interface CursorCommandRow {
  id: string;
  label: string;
  file: string;
  hoverText: string;
}

export interface CursorCommandsProps {
  commands: CursorCommandRow[];
  onRun: (commandId: string) => void;
}

export type CursorCommandsHandle = MountHandle<CursorCommandsProps>;

/* ---- Workflow panel (presentation only) ---- */

export interface WorkflowStepRow {
  id: string;
  label: string;
  /** done | current | pending */
  phase: 'done' | 'current' | 'pending';
}

export interface WorkflowPanelProps {
  /** Idle start column */
  showIdle: boolean;
  showStart: boolean;
  startLabel: string;
  startTitle: string;
  startDisabled: boolean;

  /** Active step UI */
  showActive: boolean;
  headerText: string;
  showSteps: boolean;
  steps: WorkflowStepRow[];
  runtimeError: string | null;
  bodyText: string | null;
  primaryLabel: string | null;
  showBack: boolean;

  onStart: () => void;
  onPrimary: () => void;
  onBack: () => void;
}

export type WorkflowPanelHandle = MountHandle<WorkflowPanelProps>;

/* ---- Modified files chrome button ---- */

export interface ModifiedFilesProps {
  /** When false, renderer shows nothing (host may hide host wrapper). */
  visible: boolean;
  fileCount: number;
  /** Pin state for Details freeze (aria-pressed + is-active). */
  pinned: boolean;
  title: string;
  onHover: () => void;
  onFocus: () => void;
  onClick: () => void;
}

export type ModifiedFilesHandle = MountHandle<ModifiedFilesProps>;

export interface CodeViewUIApi {
  mountCachedTools(root: HTMLElement, props: CachedToolsProps): CachedToolsHandle;
  mountQualityTools(root: HTMLElement, props: QualityToolsProps): QualityToolsHandle;
  mountCursorCommands(root: HTMLElement, props: CursorCommandsProps): CursorCommandsHandle;
  mountWorkflowPanel(root: HTMLElement, props: WorkflowPanelProps): WorkflowPanelHandle;
  mountModifiedFiles(root: HTMLElement, props: ModifiedFilesProps): ModifiedFilesHandle;
}

declare global {
  interface Window {
    CodeViewUI: CodeViewUIApi;
  }
}

export {};
