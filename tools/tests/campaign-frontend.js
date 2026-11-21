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
        'soto-tracking-config': {textContent: JSON.stringify({ga4: 'G-TEST', ads: 'AW-123', leadLabel: 'lead', whatsappLabel: 'whatsapp', service: 'google-ads'})},
        'soto-lead-receipt': {textContent: JSON.stringify({id: 'a'.repeat(32)})}
    };
    const context = {
        window: {}, document: {referrer: 'https://example.org/?email=private', cookie: '', getElementById: id => nodes[id], createElement: () => ({}), head: {appendChild: script => added.push(script)}, addEventListener: (name, fn) => listeners[name] = fn},
        location: {href: 'https://soto-digital.com/campanas/gracias.php?email=private', hostname: 'soto-digital.com', reload: () => reloads++},
        sessionStorage: {getItem: key => stored.get(key), setItem: (key, value) => stored.set(key, value)}, URL, Date, Set
    };
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/tracking.js'), 'utf8'), context);
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
    check(!JSON.stringify(events).includes('email=private'), 'PII query in tracking');
    check(events.filter(e => e[0] === 'consent').every(e => e[2].ad_personalization === 'denied'), 'Ad personalization enabled');
    listeners.click({target: {closest: () => ({href: 'https://wa.me/34659625857?text=private-message'})}});
    check(context.window.dataLayer.some(e => e[1] === 'whatsapp_click'), 'WhatsApp event missing');
    check(!JSON.stringify(context.window.dataLayer).includes('private-message'), 'WhatsApp message leaked');
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
function floatingWhatsappTest() {
    let callback;
    const button={hidden:true};
    const heroRect={bottom:300};
    const formRect={top:1600,bottom:2200};
    const mobile={matches:true,addEventListener:(name,fn)=>{callback=fn;}};
    const nodes={'[data-floating-whatsapp]':button,'.campaign-hero':{getBoundingClientRect:()=>heroRect},'[data-lead-form]':{getBoundingClientRect:()=>formRect}};
    const context={document:{addEventListener:(name,fn)=>fn(),querySelector:selector=>nodes[selector]},window:{innerHeight:800,matchMedia:()=>mobile,addEventListener(){},IntersectionObserver:class {constructor(fn){callback=fn;}observe(){}}}};
    vm.runInNewContext(fs.readFileSync(path.join(root,'js/campaign-whatsapp.js'),'utf8'),context);
    check(button.hidden,'Floating WhatsApp visible over hero');
    heroRect.bottom=0;callback();check(!button.hidden,'Floating WhatsApp missing after hero');
    formRect.top=799;callback();check(button.hidden,'Floating WhatsApp distracts from form');
    formRect.top=-1200;formRect.bottom=-1;callback();check(!button.hidden,'Floating WhatsApp missing after form');
    mobile.matches=false;callback();check(button.hidden,'Floating WhatsApp visible on desktop');
}
async function wizardTest() {
    const events = {}, controls = {}, steps = [];
    let reported = 0, requestCount = 0, reset = 0, navigated = '', succeeds = false;
    for (let i=0;i<5;i++) {
        const field = {valid: true, checkValidity() {return this.valid;}, reportValidity() {reported++;}};
        steps.push({hidden: false, field, querySelectorAll: () => [field], querySelector: () => ({focus() {}})});
    }
    ['[data-previous]', '[data-next]', '[data-submit]', '[data-progress]', '[data-progress-bar]', '[data-form-error]'].forEach(selector => controls[selector] = {hidden: false, disabled: false, textContent: 'Submit', addEventListener: (name, fn) => controls[selector][name] = fn, scrollIntoView() {}});
    const form = {action: 'https://soto-digital.com/api/lead.php', querySelectorAll: () => steps, querySelector: selector => controls[selector], addEventListener: (name, fn) => events[name] = fn};
    const context = {document: {querySelector: () => form, baseURI: 'https://soto-digital.com/', addEventListener: (name, fn) => fn()}, window: {location: {assign: url => navigated = url}, turnstile: {reset: () => reset++}}, FormData: class {}, URL, fetch: async () => {requestCount++; return {ok: succeeds, json: async () => succeeds ? {ok: true, next: 'campanas/gracias.php'} : {ok: false, error: 'Error de prueba'}};}};
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/campaign.js'), 'utf8'), context);
    check(!steps[0].hidden && steps[4].hidden && controls['[data-submit]'].hidden, 'Wizard initial step');
    steps[0].field.valid = false; controls['[data-next]'].click(); check(reported === 1 && !steps[0].hidden, 'Invalid step advanced');
    steps[0].field.valid = true; controls['[data-next]'].click(); controls['[data-previous]'].click(); check(!steps[0].hidden, 'Back button');
    for(let i=0;i<4;i++) controls['[data-next]'].click();
    check(!steps[4].hidden && !controls['[data-submit]'].hidden, 'Contact step');
    await events.submit({preventDefault() {}});
    check(requestCount === 1 && reset === 1 && controls['[data-form-error]'].textContent === 'Error de prueba' && !navigated, 'Submission failure handling');
    succeeds = true; await events.submit({preventDefault() {}});
    check(navigated === 'https://soto-digital.com/campanas/gracias.php', 'Success redirect');
}
trackingTest();
cookiesTest();
floatingWhatsappTest();
wizardTest().then(() => console.log('PASS: 5-step wizard, validation, back navigation, API errors, success redirect, consent signals, category separation, consent migration, blocked storage, conversions, deduplication and WhatsApp')).catch(error => {console.error(error); process.exitCode=1;});
