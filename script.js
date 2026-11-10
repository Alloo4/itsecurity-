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
  var:[
    ["What is a Value Added Reseller (VAR)?","<p>A Value Added Reseller (VAR) is a technology partner that goes beyond simply selling hardware and software. A trusted VAR provides strategic consulting, solution design, procurement assistance, implementation services, vendor management, and ongoing support to help healthcare organizations maximize the value of their technology investments.</p>"],
    ["How is working with a VAR different from buying directly from a manufacturer?","<p>Manufacturers focus on their own products and services. A VAR takes a vendor-neutral approach by evaluating your overall environment, business objectives, and operational requirements to recommend the best-fit solutions across multiple technology providers.</p><p>This allows healthcare organizations to build a cohesive technology strategy rather than managing isolated point solutions.</p>"],
    ["Why should healthcare organizations work with a VAR?","<p>Healthcare organizations face unique challenges, including cybersecurity threats, regulatory requirements, clinical system availability, workforce shortages, and budget pressures. A healthcare-focused VAR helps by:</p><ul><li>Reducing vendor complexity</li><li>Strengthening cybersecurity posture</li><li>Supporting compliance initiatives</li><li>Improving procurement efficiency</li><li>Providing specialized technical expertise</li><li>Accelerating project delivery</li><li>Optimizing technology investments</li></ul>"],
    ["Can a VAR help us improve our cybersecurity posture?","<p>Yes. A trusted healthcare VAR can assess your current security environment and recommend solutions that align with your risk profile, regulatory obligations, and operational requirements. Common security services include:</p><ul><li>Security assessments</li><li>Zero Trust architecture planning</li><li>Identity and access management</li><li>Endpoint protection</li><li>Security operations and monitoring</li><li>Vulnerability management</li><li>Incident response planning</li><li>Security awareness training</li></ul>"],
    ["Do VARs help with HIPAA and compliance initiatives?","<p>While a VAR is not a legal compliance authority, an experienced healthcare VAR can help implement technologies and security controls that support HIPAA, HITECH, and other healthcare compliance requirements. This includes solutions related to:</p><ul><li>Data protection</li><li>Audit logging</li><li>Identity management</li><li>Encryption</li><li>Backup and disaster recovery</li><li>Access controls</li><li>Security monitoring</li></ul>"],
    ["How can a VAR help reduce vendor management challenges?","<p>Most healthcare organizations rely on numerous technology providers, creating administrative burden and support challenges. A VAR serves as a central point of coordination by:</p><ul><li>Managing vendor relationships</li><li>Tracking renewals and licensing</li><li>Coordinating escalations</li><li>Simplifying procurement</li><li>Providing strategic guidance across multiple platforms</li></ul><p>This allows IT leaders to focus more time on strategic initiatives instead of vendor administration.</p>"],
    ["Can a VAR help us modernize aging infrastructure?","<p>Absolutely. Many healthcare organizations are operating legacy infrastructure that may increase risk, limit performance, or create operational inefficiencies. A VAR can help develop a phased modernization roadmap covering:</p><ul><li>Data center infrastructure</li><li>Networking</li><li>Cloud adoption</li><li>Storage platforms</li><li>Backup and recovery solutions</li><li>End-user computing</li><li>Security technologies</li></ul>"],
    ["What size healthcare organizations benefit most from working with a VAR?","<p>Organizations with 500 to 2,000 employees often see significant value because they frequently need enterprise-grade technology expertise but may not have large specialized teams in-house.</p><p>Healthcare systems, physician groups, behavioral health organizations, ambulatory care networks, and post-acute providers can all benefit from a strategic VAR partnership.</p>"],
    ["Will a VAR increase our technology costs?","<p>In many cases, a VAR helps organizations reduce costs by:</p><ul><li>Identifying redundant technologies</li><li>Optimizing licensing agreements</li><li>Consolidating vendors</li><li>Preventing costly implementation mistakes</li><li>Improving technology utilization</li><li>Negotiating more favorable purchasing terms</li></ul><p>The objective is not simply to purchase technology, but to maximize return on investment.</p>"],
    ["Can a VAR support cloud migration initiatives?","<p>Yes. A healthcare VAR can help organizations evaluate, plan, and execute cloud strategies that balance security, compliance, performance, and cost considerations. Typical cloud services include:</p><ul><li>Cloud readiness assessments</li><li>Migration planning</li><li>Hybrid cloud design</li><li>Microsoft Azure implementations</li><li>Backup and recovery strategies</li><li>Security and governance frameworks</li><li>Cloud cost optimization</li></ul>"],
    ["What should we look for when selecting a healthcare VAR?","<p>When evaluating potential VAR partners, consider:</p><ul><li>Healthcare industry experience</li><li>Security and compliance expertise</li><li>Strong vendor relationships</li><li>Certified technical resources</li><li>Strategic consulting capabilities</li><li>Proven implementation methodology</li><li>Long-term support model</li><li>Client references and case studies</li></ul><p>The best VARs act as strategic advisors, not just technology suppliers.</p>"],
    ["How quickly can a healthcare VAR begin delivering value?","<p>Many organizations see value immediately through initial assessments, architecture reviews, security evaluations, and vendor consolidation discussions. Longer-term benefits typically include:</p><ul><li>Improved security posture</li><li>Greater operational efficiency</li><li>Reduced technology complexity</li><li>Better alignment between IT investments and business objectives</li><li>Faster execution of strategic initiatives</li></ul>"]
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
    `<details><summary>${q}<span class="pm"></span></summary>${a.startsWith('<')?a:`<p>${a}</p>`}</details>`).join('');
});

// ---------- page links ----------
// Each service has its own page. Old one-page links (/#security etc.) forward to the new page.
const ROUTES={"home":"/","consulting":"/consulting/","var":"/healthcare-var/","staffaug":"/staff-augmentation/","security":"/security/","voicedata":"/voice-data/","contact":"/contact/"};
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
