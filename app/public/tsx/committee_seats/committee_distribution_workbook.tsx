import {h} from "preact";
import {
    buildMatrixWithPendingPartySelections,
    columnTotalForMatrix,
    getFirstUnassignedAssignmentStepIndex,
    getLastGroupInAssignmentTurnOrder,
    isCommitteeDistributionComplete,
    matrixSeatCount,
    resolveAssignmentStepGroupName,
    rowTotalForGroup,
} from "./calculate_committee_distribution";
import {AssignmentSectionIntro} from "./assignment_section_intro";
import {DistributionAssignmentSteps} from "./distribution_assignment_steps";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "./experimental_seat_distribution";
import {FloorSectionExplanation} from "./floor_section_explanation";
import {getDistributionMatrixChangedCellPresentation} from "./political_group_colours";
import type {CommitteeDistributionState} from "./types";

export interface CommitteeDistributionWorkbookProps {
    distributionState: CommitteeDistributionState;
    pendingCommitteeSelections: number[];
    onCommitteeChoiceClick: (committeeIndex: number) => void;
    onClearPendingSelections: () => void;
    onConfirmAssignmentBatch: () => void;
    onGoBackToPreviousGroup: () => void;
    onGoBackToLastGroupWhenComplete: () => void;
    onGoToFinalSummary: () => void;
    onBackToCommittees: () => void;
    onBackToResults: () => void;
}

function DistributionMatrixTable(props: {
    distributionState: CommitteeDistributionState;
    matrix: Record<string, Record<string, number>>;
    highlightChangesFromFloor: boolean;
    showSeatsRemainingColumn: boolean;
    currentTurnGroupName: string | null;
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
                    {props.showSeatsRemainingColumn && (
                        <th
                            scope="col"
                            className="committee_seats_allocation_workbook_total committee_seats_distribution_matrix_seats_remaining_heading"
                        >
                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_remainder_table_seats_left_column}
                        </th>
                    )}
                </tr>
            </thead>
            <tbody>
                {props.distributionState.group_names.map((groupName) => {
                    const targetFinalSeats =
                        props.distributionState.group_final_seats_by_name[groupName] ?? 0;
                    const rowTotal = rowTotalForGroup(props.matrix, groupName);
                    const seatsRemainingToAssign = Math.max(0, targetFinalSeats - rowTotal);
                    const isCurrentTurn =
                        props.showSeatsRemainingColumn &&
                        groupName === props.currentTurnGroupName;
                    const rowClass =
                        "committee_seats_allocation_workbook_row" +
                        (isCurrentTurn ? " committee_seats_distribution_matrix_row_current" : "");

                    return (
                        <tr key={groupName} className={rowClass}>
                            <th scope="row" className="committee_seats_allocation_workbook_label">
                                {groupName}
                                {isCurrentTurn
                                    ? " " +
                                      EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_current_turn_row_suffix
                                    : ""}
                            </th>
                            {committeeNames.map((committeeName) => {
                                const currentSeats = matrixSeatCount(
                                    props.matrix[groupName]?.[committeeName]
                                );
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
                                {rowTotal}
                                {" / "}
                                {targetFinalSeats}
                            </td>
                            {props.showSeatsRemainingColumn && (
                                <td className="committee_seats_allocation_workbook_value committee_seats_allocation_workbook_total committee_seats_distribution_matrix_seats_remaining">
                                    {seatsRemainingToAssign}
                                </td>
                            )}
                        </tr>
                    );
                })}
                <tr className="committee_seats_allocation_workbook_row committee_seats_distribution_column_totals">
                    <th scope="row" className="committee_seats_allocation_workbook_label">
                        Column total
                    </th>
                    {props.distributionState.committees.map((committee) => {
                        const columnTotal = columnTotalForMatrix(props.matrix, committee.name);

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
                    {props.showSeatsRemainingColumn && <td className="committee_seats_allocation_workbook_total" />}
                </tr>
            </tbody>
        </table>
    );
}

export function CommitteeDistributionWorkbook(props: CommitteeDistributionWorkbookProps) {
    const distributionState = props.distributionState;
    const distributionComplete = isCommitteeDistributionComplete(distributionState);
    const lastGroupInTurnOrder = getLastGroupInAssignmentTurnOrder(distributionState);
    const activeStepIndex = getFirstUnassignedAssignmentStepIndex(distributionState);
    const assignmentMatrixForDisplay =
        activeStepIndex === null
            ? distributionState.seats_matrix
            : buildMatrixWithPendingPartySelections(
                  distributionState,
                  activeStepIndex,
                  props.pendingCommitteeSelections
              );
    const currentTurnGroupName =
        distributionComplete || activeStepIndex === null
            ? null
            : resolveAssignmentStepGroupName(distributionState, activeStepIndex);

    return (
        <div className="committee_seats_step committee_seats_experimental_step">
            <h2 className="committee_seats_experimental_heading">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.page_heading}
            </h2>
            <div className="committee_seats_experimental_page_intro">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.page_intro_paragraphs.map((paragraph) => (
                    <p key={paragraph} className="committee_seats_lead">
                        {paragraph}
                    </p>
                ))}
            </div>
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
                        showSeatsRemainingColumn={false}
                        currentTurnGroupName={null}
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
                        matrix={assignmentMatrixForDisplay}
                        highlightChangesFromFloor={true}
                        showSeatsRemainingColumn={!distributionComplete}
                        currentTurnGroupName={currentTurnGroupName}
                    />
                </div>

                {!distributionComplete && (
                    <span>
                        <AssignmentSectionIntro />
                        <DistributionAssignmentSteps
                            distributionState={distributionState}
                            pendingCommitteeSelections={props.pendingCommitteeSelections}
                            onCommitteeChoiceClick={props.onCommitteeChoiceClick}
                            onClearPendingSelections={props.onClearPendingSelections}
                            onConfirmAssignmentBatch={props.onConfirmAssignmentBatch}
                            onGoBackToPreviousGroup={props.onGoBackToPreviousGroup}
                        />
                    </span>
                )}

                {distributionComplete && (
                    <div className="committee_seats_distribution_complete_block">
                        <p className="committee_seats_note committee_seats_distribution_complete">
                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_complete_message}
                        </p>
                        {lastGroupInTurnOrder !== null && (
                            <button
                                className="button_standard committee_seats_distribution_go_back_to_last_group_button"
                                type="button"
                                onClick={() => props.onGoBackToLastGroupWhenComplete()}
                            >
                                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.assignment_go_back_to_group_button_label(
                                    lastGroupInTurnOrder
                                ) + " choices"}
                            </button>
                        )}
                        <button
                            className="button_standard committee_seats_distribution_go_to_final_summary_button"
                            type="button"
                            onClick={() => props.onGoToFinalSummary()}
                        >
                            {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.go_to_final_summary_button_label}
                        </button>
                    </div>
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
