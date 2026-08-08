import { h, Component } from 'preact';
import type { WorkflowPanelProps } from '../codeviewTypes';

interface WorkflowPanelState {}

function getDefaultState(_props: WorkflowPanelProps): WorkflowPanelState {
  return {};
}

/** Star icon matching host agent-button (currentColor fill). */
function AgentIcon() {
  return (
    <svg class="agent-button-icon" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
      <path
        fill="currentColor"
        d="M8 1.5l1.3 3.1 3.2.3-2.4 2.1.7 3.1L8 9.6l-2.8 1.6.7-3.1L3.5 5l3.2-.3L8 1.5z"
      />
    </svg>
  );
}

export class WorkflowPanelView extends Component<WorkflowPanelProps, WorkflowPanelState> {
  constructor(props: WorkflowPanelProps) {
    super(props);
    this.state = getDefaultState(props);
  }

  render(props: WorkflowPanelProps) {
    return (
      <div class="cv-workflow-panel">
        {props.showIdle ? (
          <div class="workflow-idle">
            {props.showStart ? (
              <button
                type="button"
                class="agent-button"
                disabled={props.startDisabled}
                title={props.startTitle}
                onClick={() => props.onStart()}
              >
                <AgentIcon />
                <span class="agent-button-label">{props.startLabel}</span>
              </button>
            ) : null}
            {props.runtimeError && !props.showActive ? (
              <p class="workflow-runtime-error">{props.runtimeError}</p>
            ) : null}
          </div>
        ) : null}

        {props.showActive ? (
          <div class="workflow-active">
            <div class="workflow-header">{props.headerText}</div>
            {props.showSteps && props.steps.length > 0 ? (
              <ol class="workflow-steps" aria-label="Workflow steps">
                {props.steps.map((step) => (
                  <li
                    key={step.id}
                    class={
                      step.phase === 'done'
                        ? 'is-done'
                        : step.phase === 'current'
                          ? 'is-current'
                          : ''
                    }
                  >
                    {step.label}
                  </li>
                ))}
              </ol>
            ) : null}
            <div class="workflow-body">
              {props.runtimeError ? (
                <p class="workflow-runtime-error">{props.runtimeError}</p>
              ) : null}
              {props.bodyText ? <p>{props.bodyText}</p> : null}
            </div>
            <div class="workflow-actions">
              {props.primaryLabel ? (
                <button
                  type="button"
                  class="agent-button"
                  onClick={() => props.onPrimary()}
                >
                  {props.primaryLabel}
                </button>
              ) : null}
              {props.showBack ? (
                <button
                  type="button"
                  class="workflow-secondary-btn"
                  onClick={() => props.onBack()}
                >
                  Go back to previous step
                </button>
              ) : null}
            </div>
          </div>
        ) : null}
      </div>
    );
  }
}
