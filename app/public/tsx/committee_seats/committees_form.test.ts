import {describe, expect, test} from "@jest/globals";
import {
    committeesForSetup,
    createEmptyCommitteesForm,
    formatCommitteeSeatsTotalMessage,
    getListedCommittees,
    mergeCommitteesIntoForm,
    validateCommitteesSetup,
} from "./committees_form";

describe("committees_form", () => {
    test("mergeCommitteesIntoForm prefills listed committees", () => {
        const formCommittees = mergeCommitteesIntoForm([
            {name: "Planning", seat_count: 9},
            {name: "Audit", seat_count: 7},
        ]);

        expect(getListedCommittees(formCommittees)).toHaveLength(2);
        expect(formCommittees[0].name).toBe("Planning");
        expect(formCommittees[0].seat_count).toBe(9);
    });

    test("validateCommitteesSetup requires sum to match expected total", () => {
        const formCommittees = mergeCommitteesIntoForm([
            {name: "Planning", seat_count: 9},
            {name: "Audit", seat_count: 7},
        ]);

        expect(validateCommitteesSetup(formCommittees, 16).valid).toBe(true);
        expect(validateCommitteesSetup(formCommittees, 20).valid).toBe(false);
    });

    test("committeesForSetup omits empty slots", () => {
        const formCommittees = createEmptyCommitteesForm();
        formCommittees[0] = {name: "Planning", seat_count: 10};

        expect(committeesForSetup(formCommittees)).toEqual([{name: "Planning", seat_count: 10}]);
    });

    test("formatCommitteeSeatsTotalMessage describes match and mismatch", () => {
        expect(formatCommitteeSeatsTotalMessage(144, 144)).toContain("144");
        expect(formatCommitteeSeatsTotalMessage(100, 144)).toContain("44 more");
    });
});
