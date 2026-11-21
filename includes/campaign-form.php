<?php $questions = campaign_options(); ?>
<form class="campaign-form" method="post" action="/api/lead.php" data-lead-form data-token-url="/api/token.php">
<noscript><p class="campaign-error">Activa JavaScript para completar la verificación de seguridad y enviar tu solicitud. Las preguntas se muestran a continuación.</p></noscript>
<span class="section-subtitle">MINI-AUDITORÍA INICIAL GRATUITA</span>
<h3>¿Qué necesita tu negocio?</h3>
<p class="campaign-progress" data-progress hidden aria-live="polite">Paso 1 de 5</p>
<progress data-progress-bar max="5" value="1" hidden aria-label="Progreso del cuestionario"></progress>
<p class="campaign-error" data-form-error role="alert" <?= !$error ? 'hidden' : '' ?>><?= $escape($error) ?></p>
<input type="hidden" name="csrf" value="<?= $escape($formCsrf) ?>"><input type="hidden" name="service" value="<?= $escape($slug) ?>"><input type="hidden" name="submission" value="<?= $escape($formSubmission) ?>"><input type="hidden" name="attribution_token" value="<?= $escape($attributionToken) ?>">
<div class="campaign-honey" aria-hidden="true"><label>No rellenar este campo<input name="company_fax" tabindex="-1" autocomplete="off"></label></div>
<?php foreach (['sector', 'objective', 'budget', 'urgency'] as $step => $key): ?>
<fieldset data-question-step="<?= $step ?>"><legend><?= $escape($questions[$key]['label']) ?></legend>
<?php if (!empty($questions[$key]['hint'])): ?><p class="campaign-small"><?= $escape($questions[$key]['hint']) ?></p><?php endif; ?>
<div class="campaign-options"><?php foreach ($questions[$key]['options'] as $value => $label): ?>
<label><input required type="radio" name="<?= $key ?>" value="<?= $escape($value) ?>" <?= ($old[$key] ?? '') === $value ? 'checked' : '' ?>><span><?= $escape($label) ?></span></label>
<?php endforeach; ?></div></fieldset>
<?php endforeach; ?>
<fieldset data-question-step="4"><legend>¿Cómo podemos contactar contigo?</legend>
<label>Nombre y apellidos<input required name="name" autocomplete="name" maxlength="150" value="<?= $escape($old['name'] ?? '') ?>"></label>
<div class="campaign-form-pair"><label>Email<input required type="email" name="email" autocomplete="email" maxlength="254" value="<?= $escape($old['email'] ?? '') ?>"></label><label>Móvil<?= campaign_phone_required() ? '' : ' (opcional)' ?><input <?= campaign_phone_required() ? 'required' : '' ?> type="tel" name="phone" autocomplete="tel" maxlength="30" value="<?= $escape($old['phone'] ?? '') ?>"></label></div>
<p class="campaign-small">Si prefieres que te llamemos, deja tu móvil. También podemos responder por email.</p>
<label>Web del negocio (opcional)<input type="url" name="website" placeholder="https://tunegocio.com" maxlength="500" value="<?= $escape($old['website'] ?? '') ?>"></label>
<label>¿Algo más que debamos saber? (opcional)<textarea name="message" rows="3" maxlength="5000" placeholder="Cuéntanos lo que te gustaría mejorar."><?= $escape($old['message'] ?? '') ?></textarea></label>
<label class="campaign-check"><input required type="checkbox" name="privacy" value="1"><span>He leído la <a href="/privacidad.php#solicitudes-propuesta" target="_blank" rel="noopener">política de privacidad</a> sobre la gestión de mi solicitud.</span></label>
<p class="campaign-small">Responsable: Soto Digital S.L. (NIF B05897236). Finalidad: atender tu solicitud y preparar la revisión o propuesta; base jurídica: medidas precontractuales y, cuando corresponda, contacto profesional. Tus respuestas se puntúan para revisión humana. Proveedores previstos: Arsys, Brevo, Cloudflare y Claude con datos limitados, según la política enlazada. No te suscribimos a newsletters. Derechos: rebeca@soto-digital.com.</p>
<?php if ($ready): ?><div data-turnstile-container data-sitekey="<?= $escape(campaign_config('TURNSTILE_SITE_KEY')) ?>" data-action="/proposal" data-language="es"></div><?php endif; ?>
</fieldset>
<?php if (!$ready): ?><p class="campaign-form-status">Estamos preparando la solicitud online. Vuelve a intentarlo más adelante.</p><?php endif; ?>
<div class="campaign-form-actions"><button type="button" class="text-link" data-previous hidden>← Volver</button><button type="button" class="btn" data-next hidden>Continuar →</button><button class="btn" type="submit" data-submit <?= !$ready ? 'disabled' : '' ?>>Pedir mini-auditoría gratuita ↗</button></div>
<p class="campaign-small">Sin compromiso de contratación. Acordaremos contigo el alcance de la revisión inicial.</p>
</form>
