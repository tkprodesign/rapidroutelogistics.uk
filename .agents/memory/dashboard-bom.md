---
name: Dashboard BOM issue
description: dashboard/app.php had a UTF-8 BOM causing PHP session warning
---

## Issue
`dashboard/app.php` line 1 had UTF-8 BOM (`\xef\xbb\xbf`) before the `<?php` tag.
This caused: `session_start(): Session cannot be started after headers have already been sent`

## Fix
Stripped with: `python3 -c "content = open('dashboard/app.php','rb').read(); open('dashboard/app.php','wb').write(content[3:] if content.startswith(b'\xef\xbb\xbf') else content)"`

**Why:** BOM is treated as output before headers, making session_start() fail.
