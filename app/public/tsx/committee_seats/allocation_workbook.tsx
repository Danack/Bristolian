import {h} from "preact";
import {formatNumber, formatPercentage} from "./calculate_party_allocation";
import {COMMITTEE_SEATS_PAGE} from "./page_config";
import type {PartyAllocationResult} from "./types";

function AllocationWorkbookColumnHeader(props: {
    partyNames: string[];
    includeTotalSeatsAllocatedColumn?: boolean;
}) {
    return (
        <tr>
            <th scope="col" className="committee_seats_allocation_workbook_corner" />
            {props.partyNames.map((partyName) => (
                <th key={partyName} scope="col" className="committee_seats_allocation_workbook_party">
                    {partyName}
                </th>
            ))}
            {props.includeTotalSeatsAllocatedColumn && (
                <th scope="col" className="committee_seats_allocation_workbook_total">
                    {COMMITTEE_SEATS_PAGE.allocation_workbook_total_seats_allocated_heading}
                </th>
            )}
        </tr>
    );
}

function PartyAllocationWorkbookDescriptionRow(props: {
    description: string;
    partyColumnCount: number;
    includeTotalSeatsAllocatedColumn?: boolean;
}) {
    const columnCount =
        props.partyColumnCount + 1 + (props.includeTotalSeatsAllocatedColumn ? 1 : 0);

    return (
        <tr className="committee_seats_allocation_workbook_step_description">
            <td colSpan={columnCount}>{props.description}</td>
        </tr>
    );
}

function PartyAllocationWorkbookRow(props: {
    rowLabel: string;
    partyNames: string[];
    cellValues: Record<string, string | number>;
    rowClassName?: string;
    valueCellClassName?: string;
    rowKey?: string;
    totalSeatsAllocated?: number;
}) {
    const rowClassName = props.rowClassName ?? "committee_seats_allocation_workbook_row";
    const valueCellClassName =
        props.valueCellClassName ?? "committee_seats_allocation_workbook_value";

    return (
        <tr key={props.rowKey} className={rowClassName}>
            <th scope="row" className="committee_seats_allocation_workbook_label">
                {props.rowLabel}
            </th>
            {props.partyNames.map((partyName) => (
                <td key={partyName} className={valueCellClassName}>
                    {props.cellValues[partyName]}
                </td>
            ))}
            {props.totalSeatsAllocated !== undefined && (
                <td className={valueCellClassName + " committee_seats_allocation_workbook_total"}>
                    {props.totalSeatsAllocated}
                </td>
            )}
        </tr>
    );
}

function ExactEntitlementWorkbookRow(props: {
    partyNames: string[];
    allocationRows: PartyAllocationResult["rows"];
    totalCommitteeSeats: number;
    totalCouncillors: number;
}) {
    const entitlementByGroupName = Object.fromEntries(
        props.allocationRows.map((row) => [row.group_name, row])
    );

    return (
        <tr className="committee_seats_allocation_workbook_row">
            <th scope="row" className="committee_seats_allocation_workbook_label">
                Exact entitlement (before rounding)
            </th>
            {props.partyNames.map((partyName) => {
                const allocationRow = entitlementByGroupName[partyName];
                const calculationFormula =
                    props.totalCommitteeSeats +
                    " × (" +
                    allocationRow.councillor_count +
                    " ÷ " +
                    props.totalCouncillors +
                    ")";

                return (
                    <td
                        key={partyName}
                        className="committee_seats_allocation_workbook_value"
                        title={"Calculation: " + calculationFormula}
                    >
                        {formatNumber(allocationRow.raw_entitlement)}
                    </td>
                );
            })}
        </tr>
    );
}

export interface PartyAllocationStepViewProps {
    allocationResult: PartyAllocationResult;
    onBack: () => void;
    onContinue: () => void;
}

