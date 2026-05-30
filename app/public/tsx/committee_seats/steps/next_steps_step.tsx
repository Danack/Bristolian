import {h} from "preact";
import {NEXT_STEPS_COPY, getNextStepsAllocationRows} from "../next_steps";
import {
    SEND_COUNCIL_DATA_COPY,
    formatExampleCouncilSubmissionJson,
    shouldOfferSendCouncilData,
} from "../submit_example_council";
import type {ExampleCouncilJsonCopyStatus} from "../submit_example_council";
import type {CommitteeSeatsPanelState} from "../panel_state";
import type {PartyAllocationResult} from "../types";

export interface NextStepsStepProps {
    state: CommitteeSeatsPanelState;
    allocationResult: PartyAllocationResult;
    onProposedExampleCouncilNameChange: (councilDisplayName: string) => void;
    onCopyExampleCouncilSubmissionJson: (json: string) => void;
    onRegisterExampleCouncilJsonTextarea: (element: HTMLTextAreaElement | null) => void;
    onBackFromNextSteps: () => void;
    onStartOver: () => void;
}

export function NextStepsStep(props: NextStepsStepProps) {
    const allocationResult = props.allocationResult;
    const allocationRows = getNextStepsAllocationRows(allocationResult.rows);
    const sendCouncilDataPanelSnapshot = {
        data_source_mode: props.state.data_source_mode,
        selected_example_council_id: props.state.selected_example_council_id,
        political_groups: props.state.political_groups,
        committees: props.state.committees,
        total_committee_seats: props.state.total_committee_seats,
    };
    const showSendCouncilDataSection = shouldOfferSendCouncilData(sendCouncilDataPanelSnapshot);
    const exampleCouncilSubmissionJson = formatExampleCouncilSubmissionJson(
        props.state.proposed_example_council_name,
        sendCouncilDataPanelSnapshot
    );
    const copyStatus: ExampleCouncilJsonCopyStatus = props.state.example_council_json_copy_status;

    return (
        <div className="committee_seats_step committee_seats_next_steps_screen">
            <h2 className="committee_seats_next_steps_major_heading">{NEXT_STEPS_COPY.results_heading}</h2>
            <p className="committee_seats_lead">{NEXT_STEPS_COPY.intro}</p>

            <div className="committee_seats_section committee_seats_section_fit">
                <h3 className="committee_seats_section_title">{NEXT_STEPS_COPY.summary_section_title}</h3>
                <div className="committee_seats_table_scroll committee_seats_table_scroll_fit">
                    <table className="committee_seats_groups_table committee_seats_next_steps_summary_table">
                        <thead>
                            <tr>
                                <th scope="col">Political group</th>
                                <th scope="col">Councillors</th>
                                <th scope="col">Committee seats</th>
                            </tr>
                        </thead>
                        <tbody>
                            {allocationRows.map((row) => (
                                <tr key={row.group_name}>
                                    <td>{row.group_name}</td>
                                    <td>{row.councillor_count}</td>
                                    <td>{row.final_seats}</td>
                                </tr>
                            ))}
                            <tr className="committee_seats_next_steps_summary_total">
                                <th scope="row">Total</th>
                                <td>{allocationResult.total_councillors}</td>
                                <td>{allocationResult.total_allocated_seats}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {showSendCouncilDataSection && (
                <div className="committee_seats_send_council_data">
                    <h3 className="committee_seats_section_title">{SEND_COUNCIL_DATA_COPY.section_heading}</h3>
                    <div className="committee_seats_send_council_data_intro">
                        <p>{SEND_COUNCIL_DATA_COPY.section_intro}</p>
                    </div>
                    <label
                        className="committee_seats_send_council_data_label"
                        htmlFor={SEND_COUNCIL_DATA_COPY.council_name_input_id}
                    >
                        {SEND_COUNCIL_DATA_COPY.council_name_label}
                    </label>
                    <input
                        id={SEND_COUNCIL_DATA_COPY.council_name_input_id}
                        className="committee_seats_send_council_data_name_input"
                        type="text"
                        value={props.state.proposed_example_council_name}
                        onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                            props.onProposedExampleCouncilNameChange(event.currentTarget.value)
                        }
                    />
                    {exampleCouncilSubmissionJson === null ? (
                        <p className="committee_seats_send_council_data_placeholder">
                            {SEND_COUNCIL_DATA_COPY.json_placeholder}
                        </p>
                    ) : (
                        <div className="committee_seats_send_council_data_json_block">
                            <p className="committee_seats_note">{SEND_COUNCIL_DATA_COPY.json_description}</p>
                            <textarea
                                className="committee_seats_send_council_data_json"
                                readOnly
                                rows={12}
                                value={exampleCouncilSubmissionJson}
                                aria-label="Example council JSON"
                                ref={(element) => props.onRegisterExampleCouncilJsonTextarea(element)}
                            />
                            <button
                                className="button_standard"
                                type="button"
                                onClick={() => {
                                    void props.onCopyExampleCouncilSubmissionJson(
                                        exampleCouncilSubmissionJson
                                    );
                                }}
                            >
                                {copyStatus === "copied"
                                    ? SEND_COUNCIL_DATA_COPY.copy_json_button_copied_label
                                    : copyStatus === "failed"
                                      ? SEND_COUNCIL_DATA_COPY.copy_json_button_failed_label
                                      : SEND_COUNCIL_DATA_COPY.copy_json_button_label}
                            </button>
                        </div>
                    )}
                </div>
            )}

            <div className="committee_seats_next_steps_advice">
                <h3 className="committee_seats_section_title">{NEXT_STEPS_COPY.negotiation_heading}</h3>
                <p>{NEXT_STEPS_COPY.negotiation_lead}</p>
                <p>{NEXT_STEPS_COPY.negotiation_body}</p>

                <h3 className="committee_seats_section_title">
                    {NEXT_STEPS_COPY.monitoring_officer_heading}
                </h3>
                <p>{NEXT_STEPS_COPY.monitoring_officer_body}</p>

                <p className="committee_seats_note">{NEXT_STEPS_COPY.out_of_scope_note}</p>
            </div>

            <div className="committee_seats_actions">
                <button
                    className="button_standard"
                    type="button"
                    onClick={() => props.onBackFromNextSteps()}
                >
                    Back
                </button>
                <button className="button_standard" type="button" onClick={() => props.onStartOver()}>
                    {NEXT_STEPS_COPY.start_over_button_label}
                </button>
            </div>
        </div>
    );
}
