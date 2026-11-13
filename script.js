// ---------- page links ----------
// Each service has its own page. Old one-page links (/#security etc.) forward to the new page.
const ROUTES={"home":"/","consulting":"/about/","var":"/about/","about":"/about/","staffaug":"/staff-augmentation/","security":"/security/","voicedata":"/voice-data/","contact":"/contact/"};
const legacy=location.hash.slice(1);
if(legacy!=='home'&&ROUTES[legacy]&&/(^\/$|\/index\.html$)/.test(location.pathname))location.replace(ROUTES[legacy]);
function track(name,params){if(window.gtag)window.gtag('event',name,params);}
document.querySelectorAll('a.btn').forEach(a=>a.addEventListener('click',()=>track('cta_click',{cta_label:a.textContent.trim(),cta_target:a.getAttribute('href')})));

// ---------- mobile menu ----------
const nav=document.getElementById('nav'),menuBtn=document.getElementById('menuBtn');
function closeMenu(){nav.classList.remove('open');menuBtn.setAttribute('aria-expanded','false');}
menuBtn.addEventListener('click',()=>{
  const open=nav.classList.toggle('open');
  menuBtn.setAttribute('aria-expanded',open?'true':'false');
});

// ---------- forms (emailed to info@itsecuritynow.com via FormSubmit) ----------
const FORM_ENDPOINT='https://formsubmit.co/ajax/info@itsecuritynow.com';
document.querySelectorAll('[data-form]').forEach(f=>{
  f.addEventListener('submit',async e=>{
    e.preventDefault();
    const note=f.querySelector('.form-note');
    const btn=f.querySelector('button[type="submit"]');
    const label=btn.textContent;
    const data=Object.fromEntries(new FormData(f));
    data._subject='New website enquiry: '+(f.dataset.form||'Contact form')+' — '+(data.name||'');
    data._template='table';
    data._replyto=data.email;
    btn.disabled=true;btn.textContent='Sending…';
    note.classList.remove('show','error');
    try{
      const res=await fetch(FORM_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify(data)});
      const out=await res.json().catch(()=>({}));
      if(!res.ok||out.success===false||out.success==='false')throw new Error(out.message||res.status);
      note.textContent='Thanks — an ITSN expert will be in touch shortly.';
      note.classList.add('show');
      track('generate_lead',{form_name:f.dataset.form||'unknown'});
      f.reset();
    }catch(err){
      note.textContent='Sorry, your message didn’t send. Please try again or email info@itsecuritynow.com.';
      note.classList.add('show','error');
    }finally{
      btn.disabled=false;btn.textContent=label;
    }
  });
});

// ---------- scroll reveal ----------
document.querySelectorAll('section').forEach(s=>{
  if(s.classList.contains('hero')||s.classList.contains('page-hero'))return;
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