export function PartyAllocationStepView(props: PartyAllocationStepViewProps) {
    const allocationResult = props.allocationResult;
    const allocationRowsWithCouncillors = allocationResult.rows.filter(
        (row) => row.councillor_count > 0
    );
    const partyNames = allocationRowsWithCouncillors.map((row) => row.group_name);
    const totalCommitteeSeats = allocationResult.total_committee_seats;
    const totalCouncillors = allocationResult.total_councillors;
    const percentageMeaningDescription =
        "Each group's share of committee seats matches its share of councillors on the council. " +
        "This row shows each group's councillors divided by the total number of councillors, as a percentage.";
    const percentageNumbersRowLabel =
        "Each group's share of the " + totalCommitteeSeats + " seats";
    const rawEntitlementMeaningDescription =
        "Each group's exact share of committee seats before rounding — total committee seats multiplied by " +
        "this group's share of councillors (from the row above).";

    const councillorCounts: Record<string, string | number> = {};
    const percentageValues: Record<string, string | number> = {};
    const finalAllocationValues: Record<string, string | number> = {};

    for (const row of allocationRowsWithCouncillors) {
        councillorCounts[row.group_name] = row.councillor_count;
        percentageValues[row.group_name] = formatPercentage(row.percentage_of_council);
        finalAllocationValues[row.group_name] = row.final_seats;
    }

    return (
        <div className="committee_seats_step">
            <p className="committee_seats_lead">{COMMITTEE_SEATS_PAGE.allocation_step_intro}</p>

            <div className="committee_seats_allocation_workbook_tables">
                <div className="committee_seats_section">
                    <h3 className="committee_seats_section_title">
                        {COMMITTEE_SEATS_PAGE.allocation_proportional_share_section_title}
                    </h3>
                    <div className="committee_seats_table_scroll committee_seats_allocation_workbook_compact_wrap">
                        <table className="committee_seats_allocation_workbook committee_seats_allocation_workbook_compact">
                            <thead>
                                <AllocationWorkbookColumnHeader partyNames={partyNames} />
                            </thead>
                            <tbody>
                                <PartyAllocationWorkbookRow
                                    rowLabel="Number of Councillors"
                                    partyNames={partyNames}
                                    cellValues={councillorCounts}
                                />
                                <PartyAllocationWorkbookDescriptionRow
                                    description={percentageMeaningDescription}
                                    partyColumnCount={partyNames.length}
                                />
                                <PartyAllocationWorkbookRow
                                    rowLabel={percentageNumbersRowLabel}
                                    partyNames={partyNames}
                                    cellValues={percentageValues}
                                />
                                <PartyAllocationWorkbookDescriptionRow
                                    description={rawEntitlementMeaningDescription}
                                    partyColumnCount={partyNames.length}
                                />
                                <ExactEntitlementWorkbookRow
                                    partyNames={partyNames}
                                    allocationRows={allocationRowsWithCouncillors}
                                    totalCommitteeSeats={totalCommitteeSeats}
                                    totalCouncillors={totalCouncillors}
                                />
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="committee_seats_section">
                    <h3 className="committee_seats_section_title">
                        {COMMITTEE_SEATS_PAGE.allocation_rounding_section_title}
                    </h3>
                    <div className="committee_seats_table_scroll">
                        <table className="committee_seats_allocation_workbook">
                            <thead>
                                <AllocationWorkbookColumnHeader
                                    partyNames={partyNames}
                                    includeTotalSeatsAllocatedColumn={true}
                                />
                            </thead>
                            <tbody>
                                {allocationResult.workbook_steps.flatMap((workbookStep) => {
                                    const stepKey = String(workbookStep.step_number);
                                    const rows = [];

                                    if (workbookStep.description !== null) {
                                        rows.push(
                                            <tr
                                                key={stepKey + "_description"}
                                                className="committee_seats_allocation_workbook_step_description"
                                            >
                                                <td colSpan={partyNames.length + 2}>
                                                    {workbookStep.description}
                                                </td>
                                            </tr>
                                        );
                                    }

                                    rows.push(
                                        <PartyAllocationWorkbookRow
                                            key={stepKey + "_values"}
                                            rowLabel={workbookStep.label}
                                            partyNames={partyNames}
                                            cellValues={workbookStep.seats_by_group_name}
                                            rowKey={stepKey + "_values"}
                                            totalSeatsAllocated={workbookStep.total_seats_allocated}
                                        />
                                    );

                                    return rows;
                                })}
                                {allocationResult.all_committee_seats_allocated_message !== null && (
                                    <PartyAllocationWorkbookDescriptionRow
                                        description={allocationResult.all_committee_seats_allocated_message}
                                        partyColumnCount={partyNames.length}
                                        includeTotalSeatsAllocatedColumn={true}
                                    />
                                )}
                                <tr className="committee_seats_allocation_workbook_spacer">
                                    <td colSpan={partyNames.length + 2} />
                                </tr>
                                <PartyAllocationWorkbookRow
                                    rowLabel="Final allocation"
                                    partyNames={partyNames}
                                    cellValues={finalAllocationValues}
                                    rowClassName="committee_seats_allocation_workbook_row committee_seats_allocation_workbook_row_final"
                                    totalSeatsAllocated={allocationResult.total_allocated_seats}
                                />
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div className="committee_seats_actions">
                <button className="button_standard" type="button" onClick={() => props.onBack()}>
                    Back
                </button>
                <button className="button_standard" type="button" onClick={() => props.onContinue()}>
                    {COMMITTEE_SEATS_PAGE.allocation_summary_button_label}
                </button>
            </div>
        </div>
    );
}
