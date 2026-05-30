import {h} from "preact";
import {isCommitteeDistributionComplete, rowTotalForGroup} from "./calculate_committee_distribution";
import {DistributionAssignmentSteps} from "./distribution_assignment_steps";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import {FloorSectionExplanation} from "./floor_section_explanation";
import {getDistributionMatrixChangedCellPresentation} from "./political_group_colours";
import type {CommitteeDistributionState} from "./types";

export interface CommitteeDistributionWorkbookProps {
    distributionState: CommitteeDistributionState;
    pendingCommitteeByStepIndex: number[];
    onPendingCommitteeChange: (stepIndex: number, committeeIndex: number) => void;
    onAssignStep: (stepIndex: number) => void;
    onUndoStep: (stepIndex: number) => void;
    onBackToCommittees: () => void;
    onBackToResults: () => void;
}

function DistributionMatrixTable(props: {
    distributionState: CommitteeDistributionState;
    matrix: Record<string, Record<string, number>>;
    highlightChangesFromFloor: boolean;
}) {
    const committeeNames = props.distributionState.committees.map((committee) => committee.name);
    const matrixGrandTotal = props.distributionState.group_names.reduce(
        (total, groupName) => total + rowTotalForGroup(props.matrix, groupName),
        0
    );

    return (
        <table className="committee_seats_allocation_workbook committee_seats_distribution_matrix">
            <thead>
                <tr>
                    <th scope="col" className="committee_seats_allocation_workbook_corner" />
                    {committeeNames.map((committeeName) => (
                        <th
                            key={committeeName}
                            scope="col"
                            className="committee_seats_allocation_workbook_party"
                        >
                            <span className="committee_seats_distribution_committee_heading">
                                {committeeName}
                            </span>
                        </th>
                    ))}
                    <th scope="col" className="committee_seats_allocation_workbook_total">
                        Row total
                    </th>
                </tr>
            </thead>
            <tbody>
                {props.distributionState.group_names.map((groupName) => {
                    const targetFinalSeats =
                        props.distributionState.group_final_seats_by_name[groupName] ?? 0;

                    return (
                        <tr key={groupName} className="committee_seats_allocation_workbook_row">
                            <th scope="row" className="committee_seats_allocation_workbook_label">
                                {groupName}
                            </th>
                            {committeeNames.map((committeeName) => {
                                const currentSeats = props.matrix[groupName]?.[committeeName] ?? 0;
                                const floorSeats =
                                    props.distributionState.floor_matrix[groupName]?.[committeeName] ?? 0;
                                const highlightAsChanged =
                                    props.highlightChangesFromFloor && currentSeats !== floorSeats;
                                const changedCellPresentation = highlightAsChanged
                                    ? getDistributionMatrixChangedCellPresentation(groupName)
                                    : null;

                                return (
                                    <td
                                        key={committeeName}
                                        className={
                                            changedCellPresentation === null
                                                ? "committee_seats_allocation_workbook_value"
                                                : changedCellPresentation.className
                                        }
                                        style={changedCellPresentation?.style}
                                    >
                                        {currentSeats}
                                    </td>
                                );
                            })}
                            <td className="committee_seats_allocation_workbook_value committee_seats_allocation_workbook_total">
                                {rowTotalForGroup(props.matrix, groupName)}
                                {" / "}
                                {targetFinalSeats}
                            </td>
                        </tr>
                    );
                })}
                <tr className="committee_seats_allocation_workbook_row committee_seats_distribution_column_totals">
                    <th scope="row" className="committee_seats_allocation_workbook_label">
                        Column total
                    </th>
                    {props.distributionState.committees.map((committee) => {
                        let columnTotal = 0;
                        for (const groupName of props.distributionState.group_names) {
                            columnTotal += props.matrix[groupName]?.[committee.name] ?? 0;
                        }

                        return (
                            <td
                                key={committee.name}
                                className="committee_seats_allocation_workbook_value committee_seats_allocation_workbook_total"
                            >
                                {columnTotal}
                                {" / "}
                                {committee.seat_count}
                            </td>
                        );
                    })}
                    <td className="committee_seats_allocation_workbook_value committee_seats_allocation_workbook_total">
                        {matrixGrandTotal}
                        {" / "}
                        {props.distributionState.total_committee_seats}
                    </td>
                </tr>
            </tbody>
        </table>
    );
}

export function CommitteeDistributionWorkbook(props: CommitteeDistributionWorkbookProps) {
    const distributionState = props.distributionState;
    const distributionComplete = isCommitteeDistributionComplete(distributionState);

    return (
        <div className="committee_seats_step committee_seats_experimental_step">
            <h2 className="committee_seats_experimental_heading">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.page_heading}
            </h2>
            <p className="committee_seats_lead">{EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.distribution_intro}</p>

            <div className="committee_seats_section">
                <h3 className="committee_seats_section_title">
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.floor_section_title}
                </h3>
                <FloorSectionExplanation distributionState={distributionState} />
                <div className="committee_seats_table_scroll">
                    <DistributionMatrixTable
                        distributionState={distributionState}
                        matrix={distributionState.floor_matrix}
                        highlightChangesFromFloor={false}
                    />
                </div>
            </div>

            <div className="committee_seats_section">
                <h3 className="committee_seats_section_title">
                    {distributionComplete
                        ? "Final distribution"
                        : EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_section_title}
                </h3>
                <div className="committee_seats_table_scroll">
                    <DistributionMatrixTable
                        distributionState={distributionState}
                        matrix={distributionState.seats_matrix}
                        highlightChangesFromFloor={true}
                    />
                </div>

                <p className="committee_seats_note committee_seats_distribution_assignment_intro">
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_section_intro}
                </p>
                <DistributionAssignmentSteps
                    distributionState={distributionState}
                    pendingCommitteeByStepIndex={props.pendingCommitteeByStepIndex}
                    onPendingCommitteeChange={props.onPendingCommitteeChange}
                    onAssignStep={props.onAssignStep}
                    onUndoStep={props.onUndoStep}
                />

                {distributionComplete && (
                    <p className="committee_seats_note committee_seats_distribution_complete">
                        {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_complete_message}
                    </p>
                )}
            </div>

            <div className="committee_seats_actions">
                <button className="button_standard" type="button" onClick={() => props.onBackToCommittees()}>
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.back_to_committees_button_label}
                </button>
                <button className="button_standard" type="button" onClick={() => props.onBackToResults()}>
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.back_to_results_button_label}
                </button>
            </div>
        </div>
    );
}
