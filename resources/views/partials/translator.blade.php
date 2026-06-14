<style>
    /* Hide the default Google Translate widget & top banner aggressively */
    .goog-te-banner-frame.skiptranslate, 
    .goog-te-gadget-icon,
    #goog-gt-tt,
    .goog-te-spinner-pos,
    .goog-te-spinner-animation,
    .VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
    .VIpgJd-ZVi9od-ORHb-OEVmcd,
    #google_translate_element iframe,
    iframe.goog-te-banner-frame {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Prevent Google Translate from pushing the body down */
    body {
        top: 0px !important;
        position: static !important;
    }
    
    /* Hide the tooltip that shows original text on hover */
    .VIpgJd-ZVi9od-l4eHX-hSRGPd {
        display: none !important;
    }
    
    /* Custom Translation Widget Styles */
    .custom-translate-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
        margin-top: auto;
    }
    
    .custom-translate-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(120, 120, 120, 0.2);
        border-radius: 0.5rem;
        color: inherit;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        backdrop-filter: blur(8px);
    }
    
    .custom-translate-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    .custom-translate-btn svg {
        width: 1.25rem;
        height: 1.25rem;
        margin-right: 0.5rem;
        opacity: 0.8;
    }

    .custom-translate-dropdown {
        position: absolute;
        bottom: 110%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.2s ease;
        z-index: 50;
    }

    .custom-translate-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .custom-translate-option {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #334155;
        border: none;
        background: transparent;
        cursor: pointer;
        transition: background 0.15s ease;
        text-align: left;
    }

    .custom-translate-option:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .custom-translate-option.active-lang {
        background: #e2e8f0;
        font-weight: 600;
    }

    /* Floating styles for layouts without a sidebar */
    .custom-translate-wrapper.floating {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        width: auto;
        z-index: 9999;
    }
    .custom-translate-wrapper.floating .custom-translate-btn {
        background: #ffffff;
        color: #334155;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        padding: 0.75rem 1.25rem;
    }
    .custom-translate-wrapper.floating .custom-translate-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    /* The actual hidden google element */
    #google_translate_element {
        display: none;
    }
</style>

@php
    // Determine if this should be a floating widget or integrated (e.g. sidebar)
    $isFloating = $isFloating ?? false;
@endphp

<div class="custom-translate-wrapper {{ $isFloating ? 'floating' : 'mt-auto px-4 pb-4' }}" id="custom-translate-wrapper">
    <button class="custom-translate-btn" onclick="toggleTranslateDropdown(event)">
        <div class="flex items-center">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
            </svg>
            <span id="current-lang-label">English</span>
        </div>
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0; width: 1rem; height: 1rem;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div class="custom-translate-dropdown" id="custom-translate-dropdown">
        <button class="custom-translate-option" data-lang="en" onclick="changeLanguage('en', 'English')">
            🇺🇸 English
        </button>
        <button class="custom-translate-option" data-lang="id" onclick="changeLanguage('id', 'Indonesian')">
            🇮🇩 Indonesian
        </button>
    </div>
</div>

<div id="google_translate_element"></div>

<script type="text/javascript">
    function toggleTranslateDropdown(e) {
        e.stopPropagation();
        document.getElementById('custom-translate-dropdown').classList.toggle('active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#custom-translate-wrapper')) {
            const dropdown = document.getElementById('custom-translate-dropdown');
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }
    });

    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,id',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }

    function changeLanguage(langCode, langName) {
        // Close dropdown
        document.getElementById('custom-translate-dropdown').classList.remove('active');

        if (langCode === 'en') {
            // To revert to English we need to nuke ALL googtrans cookies everywhere
            // and also trigger the Google Translate "Show original" button
            
            // 1. Delete cookies on every possible domain/path combo
            const hostname = window.location.hostname;
            const domainParts = hostname.split('.');
            const possibleDomains = [hostname, '.' + hostname, ''];
            // Also add parent domain (e.g. .rplkodingan.com from bryan.rplkodingan.com)
            if (domainParts.length > 2) {
                possibleDomains.push('.' + domainParts.slice(-2).join('.'));
            }
            
            possibleDomains.forEach(domain => {
                const domainStr = domain ? '; domain=' + domain : '';
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/' + domainStr;
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;' + domainStr;
            });
            
            // 2. Also try programmatically clicking "Show original" if available
            const showOriginalBtn = document.querySelector('.goog-te-banner-frame');
            if (showOriginalBtn) {
                try {
                    const iframeDoc = showOriginalBtn.contentDocument || showOriginalBtn.contentWindow.document;
                    const btn = iframeDoc.querySelector('.goog-close-link');
                    if (btn) btn.click();
                } catch(e) {}
            }

            // 3. Try using the Google Translate API directly
            const frame = document.querySelector('iframe.goog-te-menu-frame');
            if (frame) {
                try {
                    const innerDoc = frame.contentDocument || frame.contentWindow.document;
                    const items = innerDoc.querySelectorAll('.goog-te-menu2-item');
                    // First item is usually the source language (English)
                    items.forEach(item => {
                        if (item.textContent.includes('English')) {
                            item.click();
                            return;
                        }
                    });
                } catch(e) {}
            }

            // 4. Try the select element approach
            const sel = document.querySelector('.goog-te-combo');
            if (sel) {
                sel.value = 'en';
                sel.dispatchEvent(new Event('change'));
                // Give it a moment then reload
                setTimeout(() => window.location.reload(), 500);
                return;
            }

            // 5. If nothing else worked, just nuke cookies and reload
            window.location.reload();
        } else {
            // Set Indonesian translation
            // First clear any existing cookies
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname;
            
            // Set new cookies
            document.cookie = `googtrans=/en/${langCode}; path=/`;
            document.cookie = `googtrans=/en/${langCode}; path=/; domain=${window.location.hostname}`;
            
            // Also try the select element approach for immediate effect
            const sel = document.querySelector('.goog-te-combo');
            if (sel) {
                sel.value = langCode;
                sel.dispatchEvent(new Event('change'));
                // Give it a moment then reload to ensure cookie sticks
                setTimeout(() => window.location.reload(), 500);
                return;
            }
            
            window.location.reload();
        }
    }

    // Set initial label based on Google Translate cookie
    document.addEventListener("DOMContentLoaded", function() {
        const match = document.cookie.match(/(^|;) ?googtrans=([^;]*)(;|$)/);
        if (match && match[2] && match[2].endsWith('id')) {
            document.getElementById('current-lang-label').innerText = 'Indonesian';
            document.querySelector('.custom-translate-option[data-lang="id"]')?.classList.add('active-lang');
        } else {
            document.querySelector('.custom-translate-option[data-lang="en"]')?.classList.add('active-lang');
        }
    });
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
