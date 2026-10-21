# Nexteck Lead Generation Engine (Lead Management)

A production-grade, database-driven **Marketing Automation, CRM & Social Media Management Platform** built on **Laravel 12**, **MySQL**, **Laravel Queues**, **Scheduler**, and **Blade with Bootstrap 5**.

---

## 🌟 Core Features

- **Executive CRM Dashboard:** Real-time database metrics for leads, email delivery rates, reply intents, suppression rates, social metrics, urgent manager handovers, and integration health.
- **Lead CRM & Pipeline:** Comprehensive lead profiles, company relationships, activities timeline, notes, tasks, CSV/Excel import with duplicate detection, CSV export, and permanent suppression list.
- **Email Campaign Engine:** Variable substitution (`{{first_name}}`, `{{company_name}}`, `{{service_name}}`), queue-based bulk sending, rate limits, deliverability tracking, and RFC 8058 one-click unsubscribe.
- **Follow-Up Sequence Engine:** Multi-phase automated cadence (Phase A Weekly, Phase B Twice-Monthly, Phase C Monthly), customizable sending windows, weekend exclusion, and auto-stop on reply.
- **Unified Engagement Inbox:** Centralized hub for incoming email replies and multi-platform social comments with AI intent classification (`interested`, `meeting_request`, `out_of_office`, `not_interested`).
- **Sales Manager Handover Queue:** Instant escalation for high-intent replies with automated task creation and priority triage.
- **AI Content Studio:** Automated copy generator for LinkedIn, Instagram carousels, Facebook, and TikTok video scripts tailored to Nexteck engineering services.
- **Social Media Publishing:** Connect accounts for LinkedIn, Facebook, Instagram, and TikTok; immediate or scheduled publishing with engagement logs.
- **Blog CMS & AEO / GEO Optimization:** Rich-text publishing, revision history, live SEO completeness score (0-100), Answer Engine Q&A FAQs, dynamic JSON-LD schema (`Article`, `FAQPage`), live `/sitemap.xml`, and `/llms.txt`.
- **Role-Based Access Control:** Configurable permissions for `admin`, `sales_manager`, and `sales_rep`.

---

## 🚀 Quick Start Guide

### 1. Requirements
- PHP 8.2+ with PDO, OpenSSL, Mbstring, cURL, XML, BCMath
- Composer 2+
- MySQL 8.0+ or MariaDB 10.4+

### 2. Installation
```bash
# Clone the repository
git clone https://github.com/infoharry99/leadmanagement.git
cd leadmanagement

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database migration & realistic demo seeding
php artisan migrate --seed

# Start development server
php artisan serve
```

---

## 🔑 Default Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@nexteck.com` | `password` |
| **Sales Manager** | `manager@nexteck.com` | `password` |
| **Sales Rep** | `sales@nexteck.com` | `password` |

*(A 1-click Quick Login bar is also available on the `/login` screen).*

---

## ⏰ Background Scheduling & Queues

### Crontab Setup
Add this single entry to your server's cron:
```bash
* * * * * cd /path-to-leadmanagement && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Command Triggers
- **Process Follow-Up Sequences:**
  ```bash
  php artisan nexteck:process-followups
  ```
- **Publish Scheduled Social Posts:**
  ```bash
  php artisan nexteck:publish-social
  ```
- **Run Queue Worker:**
  ```bash
  php artisan queue:work --sleep=3 --tries=3
  ```

---

## 🧪 Testing Suite

Run the full automated test suite (21 tests, 62 assertions):
```bash
php artisan test
```

---

## 📄 License
Proprietary — Nexteck Solutions. All rights reserved.
