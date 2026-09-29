# WORDPRESS.md — DN Apps (app.doctornow.hk)

> Brand-specific WordPress + Elementor reference for DN Apps. Mirror of the DNH/AC staging pattern.

## 1. Environments

| Environment | URL | Details |
|---|---|---|
| **Production** | https://app.doctornow.hk | 1Panel host `root@47.239.72.102` (hostname iZj6cbeoqdeecnf2cucflsZ), container `PHP7-4-33`, WP at `/www/sites/app.doctornow.hk/index/`. WP core **5.8** · theme **phlox-pro** · DB `wp_dnweb` on Alibaba RDS `rm-3nsd2db8f226efy18do.mysql.rds.aliyuncs.com` (user doctornow), prefix **`wp_`** (NOT emc_). 23 active plugins incl. WPML (sitepress-multilingual-cms, default lang **en**, zh-hant secondary), Elementor, auxin suite, CF7, tablepress. Wordfence WAF. |
| **Local staging** | http://localhost:8093 | Container `dnapps_wp_local` (wordpress:7.0.2-php8.2-apache) + `dnapps_db_local` (mariadb:10.11), bind `~/staging/dnapps/wp-content/`, compose at `~/staging/dnapps/`. DB `wordpress`/`wordpress`/`local_dnapps_wp_2026`, prefix `wp_`. Site URL `dnapps-staging.hkdrnow.com`. |
| **Public staging URL** | dnapps-staging.hkdrnow.com | ⚠️ PENDING: hostname must be added to the brandops tunnel (CF dashboard, Wilson) → `http://localhost:8093` |

## 2. Setup facts (2026-08-07)

- Cloned via read-only prod pulls: mysqldump (108 tables, 48.6MB, `--set-gtid-purged=OFF` for RDS) + rsync wp-content (2.1GB incl. 569MB uploads). mysql-client 8.0.46 installed on the prod host for the dump.
- URL replace (ordered): `wp search-replace https://app.doctornow.hk → https://dnapps-staging.hkdrnow.com --all-tables --precise` (1411) → bare domain (184) → siteurl/home options → Elementor CLI confirmed 0 remaining. Residual prod refs in wp_options are cache/notice data only (wpml_notices, elementor_log) — harmless.
- **PHP 8.2-incompatible plugins deactivated on staging** (fatal on the 7.0.2 core; prod runs PHP 7.4): `wp-file-manager` (fwrite TypeError at init), `wpml-string-translation` (calls removed `WP_Textdomain_Registry::reset()`). Both are utility/loader plugins — frontend renders fine without them. wpjam-basic also emits PHP 8.2 warnings (harmless) — kept active to match prod.
- mu-plugin `host-adaptive-urls.php` (pre_option home/siteurl → request host) — same as DNH/AC, enables local + tunnel access without canonical-redirect loops.
- `elementor_element_cache_ttl` = -1; `DISABLE_WP_CRON` true; wp-content chowned to www-data (33:33).
- wp-cli: `docker exec dnapps_wp_local wp ... --allow-root` (php8.2 image).

## 3. Revert 2026-08-22 — telemedicine GA4 + scroll-lock incident

**Request (Wilson):** restore `/telemedicine/` + `/zh-hant/telemedicine/` (pages 8085/8125) to pre-gtag/GA4 original; strip overnight flash/scroll fixes.

**What was changed Aug 21 (both mu-plugins now reverted):**
- `mu-plugins/dna-ga4.php` — gtag G-KVG8BQ85VC on telemedicine (8085/8125) + download (8015/8049). **Now download-only** (Search Ads tracking kept).
- `mu-plugins/dna-overflow.php` — FOUC guard + #inner-body `animation:none !important` + `html overflow-x:clip`. **Removed entirely.**

**Root cause of "can't scroll" (verified):** the FOUC guard killed the Auxin #inner-body entrance animation; the theme's scroll-unlock fires on `animationend`, which never fired → scroll stayed locked at viewport height (~844px) indefinitely. Removing the plugin restores the native unlock (~3.5s after load).

**State now:** telemedicine EN+TC = original (no gtag, no FOUC, no overflow clip, no h-overflow, scrolls to bottom). Theme's native fade-in (opacity 0.5→1) remains — identical to prod app.doctornow.hk/telemedicine/ behavior (pre-existing since 2025). If Wilson still dislikes the fade, proper fix = disable aux page animation via theme option, NOT CSS-kill (breaks scroll unlock).

