# Changelog

All notable changes to `vgcomments` will be documented in this file

## 2.0.7 - 2026-08-04

- Harden `registerFilesForComment` to skip attachment entries missing a UUID (avoids undefined-array-key 500s from malformed Livewire payloads)

## 2.0.6 - 2026-08-04

- Evaluate `moderation_users` at Gate check-time (not boot-time) so moderators auto-approve reliably

## 2.0.5 - 2026-08-04

- Make StopForumSpam checks configurable (`VGCOMMENT_STOPFORUMSPAM`) and resilient to API failures
- Skip StopForumSpam for authenticated authors (avoids false positives from proxy/Cloudflare edge IPs)
- Return status-accurate store messages from the API and session alerts (pending/spam/trash)
- Fix CommentPolicy `moderate` to use the Gate-provided user safely

## 2.0.4 - 2026-08-04

- Tighten guest `author_name` / `author_email` validation rules (required when guests are allowed, without conflicting `nullable`)

## 2.0.3 - 2026-08-04

- Normalize markdown/content formatting and harden comment services
- Improve reply nesting tree and reaction handling
- Fix upload paths and related API resource fields

## 1.0.0 - 201X-XX-XX

- initial release
