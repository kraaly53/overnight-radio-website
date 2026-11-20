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
   | Team | `team.html` — each person's own page is in `team/` (e.g. `team/allison.html`) |
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
  same file name** as the old one (e.g. `allison.jpg`). Square photos, at least 600×600, look best.
- **Replace a voice sample:** do the same in `audio/` (e.g. `allison.mp3`). Browsers remember old audio, so if the
  preview still plays the old sample, wait a little or refresh.
- **New file names:** if you upload a file with a different name, the page has to be changed to point at it — ask
  ChatGPT (section 3) or the developer.

## 5. Bigger changes

These touch several files at once, so ask the developer (or ask ChatGPT carefully, one file at a time):

- **Adding a new announcer:** a new page in `team/` (copy an existing one), plus an entry on `team.html`.
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
  hosting files are left alone) and **never uploads `.htaccess`** (the server keeps its own). README, `.github`,
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
  published. overnightradio.com is Windows (IIS) hosting with PHP 8.5; PHP's `mail()` there needs the host's
  outgoing mail (SMTP) settings — if a test says "could not be sent", ask the host to enable it.
- **History:** these files were generated from the Eleventy project at
  [kraaly53/onr](https://github.com/kraaly53/onr), which has the full documentation, the archived design review
  and the build tooling. From now on, edit the files here directly.
