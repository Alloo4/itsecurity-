// ---------- FAQ data ----------
const FAQS = {
  consulting:[
    ["How is ITSN different from a reseller or a vendor's own consultants?","We hold no reseller quotas and take no vendor commissions, so our recommendation is based only on what fits your stack, budget, and compliance needs."],
    ["Do you work with our existing vendors and contracts?","Yes. We assess what you already have and keep what serves you — we only recommend changes where they improve cost, performance, or risk."],
    ["What does \u201Czero-risk partnership\u201D actually mean?","We tie our engagement to your outcomes and stay accountable for results, rather than handing over a deliverable and walking away."],
    ["How quickly do we see a recommendation?","Most clients receive a prioritized roadmap within weeks of the initial assessment."]
  ],
  staffaug:[
    ["How fast can you place a specialist?","Timelines depend on the role, but our existing bench means most placements move in days to a couple of weeks, not months."],
    ["Do your people work on-site, remote, or hybrid?","All three. We match the working model to your security requirements and team setup."],
    ["How do you ensure a placement fits our tools and standards?","Specialists onboard to your processes and security standards before they start delivering, and our wider team backs them throughout."],
    ["Can augmentation convert into a longer engagement?","Yes — augmentation often transitions into a project or managed engagement as needs evolve."]
  ],
  security:[
    ["Which compliance frameworks and regulations do you cover?","We design around the frameworks you answer to, including patient-data privacy, sector regulation, and local data-protection law."],
    ["Can you work with the security tools we already own?","Yes. We're vendor-neutral and build around your existing investments wherever they're effective."],
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
function go(id){
  if(!document.getElementById('page-'+id))id='home';
  pages.forEach(p=>p.classList.toggle('active',p.id==='page-'+id));
  navLinks.forEach(a=>a.classList.toggle('active',a.dataset.go===id));
  document.title=titles[id]||'IT Security Now (ITSN)';
  if(history.replaceState)history.replaceState(null,'','#'+id);
  window.scrollTo({top:0,behavior:'instant' in window?'instant':'auto'});
  closeMenu();
}
document.querySelectorAll('[data-go]').forEach(el=>{
  el.addEventListener('click',e=>{e.preventDefault();go(el.dataset.go);});
});

// ---------- mobile menu ----------
const nav=document.getElementById('nav'),menuBtn=document.getElementById('menuBtn');
function closeMenu(){nav.classList.remove('open');menuBtn.setAttribute('aria-expanded','false');}
menuBtn.addEventListener('click',()=>{
  const open=nav.classList.toggle('open');
  menuBtn.setAttribute('aria-expanded',open?'true':'false');
});

// ---------- forms (prototype: no real submission) ----------
document.querySelectorAll('[data-form]').forEach(f=>{
  f.addEventListener('submit',e=>{
    e.preventDefault();
    const note=f.querySelector('.form-note');
    if(note)note.classList.add('show');
    f.querySelectorAll('input,textarea,select').forEach(i=>i.value='');
  });
});

// ---------- initial route ----------
go((location.hash||'#home').slice(1));
