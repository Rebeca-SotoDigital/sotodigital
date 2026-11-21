<footer>
    <div class="container">
<?php if (empty($campaignLanding)): ?>
        <div class="footer-top">
            <div class="footer-col">
                <h2>SOTO DIGITAL</h2>
                <p>Estrategia, creatividad y soluciones digitales para que tu negocio se vea, conecte y destaque. Contigo en el siguiente paso, estés donde estés.</p>
                <div class="social-links">
                    <a href="https://instagram.com/sotodigital" aria-label="Soto Digital en Instagram" target="_blank" rel="noopener noreferrer">
                        <i class="icon icon-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="./#diagnostico" aria-label="Pedir una propuesta"><i class="icon icon-envelope" aria-hidden="true"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h2>Servicios</h2>
                <ul>
                    <li><a href="pantalla-led-raos.php">Pantalla LED</a></li>
                    <li><a href="branding.php">Diseño gráfico</a></li>
                    <li><a href="diseno-web.php">Páginas web</a></li>
                    <li><a href="redes-sociales.php">Redes sociales</a></li>
                    <li><a href="email-marketing.php">Email marketing</a></li>
                    <li><a href="marketing-contenidos.php">Marketing de contenidos</a></li>
                    <li><a href="seo-sem.php">SEO y SEM</a></li>
                </ul>
            </div>

        </div>

<?php endif; ?>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> SOTO DIGITAL. Todos los derechos reservados.</p>
            <div class="legal-links">
                <a href="aviso-legal.php">Aviso Legal</a> |
                <a href="privacidad.php">Privacidad</a> |
                <a href="cookies.php">Cookies</a>
            </div>
            <button id="open-cookie-settings" class="btn-link" type="button">Configurar Cookies</button>
        </div>
    </div>
</footer>

<div id="cookie-notice" class="cookie-overlay" style="display: none;">
    <div class="cookie-box">
        <h2>Valoramos tu privacidad</h2>
        <p>Utilizamos cookies para mejorar tu experiencia. Puedes aceptarlas todas, rechazarlas o configurar tus preferencias, incluida la identificación de empresas visitantes con Leadfeeder/Dealfront.</p>
        <div class="cookie-buttons">
            <button id="accept-all" class="btn-cookie" type="button">Aceptar todas</button>
            <button id="reject-all" class="btn-cookie btn-outline" type="button">Rechazar todas</button>
            <button id="open-cookie-panel" class="btn-link" type="button">Configurar</button>
        </div>
    </div>
</div>

<div id="cookie-settings" class="cookie-overlay" style="display: none;">
    <div class="cookie-box settings-box">
        <h2>Configuración de Cookies</h2>

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
                <span>Con tu permiso, analizamos las visitas para identificar empresas y conocer qué servicios consultan. No identifica a la persona concreta. <a href="privacidad.php#identificacion-empresarial">Más información</a>.</span>
            </label>
        </div>

        <div class="cookie-buttons">
            <button id="save-settings" class="btn-cookie" type="button">Guardar configuración</button>
            <button id="accept-all-settings" class="btn-cookie btn-outline" type="button">Aceptar todas</button>
            <button id="reject-all-settings" class="btn-cookie btn-outline" type="button">Rechazar todas</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/company-identification.php'; ?>
<script src="<?= htmlspecialchars($siteAssets['js/tracking.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars($siteAssets['js/cookies.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars($siteAssets['js/main.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php if (!empty($leadForm)): ?><script src="<?= htmlspecialchars($siteAssets['js/campaign.js'], ENT_QUOTES, 'UTF-8') ?>" defer></script><?php endif; ?>
</body>
</html>
