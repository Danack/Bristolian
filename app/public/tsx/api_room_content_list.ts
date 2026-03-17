/**
 * Helper to call room content list endpoints (files, links, videos) with optional search params.
 * Builds URL with query string; does not modify the generated api_routes.
 */

export interface RoomContentSearchParams {
    limit?: number;
    title?: string;
    created_at_after?: string;
    created_at_before?: string;
    document_timestamp_after?: string;
    document_timestamp_before?: string;
    tag_ids?: string[];
}

function buildQueryString(params: RoomContentSearchParams): string {
    const searchParams = new URLSearchParams();
    if (params.limit != null) {
        searchParams.set("limit", String(params.limit));
    }
    if (params.title != null && params.title !== "") {
        searchParams.set("title", params.title);
    }
    if (params.created_at_after != null && params.created_at_after !== "") {
        searchParams.set("created_at_after", params.created_at_after);
    }
    if (params.created_at_before != null && params.created_at_before !== "") {
        searchParams.set("created_at_before", params.created_at_before);
    }
    if (params.document_timestamp_after != null && params.document_timestamp_after !== "") {
        searchParams.set("document_timestamp_after", params.document_timestamp_after);
    }
    if (params.document_timestamp_before != null && params.document_timestamp_before !== "") {
        searchParams.set("document_timestamp_before", params.document_timestamp_before);
    }
    if (params.tag_ids != null && params.tag_ids.length > 0) {
        searchParams.set("tag_ids", params.tag_ids.join(","));
    }
    const query = searchParams.toString();
    return query === "" ? "" : "?" + query;
}

function fetchWithParams<T>(endpoint: string, params: RoomContentSearchParams): Promise<T> {
    const url = endpoint + buildQueryString(params);
    return fetch(url).then((response: Response) => {
        if (response.status !== 200) {
            throw new Error("Server failed to return an OK response.");
        }
        return response.json() as Promise<T>;
    });
}

export interface GetRoomsFilesResponse {
    data: { files: unknown[] };
}
export interface GetRoomsLinksResponse {
    data: { links: unknown[] };
}
export interface GetRoomsVideosResponse {
    data: { videos: unknown[] };
}

export function fetchRoomFiles(
    roomId: string,
    params: RoomContentSearchParams
): Promise<GetRoomsFilesResponse> {
    return fetchWithParams<GetRoomsFilesResponse>(`/api/rooms/${roomId}/files`, params);
}

export function fetchRoomLinks(
    roomId: string,
    params: RoomContentSearchParams
): Promise<GetRoomsLinksResponse> {
    return fetchWithParams<GetRoomsLinksResponse>(`/api/rooms/${roomId}/links`, params);
}

export function fetchRoomVideos(
    roomId: string,
    params: RoomContentSearchParams
): Promise<GetRoomsVideosResponse> {
    return fetchWithParams<GetRoomsVideosResponse>(`/api/rooms/${roomId}/videos`, params);
}
