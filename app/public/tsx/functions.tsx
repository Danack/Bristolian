
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





export function formatDateTime(date: Date): string {
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMinutes = Math.floor(diffMs / 1000 / 60);

  if (diffMinutes < 60) {
    // If within the last hour, show "x minutes ago"
    return `${diffMinutes} minutes ago`;
  }

  // Check if the date is today
  const isToday =
    date.getDate() === now.getDate() &&
    date.getMonth() === now.getMonth() &&
    date.getFullYear() === now.getFullYear();

  if (isToday) {
    // If same day, show just the time
    return date.toLocaleTimeString(undefined, {
      hour: "numeric",
      minute: "2-digit",
    });
  }

  // Otherwise, show full date and time
  return date.toLocaleString(undefined, {
    hour: "numeric",
    minute: "2-digit",
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}


export function formatDateTimeForDB(date: Date): string {
  const pad = (n: number) => n.toString().padStart(2, '0');

  const year = date.getFullYear();
  const month = pad(date.getMonth() + 1); // months are 0-indexed
  const day = pad(date.getDate());
  const hours = pad(date.getHours());
  const minutes = pad(date.getMinutes());
  const seconds = pad(date.getSeconds());

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
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
