document.addEventListener("DOMContentLoaded", function () {
    const banner = document.getElementById("cookie-notice");
    const settingsPanel = document.getElementById("cookie-settings");

    const acceptAllBtn = document.getElementById("accept-all");
    const rejectAllBtn = document.getElementById("reject-all");
    const openCookiePanelBtn = document.getElementById("open-cookie-panel");
    const openCookieSettingsBtn = document.getElementById("open-cookie-settings");

    const saveSettingsBtn = document.getElementById("save-settings");
    const acceptAllSettingsBtn = document.getElementById("accept-all-settings");
    const rejectAllSettingsBtn = document.getElementById("reject-all-settings");
    const analyticsInput = document.getElementById("analytics-cookies");
    const advertisingInput = document.getElementById("advertising-cookies");

    const companyInput = document.getElementById("company-identification-cookies");
    let companyTrackerLoaded = false;
    const consentVersion = 2;

    if (!banner || !settingsPanel) return;

    function readPreferences() {
        try {
            const prefs = JSON.parse(localStorage.getItem("cookiePreferences"));
            return prefs && prefs.version === consentVersion ? prefs : null;
        } catch (_) {
            return null;
        }
    }

    function syncInputs(prefs) {
        if (analyticsInput) analyticsInput.checked = prefs.analytics === true;
        if (advertisingInput) advertisingInput.checked = prefs.advertising === true;
        if (companyInput) companyInput.checked = prefs.companyIdentification === true;
    }

    function clearCompanyCookies() {
        const names = ["_lfa", "_lfa_consent", "_lfa_test_cookie_stored", "_lfa_expiry"];
        const parts = location.hostname.split(".");
        const domains = [""];
        for (let i = 0; i < parts.length - 1; i++) domains.push(parts.slice(i).join("."));
        names.forEach(function (name) {
            domains.forEach(function (domain) {
                document.cookie = name + "=; Max-Age=0; path=/" + (domain ? "; domain=" + domain : "");
            });
        });
    }

    function loadCompanyTracker() {
        const configElement = document.getElementById("company-identification-config");
        if (companyTrackerLoaded || !configElement) return;
        let config;
        try { config = JSON.parse(configElement.textContent); } catch (_) { return; }
        if (!/^https:\/\/sc\.lftracker\.com\/[^\s"<>]+\.js$/.test(config.url)) return;
        companyTrackerLoaded = true;
        window.ldfdr = window.ldfdr || function () {
            (window.ldfdr._q = window.ldfdr._q || []).push([].slice.call(arguments));
        };
        const script = document.createElement("script");
        script.async = true;
        script.src = config.url;
        script.id = "company-identification-tracker";
        document.head.appendChild(script);
    }

    function showBanner() {
        banner.style.display = "flex";
        settingsPanel.style.display = "none";
    }

    function showSettings() {
        banner.style.display = "none";
        settingsPanel.style.display = "flex";
    }

    function hideAll() {
        banner.style.display = "none";
        settingsPanel.style.display = "none";
    }

    function setCookiePreferences(prefs) {
        prefs.version = consentVersion;
        prefs.updatedAt = new Date().toISOString();
        try { localStorage.setItem("cookiePreferences", JSON.stringify(prefs)); } catch (_) {
            // Do not start tracking if the preference cannot be saved.
            prefs.companyIdentification = false;
        }
        syncInputs(prefs);
        hideAll();
        applyCookiePreferences(prefs);
    }

    function applyCookiePreferences(prefs) {
        if (prefs.companyIdentification === true) {
            loadCompanyTracker();
        } else {
            clearCompanyCookies();
            // Unloading a script does not stop its timers; reload with consent revoked.
            if (companyTrackerLoaded) location.reload();
        }
    }

    const savedPrefs = readPreferences();
    if (!savedPrefs) {
        showBanner();
        applyCookiePreferences({companyIdentification: false});
    } else {
        syncInputs(savedPrefs);
        hideAll();
        applyCookiePreferences(savedPrefs);
    }

    if (acceptAllBtn) {
        acceptAllBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: true,
                advertising: true,
                companyIdentification: true
            });
        });
    }

    if (rejectAllBtn) {
        rejectAllBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: false,
                advertising: false,
                companyIdentification: false
            });
        });
    }

    if (openCookiePanelBtn) {
        openCookiePanelBtn.addEventListener("click", function () {
            showSettings();
        });
    }

    if (openCookieSettingsBtn) {
        openCookieSettingsBtn.addEventListener("click", function () {
            syncInputs(readPreferences() || {});
            showSettings();
        });
    }

    if (acceptAllSettingsBtn) {
        acceptAllSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: true,
                advertising: true,
                companyIdentification: true
            });
        });
    }

    if (rejectAllSettingsBtn) {
        rejectAllSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: false,
                advertising: false,
                companyIdentification: false
            });
        });
    }

    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: analyticsInput ? analyticsInput.checked : false,
                advertising: advertisingInput ? advertisingInput.checked : false,
                companyIdentification: companyInput ? companyInput.checked : false
            });
        });
    }
});