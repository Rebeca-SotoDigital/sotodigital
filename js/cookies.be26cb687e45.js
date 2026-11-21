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
    const consentVersion = 3;

    if (!banner || !settingsPanel) return;

    function readPreferences() {
        try {
            const prefs = JSON.parse(localStorage.getItem("cookiePreferences"));
            const timestamp=prefs && Date.parse(prefs.updatedAt);
            const fresh=Number.isFinite(timestamp) && timestamp<=Date.now() && Date.now()-timestamp<180*24*60*60*1000;
            return prefs && prefs.version === consentVersion && fresh ? prefs : null;
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

    let returnFocus;
    function focusPanel(panel) {
        if (!panel.querySelector) return;
        returnFocus = document.activeElement;
        const first=panel.querySelector('button, input:not([disabled]), a[href]');
        if(first && first.focus)first.focus();
    }
    document.addEventListener('keydown',function(event){
        if(!event)return;
        const panel=settingsPanel.style.display==='flex'?settingsPanel:(banner.style.display==='flex'?banner:null);
        if(!panel || !panel.querySelectorAll)return;
        if(event.key==='Escape') {event.preventDefault(); if(readPreferences())hideAll();else showBanner(); return;}
        if(event.key!=='Tab')return;
        const fields=Array.from(panel.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), [tabindex="0"]'));
        if(!fields.length)return;
        const first=fields[0],last=fields[fields.length-1];
        if(event.shiftKey && document.activeElement===first){event.preventDefault();last.focus();}
        else if(!event.shiftKey && document.activeElement===last){event.preventDefault();first.focus();}
    });
    function showBanner() {
        banner.style.display = "flex";
        settingsPanel.style.display = "none";
        focusPanel(banner);
    }

    function showSettings() {
        banner.style.display = "none";
        settingsPanel.style.display = "flex";
        focusPanel(settingsPanel);
    }

    function hideAll() {
        if(returnFocus && returnFocus.isConnected && returnFocus.focus)returnFocus.focus();
        banner.style.display = "none";
        settingsPanel.style.display = "none";
    }

    function setCookiePreferences(prefs) {
        prefs.version = consentVersion;
        prefs.updatedAt = new Date().toISOString();
        try { localStorage.setItem("cookiePreferences", JSON.stringify(prefs)); } catch (_) {
            // Do not start tracking if the preference cannot be saved.
            prefs.companyIdentification = false;
            prefs.analytics = false;
            prefs.advertising = false;
        }
        syncInputs(prefs);
        hideAll();
        applyCookiePreferences(prefs);
    }

    function applyCookiePreferences(prefs) {
        if (window.sotoTracking) window.sotoTracking.apply(prefs);
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