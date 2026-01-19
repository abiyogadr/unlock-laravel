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

    // 1. Tentukan ukuran ASLI sertifikat (Hardcode agar konsisten)
    // Standar A4 Landscape biasanya sekitar 1123px x 794px (96 DPI)
    // Sesuaikan angka ini dengan CSS width/height asli elemen sertifikat Anda
    const originalWidth = 1123; 
    const originalHeight = 794;

    htmlToImage.toPng(element, { 
        quality: 0.9, 
        pixelRatio: 1, // Jaga kualitas tajam
        
        // PENTING: Paksa ukuran kanvas output
        width: originalWidth,
        height: originalHeight,
        
        // PENTING: Style override untuk mengatasi limitasi Android Chrome Mobile
        style: {
            transform: 'none', // Matikan scale() CSS jika ada
            transformOrigin: 'top left',
            margin: '0',
            left: '0',
            top: '0',
            
            // Paksa elemen menjadi terlihat penuh & fixed di mata renderer
            position: 'absolute', 
            width: `${originalWidth}px`,
            height: `${originalHeight}px`,
            maxWidth: 'none',
            maxHeight: 'none',
            minWidth: `${originalWidth}px`,
            minHeight: `${originalHeight}px`,
            
            // Pastikan tidak ada yang hidden
            display: 'block',
            visibility: 'visible',
            overflow: 'visible',
            
            // Putih bersih di belakang (mencegah transparan hitam di beberapa Android)
            backgroundColor: '#ffffff' 
        },
        
        // Opsi tambahan untuk font di Android
        fontEmbedCSS: '', 
        skipFonts: false
    })
    .then(function (dataUrl) {
        certImageDataUrl = dataUrl;
        wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg rounded-md" id="rendered-cert-img" alt="Certificate Preview">`;
        
        // Kirim event ke parent (jika di iframe)
        if (window.parent) {
            window.parent.postMessage({ type: 'preview-loaded', timestamp: Date.now() }, '*');
        }
    })
    .catch(function (error) {
        console.error('Render Gagal:', error);
        // Fallback untuk Android jadul: Coba render ulang dengan pixelRatio 1 (lebih ringan)
        if (error.message && error.message.includes('canvas')) {
             // Retry logic here if needed
             wrapper.innerHTML = '<p class="text-red-500 text-xs">Memori penuh. Coba refresh halaman.</p>';
        } else {
             wrapper.innerHTML = `<p class="text-red-500 text-xs">Gagal render: ${error.message}</p>`;
        }
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
