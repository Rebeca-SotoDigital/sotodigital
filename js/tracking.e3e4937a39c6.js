(function () {
    'use strict';
    const configElement = document.getElementById('soto-tracking-config');
    let config = {};
    try { config = JSON.parse(configElement ? configElement.textContent : '{}'); } catch (_) { return; }
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    window.gtag('consent', 'default', {analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
    window.gtag('set', 'ads_data_redaction', true);
    window.gtag('set', 'allow_ad_personalization_signals', false);
    let loaded = false;
    let configuredGA = false;
    let configuredAds = false;
    let preferences = {analytics: false, advertising: false};
    const sent = new Set();
    function safeLocation() {
        const url = new URL(location.href);
        const safe = new URL(url.origin + url.pathname);
        ['gclid', 'gbraid', 'wbraid'].forEach(function (key) {
            const value = url.searchParams.get(key);
            if (value && /^[A-Za-z0-9_-]{1,250}$/.test(value)) safe.searchParams.set(key, value);
        });
        return safe.href;
    }
    function safeReferrer() {
        try { const url = new URL(document.referrer); return url.origin + url.pathname; } catch (_) { return ''; }
    }
    function load() {
        if (loaded) return;
        const id = preferences.analytics && config.ga4 ? config.ga4 : (preferences.advertising ? config.ads : '');
        if (!id) return;
        loaded = true;
        window.gtag('js', new Date());
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
        document.head.appendChild(script);
    }
    function once(key, callback) {
        if (sent.has(key)) return;
        try { if (sessionStorage.getItem(key)) return; } catch (_) {}
        sent.add(key);
        callback();
        try { sessionStorage.setItem(key, '1'); } catch (_) {}
    }
    function leadConversion() {
        const marker = document.getElementById('soto-lead-receipt');
        if (!marker) return;
        let receipt;
        try { receipt = JSON.parse(marker.textContent); } catch (_) { return; }
        if (!receipt || !/^[a-f0-9]{32}$/.test(receipt.id)) return;
        const params = {service: config.service, transaction_id: receipt.id, page_location: safeLocation(), page_referrer: safeReferrer()};
        if (preferences.analytics && config.ga4) once('soto-lead-ga-' + receipt.id, function () {
            window.gtag('event', 'generate_lead', Object.assign({send_to: config.ga4}, params));
        });
        if (preferences.advertising && config.ads && config.leadLabel) once('soto-lead-ads-' + receipt.id, function () {
            window.gtag('event', 'conversion', Object.assign({send_to: config.ads + '/' + config.leadLabel}, params));
        });
    }
    function clearGoogleCookies() {
        const names = document.cookie.split(';').map(function (part) { return part.trim().split('=')[0]; }).filter(function (name) { return /^(_ga(?:_|$)|_gid$|_gat(?:_|$)|_gcl_)/.test(name); });
        const parts = location.hostname.split('.');
        const domains = [''];
        for (let i = 0; i < parts.length - 1; i++) domains.push(parts.slice(i).join('.'));
        names.forEach(function (name) { domains.forEach(function (domain) {
            document.cookie = name + '=; Max-Age=0; path=/' + (domain ? '; domain=' + domain : '');
        }); });
    }
    window.sotoTracking = {
        apply: function (prefs) {
            const revoked = (preferences.analytics && !prefs.analytics) || (preferences.advertising && !prefs.advertising);
            preferences = {analytics: prefs.analytics === true, advertising: prefs.advertising === true};
            window.gtag('consent', 'update', {analytics_storage: preferences.analytics ? 'granted' : 'denied', ad_storage: preferences.advertising ? 'granted' : 'denied', ad_user_data: preferences.advertising ? 'granted' : 'denied', ad_personalization: 'denied'});
            if (revoked && loaded) { clearGoogleCookies(); location.reload(); return; }
            if (!preferences.analytics && !preferences.advertising) clearGoogleCookies();
            load();
            if (loaded && preferences.analytics && config.ga4 && !configuredGA) {
                configuredGA = true;
                window.gtag('config', config.ga4, {send_page_view: false, allow_google_signals: false, page_location: safeLocation(), page_referrer: safeReferrer()});
                window.gtag('event', 'page_view', {send_to: config.ga4, page_location: safeLocation(), page_referrer: safeReferrer()});
            }
            if (loaded && preferences.advertising && config.ads && !configuredAds) {
                configuredAds = true;
                window.gtag('config', config.ads, {page_location: safeLocation(), page_referrer: safeReferrer()});
            }
            leadConversion();
        }
    };
    document.addEventListener('click', function (event) {
        const link = event.target.closest && event.target.closest('a[href]');
        if (!link || !/^https:\/\/(?:wa\.me\/|api\.whatsapp\.com\/)/i.test(link.href)) return;
        const params = {service: config.service, page_location: safeLocation(), page_referrer: safeReferrer()};
        if (preferences.analytics && config.ga4) window.gtag('event', 'whatsapp_click', Object.assign({send_to: config.ga4}, params));
        if (preferences.advertising && config.ads && config.whatsappLabel) window.gtag('event', 'conversion', Object.assign({send_to: config.ads + '/' + config.whatsappLabel}, params));
    });
})();
