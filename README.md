# Overnight Radio Inc. — website

These are the website's actual files: the pages (`.html`), styling (`assets/css`), scripts (`assets/js`),
photos (`images`) and audio (`audio`, `sample`). There's nothing to install and nothing to build: what you see
here is exactly what goes on the web.

## 1. How it works

1. **Edit** a file here on GitHub (or upload a photo or audio file).
2. **Preview** it about a minute later at **https://kraaly53.github.io/overnight-radio-website/**
3. **Publish** it to the real website, **overnightradio.com**, when you're happy: **Actions** tab →
   **Publish to live site** → **Run workflow**. Nothing reaches overnightradio.com until you do this.

## 2. Changing words on a page

1. Open the page's file. Which file is which:

   | Page | File |
   |---|---|
   | Home | `index.html` |
   | Our Services (and prices) | `services.html` |
   | Our Work | `work.html` — each spot's own page is in `work/` (e.g. `work/quiznos.html`) |
   | Team | `team.html` — voices are listed by name and their samples play right on the page |
   | Script Calculator | `calculator.html` |
   | Contact | `contact.html` |
   | Privacy Policy | `privacypolicy.html` |
   | Blog | `blog/index.html` — each post is in its own folder (e.g. `blog/keep-your-ads-fresh/index.html`) |

2. Click the **pencil** (Edit this file).
3. Press **Ctrl+F** (Windows) or **⌘F** (Mac) to find the words you want to change, and type over them. Only change
   the words between the `>` and `<` marks — leave everything inside `< >` alone.
4. Click **Commit changes…** → **Commit changes**.
5. Check the preview (section 1), then publish.

Some words appear on every page — the phone number and email in the footer, for example. To change those
everywhere, ask the developer.

## 3. Getting help from ChatGPT

ChatGPT (the free or basic version is fine) can make the edit for you:

1. Open the file on GitHub and click the **copy** icon (Copy raw file) at the top right of the file.
2. In ChatGPT, write something like:

   > Here is a web page from my website. Change Allison's bio to: "…". Don't change anything else, and give me
   > back the whole file.

   then paste the file underneath and send it.
3. Copy ChatGPT's answer, open the same file on GitHub, click the **pencil**, select everything
   (**Ctrl+A** / **⌘A**), paste over it, and **Commit changes**.
4. **Always check the preview before publishing.** If something looks wrong, open the file's **History** and
   restore the earlier version, or just ask ChatGPT to try again.

## 4. Photos and voice samples

- **Replace a photo:** open `images/announcers/`, **Add file → Upload files**, and upload a photo with **exactly the
  same file name** as the old one (e.g. `allison.jpg`). Square photos, at least 600×600, look best. (The Team page
  no longer shows announcer photos; they're still used by the archived Team page and each person's old page.)
- **Replace a voice sample:** do the same in `audio/` (e.g. `allison.mp3`). Browsers remember old audio, so if the
  preview still plays the old sample, wait a little or refresh.
- **New file names:** if you upload a file with a different name, the page has to be changed to point at it — ask
  ChatGPT (section 3) or the developer.

## 5. Bigger changes

These touch several files at once, so ask the developer (or ask ChatGPT carefully, one file at a time):

- **Adding a new announcer:** upload their sample to `audio/` (`.mp3`, plus `.ogg` if you have one), then copy
  one `<li class="voice" …>…</li>` entry on `team.html` and change the name (in `data-name`, the name and the
  two labels) and the sample's file names. No photo or separate page is needed.
- **Adding a blog post:** a new folder in `blog/` (copy an existing post), plus an entry on `blog/index.html`.
  The blog's category, tag and month pages and its search won't include the new post automatically.
- **Removing someone or something:** delete the file **and** remove its link from the page that lists it.

## 6. Rules that keep the site working

- **Don't rename or move files or folders.** Their names are the website's addresses.
- **Keep links as they are** (e.g. `href="team.html"`, `src="../images/..."`). Don't change them to start with
  `/` or `https://`, or the preview won't work.
- **Check the preview after every change**, then publish.
- Every change is saved in the file's **History**, so nothing is ever lost — you can always go back.

---

### For the developer

