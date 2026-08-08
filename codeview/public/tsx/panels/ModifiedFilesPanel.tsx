import { h, Component } from 'preact';
import type { ModifiedFilesProps } from '../codeviewTypes';

interface ModifiedFilesPanelState {}

function getDefaultState(_props: ModifiedFilesProps): ModifiedFilesPanelState {
  return {};
}

export class ModifiedFilesPanel extends Component<ModifiedFilesProps, ModifiedFilesPanelState> {
  constructor(props: ModifiedFilesProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  render(props: ModifiedFilesProps) {
    if (!props.visible) {
      return null;
    }

    const className = props.pinned
      ? 'workflow-secondary-btn workflow-dirty-btn is-active'
      : 'workflow-secondary-btn workflow-dirty-btn';

    return (
      <button
        type="button"
        class={className}
        aria-pressed={props.pinned ? 'true' : 'false'}
        title={props.title}
        onMouseEnter={() => props.onHover()}
        onFocus={() => props.onFocus()}
        onClick={() => props.onClick()}
      >
        Modified files
      </button>
    );
  }
}
