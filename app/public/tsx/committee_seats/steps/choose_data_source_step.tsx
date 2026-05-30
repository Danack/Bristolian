import {h} from "preact";
import {EXAMPLE_COUNCILS, getExampleCouncilById} from "../example_councils";
import {COMMITTEE_SEATS_PAGE, NO_EXAMPLE_COUNCIL_SELECTED} from "../page_config";

export interface ChooseDataSourceStepProps {
    selectedExampleCouncilId: string;
    onSelectedExampleCouncilChange: (exampleCouncilId: string) => void;
    onChooseExampleCouncil: () => void;
    onChooseCustomData: () => void;
}

export function ChooseDataSourceStep(props: ChooseDataSourceStepProps) {
    const selectedExampleCouncil = getExampleCouncilById(props.selectedExampleCouncilId);
    const exampleCouncilSelected = selectedExampleCouncil !== undefined;
    const exampleButtonLabel = exampleCouncilSelected
        ? "Use data for '" + selectedExampleCouncil.display_name + "'"
        : COMMITTEE_SEATS_PAGE.choose_source_example_button_label;

    return (
        <div className="committee_seats_step committee_seats_choose_source_screen">
            <p className="committee_seats_choose_source_lead">{COMMITTEE_SEATS_PAGE.choose_source_lead}</p>
            <div className="committee_seats_choose_source">
                <div className="committee_seats_choose_source_option">
                    <p className="committee_seats_choose_source_option_description">
                        {COMMITTEE_SEATS_PAGE.choose_source_example_description}
                    </p>
                    <div className="committee_seats_example_choice">
                        <label className="committee_seats_visually_hidden" htmlFor="example_council">
                            Example council
                        </label>
                        <select
                            id="example_council"
                            value={props.selectedExampleCouncilId}
                            onChange={(event: h.JSX.TargetedEvent<HTMLSelectElement, Event>) =>
                                props.onSelectedExampleCouncilChange(event.currentTarget.value)
                            }
                        >
                            <option value={NO_EXAMPLE_COUNCIL_SELECTED}>
                                {COMMITTEE_SEATS_PAGE.choose_source_council_placeholder}
                            </option>
                            {EXAMPLE_COUNCILS.map((exampleCouncil) => (
                                <option key={exampleCouncil.id} value={exampleCouncil.id}>
                                    {exampleCouncil.display_name}
                                </option>
                            ))}
                        </select>
                        <button
                            className="button_standard"
                            type="button"
                            disabled={!exampleCouncilSelected}
                            onClick={() => props.onChooseExampleCouncil()}
                        >
                            {exampleButtonLabel}
                        </button>
                    </div>
                </div>

                <p className="committee_seats_choose_source_or" aria-hidden="true">
                    {COMMITTEE_SEATS_PAGE.choose_source_or_label}
                </p>

                <div className="committee_seats_choose_source_option">
                    <p className="committee_seats_choose_source_option_description">
                        {COMMITTEE_SEATS_PAGE.choose_source_custom_description}
                    </p>
                    <button
                        className="button_standard"
                        type="button"
                        onClick={() => props.onChooseCustomData()}
                    >
                        Enter Council Data
                    </button>
                </div>
            </div>
        </div>
    );
}
