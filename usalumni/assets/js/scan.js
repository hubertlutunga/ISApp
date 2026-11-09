(function () {
    const root = document.querySelector('[data-scan-app]');
    if (!root) return;

    const start = document.getElementById('scanStart');
    const stop = document.getElementById('scanStop');
    const status = document.getElementById('scanStatus');
    const qrReader = document.getElementById('qr-reader');
    const dayInput = document.getElementById('event_day');
    let qr = null;
    let running = false;

    const setStatus = (message) => { if (status) status.textContent = message; };
    const extractToken = (decodedText) => {
        const text = (decodedText || '').trim();
        if (!text) return '';
        if (/^https?:\/\//i.test(text)) {
            try {
                const url = new URL(text);
                return (url.searchParams.get('token') || '').trim();
            } catch (e) {
                return '';
            }
        }
        if (/^[A-Za-z0-9\-_]+$/.test(text)) {
            return text;
        }
        return '';
    };

    const stopScan = async () => {
        try {
            if (qr) {
                await qr.stop();
                await qr.clear();
            }
        } catch (error) {}
        running = false;
        if (stop) stop.style.display = 'none';
        if (start) start.style.display = 'inline-flex';
        if (qrReader) qrReader.style.display = 'none';
        setStatus('Caméra arrêtée');
    };

    const submitToken = (token) => {
        if (!token) {
            setStatus('QR invalide.');
            return;
        }
        const day = dayInput ? dayInput.value : '1';
        window.location.href = `index.php?token=${encodeURIComponent(token)}&day=${encodeURIComponent(day)}`;
    };

    const startScan = async () => {
        if (!window.Html5Qrcode) {
            setStatus('Bibliothèque QR indisponible.');
            return;
        }
        try {
            if (!qr) qr = new Html5Qrcode('qr-reader');
            running = true;
            if (qrReader) qrReader.style.display = 'block';
            if (start) start.style.display = 'none';
            if (stop) stop.style.display = 'inline-flex';
            setStatus('Ouverture de la caméra...');
            await qr.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                async (decodedText) => {
                    if (!running) return;
                    running = false;
                    setStatus('QR détecté.');
                    const token = extractToken(decodedText);
                    await stopScan();
                    submitToken(token);
                },
                () => {}
            );
            setStatus('Caméra active. Scannez un QR.');
        } catch (error) {
            console.error(error);
            await stopScan();
            setStatus('Impossible d’ouvrir la caméra.');
        }
    };

    start?.addEventListener('click', startScan);
    stop?.addEventListener('click', stopScan);
})();

