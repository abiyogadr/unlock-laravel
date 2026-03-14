let certImageDataUrl = null;

// 1. Generate QR Code
function generateQRCode() {
    const qrContainer = document.getElementById("qrcode-container");
    if (!qrContainer) return;
    const qrText = qrContainer.getAttribute("data-qr-text") || "";
    qrContainer.innerHTML = "";
    new QRCode(qrContainer, {
        text: qrText, width: 80, height: 80,
        correctLevel: QRCode.CorrectLevel.H,
        useSVG: false
    });
}

// 2. Render ke IMG (Versi Stabil)
function renderCertificateToImage() {
    const element = document.getElementById('certificate');
    const wrapper = document.getElementById('cert-image-wrapper');
    if (!element || !wrapper) return;

    htmlToImage.toPng(element, { 
        quality: 0.8, 
        pixelRatio: 1,
        skipFonts: false,
        fontEmbedCSS: '',
    })
    .then(function (dataUrl) {
        certImageDataUrl = dataUrl;
        wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg" id="rendered-cert-img">`;
        
        // ✅ FIXED: Use postMessage instead of CustomEvent for Android
        if (window.parent) {
            window.parent.postMessage({
                type: 'preview-loaded',
                timestamp: Date.now()
            }, '*');
        }
    })
    .catch(function (error) {
        console.error('Render Error:', error);
        // Fallback: Show error message in wrapper
        wrapper.innerHTML = '<p class="text-red-500">Failed to render certificate. Please check console.</p>';
    });
}

// 3. Listener Pesan dari Admin (Real-time Signature)
window.addEventListener('message', function(event) {
    // ✅ FIXED: Check for event.data and handle Android's isTrusted issue
    if (event.data && event.data.type === 'UPDATE_SIGNATURE') {
        const sigImages = document.querySelectorAll('.sig-img');
        sigImages.forEach(img => {
            img.src = event.data.base64;
            img.parentElement.style.visibility = 'visible';
        });
        setTimeout(renderCertificateToImage, 300);
    }
});

// Jalankan saat load
window.addEventListener('load', function() {
    generateQRCode();
    setTimeout(renderCertificateToImage, 500);
});

// Copy URL functionality
document.getElementById('btnCopy').addEventListener('click', async function() {
    const certUrlInput = document.getElementById('cert-url');
    const btn = this;
    
    try {
        // ✅ FIXED: Use modern Clipboard API with fallback
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(certUrlInput.value);
        } else {
            // Fallback for older Android
            certUrlInput.select();
            certUrlInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
        }
        
        // Visual feedback
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerHTML = '<i class="far fa-copy"></i> Copy';
            btn.classList.remove('bg-green-600');
        }, 2000);
    } catch (err) {
        console.error('Copy failed:', err);
        // Last resort fallback
        prompt("Copy this URL:", certUrlInput.value);
    }
});

// Download PNG functionality
document.getElementById('downloadBtn').addEventListener('click', function() {
    if (!certImageDataUrl) {
        alert('Certificate is still rendering. Please wait...');
        return;
    }
    
    const link = document.createElement('a');
    link.href = certImageDataUrl;
    link.download = 'certificate_' + Date.now() + '.png';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});
