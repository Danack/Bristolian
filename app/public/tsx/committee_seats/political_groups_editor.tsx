import {Component, h} from "preact";
import {COMMITTEE_SEATS_PAGE} from "./page_config";
import type {PoliticalGroup} from "./types";
import {
    clampGroupCouncillorCountAfterAdjustment,
    clampGroupCouncillorCountValue,
    getListedAdditionalPoliticalGroups,
    getNextEmptyAdditionalPoliticalGroupSlotIndex,
    GROUP_COUNCILLOR_COUNT_ADJUSTMENTS,
    hasReachedMaximumAdditionalPoliticalGroupSlots,
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

interface AddPoliticalGroupRowProps {
    slotIndex: number;
    onAddGroup: (groupIndex: number, name: string) => void;
}

interface AddPoliticalGroupRowState {
    draftGroupName: string;
}

class AddPoliticalGroupRow extends Component<AddPoliticalGroupRowProps, AddPoliticalGroupRowState> {
    constructor(props: AddPoliticalGroupRowProps) {
        super(props);
        this.state = {
            draftGroupName: "",
        };
    }

    handleDraftGroupNameChange(event: h.JSX.TargetedEvent<HTMLInputElement, Event>): void {
        this.setState({
            draftGroupName: event.currentTarget.value,
        });
    }

    handleAddGroup(): void {
        const trimmedGroupName = this.state.draftGroupName.trim();
        if (trimmedGroupName === "") {
            return;
        }

        this.props.onAddGroup(this.props.slotIndex, trimmedGroupName);
        this.setState({
            draftGroupName: "",
        });
    }

    handleDraftGroupNameKeyDown(event: KeyboardEvent): void {
        if (event.key === "Enter") {
            event.preventDefault();
            this.handleAddGroup();
        }
    }

    render() {
        const addGroupDisabled = this.state.draftGroupName.trim() === "";

        return (
            <tr className="committee_seats_groups_table_add_group_row">
                <td className="committee_seats_additional_group_name_cell" colSpan={3}>
                    <div className="committee_seats_add_political_group_controls">
                        <input
                            id="committee_seats_add_political_group_name"
                            type="text"
                            className="committee_seats_additional_group_name_input committee_seats_add_political_group_name_input"
                            value={this.state.draftGroupName}
                            placeholder={COMMITTEE_SEATS_PAGE.additional_political_group_name_placeholder}
                            onInput={(event: h.JSX.TargetedEvent<HTMLInputElement, Event>) =>
                                this.handleDraftGroupNameChange(event)
                            }
                            onKeyDown={(event: KeyboardEvent) => this.handleDraftGroupNameKeyDown(event)}
                        />
                        <button
                            className="button_standard committee_seats_add_political_group_button"
                            type="button"
                            disabled={addGroupDisabled}
                            onClick={() => this.handleAddGroup()}
                        >
                            {COMMITTEE_SEATS_PAGE.add_political_group_button_label}
                        </button>
                    </div>
                </td>
            </tr>
        );
    }
}

export interface PoliticalGroupsEditorProps {
    politicalGroups: PoliticalGroup[];
    expectedTotalCouncillors: number;
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
    const formGroups =
        props.politicalGroups.length >= STANDARD_POLITICAL_GROUP_NAMES.length
            ? props.politicalGroups
            : mergePoliticalGroupsIntoCouncilSetupForm(props.politicalGroups);
    const standardGroups = formGroups.slice(0, STANDARD_POLITICAL_GROUP_NAMES.length);
    const maximumAdditionalGroupsReached = hasReachedMaximumAdditionalPoliticalGroupSlots(formGroups);
    const nextEmptyAdditionalGroupSlotIndex = getNextEmptyAdditionalPoliticalGroupSlotIndex(formGroups);
    const listedAdditionalGroups = getListedAdditionalPoliticalGroups(formGroups);

    return (
        <div className="committee_seats_section committee_seats_section_fit">
            <h3
                id="committee_seats_political_groups_panel_title"
                className="committee_seats_section_title committee_seats_political_groups_section_title"
            >
                {COMMITTEE_SEATS_PAGE.council_setup_political_groups_section_title}
            </h3>
            <fieldset
                className="committee_seats_political_groups_fieldset"
                aria-labelledby="committee_seats_political_groups_panel_title"
            >
                <div className="committee_seats_political_groups_panel">
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
                                    {listedAdditionalGroups.map(({groupIndex, politicalGroup}, listedIndex) => {
                                        const countInputId = "political_group_count_" + groupIndex;
                                        const firstAdditionalGroupRow = listedIndex === 0;

                                        return (
                                            <tr
                                                key={"additional_" + groupIndex}
                                                className={
                                                    firstAdditionalGroupRow
                                                        ? "committee_seats_groups_table_first_additional_group_row"
                                                        : undefined
                                                }
                                            >
                                                <td className="committee_seats_group_name_fixed">
                                                    {politicalGroup.name}
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
                                    {maximumAdditionalGroupsReached ? (
                                        <tr className="committee_seats_groups_table_add_group_row">
                                            <td
                                                className="committee_seats_additional_group_name_cell"
                                                colSpan={3}
                                            >
                                                <input
                                                    type="text"
                                                    className="committee_seats_additional_group_name_input"
                                                    value=""
                                                    disabled
                                                    aria-disabled="true"
                                                    aria-label={
                                                        COMMITTEE_SEATS_PAGE.maximum_additional_political_groups_reached
                                                    }
                                                    placeholder={
                                                        COMMITTEE_SEATS_PAGE.maximum_additional_political_groups_reached
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    ) : (
                                        nextEmptyAdditionalGroupSlotIndex !== null && (
                                            <AddPoliticalGroupRow
                                                slotIndex={nextEmptyAdditionalGroupSlotIndex}
                                                onAddGroup={(groupIndex, name) =>
                                                    props.onGroupNameChange(groupIndex, name)
                                                }
                                            />
                                        )
                                    )}
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
                    {councilFormHasVacancies(formGroups) && (
                        <div className="committee_seats_political_groups_panel_notes">
                            <p className="committee_seats_note">
                                {formatVacancyCouncillorCountEnteredMessage(
                                    getVacancyCouncillorCountFromForm(formGroups)
                                )}{" "}
                                {VACANCY_ALLOCATION_NOTE}
                            </p>
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
