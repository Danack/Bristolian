
import { DateTime } from "luxon";

/**
 * Format bytes as human-readable text.
 *
 * @param bytes Number of bytes.
 * @param si True to use metric (SI) units, aka powers of 1000. False to use
 *           binary (IEC), aka powers of 1024.
 * @param dp Number of decimal places to display.
 *
 * @return Formatted string.
 *
 * @copyright https://stackoverflow.com/a/14919494/778719
 */
export function humanFileSize(bytes:number, si=false, dp=1) {
  const thresh = si ? 1000 : 1024;

  if (Math.abs(bytes) < thresh) {
    return bytes + ' B';
  }

  const units = si
    ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
    : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
  let u = -1;
  const r = 10**dp;

  do {
    bytes /= thresh;
    ++u;
  } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


  return bytes.toFixed(dp) + ' ' + units[u];
}



// Function to format the DateTime object
export function formatDateTime(dateTime: DateTime) {
  // Get the current time
  const now = DateTime.now();

  // Calculate the difference in minutes between the current time and the provided datetime
  const diffInMinutes = now.diff(dateTime, "minutes").minutes;

  if (diffInMinutes < 60) {
    // If within the last hour, show "x minutes ago"
    return `${Math.floor(diffInMinutes)} minutes ago`;
  } else if (dateTime.hasSame(now, "day")) {
    // If over an hour old but still the same day, show the time
    return dateTime.toLocaleString(DateTime.TIME_SIMPLE);
  } else {
    // Otherwise, show the full date and time
    // return dateTime.toLocaleString(DateTime.DATETIME_MED);
    return dateTime.toFormat('h:mma MMM d, yyyy');

  }
}


export function countWords(str:string) {
  if (typeof str !== 'string') {
    throw new TypeError('Input must be a string');
  }
  // Trim the string and split it by spaces, then filter out empty strings
  const words = str.trim().split(/\s+/);
  return words.length === 1 && words[0] === '' ? 0 : words.length;
}

/**
 *
 * @param value
 *
 * @copyright https://github.com/segmentio/is-url/blob/master/index.js
 */
export function isUrl(value: string) {

  /**
   * RegExps.
   * A URL must match #1 and then at least one of #2/#3.
   * Use two levels of REs to avoid REDOS.
   */

  var protocolAndDomainRE = /^(?:\w+:)?\/\/(\S+)$/;

  var localhostDomainRE = /^localhost[\:?\d]*(?:[^\:?\d]\S*)?$/
  var nonLocalhostDomainRE = /^[^\s\.]+\.\S{2,}$/;


  if (typeof value !== 'string') {
    return false;
  }

  var match = value.match(protocolAndDomainRE);
  if (!match) {
    return false;
  }

  var everythingAfterProtocol = match[1];
  if (!everythingAfterProtocol) {
    return false;
  }

  if (localhostDomainRE.test(everythingAfterProtocol) ||
    nonLocalhostDomainRE.test(everythingAfterProtocol)) {
    return true;
  }

  return false;
}
