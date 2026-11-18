// ---------- page links ----------
// Each service has its own page. Old one-page links (/#security etc.) forward to the new page.
const ROUTES={"home":"/","consulting":"/about/","var":"/about/","about":"/about/","staffaug":"/staff-augmentation/","security":"/security/","voicedata":"/voice-data/","contact":"/contact/"};
const legacy=location.hash.slice(1);
if(legacy!=='home'&&ROUTES[legacy]&&/(^\/$|\/index\.html$)/.test(location.pathname))location.replace(ROUTES[legacy]);
function track(name,params){if(window.gtag)window.gtag('event',name,params);}
// Remember, for this visit only, the first page, the site the visitor came from and any campaign tags,
// so each lead can be traced to its article, search or ad
function leadSource(){
  const q=new URLSearchParams(location.search),own=location.hostname.replace(/^www./,'');
  let from=document.referrer?new URL(document.referrer).hostname.replace(/^www./,''):'';
  if(from===own)from='';   // arrived from another page of this site: treat as direct
  let source='';
  if(q.get('utm_source'))source=[q.get('utm_source'),q.get('utm_medium'),q.get('utm_campaign')].filter(Boolean).join(' / ');
  else if(q.get('gclid')||q.get('gbraid')||q.get('wbraid'))source='google / cpc';
  else if(q.get('li_fat_id'))source='linkedin / paid';
  else if(from)source=from;
  return {page:location.pathname,from:from||'direct',source:source};
}
let LANDING=leadSource();
try{const saved=sessionStorage.getItem('itsn_landing');if(saved)LANDING=Object.assign({source:''},JSON.parse(saved));else sessionStorage.setItem('itsn_landing',JSON.stringify(LANDING));}catch(e){}
document.querySelectorAll('a.btn').forEach(a=>a.addEventListener('click',()=>track('cta_click',{cta_label:a.textContent.trim(),cta_target:a.getAttribute('href')})));
// Phone and email links anywhere on the page (header, footer, contact page)
document.addEventListener('click',e=>{
  const a=e.target.closest&&e.target.closest('a[href^="tel:"],a[href^="mailto:"]');if(!a)return;
  const tel=a.getAttribute('href').startsWith('tel:');
  track(tel?'phone_click':'email_click',{link_location:a.closest('footer')?'footer':a.closest('header')?'header':'page'});
});

// ---------- mobile menu ----------
const nav=document.getElementById('nav'),menuBtn=document.getElementById('menuBtn');
function closeMenu(){nav.classList.remove('open');menuBtn.setAttribute('aria-expanded','false');}
menuBtn.addEventListener('click',()=>{
  const open=nav.classList.toggle('open');
  menuBtn.setAttribute('aria-expanded',open?'true':'false');
});

// ---------- forms (saved as a numbered lead on our own server, then emailed to info@itsecuritynow.com) ----------
const FORM_ENDPOINT='/contact.php';
document.querySelectorAll('[data-form]').forEach(f=>{
  // Someone started typing in the form: lets Analytics show how many start but don't send
  f.addEventListener('focusin',()=>{if(f.dataset.started)return;f.dataset.started='1';track('form_start',{form_name:f.dataset.form||'unknown'});});
  f.addEventListener('submit',async e=>{
    e.preventDefault();
    if(f.querySelector('[name="_honey"]').value)return; // spam bot
    const note=f.querySelector('.form-note');
    const btn=f.querySelector('button[type="submit"]');
    const label=btn.textContent;
    const data=Object.fromEntries(new FormData(f));
    data._form=f.dataset.form||'Contact form';     // which form it came from
    data._source=LANDING.source;                   // where the visitor came from
    data.landing_page=LANDING.page;data.came_from=LANDING.from;
    btn.disabled=true;btn.textContent='Sending…';
    note.classList.remove('show','error');
    try{
      const res=await fetch(FORM_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify(data)});
      const out=await res.json().catch(()=>({}));
      if(!res.ok||out.success!==true){const er=new Error(out.message||res.status);if(res.status===422||res.status===429)er.show=out.message;throw er;}
      note.textContent='Thanks — an ITSN expert will be in touch shortly.';
      note.classList.add('show');
      track('generate_lead',{form_name:f.dataset.form||'unknown',lead_id:out.id||'',lead_source:LANDING.source||'direct',landing_page:LANDING.page,came_from:LANDING.from});
      f.reset();
    }catch(err){
      note.textContent=err.show||'Sorry, your message didn’t send. Please try again or email info@itsecuritynow.com.';
      track('form_error',{form_name:f.dataset.form||'unknown'});
      note.classList.add('show','error');
    }finally{
      btn.disabled=false;btn.textContent=label;
    }
  });
});

// ---------- scroll reveal ----------
document.querySelectorAll('section').forEach(s=>{
  if(s.classList.contains('hero')||s.classList.contains('page-hero')||document.querySelector('.article-body'))return;
  s.classList.add('reveal');
});
if('IntersectionObserver' in window){
  const io=new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){entry.target.classList.add('in-view');io.unobserve(entry.target);}
    });
  },{threshold:.12,rootMargin:'0px 0px -8% 0px'});
  document.querySelectorAll('section.reveal').forEach(s=>io.observe(s));
}else{
  document.querySelectorAll('section.reveal').forEach(s=>s.classList.add('in-view'));
}
