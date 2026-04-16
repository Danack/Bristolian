import { h } from "preact";
import { RoomTag } from "./generated/types";
import { ROOM_CONTENT_LIST_DEFAULT_LIMIT } from "./generated/constants";

/** Matches backend {@see OptionalRoomContentListOrder} known names. */
export type RoomFilesListSortColumn = "name" | "size" | "added" | "document_date";

export interface RoomContentSearchFormProps {
    title: string;
    description: string;
    createdAfter: string;
    createdBefore: string;
    documentAfter: string;
    documentBefore: string;
    limit: number;
    roomTags: RoomTag[];
    selectedTagIds: Set<string>;

    /** Placeholder text for the title field (e.g. "Filter by name" vs "Filter by title"). */
    titlePlaceholder: string;

    onTitleChange: (value: string) => void;
    onDescriptionChange: (value: string) => void;
    onCreatedAfterChange: (value: string) => void;
    onCreatedBeforeChange: (value: string) => void;
    onDocumentAfterChange: (value: string) => void;
    onDocumentBeforeChange: (value: string) => void;
    onLimitChange: (value: number) => void;
    onToggleTag: (tagId: string) => void;
    onClear: () => void;

    /** When set, show file list sort controls (room files panel only). */
    sortColumn?: RoomFilesListSortColumn | null;
    sortDirection?: "asc" | "desc" | null;
    onSortChange?: (
        column: RoomFilesListSortColumn | null,
        direction: "asc" | "desc" | null
    ) => void;
}

export function RoomContentSearchForm(props: RoomContentSearchFormProps) {
    const {
        title,
        description,
        createdAfter,
        createdBefore,
        documentAfter,
        documentBefore,
        limit,
        roomTags,
        selectedTagIds,
        titlePlaceholder,
        onTitleChange,
        onDescriptionChange,
        onCreatedAfterChange,
        onCreatedBeforeChange,
        onDocumentAfterChange,
        onDocumentBeforeChange,
        onLimitChange,
        onToggleTag,
        onClear,
        sortColumn = null,
        sortDirection = null,
        onSortChange,
    } = props;

    const sortControls =
        onSortChange !== undefined ? (
            <div className="room_content_search_sort_row">
                <label className="room_content_search_sort">
                    Sort by{" "}
                    <select
                        value={sortColumn ?? ""}
                        onChange={(event) => {
                            const value = (event.target as HTMLSelectElement).value;
                            if (value === "") {
                                onSortChange(null, null);
                                return;
                            }
                            const column = value as RoomFilesListSortColumn;
                            onSortChange(column, sortDirection ?? "asc");
                        }}
                    >
                        <option value="">Default (added, newest first)</option>
                        <option value="name">Name</option>
                        <option value="size">Size</option>
                        <option value="added">Added</option>
                        <option value="document_date">Document date</option>
                    </select>
                </label>
                <label className="room_content_search_sort">
                    Direction{" "}
                    <select
                        disabled={sortColumn === null}
                        value={sortDirection ?? "asc"}
                        onChange={(event) => {
                            const value = (event.target as HTMLSelectElement).value as "asc" | "desc";
                            if (sortColumn !== null) {
                                onSortChange(sortColumn, value);
                            }
                        }}
                    >
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </label>
                <div />
            </div>
        ) : null;

    return (
        <div className="room_content_search_form">
            {/* Row 1: Title / Created after / Created before */}
            <label>
                Title{" "}
                <input
                    type="text"
                    value={title}
                    onInput={(e) => onTitleChange((e.target as HTMLInputElement).value)}
                    placeholder={titlePlaceholder}
                />
            </label>
            <label>
                Created after{" "}
                <input
                    type="date"
                    value={createdAfter}
                    onInput={(e) => onCreatedAfterChange((e.target as HTMLInputElement).value)}
                />
            </label>
            <label>
                Created before{" "}
                <input
                    type="date"
                    value={createdBefore}
                    onInput={(e) => onCreatedBeforeChange((e.target as HTMLInputElement).value)}
                />
            </label>

            {/* Row 2: Description / Document date after / Document date before */}
            <label>
                Description{" "}
                <input
                    type="text"
                    value={description}
                    onInput={(e) => onDescriptionChange((e.target as HTMLInputElement).value)}
                    placeholder="Filter by description"
                />
            </label>
            <label>
                Document date after{" "}
                <input
                    type="date"
                    value={documentAfter}
                    onInput={(e) => onDocumentAfterChange((e.target as HTMLInputElement).value)}
                />
            </label>
            <label>
                Document date before{" "}
                <input
                    type="date"
                    value={documentBefore}
                    onInput={(e) => onDocumentBeforeChange((e.target as HTMLInputElement).value)}
                />
            </label>
            <label>
                Limit{" "}
                <input
                    type="number"
                    min={1}
                    max={1000}
                    value={limit}
                    onInput={(e) => {
                        const value = parseInt((e.target as HTMLInputElement).value, 10);
                        onLimitChange(Number.isNaN(value) ? ROOM_CONTENT_LIST_DEFAULT_LIMIT : value);
                    }}
                />
            </label>

            {/* Row 3: Filter by tags / Clear (and optional empty third cell) */}
            {roomTags.length > 0 ? (
                <fieldset className="room_search_tags">
                    <legend>Filter by tags (must have all)</legend>
                    {roomTags.map((tag) => (
                        <label key={tag.tag_id}>
                            <input
                                type="checkbox"
                                checked={selectedTagIds.has(tag.tag_id)}
                                onChange={() => onToggleTag(tag.tag_id)}
                            />
                            {tag.text}
                        </label>
                    ))}
                </fieldset>
            ) : (
                <div />
            )}
            <div className="room_search_actions">
                <button type="button" className="button_standard" onClick={onClear}>
                    Clear
                </button>
            </div>
            <div />
            {sortControls}
        </div>
    );
}

