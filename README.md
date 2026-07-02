# IT Security Now (ITSN) — Website

Static marketing site. Six pages (Home, Consulting, Staff Augmentation, Security, Voice & Data, Contact) in a single `index.html`, with in-page routing via hash links.

## Files
- `index.html` — markup for all pages + header/footer
- `styles.css` — all styling and responsive rules
- `script.js` — page routing, mobile menu, FAQ accordions, form handling

No build step and no dependencies (fonts load from Google Fonts).

## Preview locally in VS Code
1. Open this folder in VS Code (**File → Open Folder**).
2. Install the **Live Server** extension.
3. Right-click `index.html` → **Open with Live Server** (serves at `http://127.0.0.1:5500`).

## Deploy to Vercel
This is a static site, so **no `vercel.json` is needed** — Vercel serves `index.html` at the root automatically.

**Option A — drag & drop:** In the Vercel dashboard, **Add New… → Project**, then drag this folder in.

**Option B — CLI (better for updates):**
```bash
npm i -g vercel
cd itsn-site
vercel          # first deploy; pick the daryl-alloos-projects scope when asked
vercel --prod   # promote to production / push later updates
```

## Notes / to-do before launch
- Replace the placeholder contact details in `index.html` (Call / Email / Office / Hours) with real Nairobi info.
- The contact + "Start Your Transformation" forms are front-end only — wire them to a real endpoint, email, or form service before going live.
- Swap the three placeholder testimonials for real, attributed quotes when available.
