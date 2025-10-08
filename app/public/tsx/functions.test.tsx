import { humanFileSize, formatDateTime, formatDateTimeForDB, countWords, isUrl, seconds_since, now, call_api, open_lightbox_if_not_mobile, localTimeSimple } from './functions';
import { afterEach, beforeEach, describe, expect, test, it, jest } from '@jest/globals';

// Mock the current time for consistent testing
const mockNow = new Date('2025-10-07T12:00:00.000Z');
const mockNowTimestamp = mockNow.getTime();

describe('humanFileSize', () => {
  it('should format bytes correctly with binary units', () => {
    expect(humanFileSize(0)).toBe('0 B');
    expect(humanFileSize(512)).toBe('512 B');
    expect(humanFileSize(1024)).toBe('1.0 KiB');
    expect(humanFileSize(1024 * 1024)).toBe('1.0 MiB');
  });

  it('should format bytes correctly with SI units', () => {
    expect(humanFileSize(1000, true)).toBe('1.0 kB');
    expect(humanFileSize(1000000, true)).toBe('1.0 MB');
  });

  it('should handle decimal places', () => {
    expect(humanFileSize(1536, false, 2)).toBe('1.50 KiB');
    expect(humanFileSize(1500, true, 2)).toBe('1.50 kB');
  });
});

describe('formatDateTime', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    jest.setSystemTime(mockNow);
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('should show "x minutes ago" for times within the last hour', () => {
    const date = new Date(mockNowTimestamp - 30 * 60 * 1000); // 30 minutes ago
    expect(formatDateTime(date)).toBe('30 minutes ago');
  });

  it('should show time for same day but more than an hour ago', () => {
    const date = new Date(mockNowTimestamp - 2 * 60 * 60 * 1000); // 2 hours ago
    const result = formatDateTime(date);
    expect(result).toMatch(/^\d{1,2}:\d{2}\s*(AM|PM)?$/i);
  });

  it('should show full date and time for previous days', () => {
    const date = new Date(mockNowTimestamp - 25 * 60 * 60 * 1000); // yesterday
    const result = formatDateTime(date);
    expect(result).toContain('Oct');
  });
});

describe('formatDateTimeForDB', () => {
  it('should format date as MySQL datetime string', () => {
    const date = new Date('2025-10-07T12:30:45.000Z');
    // Note: This will format in local timezone
    const result = formatDateTimeForDB(date);
    expect(result).toMatch(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/);
  });

  it('should pad single digits with zeros', () => {
    const date = new Date('2025-01-05T08:05:03.000Z');
    const result = formatDateTimeForDB(date);
    expect(result).toMatch(/^\d{4}-01-05 \d{2}:05:03$/);
  });
});

describe('countWords', () => {
  it('should count words in a string', () => {
    expect(countWords('hello world')).toBe(2);
    expect(countWords('one two three four')).toBe(4);
  });

  it('should handle empty string', () => {
    expect(countWords('')).toBe(0);
    expect(countWords('   ')).toBe(0);
  });

  it('should handle single word', () => {
    expect(countWords('hello')).toBe(1);
  });

  it('should handle multiple spaces', () => {
    expect(countWords('hello    world')).toBe(2);
  });

  it('should throw error for non-string input', () => {
    expect(() => countWords(123 as any)).toThrow(TypeError);
  });
});

describe('isUrl', () => {
  it('should return true for valid URLs', () => {
    expect(isUrl('http://example.com')).toBe(true);
    expect(isUrl('https://example.com')).toBe(true);
    expect(isUrl('http://localhost:3000')).toBe(true);
    expect(isUrl('https://sub.domain.example.com')).toBe(true);
  });

  it('should return false for invalid URLs', () => {
    expect(isUrl('not a url')).toBe(false);
    expect(isUrl('example.com')).toBe(false);
    expect(isUrl('http://')).toBe(false);
    expect(isUrl('')).toBe(false);
  });

  it('should return false for non-string input', () => {
    expect(isUrl(123 as any)).toBe(false);
    expect(isUrl(null as any)).toBe(false);
    expect(isUrl(undefined as any)).toBe(false);
  });
});

describe('seconds_since', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    jest.setSystemTime(mockNow);
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('should return 0 for current time', () => {
    const currentDate = new Date(mockNowTimestamp);
    const result = seconds_since(currentDate);
    expect(result).toBeCloseTo(0, 0);
  });

  it('should return positive number for past dates', () => {
    const pastDate = new Date(mockNowTimestamp - 60 * 1000); // 60 seconds ago
    const result = seconds_since(pastDate);
    expect(result).toBeCloseTo(60, 0);
  });

  it('should return correct seconds for dates hours ago', () => {
    const hoursAgo = new Date(mockNowTimestamp - 2 * 60 * 60 * 1000); // 2 hours ago
    const result = seconds_since(hoursAgo);
    expect(result).toBeCloseTo(7200, 0); // 2 hours = 7200 seconds
  });

  it('should return correct seconds for dates days ago', () => {
    const daysAgo = new Date(mockNowTimestamp - 3 * 24 * 60 * 60 * 1000); // 3 days ago
    const result = seconds_since(daysAgo);
    expect(result).toBeCloseTo(259200, 0); // 3 days = 259200 seconds
  });

  it('should return negative number for future dates', () => {
    const futureDate = new Date(mockNowTimestamp + 60 * 1000); // 60 seconds in future
    const result = seconds_since(futureDate);
    expect(result).toBeCloseTo(-60, 0);
  });
});

