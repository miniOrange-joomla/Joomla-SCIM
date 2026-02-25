function add_css_tab(element) {
    jQuery(".mo_nav_tab_active ").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
}

function copyToClipboard(element) {
    jQuery(".selected-text").removeClass("selected-text");
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    jQuery(element).addClass("selected-text");
    temp.val(jQuery(element).text().trim()).select();
    document.execCommand("copy");
    temp.remove();
}

document.addEventListener('DOMContentLoaded', function () {

    // countries is defined in assets/js/countries.js
    if (typeof countries === 'undefined' || !Array.isArray(countries)) {
        return;
    }

    // Support multiple phone dropdown instances on the same page
    const containers = document.querySelectorAll('[data-mo-phone-dropdown]');
    if (!containers || containers.length === 0) {
        return;
    }

    function getFlagEmoji(countryCode) {
        if (!countryCode || typeof countryCode !== 'string' || countryCode.length !== 2) {
            return '';
        }
        const code = countryCode.toUpperCase();
        const A = 65;
        const REGIONAL_INDICATOR_A = 0x1F1E6; // 🇦
        const first = code.charCodeAt(0) - A + REGIONAL_INDICATOR_A;
        const second = code.charCodeAt(1) - A + REGIONAL_INDICATOR_A;
        try {
            return String.fromCodePoint(first, second);
        } catch (e) {
            return '';
        }
    }

    function normalizeForSearch(value) {
        return String(value || '').trim().toLowerCase();
    }

    function initOne(container) {
        const list = container.querySelector('.mo-country-list');
        const select = container.querySelector('.mo-country-select');
        const hiddenInput = container.querySelector('.mo-country-code');

        if (!list || !select || !hiddenInput) {
            return;
        }

        const tzEl = container.querySelector('.mo-client-timezone');
        const offsetEl = container.querySelector('.mo-client-timezone-offset');

        // Fill timezone hidden fields (used by controller)
        let tzName = '';
        try {
            tzName = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            tzName = '';
        }
        const offsetMinutes = new Date().getTimezoneOffset();
        if (tzEl) tzEl.value = tzName;
        if (offsetEl) offsetEl.value = String(offsetMinutes);

        function setSelectedCountry(country) {
            const flagEl = select.querySelector('.flag');
            const dialEl = select.querySelector('.dial-code');
            if (!flagEl || !dialEl) {
                return;
            }
            flagEl.className = 'flag';
            flagEl.textContent = getFlagEmoji(country.code);
            dialEl.textContent = `+${country.dial}`;
            hiddenInput.value = String(country.dial);
        }

        // Search box (sticky at top of dropdown)
        const searchLi = document.createElement('li');
        searchLi.className = 'mo-country-search';
        searchLi.innerHTML = `
            <input
                type="text"
                class="mo-country-search-input"
                placeholder="Search country or code…"
                autocomplete="off"
                spellcheck="false"
            />
        `;
        list.appendChild(searchLi);
        const searchInput = searchLi.querySelector('input');

        // Build dropdown list
        countries.forEach(country => {
            const li = document.createElement('li');
            li.dataset.name = normalizeForSearch(country.name);
            li.dataset.code = normalizeForSearch(country.code);
            li.dataset.dial = normalizeForSearch(country.dial);

            li.innerHTML = `
                <span class="flag" aria-hidden="true">${getFlagEmoji(country.code)}</span>
                <span class="name">${country.name}</span>
                <span class="dial">+${country.dial}</span>
            `;
            li.onclick = function () {
                setSelectedCountry(country);
                list.classList.remove('open');
            };
            list.appendChild(li);
        });

        // Initialize selected from hidden value (dial code) if present, else first country.
        const currentDial = String(hiddenInput.value || '').replace(/\D/g, '');
        const initial = countries.find(c => String(c.dial) === currentDial) || countries[0];
        if (initial) {
            setSelectedCountry(initial);
        }

        function applyFilter() {
            if (!searchInput) return;
            const q = normalizeForSearch(searchInput.value);
            const items = list.querySelectorAll('li');
            items.forEach(function (li) {
                if (li === searchLi) return;
                if (!li.dataset) return;
                if (q === '') {
                    li.style.display = '';
                    return;
                }
                const haystack = `${li.dataset.name || ''} ${li.dataset.code || ''} ${li.dataset.dial || ''}`;
                li.style.display = haystack.includes(q) ? '' : 'none';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    applyFilter();
                    list.classList.remove('open');
                }
            });
        }

        select.onclick = () => {
            const isOpening = !list.classList.contains('open');
            list.classList.toggle('open');
            if (isOpening && searchInput) {
                searchInput.value = '';
                applyFilter();
                setTimeout(() => searchInput.focus(), 0);
            }
        };

        document.addEventListener('click', function (e) {
            if (!select.contains(e.target) && !list.contains(e.target)) {
                list.classList.remove('open');
            }
        });
    }

    containers.forEach(initOne);
});
