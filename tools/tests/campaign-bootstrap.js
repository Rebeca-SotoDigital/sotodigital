// Isolated UI logic; no real browser, CAPTCHA or provider requests.
const fs=require('node:fs'), vm=require('node:vm'), path=require('node:path');
const source=fs.readFileSync(path.join(__dirname,'../../js/campaign-bootstrap.js'),'utf8');
async function checkWidth(width,expected){
    const events={},rendered=[];let hidden=true,token='synthetic-token',requests=0;
    const fields={service:{value:'google-ads'},csrf:{value:''},submission:{value:''},attribution_token:{value:''}};
    const challenge={clientWidth:width,dataset:{sitekey:'test-only'},closest:()=>({hidden})};
    const form={dataset:{tokenUrl:'/api/token.php'},elements:fields,querySelector:s=>s==='[data-turnstile-container]'?challenge:s==='[data-submit]'?{disabled:false}:null,addEventListener:(name,fn)=>events[name]=fn,appendChild:()=>{}};
    const context={document:{baseURI:'https://example.com/',querySelector:()=>form,addEventListener:(name,fn)=>fn(),createElement:()=>({}),head:{appendChild:tag=>tag.onload()}},window:{turnstile:{render:(node,options)=>{rendered.push(options);return 'widget';},getResponse:()=>token}},location:{href:'https://example.com/campanas/google-ads.php'},URL,fetch:async()=>{requests++;return {ok:true,json:async()=>({ok:true,csrf:'csrf',submission:'a'.repeat(32),attribution_token:'attribution'})};}};
    vm.runInNewContext(source,context);
    events.focusin();await Promise.resolve();await Promise.resolve();
    if(rendered.length)throw Error('Challenge rendered while contact step hidden');
    hidden=false;await context.window.sotoLeadForm.prepare();
    if(rendered.length!==1||rendered[0].size!==expected||requests!==1||fields.csrf.value!=='csrf')throw Error('Challenge sizing or token preparation failed');
    await context.window.sotoLeadForm.prepare();if(rendered.length!==1||requests!==1)throw Error('Challenge duplicated');
    token='';try{await context.window.sotoLeadForm.prepare();throw Error('Missing verification accepted');}catch(e){if(e.kind!=='validation')throw e;}
}
Promise.all([checkWidth(248,'compact'),checkWidth(360,'normal')]).then(()=>console.log('PASS: contact-step challenge visibility, compact/normal widths, single session/widget and missing-verification error')).catch(e=>{console.error(e);process.exitCode=1;});
