import {h} from "preact";
import {COMMITTEE_SEATS_PAGE} from "./page_config";
import type {PoliticalGroup} from "./types";
import {
    ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT,
    clampGroupCouncillorCountAfterAdjustment,
    clampGroupCouncillorCountValue,
    getVisibleAdditionalPoliticalGroupSlotCount,
    GROUP_COUNCILLOR_COUNT_ADJUSTMENTS,
    isGroupCouncillorCountAdjustmentDisabled,
    mergePoliticalGroupsIntoCouncilSetupForm,
    STANDARD_POLITICAL_GROUP_NAMES,
} from "./political_groups_form";
import {
    councilFormHasVacancies,
    formatVacancyCouncillorCountEnteredMessage,
    getVacancyCouncillorCountFromForm,
    VACANCY_ALLOCATION_NOTE,
} from "./vacancy_allocation";

function PoliticalGroupCouncillorAdjustButtons(props: {
    formGroups: PoliticalGroup[];
    groupIndex: number;
    expectedTotalCouncillors: number;
    editable: boolean;
    onGroupCountAdjust: (groupIndex: number, adjustment: number) => void;
}) {
    if (!props.editable) {
        return <td className="committee_seats_group_count_adjust_cell" />;
    }

    return (
        <td className="committee_seats_group_count_adjust_cell">
            <div
                className="committee_seats_group_count_adjust"
                role="group"
                aria-label="Adjust councillor count"
            >
                {GROUP_COUNCILLOR_COUNT_ADJUSTMENTS.map((adjustment) => {
                    const adjustmentDisabled = isGroupCouncillorCountAdjustmentDisabled(
                        props.formGroups,
                        props.groupIndex,
                        adjustment,
                        props.expectedTotalCouncillors
                    );

                    return (
                        <button
                            key={adjustment}
                            type="button"
                            className="button_standard committee_seats_group_count_adjust_button"
                            disabled={adjustmentDisabled}
                            onClick={() => props.onGroupCountAdjust(props.groupIndex, adjustment)}
                        >
                            {adjustment > 0 ? "+" + adjustment : String(adjustment)}
                        </button>
                    );
                })}
            </div>
        </td>
    );
}

function PoliticalGroupCountField(props: {
    groupIndex: number;
    councillorCount: number;
    inputId: string;
    editable: boolean;
    onGroupCountChange: (groupIndex: number, councillorCount: number) => void;
}) {
    if (!props.editable) {
        return <span>{props.councillorCount}</span>;
    }

    return (
        <input
            id={props.inputId}
            type="number"
            min="0"
            step="1"
            value={props.councillorCount}
            onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                props.onGroupCountChange(props.groupIndex, parseInt(event.currentTarget.value, 10) || 0)
            }
        />
    );
}

export interface PoliticalGroupsEditorProps {
    politicalGroups: PoliticalGroup[];
    expectedTotalCouncillors: number;
    additionalGroupNamesEditable: boolean;
    groupTotalStatusMessage: string;
    groupTotalStatusMatches: boolean;
    canContinueFromPoliticalGroups: boolean;
    onGroupNameChange: (groupIndex: number, name: string) => void;
    onGroupCountChange: (groupIndex: number, councillorCount: number) => void;
    onGroupCountAdjust: (groupIndex: number, adjustment: number) => void;
    onContinueFromPoliticalGroups: () => void;
}

