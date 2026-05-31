import {h} from "preact";
import {
    columnTotalForMatrix,
    matrixSeatCount,
    rowTotalForGroup,
} from "../calculate_committee_distribution";
import {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY} from "../experimental_seat_distribution";
import {getNextStepsAllocationRows} from "../next_steps";
import type {CommitteeDistributionState, PartyAllocationResult} from "../types";

export interface FinalSummaryStepProps {
    allocationResult: PartyAllocationResult;
    distributionState: CommitteeDistributionState;
    onBackToDistribution: () => void;
    onBackToResults: () => void;
}

function CommitteeAllocationSummaryMatrix(props: {distributionState: CommitteeDistributionState}) {
    const distributionState = props.distributionState;
    const committeeNames = distributionState.committees.map((committee) => committee.name);
    const matrix = distributionState.seats_matrix;
    const matrixGrandTotal = distributionState.group_names.reduce(
        (total, groupName) => total + rowTotalForGroup(matrix, groupName),
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
                {distributionState.group_names.map((groupName) => {
                    const targetFinalSeats =
                        distributionState.group_final_seats_by_name[groupName] ?? 0;
                    const rowTotal = rowTotalForGroup(matrix, groupName);

                    return (
                        <tr key={groupName} className="committee_seats_allocation_workbook_row">
                            <th scope="row" className="committee_seats_allocation_workbook_label">
                                {groupName}
                            </th>
                            {committeeNames.map((committeeName) => (
                                <td
                                    key={committeeName}
                                    className="committee_seats_allocation_workbook_value"
                                >
                                    {matrixSeatCount(matrix[groupName]?.[committeeName])}
                                </td>
                            ))}
                            <td className="committee_seats_allocation_workbook_value committee_seats_allocation_workbook_total">
                                {rowTotal}
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
                    {distributionState.committees.map((committee) => {
                        const columnTotal = columnTotalForMatrix(matrix, committee.name);

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
                        {distributionState.total_committee_seats}
                    </td>
                </tr>
            </tbody>
        </table>
    );
}

export function FinalSummaryStep(props: FinalSummaryStepProps) {
    const allocationResult = props.allocationResult;
    const allocationRows = getNextStepsAllocationRows(allocationResult.rows);

    return (
        <div className="committee_seats_step committee_seats_final_summary_screen">
            <h2 className="committee_seats_final_summary_heading">
                {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_page_heading}
            </h2>
            <p className="committee_seats_lead">{EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_intro}</p>

            <div className="committee_seats_section committee_seats_section_fit">
                <h3 className="committee_seats_section_title">
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_proportional_section_title}
                </h3>
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

            <div className="committee_seats_section">
                <h3 className="committee_seats_section_title">
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.final_summary_committee_allocation_section_title}
                </h3>
                <div className="committee_seats_table_scroll">
                    <CommitteeAllocationSummaryMatrix distributionState={props.distributionState} />
                </div>
            </div>

            <div className="committee_seats_actions">
                <button className="button_standard" type="button" onClick={() => props.onBackToDistribution()}>
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.back_to_distribution_button_label}
                </button>
                <button className="button_standard" type="button" onClick={() => props.onBackToResults()}>
                    {EXPERIMENTAL_SEAT_DISTRIBUTION_COPY.back_to_results_button_label}
                </button>
            </div>
        </div>
    );
}
