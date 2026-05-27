import './bootstrap';

// Global input restriction: block non-standard keyboard characters (non-ASCII)
// Allows standard ASCII (space to tilde), newlines, and tabs.
// Ignores inputs specifically designed for emojis (name="emoji").
document.addEventListener('input', function(e) {
    if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
        // Skip emoji fields, password fields, or email fields (though emails should be ASCII anyway)
        if (e.target.name === 'emoji' || e.target.type === 'password') {
            return;
        }
        
        const originalValue = e.target.value;
        // Regex: Matches any character that is NOT in the basic ASCII printable range (\x20-\x7E)
        // or newline (\n, \r) or tab (\t)
        const sanitizedValue = originalValue.replace(/[^\x20-\x7E\r\n\t]/g, '');
        
        if (originalValue !== sanitizedValue) {
            // Calculate cursor position adjustment
            const cursorStart = e.target.selectionStart;
            const cursorEnd = e.target.selectionEnd;
            
            e.target.value = sanitizedValue;
            
            // Try to restore cursor position
            try {
                e.target.setSelectionRange(cursorStart - 1, cursorEnd - 1);
            } catch (err) {
                // Ignore, some input types don't support selection ranges
            }
        }
    }
});
