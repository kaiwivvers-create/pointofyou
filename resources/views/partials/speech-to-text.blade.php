<script>
    (() => {
        if (window.__pointOfYouSpeechToTextLoaded) {
            return;
        }
        window.__pointOfYouSpeechToTextLoaded = true;

        const RecognitionCtor = window.SpeechRecognition || window.webkitSpeechRecognition;

        function getWrapper(button) {
            return button.closest('[data-speech-wrapper]');
        }

        function getStatus(button) {
            return getWrapper(button)?.querySelector('[data-speech-status]');
        }

        function getLabel(button) {
            return button.querySelector('[data-speech-label]');
        }

        function setStatus(button, message, tone = 'idle') {
            const status = getStatus(button);
            if (!status) return;

            status.textContent = message || '';
            status.style.color = tone === 'error' ? '#dc2626' : tone === 'active' ? '#b45309' : '#9ca3af';
            status.style.fontWeight = tone === 'error' || tone === 'active' ? '700' : '400';
        }

        function setRecordingUI(button, isRecording) {
            const label = getLabel(button);
            button.setAttribute('aria-pressed', isRecording ? 'true' : 'false');
            button.style.background = isRecording ? '#fef3c7' : '#ffffff';
            button.style.borderColor = isRecording ? '#f59e0b' : '#fcd34d';
            button.style.boxShadow = isRecording ? '0 0 0 3px rgba(245, 158, 11, 0.18)' : '0 1px 2px rgba(0,0,0,0.04)';
            button.style.transform = isRecording ? 'scale(1.02)' : '';
            if (label) {
                label.textContent = isRecording ? 'Recording...' : 'Speak';
            }
        }

        function appendTranscript(textarea, transcript) {
            const current = textarea.value || '';
            const start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : current.length;
            const end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : current.length;
            const before = current.slice(0, start);
            const after = current.slice(end);
            const needsSpaceBefore = before.length > 0 && !/\s$/.test(before) && !/^[,.;!?]/.test(transcript);
            const needsSpaceAfter = after.length > 0 && !/^\s/.test(after) && !/[,.!?]$/.test(transcript);
            const nextValue = `${before}${needsSpaceBefore ? ' ' : ''}${transcript}${needsSpaceAfter ? ' ' : ''}${after}`;

            textarea.value = nextValue;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));

            try {
                const cursor = (before + (needsSpaceBefore ? ' ' : '') + transcript).length;
                textarea.focus();
                textarea.setSelectionRange(cursor, cursor);
            } catch (error) {
                // Ignore selection issues on unsupported inputs.
            }
        }

        function resolveLanguage(button) {
            const select = getWrapper(button)?.querySelector('[data-speech-lang]');
            const value = select?.value || 'auto';

            if (value === 'auto') {
                return (navigator.language || 'en-US').toLowerCase().startsWith('id') ? 'id-ID' : 'en-US';
            }

            return value;
        }

        function initButton(button) {
            if (button.dataset.speechBound === '1') return;
            button.dataset.speechBound = '1';

            const textarea = document.getElementById(button.dataset.target || '');
            if (!textarea) {
                button.disabled = true;
                setStatus(button, 'Notes field not found.', 'error');
                return;
            }

            const secureEnough = window.isSecureContext && RecognitionCtor;
            if (!secureEnough) {
                button.disabled = true;
                if (getLabel(button)) {
                    getLabel(button).textContent = 'Unavailable';
                }
                setStatus(button, 'Use Chrome or Edge over HTTPS or localhost.', 'error');
                return;
            }

            let recognition = null;

            button.addEventListener('click', () => {
                if (recognition) {
                    recognition.stop();
                    return;
                }

                recognition = new RecognitionCtor();
                recognition.lang = resolveLanguage(button);
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    setRecordingUI(button, true);
                    setStatus(button, `Recording in ${recognition.lang}...`, 'active');
                };

                recognition.onresult = (event) => {
                    const transcript = event.results?.[0]?.[0]?.transcript?.trim();
                    if (transcript) {
                        appendTranscript(textarea, transcript);
                        setStatus(button, `Inserted: ${transcript}`, 'active');
                    }
                };

                recognition.onerror = (event) => {
                    const reason = event?.error ? `Speech input failed: ${event.error}` : 'Speech input failed.';
                    setRecordingUI(button, false);
                    setStatus(button, reason, 'error');
                    recognition = null;
                };

                recognition.onend = () => {
                    setRecordingUI(button, false);
                    setStatus(button, 'Ready.', 'idle');
                    recognition = null;
                };

                try {
                    recognition.start();
                } catch (error) {
                    setRecordingUI(button, false);
                    setStatus(button, 'Could not start speech input.', 'error');
                    recognition = null;
                }
            });
        }

        function initAll() {
            document.querySelectorAll('[data-speech-to-text]').forEach(initButton);
        }

        document.addEventListener('DOMContentLoaded', initAll);
        if (document.readyState !== 'loading') {
            initAll();
        }
    })();
</script>
