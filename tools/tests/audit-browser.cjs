const path=require('node:path'),fs=require('node:fs');
const dep=name=>require(process.env.SOTO_AUDIT_MODULES?path.join(process.env.SOTO_AUDIT_MODULES,name):name);
const {chromium}=dep('playwright');const AxeBuilder=dep('@axe-core/playwright').default;
const root=path.resolve(__dirname,'../..'),base=process.env.SOTO_AUDIT_BASE||'http://127.0.0.1:8092';
const stage=process.env.SOTO_AUDIT_STAGE||'after',out=path.join(root,'docs/auditoria-2026-11');fs.mkdirSync(path.join(out,'capturas'),{recursive:true});
(async()=>{
 const browser=await chromium.launch({headless:true,args:['--no-proxy-server'],executablePath:process.env.SOTO_AUDIT_CHROME||'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const results=[],failures=[],consoleErrors=[];
 for(const width of [390,1440]){
  const context=await browser.newContext({viewport:{width,height:900},deviceScaleFactor:1,serviceWorkers:'block'});
  await context.route('**/*',route=>{
   const url=route.request().url();if(url.startsWith(base))return route.continue();
   if(url.startsWith('https://challenges.cloudflare.com/turnstile/v0/api.js'))return route.fulfill({contentType:'application/javascript',body:"window.turnstile={render:function(el){var i=document.createElement('input');i.type='hidden';i.name='cf-turnstile-response';i.value='audit-valid';el.appendChild(i);return 'audit';},reset:function(){}};document.querySelectorAll('.cf-turnstile').forEach(function(el){window.turnstile.render(el);});"});
   if(url.includes('googletagmanager.com/gtag/js'))return route.fulfill({contentType:'application/javascript',body:'/* isolated analytics fixture: no network transport */'});
   return route.abort();
  });
  const page=await context.newPage();page.on('pageerror',error=>consoleErrors.push({width,message:error.message}));
  page.on('response',response=>{if(response.url().startsWith(base)&&response.status()>=400)failures.push({url:response.url(),status:response.status()});});
  for(const [name,url] of [['home','/'],['google-ads','/campanas/google-ads.php'],['servicio','/diseno-web.php'],['privacidad','/privacidad.php'],['cookies','/cookies.php'],['aviso','/aviso-legal.php']]){
   const response=await page.goto(base+url,{waitUntil:'domcontentloaded',timeout:20000});
   if(name==='home'&&width===390)await page.screenshot({path:path.join(out,'capturas',stage+'-banner-390.png'),fullPage:false});
   if(await page.locator('#reject-all').isVisible())await page.locator('#reject-all').click();
   await page.evaluate(()=>document.fonts.ready);
   await page.screenshot({path:path.join(out,'capturas',stage+'-'+name+'-'+width+'.png'),fullPage:true});
   let axe=[];
   if(['home','google-ads','servicio'].includes(name)){const scan=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();axe=scan.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary}))}));}
   results.push({name,width,status:response.status(),setCookie:!!response.headers()['set-cookie'],overflow:await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),axe});
  }
  if(stage==='after'){
   await page.goto(base+'/campanas/google-ads.php?utm_source=google&utm_campaign=123&v=cantabria',{waitUntil:'domcontentloaded'});
   await page.locator('#open-cookie-settings').click();await page.locator('#accept-all-settings').click();
   for(const [key,value] of [['sector','servicios'],['objective','consultas'],['budget','1000-3000'],['urgency','ahora']]){await page.locator('input[name="'+key+'"][value="'+value+'"]').check();await page.waitForTimeout(300);}
   await page.locator('input[name=name]').fill('Auditoría local');await page.locator('input[name=email]').fill('audit-'+width+'@example.com');await page.locator('input[name=privacy]').check();
   await page.waitForFunction(()=>!!document.querySelector('[name="cf-turnstile-response"]')?.value);
   await page.locator('[data-submit]').click();await page.waitForURL(base+'/campanas/gracias.php',{timeout:15000});
   await page.getByRole('heading',{name:'Gracias por contarnos tu proyecto.'}).waitFor();
   await page.screenshot({path:path.join(out,'capturas',stage+'-gracias-'+width+'.png'),fullPage:true});
   const events=await page.evaluate(()=>window.dataLayer.filter(e=>e[0]==='event'&&e[1]==='generate_lead').length);
   await page.reload({waitUntil:'domcontentloaded'});
   const reloadEvents=await page.evaluate(()=>window.dataLayer.filter(e=>e[0]==='event'&&e[1]==='generate_lead').length);
   if(events!==1||reloadEvents!==0)throw new Error('Conversion count '+events+'/'+reloadEvents);
   results.push({name:'gracias',width,conversionEvents:events,reloadConversionEvents:reloadEvents});
  }else{
   await page.goto(base+'/campanas/gracias.php',{waitUntil:'domcontentloaded'});await page.screenshot({path:path.join(out,'capturas',stage+'-gracias-'+width+'.png'),fullPage:true});
  }
  await context.close();
 }
 await browser.close();
 fs.writeFileSync(path.join(out,stage+'-browser.json'),JSON.stringify({results,failures,consoleErrors},null,2));
 console.log(JSON.stringify({pages:results.length,httpFailures:failures.length,jsErrors:consoleErrors.length,axeViolations:results.reduce((n,r)=>n+(r.axe?.length||0),0),overflow:results.filter(r=>r.overflow).length}));
 if(failures.length||consoleErrors.length)process.exitCode=1;
})().catch(error=>{console.error(error);process.exit(1)});
