# IT Security Now (ITSN) — Website

Static marketing site for IT Security Now, live at **https://itsecurity-five.vercel.app/**.

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

### Adding a new article
1. Copy an existing article folder to `blog/<new-slug>/` and replace the content. Update:
   - `<title>`, meta description, canonical URL, Open Graph/Twitter tags
   - both JSON-LD blocks (`BlogPosting` and `FAQPage`)
   - the hero, date and read time
2. Add a card to the **top** of the grid in `blog/index.html` and in the "From the Blog" section of `index.html`.
3. Add the URL to `sitemap.xml`.
4. Keep the `google-site-verification` meta tag in the `<head>`.
5. After it's live, request indexing in Google Search Console.

Article helpers in `styles.css`:
- `.article-callout` — highlighted stat box
- `.article-table-wrap` / `.article-table` — comparison tables; scroll sideways on phones
- `.faq` — click-to-open FAQ questions
- `.page-hero--<name>` — hero background images

## SEO and Search Console
- Google Search Console is verified with a URL-prefix property for `https://itsecurity-five.vercel.app/`. The verification meta tag is on every page — **don't remove it**.
- The sitemap is submitted in Search Console. It updates automatically when you edit `sitemap.xml`.
- Every page has a canonical URL, Open Graph and Twitter tags, and JSON-LD structured data.

## Preview locally in VS Code
1. Open this folder in VS Code (**File → Open Folder**).
2. Install the **Live Server** extension.
3. Right-click `index.html` → **Open with Live Server** (serves at `http://127.0.0.1:5500`).

Blog links use root-relative paths (`/blog/`), so preview through Live Server rather than opening the files directly.

## Deploying
The site deploys to Vercel **automatically on every push to `main`** on GitHub (`Alloo4/itsecurity-`). Changes are usually live within a minute; use Ctrl+F5 to bypass the browser cache.

No `vercel.json` is needed. For a manual deploy, use the Vercel CLI:
```bash
npm i -g vercel
vercel --prod
```

## To-do
- Replace `GTM-XXXXXXX` (in every page's `<head>` and `<body>`) with the real Google Tag Manager container ID to turn on analytics.
- Swap the three placeholder testimonials for real, attributed quotes.
- If the site moves to `itsecuritynow.com`, do the following, because Search Console treats it as a separate site:
  - Update the canonical URLs, `sitemap.xml` and `robots.txt`.
  - Add a new Search Console property for the new domain.
