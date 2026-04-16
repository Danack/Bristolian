/**
 * Helper to call room content list endpoints (files, links, videos) with optional search params.
 * Builds URL with query string; does not modify the generated api_routes.
 */

export interface RoomContentSearchParams {
    limit?: number;
    title?: string;
    description?: string;
    created_at_after?: string;
    created_at_before?: string;
    document_timestamp_after?: string;
    document_timestamp_before?: string;
    tag_ids?: string[];
    /** DataType Order format, e.g. `+name` or `-document_date`. */
    order?: string;
}

function buildQueryString(params: RoomContentSearchParams): string {
    const searchParams = new URLSearchParams();
    if (params.limit != null) {
        searchParams.set("limit", String(params.limit));
    }
    if (params.title != null && params.title !== "") {
        searchParams.set("title", params.title);
    }
    if (params.description != null && params.description !== "") {
        searchParams.set("description", params.description);
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
    if (params.order != null && params.order !== "") {
        searchParams.set("order", params.order);
    }
    const query = searchParams.toString();
    return query === "" ? "" : "?" + query;
}

export interface FetchRoomContentListOptions {
    /** Append `_=${Date.now()}` so the browser does not reuse a cached GET after mutations (e.g. tag changes). */
    cacheBust?: boolean;
}

function appendCacheBustQuery(url: string): string {
    return url + (url.includes("?") ? "&" : "?") + "_=" + String(Date.now());
}

function fetchWithParams<T>(
    endpoint: string,
    params: RoomContentSearchParams,
    options?: FetchRoomContentListOptions
): Promise<T> {
    let url = endpoint + buildQueryString(params);
    if (options?.cacheBust) {
        url = appendCacheBustQuery(url);
    }
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
    params: RoomContentSearchParams,
    options?: FetchRoomContentListOptions
): Promise<GetRoomsFilesResponse> {
    return fetchWithParams<GetRoomsFilesResponse>(`/api/rooms/${roomId}/files`, params, options);
}

export function fetchRoomLinks(
    roomId: string,
    params: RoomContentSearchParams,
    options?: FetchRoomContentListOptions
): Promise<GetRoomsLinksResponse> {
    return fetchWithParams<GetRoomsLinksResponse>(`/api/rooms/${roomId}/links`, params, options);
}

export function fetchRoomVideos(
    roomId: string,
    params: RoomContentSearchParams,
    options?: FetchRoomContentListOptions
): Promise<GetRoomsVideosResponse> {
    return fetchWithParams<GetRoomsVideosResponse>(`/api/rooms/${roomId}/videos`, params, options);
}
