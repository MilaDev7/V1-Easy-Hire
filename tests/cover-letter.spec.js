import { test, expect } from '@playwright/test';
import { loginViaUi, mockChapaEndpoints } from './utils/login.js';

test.describe('Cover Letter - Job Application Flow', () => {

  test('professional submits cover letter with application', async ({ page }) => {
    const professional = {
      email: 'Painterone@gmail.com',
      password: '123456',
    };

    const jobs = [
      {
        id: 901,
        title: 'Paint Living Room',
        description: 'Need a painter for a 3-room apartment.',
        budget: 300,
        location: 'Addis Ababa',
        skill: 'Painter',
        skills: 'Painter',
        status: 'open',
        has_applied: false,
        skill_match: true,
      },
    ];

    await mockChapaEndpoints(page);

    await page.route('**/api/jobs**', async (route) => {
      if (route.request().method() !== 'GET') {
        await route.fallback();
        return;
      }
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(jobs),
      });
    });

    await page.route('**/api/jobs/*/apply', async (route) => {
      if (route.request().method() !== 'POST') {
        await route.fallback();
        return;
      }
      const body = route.request().postDataJSON();
      expect(body.cover_letter).toBeTruthy();
      expect(body.cover_letter.length).toBeGreaterThanOrEqual(20);
      expect(body.cover_letter.length).toBeLessThanOrEqual(2000);
      expect(body.cover_letter).toContain('experienced painter');

      await route.fulfill({
        status: 201,
        contentType: 'application/json',
        body: JSON.stringify({
          message: 'Application submitted successfully',
          application: {
            id: 99,
            job_id: 901,
            cover_letter: body.cover_letter,
            status: 'pending',
          },
        }),
      });
    });

    await loginViaUi(page, { email: professional.email, password: professional.password });
    await expect(page).toHaveURL(/\/pro\/dashboard/);

    const applyButton = page.getByTestId('professional-apply-901');
    await expect(applyButton).toBeVisible();
    await applyButton.click();

    await expect(page.locator('#apply-cover-letter-modal')).toBeVisible();
    await page.fill('#cover-letter-input', 'I am an experienced painter with 8 years in residential and commercial painting. I will do a great job.');
    await page.click('#submit-cover-letter-btn');

    await expect(page.getByRole('button', { name: 'Applied' })).toBeVisible();
  });

  test('empty cover letter is rejected with inline validation', async ({ page }) => {
    const professional = {
      email: 'Painterone@gmail.com',
      password: '123456',
    };

    const jobs = [
      {
        id: 902,
        title: 'Fix Bathroom Leak',
        description: 'Bathroom pipe leaking.',
        budget: 200,
        location: 'Addis Ababa',
        skill: 'Plumber',
        skills: 'Plumber',
        status: 'open',
        has_applied: false,
        skill_match: true,
      },
    ];

    await mockChapaEndpoints(page);

    await page.route('**/api/jobs**', async (route) => {
      if (route.request().method() !== 'GET') {
        await route.fallback();
        return;
      }
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(jobs),
      });
    });

    await loginViaUi(page, { email: professional.email, password: professional.password });
    await expect(page).toHaveURL(/\/pro\/dashboard/);

    const applyButton = page.getByTestId('professional-apply-902');
    await expect(applyButton).toBeVisible();
    await applyButton.click();

    await expect(page.locator('#apply-cover-letter-modal')).toBeVisible();

    // Submit with empty cover letter
    await page.fill('#cover-letter-input', '');
    await page.click('#submit-cover-letter-btn');

    // Should see the validation modal instead of submitting
    await expect(page.locator('#professional-apply-invalid-modal')).toBeVisible();
    await expect(page.locator('#professional-apply-invalid-message')).toContainText('at least 20 characters');
  });

  test('short cover letter (under 20 chars) is rejected', async ({ page }) => {
    const professional = {
      email: 'Painterone@gmail.com',
      password: '123456',
    };

    await mockChapaEndpoints(page);

    await page.route('**/api/jobs**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([{
          id: 903, title: 'Test Job', skill: 'Painter', skills: 'Painter',
          status: 'open', has_applied: false, skill_match: true,
        }]),
      });
    });

    await loginViaUi(page, { email: professional.email, password: professional.password });
    await expect(page).toHaveURL(/\/pro\/dashboard/);

    await page.getByTestId('professional-apply-903').click();
    await expect(page.locator('#apply-cover-letter-modal')).toBeVisible();

    await page.fill('#cover-letter-input', 'Hi');
    await page.click('#submit-cover-letter-btn');

    await expect(page.locator('#professional-apply-invalid-modal')).toBeVisible();
    await expect(page.locator('#professional-apply-invalid-message')).toContainText('at least 20 characters');
  });

  test('client can view cover letter content in applications list', async ({ page }) => {
    const client = {
      email: 'clthree@gmail.com',
      password: '123456',
    };

    const coverLetterText = 'I have 10 years of plumbing experience and can fix the leak quickly. I have all necessary tools.';
    const applications = [
      {
        id: 77,
        status: 'pending',
        job_title: 'Kitchen Sink Repair',
        date_applied: '2026-04-10',
        professional_name: 'Pro Plumber',
        professional_profile_id: null,
        cover_letter: coverLetterText,
      },
      {
        id: 78,
        status: 'accepted',
        job_title: 'Bathroom Fix',
        date_applied: '2026-04-11',
        professional_name: 'Another Pro',
        professional_profile_id: null,
        cover_letter: 'I will handle the bathroom renovation with care and precision.',
      },
    ];

    await mockChapaEndpoints(page);

    await page.route('**/api/client/my-subscription', async (route) => {
      await route.fulfill({
        status: 200, contentType: 'application/json',
        body: JSON.stringify({
          has_subscription: true, plan_name: 'Mock Pro Plan', plan: 'Mock Pro Plan',
          price: 100, job_post_limit: 10, remaining_posts: 9,
          direct_requests_remaining: 5, duration_days: 30, expires_at: '2099-12-31',
        }),
      });
    });
    await page.route('**/api/client/contracts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/contracts/active', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts/count', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ count: 0 }) });
    });
    await page.route('**/api/client/job-posts/remaining', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ remaining: 10 }) });
    });
    await page.route('**/api/client/requests', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });

    await page.route('**/api/client/applications', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(applications),
      });
    });

    await loginViaUi(page, { email: client.email, password: client.password });
    await expect(page).toHaveURL(/\/client\/dashboard/);

    // Navigate to applications
    await page.locator('[data-view="applications"]').click();
    await expect(page.getByText('Kitchen Sink Repair')).toBeVisible();

    // Check cover letter button exists
    const coverLetterBtn = page.getByTestId('view-cover-letter-77');
    await expect(coverLetterBtn).toBeVisible();
    await expect(coverLetterBtn).toContainText('Cover Letter');
    await coverLetterBtn.click();

    // Verify modal shows cover letter content
    await expect(page.locator('#cover-letter-modal')).toBeVisible();
    await expect(page.locator('#cover-letter-modal-name')).toHaveText('Pro Plumber');
    await expect(page.locator('#cover-letter-modal-job')).toHaveText('Kitchen Sink Repair');
    await expect(page.locator('#cover-letter-modal-body')).toHaveText(coverLetterText);

    // Close modal
    await page.locator('#cover-letter-modal .btn-close').click();
    await expect(page.locator('#cover-letter-modal')).not.toBeVisible();
  });

  test('cover letter preserves line breaks and formatting', async ({ page }) => {
    const client = {
      email: 'clthree@gmail.com',
      password: '123456',
    };

    const multiLineCoverLetter = 'Dear Client,\n\nI am writing to express my interest in this job.\n\nI have:\n- 5 years experience\n- All necessary tools\n- Flexible schedule\n\nBest regards,\nPro Candidate';
    const applications = [
      {
        id: 79,
        status: 'pending',
        job_title: 'Garden Maintenance',
        date_applied: '2026-04-12',
        professional_name: 'Green Thumb',
        professional_profile_id: null,
        cover_letter: multiLineCoverLetter,
      },
    ];

    await mockChapaEndpoints(page);

    await page.route('**/api/client/my-subscription', async (route) => {
      await route.fulfill({
        status: 200, contentType: 'application/json',
        body: JSON.stringify({
          has_subscription: true, plan_name: 'Mock Pro Plan', plan: 'Mock Pro Plan',
          price: 100, job_post_limit: 10, remaining_posts: 9,
          direct_requests_remaining: 5, duration_days: 30, expires_at: '2099-12-31',
        }),
      });
    });
    await page.route('**/api/client/contracts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/contracts/active', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts/count', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ count: 0 }) });
    });
    await page.route('**/api/client/job-posts/remaining', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ remaining: 10 }) });
    });
    await page.route('**/api/client/requests', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });

    await page.route('**/api/client/applications', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(applications),
      });
    });

    await loginViaUi(page, { email: client.email, password: client.password });
    await expect(page).toHaveURL(/\/client\/dashboard/);

    await page.locator('[data-view="applications"]').click();
    await expect(page.getByText('Garden Maintenance')).toBeVisible();

    await page.getByTestId('view-cover-letter-79').click();
    await expect(page.locator('#cover-letter-modal')).toBeVisible();

    // Verify line breaks are preserved (modal-body uses white-space: pre-wrap)
    const bodyEl = page.locator('#cover-letter-modal-body');
    const text = await bodyEl.textContent();
    expect(text).toContain('Dear Client,');
    expect(text).toContain('I am writing to express my interest in this job.');
    expect(text).toContain('- 5 years experience');
    expect(text).toContain('- All necessary tools');
    expect(text).toContain('- Flexible schedule');
    expect(text).toContain('Best regards,');
    expect(text).toContain('Pro Candidate');
  });

  test('application without cover letter hides cover letter button', async ({ page }) => {
    const client = {
      email: 'clthree@gmail.com',
      password: '123456',
    };

    const applications = [
      {
        id: 80,
        status: 'pending',
        job_title: 'No Cover Letter Job',
        date_applied: '2026-04-13',
        professional_name: 'Quiet Pro',
        professional_profile_id: null,
        cover_letter: null,
      },
    ];

    await mockChapaEndpoints(page);

    await page.route('**/api/client/my-subscription', async (route) => {
      await route.fulfill({
        status: 200, contentType: 'application/json',
        body: JSON.stringify({
          has_subscription: true, plan_name: 'Mock Pro Plan', plan: 'Mock Pro Plan',
          price: 100, job_post_limit: 10, remaining_posts: 9,
          direct_requests_remaining: 5, duration_days: 30, expires_at: '2099-12-31',
        }),
      });
    });
    await page.route('**/api/client/contracts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/contracts/active', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });
    await page.route('**/api/client/job-posts/count', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ count: 0 }) });
    });
    await page.route('**/api/client/job-posts/remaining', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ remaining: 10 }) });
    });
    await page.route('**/api/client/requests', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
    });

    await page.route('**/api/client/applications', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(applications),
      });
    });

    await loginViaUi(page, { email: client.email, password: client.password });
    await expect(page).toHaveURL(/\/client\/dashboard/);

    await page.locator('[data-view="applications"]').click();
    await expect(page.getByText('No Cover Letter Job')).toBeVisible();

    // No Cover Letter button should exist
    await expect(page.getByTestId('view-cover-letter-80')).not.toBeVisible();
  });

});
