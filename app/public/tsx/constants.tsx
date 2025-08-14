import {sendMessage} from "./message/message";


export enum PdfSelectionType {
  TEXT_SELECTED = "selectionData",
  TEXT_DESELECTED = "textDeselected",

  ROOM_FILES_CHANGED = "room_files_changed",


  ROOM_LINKS_CHANGED = "room_links_changed",

}