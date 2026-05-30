import {describe, expect, test} from "@jest/globals";
import {mergePoliticalGroupsIntoCouncilSetupForm} from "./political_groups_form";
import {getExampleCouncilById} from "./example_councils";
import {
    getWizardDisplayStepLabel,
    WizardDisplayStep,
} from "./wizard_display_step";

describe("wizard_display_step labels", () => {
    test("getWizardDisplayStepLabel finds proportional calculation by step number", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const state = {
            wizard_step: 2,
            council_setup_substep: "choose_independent_allocation",
            political_groups: mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups),
        };

        expect(getWizardDisplayStepLabel(state, WizardDisplayStep.PartyAllocation)).toBe(
            "Proportional calculation"
        );
    });

    test("getWizardDisplayStepLabel finds next steps by step number", () => {
        const bristol = getExampleCouncilById("bristol");
        expect(bristol).toBeDefined();

        const state = {
            wizard_step: 3,
            council_setup_substep: "choose_independent_allocation",
            political_groups: mergePoliticalGroupsIntoCouncilSetupForm(bristol!.political_groups),
        };

        expect(getWizardDisplayStepLabel(state, WizardDisplayStep.NextSteps)).toBe("Next steps");
    });
});