- **Preview:** GitHub Pages, from the `main` branch, root folder. Links are relative, so the same files work on
  GitHub Pages, in a sub-folder on the test server, and at the root of overnightradio.com. `.nojekyll` makes
  GitHub Pages serve the files as they are.
- **Publish:** `.github/workflows/publish.yml` (manual only) uploads to the live overnightradio.com by FTP. It needs
  the secrets `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` (overnightradio.com's FTP login) and the variables
  `FTP_REMOTE_DIR` (the web folder as that FTP login sees it) and `SITE_URL` (`https://overnightradio.com/`). It
  only adds and updates files: it **never deletes** anything on the server (so WordPress, old pages and other
  hosting files are left alone) and **never uploads `.htaccess`** (the server keeps its own). Before uploading, it downloads the server's current copy of every file it's about to replace and keeps it
  for 90 days as the run's **server-backup** download (Actions → the run → Artifacts) — to undo a publish,
  upload those files back by FTP. README, `.github`,
  `.nojekyll` and git files are never uploaded. To remove a page from the live site, delete it on the server too.
- **Server-only behaviour:** the repository's `.htaccess` (caching rules) isn't published — copy its lines into
  the server's own `.htaccess` by hand if wanted; GitHub Pages ignores it.
  GitHub Pages can't run PHP, so any `.php` files work on the real server only.
- **Contact form:** `contact.html` posts to `contact.php`, which emails each message to
  info@overnightradio.com (sender `website@overnightradio.com`, with Reply-To set to the visitor, so replying
  goes straight to them). The addresses, subject, allowed sites and spam limits are the settings at the top of
  `contact.php`. Spam protection: a hidden field, a 3-second minimum and 5 messages per visitor per hour.
  PHP only runs on the real server, so on the GitHub Pages preview the form sends to
  `https://overnightradio.com/contact.php` instead — it works from the preview only after `contact.php` has been
  published. overnightradio.com is Windows hosting (Site4Now) with no local mail server, so `contact.php` logs in
  to a mail server (SMTP) to send. Add these repository **secrets** and run the publish: `SMTP_HOST`, `SMTP_PORT`
  (465 for SSL, otherwise STARTTLS, default 587), `SMTP_USERNAME`, `SMTP_PASSWORD`, and optionally `SMTP_FROM`
  (the sender address; defaults to the username if it's an email address). The domain's SPF record allows
  Amazon SES and Site4Now mail servers. The publish writes them to `contact-config.php` on the server only (it's
  in `.gitignore`, never in the repository or the backup download). If sending fails, the reason is in the
  server's PHP error log.
- **Tab and home-screen icons:** `favicon.svg` (modern browsers), `favicon.ico` (16/32/48 px fallback) and
  `apple-touch-icon.png` (180 px, iPhone home screen) — the radio-tower logo in white on midnight blue. The pages link them with `?v=2`; if an icon changes, raise that
  number on every page so browsers (Safari especially) stop showing the old one.
- **Team page:** since November 2026 the Talent list has no photos or links: each card shows the name with the
  same audio player as the individual pages underneath (play/pause, waveform you can click to jump, time). Only
  one sample plays at a time, and the playing card is outlined in gold. The earlier version — photos, each linking to
  the person's page in `team/` — is kept in `archive/team-with-photos.html` (with frozen copies of the CSS and
  JavaScript beside it). It's viewable on the preview at `archive/team-with-photos.html`, isn't linked from the
  site, and the publish never uploads `archive/`. The pages in `team/` still exist so old links keep working.
- **Alternative logo (not in use):** the "Moonwave" mark — a crescent moon with two amber radio waves — was
  tried in November 2026 and set aside; the site uses the original radio-tower logo. Its artwork is kept in
  `images/logo/` (`logo-moonwave.svg`, `favicon-moonwave.svg`, `favicon-moonwave.ico`,
  `apple-touch-icon-moonwave.png`), and the commit "New Moonwave logo on every page…" shows how it was applied.
- **History:** these files were generated from the Eleventy project at
  [kraaly53/onr](https://github.com/kraaly53/onr), which has the full documentation, the archived design review
  and the build tooling. From now on, edit the files here directly.
