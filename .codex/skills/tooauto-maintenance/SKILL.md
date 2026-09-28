---
name: tooauto-maintenance
description: Maintain and extend the TooAuto Laravel super-admin application, especially washes and commercials, call-center follow-up, privilege reduction cards, Firebase notification campaigns, scheduling, and production deployment. Use when diagnosing or changing these TooAuto modules or preparing a technical handover.
---

# TooAuto Maintenance

Start by reading `docs/AI_HANDOVER.md` from the repository root. Treat the code and current database schema as the final source of truth when they differ from the document.

## Working Method

1. Inspect `git status`, the relevant routes, controller or service, Blade view, model, and migration before editing.
2. Preserve unrelated work. This project may contain changes made directly by the owner or tables created manually in production.
3. Follow the existing Laravel 10 and Blade patterns. Keep administrator and call-center route names, guards, menus, and view variables aligned.
4. Keep filters paginated with their query string. Large lists must not be rendered in full.
5. Use `Schema::hasTable()` and `Schema::hasColumn()` where the existing module supports more than one deployed schema. Do not remove compatibility branches without checking production data.
6. Never expose or replace `.env` values or Firebase credentials. Do not send a real push notification during a test unless the user explicitly identifies the recipient and authorizes it.

## Module Invariants

- A wash and its station are separate records in `lavages` and `station_de_lavages`. Their commercial attribution is based on `created_by`. Some installations lack a direct foreign key, so the wash listing has compatibility joins.
- Authentication fields on a wash belong to `lavages`; establishment information and logo belong to `station_de_lavages`.
- Reduction-card duration follows the linked active user subscription. Card usage must always create a `reduction_card_histories` row with the applying actor and establishment identity.
- Scheduled campaigns support users, professionals, washes, and service stations. Every audience includes only recipients with a non-empty token; lavage and station audiences may contain several devices per entity. Audience filters are stored in `audience_filters`, then reevaluated at send time.
- Due campaigns must be claimed atomically before sending. Keep batch processing in `NotificationCampaignService::sendDueCampaigns()` and do not reintroduce a scheduler-wide overlap lock that leaves later campaigns waiting behind one long send.
- The production scheduler requires the system cron shown in the handover document. Laravel scheduling code alone is insufficient.

## Verification

Run focused PHP syntax checks for every changed PHP or Blade file, then use the smallest relevant Artisan check. For notification scheduling, verify both `php artisan schedule:list` and a multi-campaign test with Firebase replaced by a fake service and all temporary database writes wrapped in a rolled-back transaction.

Before handing off, update `docs/AI_HANDOVER.md` when architecture, routes, tables, cron behavior, deployment steps, or known limitations changed.
