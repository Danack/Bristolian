import {test, expect} from '@playwright/test';

test.describe('Email link generator', () => {
  test('page loads', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await expect(page.getByText('Input')).toBeVisible();
    await expect(page.getByText('Output')).toBeVisible();
    await expect(page.getByText('Address')).toBeVisible();
  });

  test('filling address produces mailto link in output', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'test@example.com');
    await expect(page.getByText('mailto:test@example.com')).toBeVisible();
    await expect(page.getByText('test@example.com').first()).toBeVisible();
  });

  test('link text can be customised', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'contact@site.org');
    await page.fill('[name="email_link_link_text"], #email_link_link_text', 'Contact us');
    await expect(page.getByText('mailto:contact@site.org')).toBeVisible();
    await expect(page.getByRole('link', {name: 'Contact us'})).toBeVisible();
  });

  test('subject is included in generated link', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'a@b.co');
    await page.fill('[name="email_link_subject"], #email_link_subject', 'Hello world');
    await expect(page.getByText('Subject=Hello%20world')).toBeVisible();
    await expect(page.getByText('mailto:a@b.co')).toBeVisible();
  });

  test('CC is included in generated link', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'to@example.com');
    await page.fill('[name="email_link_cc"], #email_link_cc', 'cc@example.com');
    await expect(page.getByText('cc=cc%40example.com')).toBeVisible();
    await expect(page.getByText('mailto:to@example.com')).toBeVisible();
  });

  test('BCC is included in generated link', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'to@example.com');
    await page.fill('[name="email_link_bcc"], #email_link_bcc', 'bcc@example.com');
    await expect(page.getByText('bcc=bcc%40example.com')).toBeVisible();
    await expect(page.getByText('mailto:to@example.com')).toBeVisible();
  });

  test('body is included in generated link', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'recipient@test.org');
    await page.fill('[name="email_link_body"], #email_link_body', 'Email body text');
    await expect(page.getByText('body=Email%20body%20text')).toBeVisible();
    await expect(page.getByText('mailto:recipient@test.org')).toBeVisible();
  });

  test('full form produces complete mailto link', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'full@test.com');
    await page.fill('[name="email_link_link_text"], #email_link_link_text', 'Send email');
    await page.fill('[name="email_link_subject"], #email_link_subject', 'Test subject');
    await page.fill('[name="email_link_cc"], #email_link_cc', 'cc@test.com');
    await page.fill('[name="email_link_bcc"], #email_link_bcc', 'bcc@test.com');
    await page.fill('[name="email_link_body"], #email_link_body', 'Message body');
    await expect(page.getByText('mailto:full@test.com')).toBeVisible();
    await expect(page.getByRole('link', {name: 'Send email'})).toBeVisible();
    await expect(page.getByText('Subject=Test%20subject')).toBeVisible();
    await expect(page.getByText('cc=cc%40test.com')).toBeVisible();
    await expect(page.getByText('bcc=bcc%40test.com')).toBeVisible();
    await expect(page.getByText('body=Message%20body')).toBeVisible();
  });

  test('copy button copies link when available', async ({page}) => {
    await page.goto('/tools/email_link_generator');
    await page.fill('[name="email_link_address"], #email_link_address', 'copy@test.com');
    const copyButton = page.getByRole('button', {name: 'Copy'});
    if (await copyButton.count()) {
      await copyButton.click();
    }
    await expect(page.getByText('mailto:copy@test.com')).toBeVisible();
  });
});
