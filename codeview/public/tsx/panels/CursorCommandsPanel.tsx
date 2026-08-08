import { h, Component } from 'preact';
import type { CursorCommandsProps } from '../codeviewTypes';

interface CursorCommandsPanelState {}

function getDefaultState(_props: CursorCommandsProps): CursorCommandsPanelState {
  return {};
}

export class CursorCommandsPanel extends Component<
  CursorCommandsProps,
  CursorCommandsPanelState
> {
  constructor(props: CursorCommandsProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  render(props: CursorCommandsProps) {
    return (
      <div class="cv-cursor-commands">
        {props.commands.map((cmd) => (
          <div key={cmd.id} class="chrome-action-row" data-command-id={cmd.id}>
            <button
              type="button"
              class="chrome-action-btn"
              title={cmd.hoverText}
              onClick={() => props.onRun(cmd.id)}
            >
              {cmd.label}
            </button>
          </div>
        ))}
      </div>
    );
  }
}
