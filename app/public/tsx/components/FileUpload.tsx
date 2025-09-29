// FileUpload.tsx
import { h, Component } from "preact";
import * as exifr from "exifr";

export interface FileUploadProps {
  uploadUrl: string; // endpoint to send files to
  formFieldName: string; // name used in FormData
  allowedTypes?: string[];
  allowedExtensions?: string[];
  extraFormData?: Record<string, string>;
  onUploadSuccess?: (data: any) => void;
  onUploadError?: (error: string) => void;
}

interface FileUploadState {
  selectedFile: File | null;
  uploadProgress: number | null;
  error: string | null;

  gps_latitude: number|null,
  gps_longitude: number|null,
  debug: string,

}

export class FileUpload extends Component<FileUploadProps, FileUploadState> {
  constructor(props: FileUploadProps) {
    super(props);
    this.state = {
      selectedFile: null,
      uploadProgress: null,
      error: null,
      gps_latitude: null,
      gps_longitude: null,
      debug: "no debug yet"
    };
  }

  handleDragEnter = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
  };

  handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
  };

  handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();
  };

  handleDrop = (event: DragEvent) => {
    event.preventDefault();
    event.stopPropagation();

    if (event.dataTransfer && event.dataTransfer.files.length > 0) {
      const file = event.dataTransfer.files[0];
      this.validateAndSetFile(file);
      event.dataTransfer.clearData();
    }
  };

  handleFileChange = (event: any) => {
    const file = event.target.files[0];
    this.validateAndSetFile(file);
  };

  validateAndSetFile(file: File) {
    const { allowedTypes = [], allowedExtensions = [] } = this.props;
    const fileExtension = file.name.split(".").pop()?.toLowerCase();

    if (
      (file.type && allowedTypes.includes(file.type)) &&
      (fileExtension && allowedExtensions.includes(fileExtension))
    ) {
      this.setState({ selectedFile: file, error: null });

      // Try to extract GPS metadata right after selecting
      exifr.gps(file).then((gps) => {
        if (gps) {
          console.log("Extracted GPS:", gps);

          // You could store it in state if you want to inspect later
          // or just attach it to extraFormData in handleUpload
          this.setState((prev) => ({
            ...prev,
            error: null,
            // keep selectedFile as is, but add gps info
            selectedFile: Object.assign(file, { gpsData: gps }),

            gps_latitude: gps.latitude,
            gps_longitude: gps.longitude,
            debug: "gps was probably read"
          }));
        }
        else {
          this.setState({debug: "no gps info"})
        }
      }).catch((err) => {
        console.warn("No GPS data found or failed to parse:", err);
      });

    } else {
      const allowedList = allowedExtensions.length
        ? allowedExtensions.join(", ")
        : "allowed types";
      this.setState({
        selectedFile: null,
        error: `Invalid file type. Allowed file types: ${allowedList}.`
      });
    }
  }

  handleUpload = () => {
    const { selectedFile } = this.state;
    const {
      uploadUrl,
      formFieldName,
      extraFormData,
      onUploadSuccess,
      onUploadError,
    } = this.props;

    if (!selectedFile) {
      this.setState({ error: "Please select a file." });
      return;
    }

    const formData = new FormData();
    formData.append(formFieldName, selectedFile, selectedFile.name);

    // Add extra data from props
    if (extraFormData) {
      Object.entries(extraFormData).forEach(([k, v]) => {
        formData.append(k, v);
      });
    }

    // Add extracted GPS data if available
    if ((selectedFile as any).gpsData) {
      const gps = (selectedFile as any).gpsData;
      formData.append("gps_latitude", gps.latitude);
      formData.append("gps_longitude", gps.longitude);
      if (gps.altitude) formData.append("gps_altitude", gps.altitude);
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", uploadUrl, true);

    // rest of your existing xhr handlers...
    xhr.upload.onprogress = (event) => {
      if (event.lengthComputable) {
        const progress = Math.round((event.loaded / event.total) * 100);
        this.setState({ uploadProgress: progress });
      }
    };

    xhr.onload = () => {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          this.setState({ selectedFile: null, uploadProgress: null });
          onUploadSuccess?.(data);
        } catch {
          this.setState({ error: "Failed to parse server response." });
          onUploadError?.("Failed to parse server response.");
        }
      } else {
        this.setState({ error: "Upload failed", uploadProgress: null });
        onUploadError?.("Upload failed");
      }
    };

    xhr.onerror = () => {
      this.setState({ error: "An error occurred during upload", uploadProgress: null });
      onUploadError?.("An error occurred during upload");
    };

    xhr.send(formData);
  };

  render() {
    const { selectedFile, uploadProgress, error } = this.state;
    const { allowedExtensions = [] } = this.props;

    const acceptValue = allowedExtensions.length
      ? allowedExtensions.map((ext) => `.${ext}`).join(",")
      : undefined;

    return (
      <div class="file-upload">
        <h3>Drag a file here to upload</h3>
        <div
          class="drop-area"
          onDragEnter={(e) => this.handleDragEnter(e as DragEvent)}
          onDragOver={(e) => this.handleDragOver(e as DragEvent)}
          onDragLeave={(e) => this.handleDragLeave(e as DragEvent)}
          onDrop={(e) => this.handleDrop(e as DragEvent)}
          style={{ border: "2px dashed #ccc", padding: "20px", borderRadius: "5px" }}
        >
          <p>
            {selectedFile
              ? `Selected file: ${selectedFile.name}`
              : "Drop files here or click to select files."}
          </p>
          <input
            type="file"
            accept={acceptValue}
            onChange={this.handleFileChange}
            style={{ display: "block", marginTop: "10px" }}
          />
          <button onClick={this.handleUpload}>Upload</button>

          <div>
            gps_latitude {this.state.gps_latitude}<br/>
            gps_longtitude: {this.state.gps_longitude} <br/>
            debug: {this.state.debug}
          </div>

        </div>

        {uploadProgress !== null && (
          <div class="progress-bar" style={{ marginTop: "10px" }}>
            <div
              style={{
                width: `${uploadProgress}%`,
                backgroundColor: "#4caf50",
                height: "10px",
              }}
            ></div>
            <p>{uploadProgress}%</p>
          </div>
        )}

        {error && <div class="error">Error: {error}</div>}
      </div>
    );
  }
}