describe('now', () => {
  it('should return current timestamp in seconds', () => {
    const before = Date.now() / 1000;
    const result = now();
    const after = Date.now() / 1000;
    
    expect(result).toBeGreaterThanOrEqual(before);
    expect(result).toBeLessThanOrEqual(after);
  });

  it('should return a number', () => {
    const result = now();
    expect(typeof result).toBe('number');
  });
});

describe('call_api', () => {
  const originalFetch = global.fetch;

  afterEach(() => {
    global.fetch = originalFetch;
  });

  it('should make a POST request with FormData', async () => {
    const mockResponse = { success: true, data: 'test' };
    global.fetch = jest.fn(() =>
      Promise.resolve({
        ok: true,
        json: () => Promise.resolve(mockResponse),
      })
    ) as any;

    const formData = new FormData();
    formData.append('key', 'value');
    
    const result = await call_api('/api/test', formData);
    
    expect(global.fetch).toHaveBeenCalledWith('/api/test', {
      method: 'POST',
      body: formData
    });
    expect(result).toEqual(mockResponse);
  });

  it('should throw error when response is not ok', async () => {
    global.fetch = jest.fn(() =>
      Promise.resolve({
        ok: false,
        status: 404,
      })
    ) as any;

    const formData = new FormData();
    
    await expect(call_api('/api/test', formData)).rejects.toThrow('Server returned status 404');
  });
});

describe('open_lightbox_if_not_mobile', () => {
  const originalInnerWidth = window.innerWidth;

  beforeEach(() => {
    document.body.innerHTML = '';
  });

  afterEach(() => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: originalInnerWidth,
    });
    document.body.innerHTML = '';
  });

  it('should not create lightbox on mobile', () => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 500,
    });

    open_lightbox_if_not_mobile('/test.jpg');
    
    const lightbox = document.querySelector('.lightbox');
    expect(lightbox).toBeNull();
  });

  it('should create lightbox on desktop', () => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 1024,
    });

    open_lightbox_if_not_mobile('/test.jpg');
    
    const lightbox = document.querySelector('.lightbox');
    expect(lightbox).not.toBeNull();
    expect(lightbox?.className).toBe('lightbox');
  });

  it('should create lightbox with correct image src', () => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 1024,
    });

    open_lightbox_if_not_mobile('/test-image.jpg');
    
    const img = document.querySelector('.lightbox img');
    expect(img).not.toBeNull();
    expect((img as HTMLImageElement).src).toContain('test-image.jpg');
  });

  it('should remove lightbox on click', () => {
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 1024,
    });

    open_lightbox_if_not_mobile('/test.jpg');
    
    const lightbox = document.querySelector('.lightbox') as HTMLElement;
    expect(lightbox).not.toBeNull();
    
    lightbox.click();
    
    const lightboxAfterClick = document.querySelector('.lightbox');
    expect(lightboxAfterClick).toBeNull();
  });
});

describe('localTimeSimple', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    // Set current time to Oct 7, 2025, 12:00 PM
    jest.setSystemTime(mockNow);
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('should show time only for today', () => {
    const todayDate = new Date(mockNowTimestamp - 2 * 60 * 60 * 1000); // 2 hours ago today
    const result = localTimeSimple(todayDate);
    
    expect(result).toMatch(/^\d{1,2}:\d{2}$/);
  });

  it('should show "yst" prefix for yesterday', () => {
    const yesterdayDate = new Date(mockNowTimestamp - 24 * 60 * 60 * 1000); // 24 hours ago
    const result = localTimeSimple(yesterdayDate);
    
    expect(result).toMatch(/^yst \d{1,2}:\d{2}$/);
  });

  it('should show weekday name for dates within 6 days', () => {
    const threeDaysAgo = new Date(mockNowTimestamp - 3 * 24 * 60 * 60 * 1000); // 3 days ago
    const result = localTimeSimple(threeDaysAgo);
    
    // Should have format like "Sat 12:00" (weekday name + time)
    expect(result).toMatch(/^(Sun|Mon|Tue|Wed|Thu|Fri|Sat) \d{1,2}:\d{2}$/);
  });

  it('should show month and date for dates older than 6 days', () => {
    const tenDaysAgo = new Date(mockNowTimestamp - 10 * 24 * 60 * 60 * 1000); // 10 days ago
    const result = localTimeSimple(tenDaysAgo);
    
    // Should have format like "Sep 27, 12:00"
    expect(result).toMatch(/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{1,2}, \d{1,2}:\d{2}$/);
  });

  it('should pad minutes with zero when less than 10', () => {
    const dateWithSingleDigitMinute = new Date(mockNowTimestamp);
    dateWithSingleDigitMinute.setMinutes(5);
    
    const result = localTimeSimple(dateWithSingleDigitMinute);
    
    expect(result).toContain(':05');
  });

  it('should not pad minutes with zero when 10 or greater', () => {
    const dateWithDoubleDigitMinute = new Date(mockNowTimestamp);
    dateWithDoubleDigitMinute.setMinutes(45);
    
    const result = localTimeSimple(dateWithDoubleDigitMinute);
    
    expect(result).toContain(':45');
  });
});

