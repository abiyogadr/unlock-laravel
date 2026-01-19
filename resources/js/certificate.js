/**
 * ================================
 * GLOBAL STATE
 * ================================
 */
let certImageDataUrl = null;


/**
 * ================================
 * UTILITIES
 * ================================
 */

// Tunggu semua image benar-benar load
function waitImagesLoaded(container) {
    const images = container.querySelectorAll('img');
    return Promise.all([...images].map(img => {
        if (img.complete && img.naturalHeight !== 0) {
            return Promise.resolve();
        }
        return new Promise(resolve => {
            img.onload = resolve;
            img.onerror = resolve;
        });
    }));
}

// Set crossOrigin agar canvas tidak tainted
function fixImageCORS(container) {
    container.querySelectorAll('img').forEach(img => {
        if (!img.crossOrigin) {
            img.crossOrigin = 'anonymous';
        }
    });
}


/**
 * ================================
 * QR CODE (SAFE VERSION)
 * Render -> convert canvas to image
 * ================================
 */
function generateQRCode() {
    const qrContainer = document.getElementById('qrcode-container');
    if (!qrContainer) return;

    const qrText = qrContainer.dataset.qrText || '';
    qrContainer.innerHTML = '';

    const qr = new QRCode(qrContainer, {
        text: qrText,
        width: 80,
        height: 80,
        correctLevel: QRCode.CorrectLevel.H
    });

    // Convert canvas QR → IMG (WAJIB agar html-to-image aman)
    setTimeout(() => {
        const canvas = qrContainer.querySelector('canvas');
        if (!canvas) return;

        const img = new Image();
        img.src = canvas.toDataURL('image/png');
        img.width = 80;
        img.height = 80;
        img.crossOrigin = 'anonymous';

        qrContainer.innerHTML = '';
        qrContainer.appendChild(img);
    }, 200);
}


/**
 * ================================
 * CERTIFICATE RENDER
 * ================================
 */
async function renderCertificateToImage() {
    const element = document.getElementById('certificate');
    const wrapper = document.getElementById('cert-image-wrapper');

    if (!element || !wrapper) return;

    try {
        // Pastikan semua IMG aman & sudah load
        fixImageCORS(element);
        await waitImagesLoaded(element);

        const dataUrl = await htmlToImage.toPng(element, {
            pixelRatio: 2,
            cacheBust: true,
            skipFonts: false,
            fontEmbedCSS: '',
        });

        certImageDataUrl = dataUrl;

        wrapper.innerHTML = `
            <img 
                src="${dataUrl}" 
                class="w-full h-auto shadow-lg" 
                id="rendered-cert-img"
            >
        `;

        // Sinyal ke parent (admin panel)
        if (window.parent) {
            window.parent.dispatchEvent(
                new CustomEvent('preview-loaded')
            );
        }

    } catch (err) {
        console.error(
            'Render FAILED:',
            err?.message || err,
            err?.type || ''
        );
    }
}


/**
 * ================================
 * REALTIME SIGNATURE UPDATE
 * ================================
 */
window.addEventListener('message', function (event) {
    if (event?.data?.type !== 'UPDATE_SIGNATURE') return;

    const base64 = event.data.base64;
    if (!base64) return;

    document.querySelectorAll('.sig-img').forEach(img => {
        img.crossOrigin = 'anonymous';
        img.src = base64;
        img.parentElement.style.visibility = 'visible';
    });

    // Render ulang setelah image benar-benar masuk
    setTimeout(() => {
        renderCertificateToImage();
    }, 600);
});


/**
 * ================================
 * COPY CERT URL
 * ================================
 */
const btnCopy = document.getElementById('btnCopy');
if (btnCopy) {
    btnCopy.addEventListener('click', function () {
        const input = document.getElementById('cert-url');
        if (!input) return;

        input.select();
        input.setSelectionRange(0, 99999);

        try {
            document.execCommand('copy');
            this.innerHTML = '<i class="fas fa-check"></i> Copied!';
            this.classList.add('bg-green-600');

            setTimeout(() => {
                this.innerHTML = '<i class="far fa-copy"></i> Copy';
                this.classList.remove('bg-green-600');
            }, 2000);
        } catch (err) {
            prompt('Copy this URL:', input.value);
        }
    });
}


/**
 * ================================
 * DOWNLOAD PNG
 * ================================
 */
const downloadBtn = document.getElementById('downloadBtn');
if (downloadBtn) {
    downloadBtn.addEventListener('click', function () {
        if (!certImageDataUrl) {
            alert('Certificate is still rendering. Please wait...');
            return;
        }

        const link = document.createElement('a');
        link.href = certImageDataUrl;
        link.download = `certificate_${Date.now()}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}


/**
 * ================================
 * INIT ON LOAD
 * ================================
 */
window.addEventListener('load', function () {
    generateQRCode();

    // Delay agar font & QR benar-benar siap
    setTimeout(() => {
        renderCertificateToImage();
    }, 1200);
});
