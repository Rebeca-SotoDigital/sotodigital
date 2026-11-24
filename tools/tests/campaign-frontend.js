// Run with Node.js. Logic tests only; no browser, real tracking or API requests.
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
function check(condition, message) { if (!condition) throw new Error(message); }
function trackingTest() {
    const added = [], listeners = {}, stored = new Map();
    let reloads = 0;
    const nodes = {
        'soto-tracking-config': {textContent: JSON.stringify({ga4: 'G-TEST', ads: 'AW-123', leadLabel: 'lead', service: 'google-ads'})},
        'soto-lead-receipt': {textContent: JSON.stringify({id: 'a'.repeat(32)})}
    };
    const context = {
        window: {}, document: {referrer: 'https://example.org/?email=private', cookie: '', getElementById: id => nodes[id], createElement: () => ({}), head: {appendChild: script => added.push(script)}, addEventListener: (name, fn) => listeners[name] = fn},
        location: {href: 'https://soto-digital.com/campanas/gracias.php?email=private', hostname: 'soto-digital.com', reload: () => reloads++},
        sessionStorage: {getItem: key => stored.get(key), setItem: (key, value) => stored.set(key, value)}, URL, Date, Set
    };
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/tracking.js'), 'utf8'), context);
    context.window.sotoTracking.event('lead_form_start', {email:'private'});
    check(!context.window.dataLayer.some(e=>e[1]==='lead_form_start'),'Funnel before consent');
    context.window.sotoTracking.apply({analytics: false, advertising: false});
    check(added.length === 0, 'Google loaded before consent');
    context.window.sotoTracking.apply({analytics: false, advertising: true});
    context.window.sotoTracking.apply({analytics: false, advertising: true});
    let events = context.window.dataLayer.map(args => Array.from(args));
    check(added.length === 1, 'Google loaded more than once');
    check(events.filter(e => e[0] === 'event' && e[1] === 'conversion').length === 1, 'Lead conversion duplicate');
    check(!events.some(e => e[1] === 'generate_lead'), 'Analytics without analytics consent');
    context.window.sotoTracking.apply({analytics: true, advertising: true});
    context.window.sotoTracking.apply({analytics: true, advertising: true});
    events = context.window.dataLayer.map(args => Array.from(args));
    check(events.filter(e => e[1] === 'generate_lead').length === 1, 'GA lead duplicate');
    context.window.sotoTracking.event('lead_form_step',{step:2,email:'private',type:'network'});
    const funnel=context.window.dataLayer.find(e=>e[1]==='lead_form_step');
    check(funnel && funnel[2].step===2 && !('email' in funnel[2]),'Funnel allowlist');
    check(!JSON.stringify(events).includes('email=private'), 'PII query in tracking');
    check(events.filter(e => e[0] === 'consent').every(e => e[2].ad_personalization === 'denied'), 'Ad personalization enabled');
    context.window.sotoTracking.apply({analytics: false, advertising: false});
    check(reloads === 1, 'Revocation did not unload Google');
    // A fresh page without a genuine receipt never reports a lead.
    delete nodes['soto-lead-receipt']; context.window.dataLayer = []; stored.clear();
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/tracking.js'), 'utf8'), context);
    context.window.sotoTracking.apply({analytics: true, advertising: true});
    check(!context.window.dataLayer.some(e => e[1] === 'generate_lead' || (e[1] === 'conversion' && e[2].send_to.endsWith('/lead'))), 'False receipt conversion');
}
function cookiesTest() {
    function run(saved, blocked) {
        const nodes={}, added=[], applied=[];
        let stored=saved, reloads=0;
        ['cookie-notice','cookie-settings','accept-all','reject-all','open-cookie-panel','open-cookie-settings','save-settings','accept-all-settings','reject-all-settings','analytics-cookies','advertising-cookies','company-identification-cookies'].forEach(id => nodes[id]={style:{},checked:false,addEventListener:(event,fn)=>nodes[id][event]=fn});
        nodes['company-identification-config']={textContent:JSON.stringify({url:'https://sc.lftracker.com/scripts/test.js'})};
        const context={document:{addEventListener:(event,fn)=>fn(),getElementById:id=>nodes[id],createElement:()=>({}),head:{appendChild:script=>added.push(script)},cookie:''},window:{sotoTracking:{apply:prefs=>applied.push({...prefs})}},location:{hostname:'soto-digital.com',reload:()=>reloads++},localStorage:{getItem:()=>stored,setItem:(key,value)=>{if(blocked) throw new Error('Storage unavailable'); stored=value;}},Date};
        vm.runInNewContext(fs.readFileSync(path.join(root,'js/cookies.js'),'utf8'),context);
        return {nodes,added,applied,get reloads(){return reloads;}};
    }
    const old=run(JSON.stringify({version:2,analytics:true,advertising:true,companyIdentification:true}));
    check(old.added.length===0 && old.applied.every(p=>!p.analytics&&!p.advertising), 'Old consent reused for Google');
    old.nodes['accept-all'].click(); old.nodes['accept-all'].click();
    check(old.added.length===1, 'Company tracker duplicate');
    old.nodes['reject-all-settings'].click(); check(old.reloads===1, 'Company revocation');
    const blocked=run(null,true); blocked.nodes['accept-all'].click();
    check(blocked.added.length===0 && !blocked.applied.at(-1).analytics && !blocked.applied.at(-1).advertising, 'Storage failure enabled tracking');
}
async function wizardTest() {
    const events = {}, controls = {}, steps = [];
    let reported = 0, requestCount = 0, reset = 0, navigated = '', succeeds = false;
    for (let i=0;i<5;i++) {
        const field = {valid: true, checkValidity() {return this.valid;}, reportValidity() {reported++;}};
        steps.push({hidden: false, field, querySelectorAll: () => [field], querySelector: () => ({focus() {}})});
    }
    ['[data-previous]', '[data-next]', '[data-submit]', '[data-progress]', '[data-progress-bar]', '[data-form-error]'].forEach(selector => controls[selector] = {hidden: false, disabled: false, textContent: 'Submit', addEventListener: (name, fn) => controls[selector][name] = fn, scrollIntoView() {}, setAttribute(name, value) { this[name] = value; }, removeAttribute(name) { delete this[name]; }});
    const form = {action: 'https://soto-digital.com/api/lead.php', querySelectorAll: () => steps, querySelector: selector => controls[selector], addEventListener: (name, fn) => events[name] = fn};
    const context = {document: {querySelector: () => form, baseURI: 'https://soto-digital.com/', addEventListener: (name, fn) => fn()}, window: {location: {assign: url => navigated = url}, turnstile: {reset: () => reset++}}, FormData: class {}, URL, setTimeout, clearTimeout, fetch: async () => {requestCount++; return {ok: succeeds, json: async () => succeeds ? {ok: true, next: '/campanas/gracias.php'} : {ok: false, error: 'Error de prueba'}};}};
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/campaign.js'), 'utf8'), context);
    check(!steps[0].hidden && steps[4].hidden && controls['[data-submit]'].hidden, 'Wizard initial step');
    steps[0].field.valid = false; controls['[data-next]'].click(); check(reported === 1 && !steps[0].hidden, 'Invalid step advanced');
    check(!controls['[data-form-error]'].hidden && controls['[data-form-error]'].textContent==='Elige una opción para continuar.','Missing option failure was silent');
    steps[0].field.valid = true; events.change({target:{matches:()=>true}}); await new Promise(resolve=>setTimeout(resolve,275)); check(!steps[1].hidden,'Radio auto advance'); controls['[data-previous]'].click(); check(!steps[0].hidden, 'Back button');
    for(let i=0;i<4;i++) controls['[data-next]'].click();
    check(!steps[4].hidden && !controls['[data-submit]'].hidden, 'Contact step');
    steps[4].field.name='website';steps[4].field.valid=false;
    await events.submit({preventDefault() {}});
    check(requestCount===0 && controls['[data-form-error]'].textContent.includes('https://'),'Invalid website did not explain how to fix it');
    steps[4].field.name='privacy';
    await events.submit({preventDefault() {}});
    check(requestCount===0 && controls['[data-form-error]'].textContent.includes('casilla'),'Missing privacy failure was silent');
    steps[4].field.valid=true;
    await Promise.all([events.submit({preventDefault() {}}),events.submit({preventDefault() {}})]);
    check(requestCount === 1 && reset === 1 && controls['[data-form-error]'].textContent === 'No hemos podido enviar tu solicitud. Inténtalo de nuevo o escríbenos por WhatsApp.' && !navigated, 'Submission failure handling');
    succeeds = true; await events.submit({preventDefault() {}});
    check(navigated === 'https://soto-digital.com/campanas/gracias.php', 'Success redirect');
}
trackingTest();
cookiesTest();
wizardTest().then(() => console.log('PASS: 5-step wizard, validation, back navigation, API errors, success redirect, consent signals, category separation, consent migration, blocked storage, conversions, deduplication')).catch(error => {console.error(error); process.exitCode=1;});