export function PoliticalGroupsEditor(props: PoliticalGroupsEditorProps) {
    const partyCouncillorCountsEditable = true;
    const additionalNamesEditable = props.additionalGroupNamesEditable;
    const formGroups =
        props.politicalGroups.length >= STANDARD_POLITICAL_GROUP_NAMES.length
            ? props.politicalGroups
            : mergePoliticalGroupsIntoCouncilSetupForm(props.politicalGroups);
    const standardGroups = formGroups.slice(0, STANDARD_POLITICAL_GROUP_NAMES.length);
    const additionalGroups = formGroups.slice(STANDARD_POLITICAL_GROUP_NAMES.length);
    const visibleAdditionalGroupCount = getVisibleAdditionalPoliticalGroupSlotCount(formGroups);
    const visibleAdditionalGroups = additionalGroups.slice(0, visibleAdditionalGroupCount);

    return (
        <div className="committee_seats_section committee_seats_section_fit">
            <fieldset
                className="committee_seats_political_groups_fieldset"
                aria-labelledby="committee_seats_political_groups_panel_title"
            >
                <div className="committee_seats_political_groups_panel">
                    <h3
                        id="committee_seats_political_groups_panel_title"
                        className="committee_seats_section_title committee_seats_political_groups_panel_title"
                    >
                        {COMMITTEE_SEATS_PAGE.council_setup_political_groups_section_title}
                    </h3>
                    <div className="committee_seats_political_groups_body">
                        <div className="committee_seats_table_scroll committee_seats_table_scroll_fit committee_seats_political_groups_table">
                            <table className="committee_seats_groups_table">
                                <thead>
                                    <tr>
                                        <th>Group</th>
                                        <th colSpan={2} scope="colgroup">
                                            Number of councillors
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {standardGroups.map((politicalGroup, standardIndex) => {
                                        const groupIndex = standardIndex;
                                        const countInputId = "political_group_count_" + groupIndex;

                                        return (
                                            <tr key={STANDARD_POLITICAL_GROUP_NAMES[standardIndex]}>
                                                <td className="committee_seats_group_name_fixed">
                                                    {STANDARD_POLITICAL_GROUP_NAMES[standardIndex]}
                                                </td>
                                                <td className="committee_seats_group_count_cell">
                                                    <PoliticalGroupCountField
                                                        groupIndex={groupIndex}
                                                        councillorCount={politicalGroup.councillor_count}
                                                        inputId={countInputId}
                                                        editable={partyCouncillorCountsEditable}
                                                        onGroupCountChange={props.onGroupCountChange}
                                                    />
                                                </td>
                                                <PoliticalGroupCouncillorAdjustButtons
                                                    formGroups={formGroups}
                                                    groupIndex={groupIndex}
                                                    expectedTotalCouncillors={props.expectedTotalCouncillors}
                                                    editable={partyCouncillorCountsEditable}
                                                    onGroupCountAdjust={props.onGroupCountAdjust}
                                                />
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                <tbody>
                                    <tr className="committee_seats_groups_table_additional_heading">
                                        <th colSpan={3} scope="colgroup">
                                            Other groups
                                        </th>
                                    </tr>
                                    {visibleAdditionalGroups.map((politicalGroup, additionalIndex) => {
                                        const groupIndex =
                                            STANDARD_POLITICAL_GROUP_NAMES.length + additionalIndex;
                                        const nameInputId = "political_group_name_" + groupIndex;
                                        const countInputId = "political_group_count_" + groupIndex;

                                        return (
                                            <tr key={"additional_" + additionalIndex}>
                                                <td>
                                                    {additionalNamesEditable ? (
                                                        <input
                                                            id={nameInputId}
                                                            type="text"
                                                            value={politicalGroup.name}
                                                            onInput={(
                                                                event: h.JSX.TargetedEvent<HTMLInputElement, Event>
                                                            ) =>
                                                                props.onGroupNameChange(
                                                                    groupIndex,
                                                                    event.currentTarget.value
                                                                )
                                                            }
                                                        />
                                                    ) : (
                                                        politicalGroup.name
                                                    )}
                                                </td>
                                                <td className="committee_seats_group_count_cell">
                                                    <PoliticalGroupCountField
                                                        groupIndex={groupIndex}
                                                        councillorCount={politicalGroup.councillor_count}
                                                        inputId={countInputId}
                                                        editable={partyCouncillorCountsEditable}
                                                        onGroupCountChange={props.onGroupCountChange}
                                                    />
                                                </td>
                                                <PoliticalGroupCouncillorAdjustButtons
                                                    formGroups={formGroups}
                                                    groupIndex={groupIndex}
                                                    expectedTotalCouncillors={props.expectedTotalCouncillors}
                                                    editable={partyCouncillorCountsEditable}
                                                    onGroupCountAdjust={props.onGroupCountAdjust}
                                                />
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                        <div className="committee_seats_political_groups_aside">
                            <p
                                className={
                                    props.groupTotalStatusMatches
                                        ? "committee_seats_total_councillors"
                                        : "committee_seats_total_councillors committee_seats_total_councillors_mismatch"
                                }
                            >
                                {props.groupTotalStatusMessage}
                            </p>
                            <button
                                className="button_standard committee_seats_political_groups_aside_continue"
                                type="button"
                                onClick={() => props.onContinueFromPoliticalGroups()}
                                disabled={!props.canContinueFromPoliticalGroups}
                            >
                                Continue
                            </button>
                        </div>
                    </div>
                    {(additionalNamesEditable || councilFormHasVacancies(formGroups)) && (
                        <div className="committee_seats_political_groups_panel_notes">
                            {additionalNamesEditable && (
                                <p className="committee_seats_note">
                                    You can add up to {ADDITIONAL_POLITICAL_GROUP_SLOT_COUNT} other groups.
                                </p>
                            )}
                            {councilFormHasVacancies(formGroups) && (
                                <p className="committee_seats_note">
                                    {formatVacancyCouncillorCountEnteredMessage(
                                        getVacancyCouncillorCountFromForm(formGroups)
                                    )}{" "}
                                    {VACANCY_ALLOCATION_NOTE}
                                </p>
                            )}
                        </div>
                    )}
                </div>
            </fieldset>
        </div>
    );
}

export function resolveFormPoliticalGroups(politicalGroups: PoliticalGroup[]): PoliticalGroup[] {
    return politicalGroups.length >= STANDARD_POLITICAL_GROUP_NAMES.length
        ? politicalGroups
        : mergePoliticalGroupsIntoCouncilSetupForm(politicalGroups);
}

export function clampGroupCountForPanel(
    politicalGroups: PoliticalGroup[],
    groupIndex: number,
    councillorCount: number,
    expectedTotalCouncillors: number
): number {
    const formGroups = resolveFormPoliticalGroups(politicalGroups);
    return clampGroupCouncillorCountValue(
        formGroups,
        groupIndex,
        councillorCount,
        expectedTotalCouncillors
    );
}

export function clampGroupCountAfterAdjustForPanel(
    politicalGroups: PoliticalGroup[],
    groupIndex: number,
    adjustment: number,
    expectedTotalCouncillors: number
): number {
    const formGroups = resolveFormPoliticalGroups(politicalGroups);
    return clampGroupCouncillorCountAfterAdjustment(
        formGroups,
        groupIndex,
        adjustment,
        expectedTotalCouncillors
    );
}
