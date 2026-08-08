import { h, Component } from 'preact';
import type {
  CachedToolConfig,
  CachedToolRunOverlay,
  CachedToolsProps,
  CachedToolStatus,
} from '../codeviewTypes';

interface CachedToolsPanelState {
  // Reserved for local UI state; props drive status.
}

function getDefaultState(_props: CachedToolsProps): CachedToolsPanelState {
  return {};
}

function shortStatus(
  props: CachedToolsProps,
  tool: CachedToolConfig,
  status: CachedToolStatus | undefined,
  run: CachedToolRunOverlay | undefined
): string {
  if (props.loading) {
    return 'Loading…';
  }
  if (run && run.running) {
    return 'Running…';
  }
  if (run && run.runExitCode !== null && run.runExitCode !== 0) {
    return `Last run failed (exit ${run.runExitCode})`;
  }
  if (props.dockerUnavailable) {
    return 'Docker not available, des ne';
  }
  if (!props.dockerReady && !props.dockerGateDismissed) {
    return `Container ${props.containerName} not running`;
  }
  if (!status || !status.present) {
    return 'No coverage cache';
  }
  if (status.stale) {
    const pct =
      typeof status.percent === 'number' && Number.isFinite(status.percent)
        ? ` · ${status.percent}%`
        : '';
    return `Stale${pct}`;
  }
  if (typeof status.percent === 'number' && Number.isFinite(status.percent)) {
    if (status.uncovered === 0 || status.percent >= 100) {
      return `${status.percent}% · no gaps`;
    }
    return `${status.percent}% · gaps remain`;
  }
  return 'Cache present';
}

function detailLines(
  props: CachedToolsProps,
  tool: CachedToolConfig,
  status: CachedToolStatus | undefined,
  run: CachedToolRunOverlay | undefined
): string[] {
  const lines: string[] = [
    `CodeView UI — ${tool.label}`,
    `id: ${tool.id}`,
    `command: ${tool.command}`,
    `tool_path: ${tool.tool_path}`,
  ];
  if (run && run.runError) {
    lines.push(`run error: ${run.runError}`);
  }
  if (status) {
    lines.push(
      `present=${status.present} stale=${status.stale} percent=${status.percent ?? 'n/a'}`
    );
    if (status.generatedAt) {
      lines.push(`generatedAt: ${status.generatedAt}`);
    }
    if (status.source) {
      lines.push(`source: ${status.source}`);
    }
    if (Array.isArray(status.topFiles) && status.topFiles.length > 0) {
      lines.push('topFiles:');
      for (const file of status.topFiles.slice(0, 10)) {
        lines.push(`  ${file.uncovered}/${file.statements}  ${file.path}`);
      }
    }
  }
  lines.push(
    `dockerReady=${props.dockerReady} dockerUnavailable=${props.dockerUnavailable} gateDismissed=${props.dockerGateDismissed}`
  );
  return lines;
}

export class CachedToolsPanel extends Component<CachedToolsProps, CachedToolsPanelState> {
  constructor(props: CachedToolsProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  private showHover(
    tool: CachedToolConfig,
    status: CachedToolStatus | undefined,
    run: CachedToolRunOverlay | undefined
  ) {
    this.props.onHoverDetail({
      title: shortStatus(this.props, tool, status, run),
      lines: detailLines(this.props, tool, status, run),
    });
  }

  render(props: CachedToolsProps) {
    if (props.loading && props.tools.length === 0) {
      return (
        <div class="cv-cached-tools">
          <p class="cv-cached-tools-marker">CodeView UI bundle</p>
          <p class="cv-cached-tool-status">Loading…</p>
        </div>
      );
    }

    const showProceed =
      !props.loading &&
      !props.dockerUnavailable &&
      !props.dockerReady &&
      !props.dockerGateDismissed;

    const allowMainAction =
      !props.loading &&
      (props.dockerReady || props.dockerGateDismissed || props.dockerUnavailable);

    return (
      <div class="cv-cached-tools">
        <p class="cv-cached-tools-marker">CodeView UI bundle</p>
        {props.tools.map((tool) => {
          const status = props.statusById[tool.id];
          const run = props.runById[tool.id];
          const statusText = shortStatus(props, tool, status, run);
          const running = run?.running === true;
          return (
            <div
              key={tool.id}
              class="cv-cached-tool-block"
              tabIndex={0}
              onMouseEnter={() => this.showHover(tool, status, run)}
              onFocus={() => this.showHover(tool, status, run)}
            >
              <p class="cv-cached-tool-status">{statusText}</p>
              <div class="cv-cached-tool-actions">
                {showProceed ? (
                  <button
                    type="button"
                    class="cv-cached-tool-proceed-btn"
                    onClick={() => props.onProceedAnyway()}
                  >
                    {`Container '${props.containerName}' not running, proceed anyway`}
                  </button>
                ) : null}
                {allowMainAction && !showProceed ? (
                  <button
                    type="button"
                    class="cv-cached-tool-run-btn"
                    title={tool.command}
                    disabled={running}
                    onClick={() => props.onRun(tool.id)}
                  >
                    {tool.label}
                  </button>
                ) : null}
              </div>
            </div>
          );
        })}
      </div>
    );
  }
}
