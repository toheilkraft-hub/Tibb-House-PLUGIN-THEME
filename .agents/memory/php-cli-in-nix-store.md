---
name: No php on PATH in this environment
description: How to find a working php CLI binary for `php -l` syntax checks when `php` is not on PATH.
---

`php`, `php8`, `php81`, etc. are not on PATH in this Replit environment by default, even though PHP is used via the WordPress/wp-* toolchain elsewhere. Attempts to run `php -l file.php` directly fail with "command not found".

**Why:** Needed to lint WordPress PHP files (no build step) without a package-management install; a matching PHP build already exists in the Nix store.

**How to apply:** Find it with `ls /nix/store | grep -i php-with-extensions` and call the full path directly, e.g. `/nix/store/<hash>-php-with-extensions-8.1.20/bin/php -l some-file.php`. The exact hash/version can change between environments, so always re-glob for it rather than hardcoding a path.
