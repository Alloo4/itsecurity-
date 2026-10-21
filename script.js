// ---------- FAQ data ----------
const FAQS = {
  consulting:[
    ["How is ITSN different from a reseller or a vendor’s own consultants?","We hold no reseller quotas and don’t provide solutions based on vendor commissions. Our recommendations are solely based on what fits your stack, budget, and compliance needs."],
    ["Do you work with our existing vendors and contracts?","Yes. We assess what you already have and keep what serves you — we only recommend changes where they improve cost, performance, or risk."],
    ["What does \u201Czero-risk partnership\u201D actually mean?","We tie our engagement to your outcomes and stay accountable for results, rather than handing over a deliverable and walking away."],
    ["How quickly do we see a recommendation?","Most clients receive a prioritized roadmap within weeks of the initial assessment."]
  ],
  staffaug:[
    ["How fast can you place a specialist?","Timelines depend on the role, but our existing bench means most placements move in days to a couple of weeks, not months."],
    ["Do your people work on-site, remote, or hybrid?","All three. We match the working model to your security requirements and team setup."],
    ["How do you ensure a placement fits our tools and standards?","Specialists onboard to your processes and security standards before they start delivering, and our wider team backs them throughout."],
    ["Can augmentation convert into a longer engagement?","Yes — staff augmentation often transitions into additional projects, which may include a bucket of hours, fractional employee, and even full-time placements."]
  ],
  security:[
    ["Which compliance frameworks and regulations do you cover?","We design around the frameworks you answer to, including patient-data privacy, sector regulation, and local data-protection law. Some examples: CMMC, COBIT, HIPAA, HITECH, PCI DSS, SOC 2, NIST CSF, and NIST 800-53."],
    ["Can you work with the security tools we already own?","Yes — we’re vendor-neutral. ITSN helps plan, build, and strategize by leveraging your existing investments (wherever they’re most effective)."],
    ["Do you provide ongoing monitoring or just one-time assessments?","Both. We can run a point-in-time assessment or provide continuous monitoring and improvement."],
    ["How do you balance strong security with clinical or production workflows?","We design controls that protect what matters without slowing the people who keep operations running."]
  ],
  voicedata:[
    ["Can you modernize our communications without replacing the whole network?","Yes. We integrate with what works and modernize in stages to avoid disruption."],
    ["How do patient-communication platforms integrate with our existing systems?","We connect platforms like Artera and Infobip to your core systems so communication flows without manual rekeying."],
    ["Do you manage the connectivity long-term or hand it back?","Your choice — we can run it as a managed layer or transition it to your team with documentation and training."],
    ["How quickly can we expect cost savings?","Consolidating contracts and right-sizing connectivity typically reduces spend within the first optimization cycle."]
  ],
  contact:[
    ["What happens after I request an assessment?","An ITSN expert reviews your details and reaches out to scope a short, no-obligation conversation."],
    ["Is the initial assessment really free / no-obligation?","Yes. The first conversation and assessment carry no cost or commitment."],
    ["Which industries and regions do you serve?","We focus on healthcare, health tech, and manufacturing, serving mid-market and enterprise organizations."],
    ["How soon will someone respond?","We aim to respond within one business day."]
  ]
};
document.querySelectorAll('[data-faq]').forEach(box=>{
  const items=FAQS[box.dataset.faq]||[];
  box.innerHTML=items.map(([q,a])=>
    `<details><summary>${q}<span class="pm"></span></summary><p>${a}</p></details>`).join('');
});

// ---------- routing ----------
const pages=document.querySelectorAll('.page');
const navLinks=document.querySelectorAll('.nav a');
const titles={home:'IT Security Now (ITSN)',consulting:'Consulting Service — ITSN',staffaug:'Staff Augmentation — ITSN',security:'Security Service — ITSN',voicedata:'Voice & Data — ITSN',contact:'Contact — ITSN'};
const descriptions={
  home:'ITSN empowers healthcare, health tech, and manufacturing organizations with AI-guided decisions, a zero-risk partnership model, and end-to-end transformation.',
  consulting:'Vendor-neutral, AI-guided IT consulting with a zero-risk partnership model — strategy that’s accountable to your outcomes, not your vendors.',
  staffaug:'Vetted IT, cloud, and security specialists who plug into your workflows and standards from day one — an extension of your team, without the hiring risk.',
  security:'End-to-end security and compliance for regulated environments: assessment, zero-trust architecture, continuous monitoring, and incident response.',
  voicedata:'Unified network, voice, and patient-engagement connectivity — one reliable, optimized layer from the network up to the conversation.',
  contact:'Request a free, no-obligation IT assessment from ITSN — serving healthcare, health tech, and manufacturing organizations.'
};
const metaDescription=document.querySelector('meta[name="description"]');
window.dataLayer=window.dataLayer||[];
let isInitialRoute=true;
function go(id){
  if(!document.getElementById('page-'+id))id='home';
  pages.forEach(p=>p.classList.toggle('active',p.id==='page-'+id));
  navLinks.forEach(a=>a.classList.toggle('active',a.dataset.go===id));
  document.title=titles[id]||'IT Security Now (ITSN)';
  if(metaDescription)metaDescription.setAttribute('content',descriptions[id]||descriptions.home);
  if(history.replaceState)history.replaceState(null,'','#'+id);
  if(!isInitialRoute){
    requestAnimationFrame(()=>window.scrollTo(0,0));
    window.dataLayer.push({event:'virtual_pageview',page_path:'/#'+id,page_title:titles[id]||'IT Security Now (ITSN)'});
  }
  isInitialRoute=false;
  closeMenu();
}
document.querySelectorAll('[data-go]').forEach(el=>{
  el.addEventListener('click',e=>{
    e.preventDefault();
    if(el.tagName==='BUTTON')window.dataLayer.push({event:'cta_click',cta_label:el.textContent.trim(),cta_target:el.dataset.go});
    go(el.dataset.go);
  });
});

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
      window.dataLayer.push({event:'generate_lead',form_id:f.dataset.form||'unknown'});
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

// ---------- initial route ----------
go((location.hash||'#home').slice(1));
window.addEventListener('hashchange',()=>go((location.hash||'#home').slice(1)));
