document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-lead-form]');
    if (!form) return;
    const steps = Array.from(form.querySelectorAll('[data-question-step]'));
    const previous = form.querySelector('[data-previous]');
    const next = form.querySelector('[data-next]');
    const submit = form.querySelector('[data-submit]');
    const progress = form.querySelector('[data-progress]');
    const bar = form.querySelector('[data-progress-bar]');
    const error = form.querySelector('[data-form-error]');
    const canSubmit = !submit.disabled;
    let current = 0;
    let pending = false;
    let advanceTimer;
    let generation = 0;
    let started = false;
    function track(name, params) { if (window.sotoTracking) window.sotoTracking.event(name, params || {}); }
    function start() {
        if (started) return;
        started = true;
        track('lead_form_start');
        track('lead_form_step', {step: current + 1});
    }
    function show(index, focus) {
        clearTimeout(advanceTimer);
        generation++;
        current = index;
        steps.forEach(function (step, i) { step.hidden = i !== current; });
        previous.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        submit.hidden = current !== steps.length - 1;
        progress.hidden = false;
        bar.hidden = false;
        progress.textContent = 'Paso ' + (current + 1) + ' de ' + steps.length;
        bar.value = current + 1;
        if (started) track('lead_form_step', {step: current + 1});
        if (focus) {
            const legend = steps[current].querySelector('legend');
            legend.tabIndex = -1;
            legend.focus();
        }
    }
    function validStep(index) {
        const fields = Array.from(steps[index].querySelectorAll('input, textarea'));
        const invalid = fields.find(function (field) { return !field.checkValidity(); });
        if (!invalid) return true;
        show(index, false);
        invalid.reportValidity();
        track('lead_form_error', {type:'validation', step:index+1});
        return false;
    }
    // Progressive enhancement: without JS all steps remain visible and post normally.
    form.noValidate = true;
    show(0, false);
    next.addEventListener('click', function () { if (validStep(current)) show(current + 1, true); });
    form.addEventListener('focusin', start);
    form.addEventListener('change', function (event) {
        start();
        if (pending || current >= 4 || !event.target.matches('input[type=radio]')) return;
        clearTimeout(advanceTimer);
        const selectedGeneration = generation;
        advanceTimer = setTimeout(function () {
            if (generation === selectedGeneration && validStep(current)) show(current + 1, true);
        }, 250);
    });
    previous.addEventListener('click', function () { if (!pending) show(current - 1, true); });
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        start();
        if (pending) return;
        if (current < steps.length - 1) { if (validStep(current)) show(current + 1, true); return; }
        for (let i = 0; i < steps.length; i++) if (!validStep(i)) return;
        if (!canSubmit) return;
        error.hidden = true;
        pending = true;
        submit.disabled = true;
        previous.disabled = true;
        const label = submit.textContent;
        submit.textContent = 'Enviando…';
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            const result = await response.json();
            if (!response.ok || !result.ok) { const failure = new Error(result.error || 'No se ha podido enviar. Inténtalo de nuevo.'); failure.kind = response.status === 429 ? 'limit' : (response.status === 403 ? 'session' : 'api'); throw failure; }
            // Conversion originates from the stored server receipt, never the submit click.
            window.location.assign(new URL(result.next, document.baseURI).href);
        } catch (failure) {
            track('lead_form_error', {type:failure.kind || 'network', step:current+1});
            error.textContent = failure.message || 'No se ha podido enviar. Inténtalo de nuevo.';
            error.hidden = false;
            if (window.turnstile) window.turnstile.reset();
            error.scrollIntoView({block: 'nearest'});
        } finally {
            pending = false;
            submit.disabled = !canSubmit;
            previous.disabled = false;
            submit.textContent = label;
        }
    });
});
