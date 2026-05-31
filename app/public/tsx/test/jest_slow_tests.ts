import {describe, test} from "@jest/globals";

/**
 * When false (default), {@link describeSlow} and {@link testSlow} are skipped.
 * Set `JEST_RUN_SLOW_TESTS=1` to include them — e.g. `npm run test:all`.
 */
export function runSlowTests(): boolean {
    const flag = process.env.JEST_RUN_SLOW_TESTS;

    return flag === "1" || flag === "true";
}

/** Like `describe`, skipped unless `JEST_RUN_SLOW_TESTS=1`. */
export const describeSlow = runSlowTests() ? describe : describe.skip;

/** Like `test`, skipped unless `JEST_RUN_SLOW_TESTS=1`. */
export const testSlow = runSlowTests() ? test : test.skip;