**Backups:** `mu-plugins/backup-2026-08-22-revert/` (dna-ga4.php.orig, dna-overflow.php.orig). Evidence: `verify-2026-08-22/` (in this repo; moved from `~/projects/dn-apps` 2026-09-29 Wilson GO A).

## 4. Telemedicine entrance-animation fix — 2026-08-22 (prod follow-up task)

**Symptom:** prod app.doctornow.hk/telemedicine + /zh-hant/telemedicine show "left side out of frame" during load (Auxin 'circle' entrance animation: #inner-body clip-path circle + translateZ(-180px) + opacity 0.5, body locked overflow:hidden/height:100vh until transitionend → `aux-page-animation-done`).

**Mechanism (theme phlox-pro):** `page_animation_nav_enable=true`, type `circle` (stored in `phlox-pro_theme_options`; body gets `aux-page-animation aux-page-animation-circle`, `data-page-animation-type="circle"`). Unlock: transitionend on **#inner-body transform** (config `eventTarget:'#inner-body', propertyWatch:'transform'`) → done class → `height:auto; overflow:visible` on #inner-body AND body. This is why last night's `animation:none !important` kill broke scroll (no transitionend ever fired). **Option is GLOBAL (sitewide).**

**Two verified staging fixes (browser-verified EN+TC telemedicine + home):**
- **A (global):** `auxin_update_option('page_animation_nav_enable', 0)` → body class `aux-page-animation-off`; animation gone everywhere, scroll immediate. All pages lose the nav transition.
- **B (targeted, CURRENTLY ACTIVE on staging):** mu-plugin `dna-anim-fix.php` — telemedicine pages 8085/8125 only; pins #inner-body to final visual state (clip none, opacity 1, height auto, 150ms collapsed transform) + unlocks body + synthetic transitionend fallback so the theme still reaches `done` (nav hide-out lifecycle intact). Other pages keep animation.

**PROD BLOCKER:** SSH root@47.239.72.102:22 unreachable from VM (and from DNH host 47.238.72.84) — all ports scanned closed; security group likely changed since Aug 7 (password still in session transcripts). VM public IP for whitelist: **223.19.51.117**. Prod apply pending Wilson: (1) A vs B choice, (2) access restore.

**Observed (pre-existing, out of scope):** home page has horizontal overflow (scrollWidth>clientWidth) — possible "left side out of frame" contributor on other pages; candidate follow-up.

**GA4 REAPPLY (2026-08-22, Wilson):** telemedicine 8085/8125 restored to GA4 scope in `dna-ga4.php` (G-KVG8BQ85VC head tag + config + download-intent/app-store events). Additive — `dna-anim-fix.php` untouched. Verified both pages (browser): gtag loaded, config fired, no flash, immediate scroll, done lifecycle true, `dn_download_intent` fires on download-button click. Rollback: `cp backup-2026-08-22-revert/dna-ga4.php.pre-reapply dna-ga4.php`. Download pages 8015/8049 unchanged.

**PROD APPLY DONE 2026-08-22 ~11:25 (Wilson GO, after IT whitelist + pubkey):** SSH key-auth to root@47.239.72.102 works (pubkey added, matches DNH-host pattern). Deployed `dna-anim-fix.php` + `dna-ga4.php` to prod mu-plugins (`/opt/1panel/www/sites/app.doctornow.hk/index/wp-content/mu-plugins/`; container path `/www/sites/...`). Prod page IDs confirmed identical (8085/8125 telemedicine, 8015/8049 download). Prod has NO wp-cli — use `docker exec -u www-data PHP7-4-33 php -d display_errors=0 -r '...wp-load.php...'`. Browser-verified prod EN+TC: gtag loads + config G-KVG8BQ85VC fired, no flash/left-shift, scroll y=3796 @0.5s, done=true, no h-overflow, dn_download_intent fires. Home: still animates, no gtag. Rollback: `rm wp-content/mu-plugins/dna-anim-fix.php wp-content/mu-plugins/dna-ga4.php` (pre-apply state backed up in `mu-plugins/backup-2026-08-22-prod/`). MARKETING-LOG DNH-2026-08-22-02. Also noted: prod PHP container has broken imagick extension (warning on php -v; harmless for WP but flag for later). PROD apply still pending SSH whitelist (VM IP 223.19.51.117).

## 5. Notes
- Cache drop-ins from prod (object-cache.php / advanced-cache*.php) removed on staging (referenced prod Redis).
- wp-admin: imported users carry prod hashes — reset password via `wp user update <admin> --user_pass=...` on request.
