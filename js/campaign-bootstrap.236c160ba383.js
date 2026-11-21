document.addEventListener('DOMContentLoaded',function(){
    'use strict';
    const form=document.querySelector('[data-lead-form]');
    if(!form || !form.dataset.tokenUrl)return;
    const container=form.querySelector('[data-turnstile-container]');
    const button=form.querySelector('[data-submit]');
    if(!container || button.disabled)return;
    let tokens=null,script=null,widget=null;
    function issueTokens(){
        if(tokens)return tokens;
        const origin=new URL(location.href);const attribution={};
        ['utm_source','utm_medium','utm_campaign','utm_content','utm_term','gclid','gbraid','wbraid'].forEach(key=>{if(origin.searchParams.has(key))attribution[key]=origin.searchParams.get(key);});
        tokens=fetch(new URL(form.dataset.tokenUrl,document.baseURI),{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-Soto-Form':'1','Accept':'application/json'},body:JSON.stringify({landing:origin.pathname,service:form.elements.service.value,attribution,variant:origin.searchParams.get('v')||''})}).then(async response=>{
            const data=await response.json();if(!response.ok||!data.ok)throw new Error('No se ha podido preparar el formulario. Recarga la página e inténtalo de nuevo.');
            ['csrf','submission','attribution_token'].forEach(key=>{form.elements[key].value=data[key];});
        }).catch(error=>{tokens=null;throw error;});
        return tokens;
    }
    function loadChallenge(){
        if(script)return script;
        script=new Promise((resolve,reject)=>{
            const tag=document.createElement('script');tag.src='https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';tag.async=true;
            tag.onload=resolve;tag.onerror=()=>{script=null;reject(new Error('No se ha podido cargar la verificación de seguridad.'));};document.head.appendChild(tag);
        });return script;
    }
    function render(){
        if(!window.turnstile || widget!==null || container.closest('fieldset').hidden)return;
        widget=window.turnstile.render(container,{sitekey:container.dataset.sitekey,action:'proposal',language:'es'});
    }
    function warm(){Promise.all([issueTokens(),loadChallenge()]).then(render).catch(()=>{});}
    if(window.IntersectionObserver){const observer=new IntersectionObserver(entries=>{if(entries.some(entry=>entry.isIntersecting)){warm();observer.disconnect();}},{rootMargin:'250px'});observer.observe(form);}
    form.addEventListener('focusin',warm,{once:true});form.addEventListener('soto:step',()=>{warm();render();});
    window.sotoLeadForm={prepare:async function(){
        await issueTokens();await loadChallenge();render();
        let token=form.querySelector('[name="cf-turnstile-response"]');
        const response=typeof window.turnstile.getResponse==='function' && widget!==null ? window.turnstile.getResponse(widget) : (token && token.value);
        if(!response){const error=new Error('Completa la verificación de seguridad antes de enviar. Si había caducado, espera a que termine de nuevo.');error.kind='validation';throw error;}
        if(!token){token=document.createElement('input');token.type='hidden';token.name='cf-turnstile-response';form.appendChild(token);}
        token.value=response;
    }};
});
