import {test, expect} from '@playwright/test';
import {stubClipboardWriteText} from './helpers/browser';

async function typeIntoTwitterSplitter(page: import('@playwright/test').Page, text: string): Promise<void> {
  const textarea = page.locator('.twitter_splitter_panel_react textarea[placeholder="Type here..."]');
  await expect(textarea).toBeVisible();
  await textarea.fill(text);
}

test.describe('Twitter splitter', () => {
  test('page loads', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await expect(page.getByText('Twitter splitter')).toBeVisible();
    await expect(page.getByText('No tweets yet.')).toBeVisible();
  });

  test('typing text generates tweets and char info', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await typeIntoTwitterSplitter(page, 'Hello world. This is a short tweet.');
    await expect(page.getByText('Chars:')).toBeVisible();
    await expect(page.getByText('Hello world.')).toBeVisible();
    await expect(page.getByText('280')).toBeVisible();
  });

  test('numbering prefix adds tweet numbering', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await page.locator('.twitter_splitter_panel_react select').selectOption({label: '1/ Prefix'});
    await typeIntoTwitterSplitter(page, 'First tweet. Second tweet.');
    await expect(page.locator('.twitter_splitter_panel_react table.split_tweets td', {hasText: '1/'})).toBeVisible();
  });

  test('long text splits into multiple tweets', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await typeIntoTwitterSplitter(
      page,
      'This is a long paragraph intended to force the tool to split the text into multiple tweets. It has sentences. It has commas, and break points, to exercise the splitting logic. Here is another sentence to push it over the limit. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text.'
    );
    await expect(page.locator('.twitter_splitter_panel_react table.split_tweets tr')).not.toHaveCount(1);
    const tweetRows = page.locator('.twitter_splitter_panel_react table.split_tweets tr');
    expect(await tweetRows.count()).toBeGreaterThan(1);
  });

  test('URL length is counted as 23 characters', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await typeIntoTwitterSplitter(
      page,
      'Here is a URL that should be treated specially: https://example.com/some/really/long/path/with/query?abc=def&ghi=jkl\nAnd a second URL: http://example.org/another/path'
    );
    await expect(page.getByText('https://example.com')).toBeVisible();
    await expect(page.getByText('http://example.org')).toBeVisible();
  });

  test('copy marks tweet as copied', async ({page}) => {
    await page.goto('/tools/twitter_splitter');
    await stubClipboardWriteText(page);
    await typeIntoTwitterSplitter(page, 'Copy me please.');
    await page.locator('.twitter_splitter_panel_react table.split_tweets tr td:nth-child(2)').first().click();
    await expect(page.getByText('copied')).toBeVisible();
  });
});
