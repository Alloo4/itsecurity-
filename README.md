# IT Security Now (ITSN) — Website

Static marketing site for IT Security Now, live at **https://itsecuritynow.com/**.

A preview copy also runs on Vercel at https://itsecurity-five.vercel.app/. Its pages point search engines to itsecuritynow.com, so Google treats itsecuritynow.com as the real site.

- **Main site:** six pages (Home, Consulting, Staff Augmentation, Security, Voice & Data, Contact) in a single `index.html`, with in-page routing via hash links (e.g. `/#contact`).
- **Blog:** separate static pages under `/blog/`.

No build step and no dependencies (fonts load from Google Fonts).

## Files
| Path | What it is |
|---|---|
| `index.html` | Markup for all main pages, header and footer |
| `styles.css` | All styling and responsive rules (shared by the blog) |
| `script.js` | Page routing, mobile menu, FAQ accordions, contact form submission |
| `blog/index.html` | Blog listing page |
| `blog/<slug>/index.html` | One folder per article |
| `assets/logos/` | Official logo files (SVG + PNG) — see [assets/logos/README.md](assets/logos/README.md) |
| `sitemap.xml`, `robots.txt` | For search engines |
| `.htaccess` | Redirects `www.itsecuritynow.com` to `itsecuritynow.com` (used by the live host; ignored by Vercel) |

## Branding
- **Header:** teal linear logo (`itsn-logo-linear-teal.svg`)
- **Footer:** white linear logo (`itsn-logo-linear-white.svg`)
- **Browser tab icon:** `itsn-icon-teal.svg`; Apple touch icon `itsn-icon-square-teal-bg.png`
- **Brand teal:** `#13566f`

## Contact forms
Both forms (Home "Start Your Transformation" and the Contact page) send submissions to **info@itsecuritynow.com** through [FormSubmit](https://formsubmit.co) — see `FORM_ENDPOINT` in `script.js`.

- The form is activated. If the destination email ever changes, the new address must be activated once: submit the form, then click **Activate Form** in the email FormSubmit sends.
- Each email's subject names the form it came from, and pressing Reply answers the visitor.
- A hidden `_honey` field filters most spam bots.

## Blog
Current articles:
1. `cybersecurity-services-for-healthcare/` — Sep 17, 2026 (Security)
2. `soc-2-vs-hipaa-compliance/` — Oct 9, 2026 (Compliance)
3. `manufacturing-cybersecurity-production-downtime/` — Oct 16, 2026 (Security)
4. `healthcare-network-security-best-practices/` — Oct 21, 2026 (Security)

### Adding a new article
1. Copy an existing article folder to `blog/<new-slug>/` and replace the content. Update:
   - `<title>`, meta description, canonical URL, Open Graph/Twitter tags
   - both JSON-LD blocks (`BlogPosting` and `FAQPage`)
   - the hero, date and read time
2. Add a card to the **top** of the grid in `blog/index.html` and in the "From the Blog" section of `index.html` (the home page shows the 3 newest — remove the oldest card there).
3. Add the URL to `sitemap.xml`.
4. Keep both `google-site-verification` meta tags in the `<head>`.
5. After it's live, request indexing in Google Search Console.

Article helpers in `styles.css`:
- `.article-callout` — highlighted stat box
- `.article-table-wrap` / `.article-table` — comparison tables; scroll sideways on phones
- `.faq` — click-to-open FAQ questions
- `.page-hero--<name>` — hero background images

## SEO and Search Console
- Google Search Console tracks **https://itsecuritynow.com/**, verified by the `google-site-verification` meta tag with content `79mfcSNk…` on every page. **Never remove it**, and make sure it's in every upload; if Google can't find it, verification is lost.
- The sitemap `https://itsecuritynow.com/sitemap.xml` is submitted in Search Console. Google re-reads it automatically after each upload.
- All canonical URLs, Open Graph tags, structured data, `sitemap.xml` and `robots.txt` use `https://itsecuritynow.com`.
- A second verification tag (`ad7PQXWM…`) is from the old vercel.app property. It's harmless and can be removed once that property is deleted from Search Console.
- Every page has a canonical URL, Open Graph and Twitter tags, and JSON-LD structured data.

## Preview locally in VS Code
1. Open this folder in VS Code (**File → Open Folder**).
2. Install the **Live Server** extension.
3. Right-click `index.html` → **Open with Live Server** (serves at `http://127.0.0.1:5500`).

Blog links use root-relative paths (`/blog/`), so preview through Live Server rather than opening the files directly.

## Deploying
There are two copies of the site.

**1. itsecuritynow.com (the real site) — manual upload.** It's on separate hosting and does **not** update when you push to GitHub. After every change:
1. Commit and push to GitHub as usual.
2. Build an upload zip of the current site:
   ```bash
   git archive --format=zip -o ../itsecuritynow-upload.zip HEAD
   ```
3. In the hosting control panel, open **File Manager**, go to the site's folder (usually `public_html`), upload the zip and **Extract** it, replacing existing files.
4. Turn on **Show hidden files** in File Manager to confirm `.htaccess` is there.
5. Check https://itsecuritynow.com with Ctrl+F5.

**2. Vercel preview — automatic.** Every push to `main` on GitHub (`Alloo4/itsecurity-`) deploys to https://itsecurity-five.vercel.app/ within about a minute. No `vercel.json` is needed.

## To-do
- Replace `GTM-XXXXXXX` (in every page's `<head>` and `<body>`) with the real Google Tag Manager container ID to turn on analytics.
- Swap the three placeholder testimonials for real, attributed quotes.
- Consider pointing itsecuritynow.com's DNS to Vercel, so every GitHub push goes live automatically and the manual upload step goes away.
