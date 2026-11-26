<footer>
    <div class="container">
<?php if (empty($campaignLanding)): ?>
        <div class="footer-top">
            <div class="footer-col">
                <h2>SOTO DIGITAL</h2>
                <p>Estrategia, creatividad y soluciones digitales para que tu negocio se vea, conecte y destaque.</p>
                <p>Maliaño, Cantabria · Trabajamos con empresas de toda España.</p>
                <div class="social-links">
                    <a href="https://instagram.com/sotodigital" aria-label="Instagram de Soto Digital" target="_blank" rel="noopener noreferrer">
                        <i class="icon icon-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="mailto:rebeca@soto-digital.com" aria-label="Escríbenos por email"><i class="icon icon-envelope" aria-hidden="true"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h2>Servicios</h2>
                <ul>
                    <?php foreach (SOTO_SERVICES as [$label, $url]): ?>
                        <li><a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a></li>
                        <?php if ($url === '/publicidad-digital.php'): ?>
                        <li><a href="/chatgpt-ads.php">Publicidad en ChatGPT</a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <li><a href="/blog/">Blog</a></li>
                </ul>
            </div>

        </div>

<?php endif; ?>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> SOTO DIGITAL. Todos los derechos reservados.</p>
            <div class="legal-links">
                <a href="/aviso-legal.php">Aviso Legal</a> |
                <a href="/privacidad.php">Privacidad</a> |
                <a href="/cookies.php">Cookies</a>
            </div>
            <button id="open-cookie-settings" class="btn-link" type="button">Configurar Cookies</button>
        </div>
    </div>
</footer>

<div id="cookie-notice" class="cookie-overlay" role="dialog" aria-modal="true" aria-labelledby="cookie-notice-title" tabindex="-1" style="display: none;">
    <div class="cookie-box">
        <h2 id="cookie-notice-title">Valoramos tu privacidad</h2>
        <p>Utilizamos cookies para mejorar tu experiencia. Puedes aceptarlas todas, rechazarlas o configurar tus preferencias, incluida la identificación de empresas visitantes con Leadfeeder/Dealfront.</p>
        <div class="cookie-buttons">
            <button id="accept-all" class="btn-cookie" type="button">Aceptar todas</button>
            <button id="reject-all" class="btn-cookie" type="button">Rechazar todas</button>
            <button id="open-cookie-panel" class="btn-link" type="button">Configurar</button>
        </div>
    </div>
</div>

<div id="cookie-settings" class="cookie-overlay" role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title" tabindex="-1" style="display: none;">
    <div class="cookie-box settings-box">
        <h2 id="cookie-settings-title">Configuración de Cookies</h2>

        <div class="cookie-option">
            <label>
                <input type="checkbox" checked disabled>
                <strong>Técnicas (Necesarias)</strong>
                <span>Permiten el funcionamiento básico de la web.</span>
            </label>
        </div>

        <div class="cookie-option">
            <label>
                <input type="checkbox" id="analytics-cookies">
                <strong>Analíticas</strong>
                <span>Medición de visitas con Google Analytics, cuando esté configurado.</span>
            </label>
        </div>

        <div class="cookie-option">
            <label>
                <input type="checkbox" id="advertising-cookies">
                <strong>Publicitarias</strong>
                <span>Medición de campañas con Google Ads. No activamos personalización de anuncios.</span>
            </label>
        </div>

        <div class="cookie-option">
            <label>
                <input type="checkbox" id="company-identification-cookies">
                <strong>Identificación empresarial (Leadfeeder/Dealfront)</strong>
                <span>Con tu permiso, analizamos las visitas para identificar empresas y conocer qué servicios consultan. No identifica a la persona concreta. <a href="/privacidad.php#identificacion-empresarial">Más información</a>.</span>
            </label>
        </div>

        <div class="cookie-buttons">
            <button id="save-settings" class="btn-cookie" type="button">Guardar configuración</button>
            <button id="accept-all-settings" class="btn-cookie" type="button">Aceptar todas</button>
            <button id="reject-all-settings" class="btn-cookie" type="button">Rechazar todas</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/company-identification.php'; ?>
<?php if (empty($campaignLanding) && preg_match('/^[1-9][0-9]{6,14}$/D', SOTO_WHATSAPP_NUMBER)): ?>
<a class="whatsapp-float" href="<?= htmlspecialchars('https://wa.me/' . SOTO_WHATSAPP_NUMBER . '?text=' . rawurlencode(SOTO_WHATSAPP_MESSAGE), ENT_QUOTES, 'UTF-8') ?>" aria-label="¿Hablamos por WhatsApp?">
    <i class="icon icon-phone" aria-hidden="true"></i><span class="whatsapp-label">¿Hablamos?</span>
</a>
<?php elseif (empty($campaignLanding) && soto_show_pending()): ?>
<div class="whatsapp-float whatsapp-pending">
    <i class="icon icon-phone" aria-hidden="true"></i><span class="whatsapp-label">¿Hablamos?</span><span>[PENDIENTE: número]</span>
</div>
<?php endif; ?>
<script src="/<?= htmlspecialchars($siteAssets['js/tracking.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="/<?= htmlspecialchars($siteAssets['js/cookies.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="/<?= htmlspecialchars($siteAssets['js/main.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php if (!empty($leadForm)): ?><script src="/<?= htmlspecialchars($siteAssets['js/campaign-bootstrap.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script><script src="/<?= htmlspecialchars($siteAssets['js/campaign.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script><?php endif; ?>
</body>
</html>
