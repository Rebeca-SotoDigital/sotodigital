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
    function show(index, focus) {
        current = index;
        steps.forEach(function (step, i) { step.hidden = i !== current; });
        previous.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        submit.hidden = current !== steps.length - 1;
        progress.hidden = false;
        bar.hidden = false;
        progress.textContent = 'Paso ' + (current + 1) + ' de ' + steps.length;
        bar.value = current + 1;
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
        return false;
    }
    // Progressive enhancement: without JS all steps remain visible and post normally.
    form.noValidate = true;
    show(0, false);
    next.addEventListener('click', function () { if (validStep(current)) show(current + 1, true); });
    previous.addEventListener('click', function () { if (!pending) show(current - 1, true); });
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
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
            if (!response.ok || !result.ok) throw new Error(result.error || 'No se ha podido enviar. Inténtalo de nuevo.');
            // Conversion originates from the stored server receipt, never the submit click.
            window.location.assign(new URL(result.next, document.baseURI).href);
        } catch (failure) {
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
