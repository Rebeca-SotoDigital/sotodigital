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

    if (!banner || !settingsPanel) return;

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
        localStorage.setItem("cookiePreferences", JSON.stringify(prefs));
        hideAll();
        applyCookiePreferences(prefs);
    }

    function applyCookiePreferences(prefs) {
        console.log("Preferencias aplicadas:", prefs);

        if (!prefs.analytics) {
            console.log("Cookies analíticas deshabilitadas.");
        } else {
            console.log("Cookies analíticas habilitadas.");
        }

        if (!prefs.advertising) {
            console.log("Cookies publicitarias deshabilitadas.");
        } else {
            console.log("Cookies publicitarias habilitadas.");
        }
    }

    const savedPrefs = localStorage.getItem("cookiePreferences");

    if (!savedPrefs) {
        showBanner();
        applyCookiePreferences({
            technical: true,
            analytics: false,
            advertising: false
        });
    } else {
        const prefs = JSON.parse(savedPrefs);

        if (analyticsInput) analyticsInput.checked = !!prefs.analytics;
        if (advertisingInput) advertisingInput.checked = !!prefs.advertising;

        hideAll();
        applyCookiePreferences(prefs);
    }

    if (acceptAllBtn) {
        acceptAllBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: true,
                advertising: true
            });
        });
    }

    if (rejectAllBtn) {
        rejectAllBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: false,
                advertising: false
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
            const saved = localStorage.getItem("cookiePreferences");
            if (saved) {
                const prefs = JSON.parse(saved);
                if (analyticsInput) analyticsInput.checked = !!prefs.analytics;
                if (advertisingInput) advertisingInput.checked = !!prefs.advertising;
            }
            showSettings();
        });
    }

    if (acceptAllSettingsBtn) {
        acceptAllSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: true,
                advertising: true
            });
        });
    }

    if (rejectAllSettingsBtn) {
        rejectAllSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: false,
                advertising: false
            });
        });
    }

    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener("click", function () {
            setCookiePreferences({
                technical: true,
                analytics: analyticsInput ? analyticsInput.checked : false,
                advertising: advertisingInput ? advertisingInput.checked : false
            });
        });
    }
});