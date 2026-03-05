import { h, Component, Fragment } from "preact";
import { api, GetRoomsVideosResponse } from "./generated/api_routes";
import { RoomVideoWithTags, createRoomVideoWithTags, createRoomTag, RoomTag } from "./generated/types";
import { get_logged_in, subscribe_logged_in } from "./store";
import { setVideoTags } from "./api_room_entity_tags";

/** Set to true when transcript fetching is fixed. */
const TRANSCRIPT_ENABLED = false;

const YOUTUBE_IFRAME_API_URL = "https://www.youtube.com/iframe_api";

/** Minimal type for YouTube IFrame API player. */
interface YouTubePlayer {
    getCurrentTime(): number;
    destroy(): void;
}

function formatTimestamp(seconds: number): string {
    const totalSeconds = Math.floor(seconds);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const secs = totalSeconds % 60;
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, "0")}:${secs.toString().padStart(2, "0")}`;
    }
    return `${minutes}:${secs.toString().padStart(2, "0")}`;
}

/**
 * Parse timestamp string to seconds. Accepts: "45", "1:23" (M:SS), "1:00:00" (H:MM:SS).
 * @return seconds or null if invalid
 */
function parseTimestampToSeconds(input: string): number | null {
    const trimmed = input.trim();
    if (trimmed === "") return null;
    const parts = trimmed.split(":");
    if (parts.length === 1) {
        const seconds = parseInt(parts[0], 10);
        return Number.isNaN(seconds) || seconds < 0 ? null : seconds;
    }
    if (parts.length === 2) {
        const minutes = parseInt(parts[0], 10);
        const seconds = parseInt(parts[1], 10);
        if (Number.isNaN(minutes) || Number.isNaN(seconds) || minutes < 0 || seconds < 0 || seconds >= 60) return null;
        return minutes * 60 + seconds;
    }
    if (parts.length === 3) {
        const hours = parseInt(parts[0], 10);
        const minutes = parseInt(parts[1], 10);
        const seconds = parseInt(parts[2], 10);
        if (
            Number.isNaN(hours) ||
            Number.isNaN(minutes) ||
            Number.isNaN(seconds) ||
            hours < 0 ||
            minutes < 0 ||
            minutes >= 60 ||
            seconds < 0 ||
            seconds >= 60
        )
            return null;
        return hours * 3600 + minutes * 60 + seconds;
    }
    return null;
}

function parseVttToCues(vtt: string): VttCue[] {
    const cues: VttCue[] = [];
    const lines = vtt.split(/\r?\n/);
    let i = 0;
    while (i < lines.length) {
        const line = lines[i];
        const match = line.match(/^(\d{2}):(\d{2}):(\d{2})\.(\d{3})\s*-->\s*(\d{2}):(\d{2}):(\d{2})\.(\d{3})/);
        if (match) {
            const startSeconds =
                parseInt(match[1], 10) * 3600 +
                parseInt(match[2], 10) * 60 +
                parseInt(match[3], 10) +
                parseInt(match[4], 10) / 1000;
            const endSeconds =
                parseInt(match[5], 10) * 3600 +
                parseInt(match[6], 10) * 60 +
                parseInt(match[7], 10) +
                parseInt(match[8], 10) / 1000;
            const textLines: string[] = [];
            i++;
            while (i < lines.length && lines[i].trim() !== "") {
                textLines.push(lines[i]);
                i++;
            }
            cues.push({ startSeconds, endSeconds, text: textLines.join(" ").trim() });
        }
        i++;
    }
    return cues;
}

export interface RoomVideosPanelProps {
    room_id: string;
}

interface TranscriptItem {
    id: string;
    transcript_number: number;
    language: string | null;
    created_at: string;
}

interface VttCue {
    startSeconds: number;
    endSeconds: number;
    text: string;
}

interface RoomVideosPanelState {
    videos: RoomVideoWithTags[];
    error: string | null;
    logged_in: boolean;
    editingVideoId: string | null;
    roomTags: RoomTag[];
    selectedTagIds: Set<string>;
    tagsSaveInProgress: boolean;
    addUrl: string;
    addTitle: string;
    addDescription: string;
    addError: string | null;
    addSuccess: string | null;
    clipMarkedStartSeconds: number | null;
    clipMarkedEndSeconds: number | null;
    clipStartInput: string;
    clipEndInput: string;
    clipTitle: string;
    clipDescription: string;
    clipError: string | null;
    playingVideo: RoomVideoWithTags | null;
    transcripts: TranscriptItem[];
    selectedTranscriptId: string | null;
    vttContent: string | null;
    vttCues: VttCue[];
    transcriptFetching: boolean;
    autoScrollTranscript: boolean;
    embedKey: number; // force iframe re-mount when seek changes
    embedStartSeconds: number;
    currentTimeSeconds: number | null;
}

function getDefaultState(): RoomVideosPanelState {
    return {
        videos: [],
        error: null,
        logged_in: get_logged_in(),
        editingVideoId: null,
        roomTags: [],
        selectedTagIds: new Set(),
        tagsSaveInProgress: false,
        addUrl: "",
        addTitle: "",
        addDescription: "",
        addError: null,
        addSuccess: null,
        clipMarkedStartSeconds: null,
        clipMarkedEndSeconds: null,
        clipStartInput: "",
        clipEndInput: "",
        clipTitle: "",
        clipDescription: "",
        clipError: null,
        playingVideo: null,
        transcripts: [],
        selectedTranscriptId: null,
        vttContent: null,
        vttCues: [],
        transcriptFetching: false,
        autoScrollTranscript: true,
        embedKey: 0,
        embedStartSeconds: 0,
        currentTimeSeconds: null,
    };
}

export class RoomVideosPanel extends Component<RoomVideosPanelProps, RoomVideosPanelState> {
    unsubscribe_logged_in: (() => void) | null = null;
    embedContainerRef: HTMLDivElement | null = null;
    ytPlayer: YouTubePlayer | null = null;
    timeUpdateIntervalId: ReturnType<typeof setInterval> | null = null;

    constructor(props: RoomVideosPanelProps) {
        super(props);
        this.state = getDefaultState();
    }

    componentDidMount() {
        this.refreshVideos();
        this.unsubscribe_logged_in = subscribe_logged_in((logged_in: boolean) => {
            this.setState({ logged_in });
        });
    }

    componentDidUpdate(prevProps: RoomVideosPanelProps, prevState: RoomVideosPanelState) {
        const { playingVideo } = this.state;
        if (!playingVideo) {
            if (prevState.playingVideo) {
                this.destroyYouTubePlayer();
            }
            return;
        }
        if (!prevState.playingVideo || prevState.playingVideo.id !== playingVideo.id) {
            this.setState({ currentTimeSeconds: null });
            this.destroyYouTubePlayer();
        }
        if (playingVideo && this.embedContainerRef && !this.ytPlayer) {
            this.ensureYouTubeApi().then(() => this.createYouTubePlayer());
        }
    }

    componentWillUnmount() {
        if (this.unsubscribe_logged_in) {
            this.unsubscribe_logged_in();
            this.unsubscribe_logged_in = null;
        }
        this.destroyYouTubePlayer();
    }

    ensureYouTubeApi(): Promise<void> {
        if (typeof window !== "undefined" && (window as unknown as { YT?: unknown }).YT) {
            return Promise.resolve();
        }
        return new Promise((resolve) => {
            (window as unknown as { onYouTubeIframeAPIReady?: () => void }).onYouTubeIframeAPIReady = () => resolve();
            const script = document.createElement("script");
            script.src = YOUTUBE_IFRAME_API_URL;
            script.async = true;
            document.head.appendChild(script);
        });
    }

    createYouTubePlayer() {
        const { playingVideo, embedStartSeconds } = this.state;
        if (!playingVideo || !this.embedContainerRef) return;
        const YT = (window as unknown as { YT?: { Player: new (elementId: string, opts: unknown) => YouTubePlayer } }).YT;
        if (!YT) return;
        this.ytPlayer = new YT.Player("room_video_yt_player", {
            videoId: playingVideo.youtube_video_id,
            playerVars: { start: Math.floor(embedStartSeconds) },
            events: {
                onReady: (event: { target: YouTubePlayer }) => {
                    this.startTimePolling(event.target);
                },
            },
        });
    }

    startTimePolling(player: YouTubePlayer) {
        this.ytPlayer = player;
        this.timeUpdateIntervalId = setInterval(() => {
            if (!this.ytPlayer) return;
            const seconds = this.ytPlayer.getCurrentTime();
            if (typeof seconds === "number" && !Number.isNaN(seconds)) {
                this.setState({ currentTimeSeconds: seconds });
            }
        }, 500);
    }

    destroyYouTubePlayer() {
        if (this.timeUpdateIntervalId !== null) {
            clearInterval(this.timeUpdateIntervalId);
            this.timeUpdateIntervalId = null;
        }
        if (this.ytPlayer && typeof this.ytPlayer.destroy === "function") {
            this.ytPlayer.destroy();
            this.ytPlayer = null;
        }
        this.setState({ currentTimeSeconds: null });
    }

    refreshVideos() {
        api.rooms.videos(this.props.room_id)
            .then((data: GetRoomsVideosResponse) => this.processData(data))
            .catch((err) => this.setState({ error: String(err) }));
    }

    processData(data: GetRoomsVideosResponse) {
        if (data.data.videos === undefined) {
            this.setState({ error: "Server response did not contain 'videos'." });
            return;
        }
        const videos: RoomVideoWithTags[] = data.data.videos.map((v) => createRoomVideoWithTags(v));
        this.setState({ videos, error: null });
    }

    handleAddVideoResponse(response: Response, data: { result?: string; error?: string; errors?: string[] }) {
        if (!response.ok) {
            const message =
                data?.error ??
                (Array.isArray(data?.errors) ? data.errors.join(" ") : null) ??
                (response.statusText || "Failed to add video");
            this.setState({ addError: message });
            return;
        }
        if (data.result === "error") {
            this.setState({ addError: data.error || "Failed to add video" });
            return;
        }
        this.setState({
            addUrl: "",
            addTitle: "",
            addDescription: "",
            addSuccess: "Video added",
        });
        this.refreshVideos();
    }

    addVideo() {
        const { addUrl, addTitle, addDescription } = this.state;
        this.setState({ addError: null, addSuccess: null });
        fetch(`/api/rooms/${this.props.room_id}/videos`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url: addUrl, title: addTitle || null, description: addDescription || null }),
        })
            .then((response) => {
                return response.json().then((data) => ({ response, data }));
            })
            .then(({ response, data }) => this.handleAddVideoResponse(response, data))
            .catch((err) => this.setState({ addError: String(err) }));
    }

    markClipTimestamp() {
        const currentSeconds = this.state.currentTimeSeconds ?? this.state.embedStartSeconds;
        const currentFormatted = formatTimestamp(currentSeconds);
        this.setState((prev) => {
            const { clipMarkedStartSeconds, clipMarkedEndSeconds } = prev;
            if (clipMarkedStartSeconds === null) {
                return {
                    clipMarkedStartSeconds: currentSeconds,
                    clipMarkedEndSeconds: null,
                    clipStartInput: currentFormatted,
                    clipEndInput: "",
                };
            }
            if (clipMarkedEndSeconds === null) {
                if (currentSeconds >= clipMarkedStartSeconds) {
                    return {
                        clipMarkedEndSeconds: currentSeconds,
                        clipEndInput: currentFormatted,
                    };
                }
                return {
                    clipMarkedStartSeconds: currentSeconds,
                    clipMarkedEndSeconds: clipMarkedStartSeconds,
                    clipStartInput: currentFormatted,
                    clipEndInput: formatTimestamp(clipMarkedStartSeconds),
                };
            }
            return {
                clipMarkedStartSeconds: currentSeconds,
                clipMarkedEndSeconds: null,
                clipStartInput: currentFormatted,
                clipEndInput: "",
            };
        });
    }

    createClip() {
        const { playingVideo, clipStartInput, clipEndInput, clipTitle, clipDescription } = this.state;
        if (!playingVideo) return;
        const startSeconds = parseTimestampToSeconds(clipStartInput);
        const endSeconds = parseTimestampToSeconds(clipEndInput);
        if (startSeconds === null || endSeconds === null) {
            this.setState({ clipError: "Start and end must be in timestamp format (e.g. 1:23 or 1:00:00)" });
            return;
        }
        this.setState({ clipError: null });
        fetch(`/api/rooms/${this.props.room_id}/videos/clips`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                room_video_id: playingVideo.id,
                start_seconds: startSeconds,
                end_seconds: endSeconds,
                title: clipTitle || null,
                description: clipDescription || null,
            }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.result === "error") {
                    this.setState({ clipError: data.error || "Failed to create clip" });
                    return;
                }
                this.setState({
                    clipMarkedStartSeconds: null,
                    clipMarkedEndSeconds: null,
                    clipStartInput: "",
                    clipEndInput: "",
                    clipTitle: "",
                    clipDescription: "",
                });
                this.refreshVideos();
            })
            .catch((err) => this.setState({ clipError: String(err) }));
    }

    openEditTags(video: RoomVideoWithTags) {
        const selectedTagIds = new Set(video.tags.map((t) => t.tag_id));
        this.setState({ editingVideoId: video.id, selectedTagIds });
        api.rooms.tags(this.props.room_id)
            .then((data) => {
                const roomTags = data.data.tags.map((t) => createRoomTag(t));
                this.setState({ roomTags });
            })
            .catch(() => this.setState({ roomTags: [] }));
    }

    closeEditTags() {
        this.setState({ editingVideoId: null, roomTags: [], selectedTagIds: new Set() });
    }

    toggleTagForEdit(tag_id: string) {
        const next = new Set(this.state.selectedTagIds);
        if (next.has(tag_id)) next.delete(tag_id);
        else next.add(tag_id);
        this.setState({ selectedTagIds: next });
    }

    saveVideoTags() {
        const { editingVideoId, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingVideoId || tagsSaveInProgress) return;
        this.setState({ tagsSaveInProgress: true });
        setVideoTags(this.props.room_id, editingVideoId, { tag_ids: Array.from(selectedTagIds) })
            .then(() => {
                this.closeEditTags();
                this.setState({ tagsSaveInProgress: false });
                this.refreshVideos();
            })
            .catch(() => this.setState({ tagsSaveInProgress: false }));
    }

    loadTranscriptsForVideo(room_video_id: string) {
        this.setState({ transcripts: [], selectedTranscriptId: null, vttContent: null, vttCues: [] });
        fetch(`/api/room-videos/${room_video_id}/transcripts`)
            .then((r) => r.json())
            .then((data) => {
                if (data.result === "success" && Array.isArray(data.data.transcripts)) {
                    this.setState({ transcripts: data.data.transcripts });
                }
            })
            .catch(() => {});
    }

    loadTranscriptContent(room_video_id: string, transcript_id: string) {
        fetch(`/api/room-videos/${room_video_id}/transcripts/${transcript_id}`)
            .then((r) => r.json())
            .then((data) => {
                if (data.result === "success" && data.data.vtt_content != null) {
                    const cues = parseVttToCues(data.data.vtt_content);
                    this.setState({ vttContent: data.data.vtt_content, vttCues: cues, selectedTranscriptId: transcript_id });
                }
            })
            .catch(() => {});
    }

    fetchTranscript(room_video_id: string) {
        this.setState({ transcriptFetching: true });
        fetch(`/api/room-videos/${room_video_id}/transcripts/fetch`, { method: "POST" })
            .then((r) => r.json())
            .then((data) => {
                this.setState({ transcriptFetching: false });
                if (data.result === "success" && data.data.transcript_id) {
                    this.loadTranscriptsForVideo(room_video_id);
                    this.loadTranscriptContent(room_video_id, data.data.transcript_id);
                }
            })
            .catch(() => this.setState({ transcriptFetching: false }));
    }

    seekToSeconds(seconds: number) {
        this.setState((s) => ({ embedStartSeconds: seconds, embedKey: (s.embedKey ?? 0) + 1 }));
    }

    youtubeUrl(v: RoomVideoWithTags): string {
        return `https://www.youtube.com/watch?v=${v.youtube_video_id}`;
    }

    embedUrl(v: RoomVideoWithTags, startSeconds?: number): string {
        const start = startSeconds ?? v.start_seconds ?? 0;
        return `https://www.youtube.com/embed/${v.youtube_video_id}?start=${Math.floor(start)}`;
    }

    renderVideoRow(video: RoomVideoWithTags) {
        const title = video.title || video.youtube_video_id || video.id;
        const isClip = video.parent_room_video_id != null;
        const tagsBlock =
            video.tags.length > 0 ? (
                <span className="room_entity_tags">
                    {video.tags.map((t) => (
                        <span key={t.tag_id} className="room_entity_tag_chip">
                            {t.text}
                        </span>
                    ))}
                </span>
            ) : (
                <span className="room_entity_tags empty">—</span>
            );
        return (
            <tr key={video.id}>
                <td>
                    {isClip && (
                        <span className="room_video_clip_badge">Clip</span>
                    )}
                    <span>{title}</span>
                    {video.description && <div className="room_video_description">{video.description}</div>}
                </td>
                <td>{tagsBlock}</td>
                {this.state.logged_in && (
                    <td>
                        <a
                            href={this.youtubeUrl(video)}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="button_standard button_chat"
                        >
                            YouTube
                        </a>
                        <button
                            className="button_standard button_chat"
                            onClick={() => {
                                const start = video.start_seconds ?? 0;
                                this.setState({
                                    playingVideo: video,
                                    embedStartSeconds: start,
                                    embedKey: (this.state.embedKey ?? 0) + 1,
                                    clipMarkedStartSeconds: null,
                                    clipMarkedEndSeconds: null,
                                    clipStartInput: "",
                                    clipEndInput: "",
                                    clipTitle: "",
                                    clipDescription: "",
                                });
                                if (TRANSCRIPT_ENABLED) {
                                    this.loadTranscriptsForVideo(video.id);
                                }
                            }}
                        >
                            Play
                        </button>
                        <button className="button_standard button_chat" onClick={() => this.openEditTags(video)}>
                            Edit tags
                        </button>
                    </td>
                )}
            </tr>
        );
    }

    renderTranscriptActions(
        playingVideo: RoomVideoWithTags,
        transcripts: TranscriptItem[],
        selectedTranscriptId: string | null,
        transcriptFetching: boolean,
    ) {
        const transcriptButtons = transcripts.map((transcript) => (
            <button
                key={transcript.id}
                className={"button_standard " + (selectedTranscriptId === transcript.id ? "selected" : "")}
                onClick={() => this.loadTranscriptContent(playingVideo.id, transcript.id)}
            >
                Transcript {transcript.transcript_number}
                {transcript.language ? ` (${transcript.language})` : ""}
            </button>
        ));

        return (
            // @ts-ignore Fragment should be valid
            <Fragment>
                {transcriptButtons}
                <button
                    className="button_standard"
                    onClick={() => this.fetchTranscript(playingVideo.id)}
                    disabled={transcriptFetching}
                >
                    {transcriptFetching ? "Fetching…" : "Fetch transcript"}
                </button>
            </Fragment>
        );
    }

    renderEditTagsModal() {
        const { editingVideoId, roomTags, selectedTagIds, tagsSaveInProgress } = this.state;
        if (!editingVideoId) return null;
        return (
            <div
                className="room_edit_tags_modal_overlay"
                onClick={() => !tagsSaveInProgress && this.closeEditTags()}
            >
                <div className="room_edit_tags_modal" onClick={(e) => e.stopPropagation()}>
                    <h3>Edit tags</h3>
                    {roomTags.length === 0 ? (
                        <p>Loading room tags…</p>
                    ) : (
                        <div className="room_edit_tags_checkboxes">
                            {roomTags.map((tag) => (
                                <label key={tag.tag_id}>
                                    <input
                                        type="checkbox"
                                        checked={selectedTagIds.has(tag.tag_id)}
                                        onChange={() => this.toggleTagForEdit(tag.tag_id)}
                                    />
                                    {tag.text}
                                </label>
                            ))}
                        </div>
                    )}
                    <div className="room_edit_tags_actions">
                        <button className="button_standard" onClick={() => this.saveVideoTags()} disabled={tagsSaveInProgress}>
                            Save
                        </button>
                        <button className="button_standard" onClick={() => this.closeEditTags()} disabled={tagsSaveInProgress}>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    render(props: RoomVideosPanelProps, state: RoomVideosPanelState) {
        const playingVideo = state.playingVideo;
        return (
            <div className="room_videos_panel_react">
                <h2>Video</h2>
                {state.error && <div className="error">{state.error}</div>}
                {playingVideo ? (
                    <div className="room_video_playing_block">
                        <div className="room_video_playing_content">
                            <div className="room_video_player_with_controls">
                                <div id="room_video_yt_player" className="room_video_embed_container" ref={(el) => { this.embedContainerRef = el; }} />
                                <div className="room_video_player_controls">
                                    <div className="room_video_current_time">
                                        {state.currentTimeSeconds != null
                                            ? formatTimestamp(state.currentTimeSeconds)
                                            : formatTimestamp(state.embedStartSeconds)}
                                    </div>
                                    {state.logged_in && !playingVideo.parent_room_video_id && (
                                        <Fragment>
                                            <button
                                                type="button"
                                                className="button_standard room_video_mark_clip_ts"
                                                onClick={() => this.markClipTimestamp()}
                                            >
                                                Mark clip timestamp
                                            </button>
                                            <label className="room_video_clip_field">
                                                <span className="room_video_clip_field_label">Start (e.g. 1:23 or 1:00:00)</span>
                                                <input
                                                    type="text"
                                                    placeholder="0:00"
                                                    value={state.clipStartInput}
                                                    onInput={(e) => this.setState({ clipStartInput: (e.target as HTMLInputElement).value })}
                                                />
                                            </label>
                                            <label className="room_video_clip_field">
                                                <span className="room_video_clip_field_label">End (e.g. 2:45 or 1:00:00)</span>
                                                <input
                                                    type="text"
                                                    placeholder="0:00"
                                                    value={state.clipEndInput}
                                                    onInput={(e) => this.setState({ clipEndInput: (e.target as HTMLInputElement).value })}
                                                />
                                            </label>
                                            <label className="room_video_clip_field">
                                                <span className="room_video_clip_field_label">Title</span>
                                                <input
                                                    type="text"
                                                    placeholder="Clip title (optional)"
                                                    value={state.clipTitle}
                                                    onInput={(e) => this.setState({ clipTitle: (e.target as HTMLInputElement).value })}
                                                />
                                            </label>
                                            <label className="room_video_clip_field">
                                                <span className="room_video_clip_field_label">Description (optional)</span>
                                                <textarea
                                                    placeholder="Clip description"
                                                    value={state.clipDescription}
                                                    onInput={(e) => this.setState({ clipDescription: (e.target as HTMLTextAreaElement).value })}
                                                    rows={2}
                                                />
                                            </label>
                                            <button
                                                type="button"
                                                className="button_standard"
                                                onClick={() => this.createClip()}
                                                disabled={
                                                    parseTimestampToSeconds(state.clipStartInput) === null ||
                                                    parseTimestampToSeconds(state.clipEndInput) === null
                                                }
                                            >
                                                Create clip
                                            </button>
                                            {state.clipError && (
                                                <div className="error room_video_clip_error">{state.clipError}</div>
                                            )}
                                        </Fragment>
                                    )}
                                </div>
                            </div>
                            {TRANSCRIPT_ENABLED && (
                                <div className="room_video_transcript_section">
                                    <h3>Transcript</h3>
                                    <div className="room_video_transcript_actions">
                                        {this.renderTranscriptActions(
                                            playingVideo,
                                            state.transcripts,
                                            state.selectedTranscriptId,
                                            state.transcriptFetching,
                                        )}
                                    </div>
                                    <label className="room_video_auto_scroll">
                                        <input
                                            type="checkbox"
                                            checked={state.autoScrollTranscript}
                                            onChange={(e) => this.setState({ autoScrollTranscript: (e.target as HTMLInputElement).checked })}
                                        />
                                        Auto-scroll transcript
                                    </label>
                                    <div className="room_video_transcript_view">
                                        {state.vttCues.length > 0 ? (
                                            state.vttCues.map((cue, idx) => (
                                                <div
                                                    key={idx}
                                                    className="room_video_transcript_cue"
                                                    data-start={cue.startSeconds}
                                                    onClick={() => this.seekToSeconds(cue.startSeconds)}
                                                >
                                                    <span className="room_video_transcript_cue_time">
                                                        {formatTimestamp(cue.startSeconds)}
                                                    </span>
                                                    {cue.text}
                                                </div>
                                            ))
                                        ) : state.selectedTranscriptId ? (
                                            <p>Loading…</p>
                                        ) : state.transcripts.length === 0 ? (
                                            <p>No transcripts. Click &quot;Fetch transcript&quot; to get captions from YouTube.</p>
                                        ) : (
                                            <p>Select a transcript above.</p>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="room_video_close_player_wrapper">
                            <button
                                className="button_standard"
                                onClick={() => this.setState({ playingVideo: null, clipMarkedStartSeconds: null, clipMarkedEndSeconds: null, clipStartInput: "", clipEndInput: "" })}
                            >
                                Close player
                            </button>
                        </div>
                    </div>
                ) : (
                    <Fragment>
                        <div className="room_videos_list">
                            {state.videos.length === 0 ? (
                                <p>No videos.</p>
                            ) : (
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>Title</td>
                                            <td>Tags</td>
                                            {state.logged_in && <td />}
                                        </tr>
                                        {state.videos.map((v) => this.renderVideoRow(v))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                        <button className="button_standard" onClick={() => this.refreshVideos()}>
                            Refresh
                        </button>

                        {state.logged_in && (
                            <div className="room_videos_add_section">
                                <h3>Add video</h3>
                                <div className="room_videos_add_form">
                                    <input
                                        type="text"
                                        placeholder="YouTube URL"
                                        value={state.addUrl}
                                        onInput={(e) => this.setState({ addUrl: (e.target as HTMLInputElement).value })}
                                    />
                                    <input
                                        type="text"
                                        placeholder="Title (optional)"
                                        value={state.addTitle}
                                        onInput={(e) => this.setState({ addTitle: (e.target as HTMLInputElement).value })}
                                    />
                                    <textarea
                                        placeholder="Description (optional)"
                                        value={state.addDescription}
                                        onInput={(e) => this.setState({ addDescription: (e.target as HTMLTextAreaElement).value })}
                                    />
                                    <button className="button_standard" onClick={() => this.addVideo()}>
                                        Add video
                                    </button>
                                </div>
                                {state.addError && <div className="error">{state.addError}</div>}
                                {state.addSuccess && <div className="success">{state.addSuccess}</div>}
                            </div>
                        )}
                    </Fragment>
                )}

                {this.renderEditTagsModal()}
            </div>
        );
    }
}
