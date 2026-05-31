/** Shared seat matrix helpers for committee distribution. */

/** Matrix cells must be numbers; form or JSON data can arrive as numeric strings. */
export function matrixSeatCount(value: unknown): number {
    const parsed = Number(value);

    if (!Number.isFinite(parsed)) {
        return 0;
    }

    return parsed;
}

export function buildEmptyMatrix(
    groupNames: string[],
    committeeNames: string[]
): Record<string, Record<string, number>> {
    const matrix: Record<string, Record<string, number>> = {};

    for (const groupName of groupNames) {
        matrix[groupName] = {};
        for (const committeeName of committeeNames) {
            matrix[groupName][committeeName] = 0;
        }
    }

    return matrix;
}

export function columnTotalForMatrix(
    matrix: Record<string, Record<string, number>>,
    committeeName: string
): number {
    let total = 0;

    for (const groupName of Object.keys(matrix)) {
        total += matrixSeatCount(matrix[groupName]?.[committeeName]);
    }

    return total;
}

export function cloneFloorMatrix(
    floorMatrix: Record<string, Record<string, number>>
): Record<string, Record<string, number>> {
    const matrix: Record<string, Record<string, number>> = {};

    for (const groupName of Object.keys(floorMatrix)) {
        matrix[groupName] = {...floorMatrix[groupName]};
    }

    return matrix;
}

export function rowTotalForGroup(
    matrix: Record<string, Record<string, number>>,
    groupName: string
): number {
    const groupRow = matrix[groupName];
    if (groupRow === undefined) {
        return 0;
    }

    return Object.values(groupRow).reduce(
        (total, seats) => total + matrixSeatCount(seats),
        0
    );
}
