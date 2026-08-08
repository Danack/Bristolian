import { h, Component } from 'preact';
import type { QualityToolsProps, QualityToolStatus } from '../codeviewTypes';

interface QualityToolsPanelState {}

function getDefaultState(_props: QualityToolsProps): QualityToolsPanelState {
  return {};
}

function lightTitle(status: QualityToolStatus): string {
  if (status === 'green') {
    return 'Last run passed';
  }
  if (status === 'red') {
    return 'Last run failed';
  }
  if (status === 'running') {
    return 'Running…';
  }
  return 'Not run yet';
}

export class QualityToolsPanel extends Component<QualityToolsProps, QualityToolsPanelState> {
  constructor(props: QualityToolsProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  render(props: QualityToolsProps) {
    return (
      <div class="cv-quality-tools">
        {props.tools.map((tool) => (
          <div key={tool.id} class="qc-button-row" data-tool-id={tool.id}>
            <span
              class={`qc-status-light is-${tool.status}`}
              aria-hidden="true"
              title={lightTitle(tool.status)}
            />
            <button
              type="button"
              class="qc-tool-btn"
              title={tool.hoverText}
              disabled={tool.disabled}
              onClick={() => props.onRun(tool.id)}
            >
              {tool.label}
            </button>
          </div>
        ))}
      </div>
    );
  }
}
