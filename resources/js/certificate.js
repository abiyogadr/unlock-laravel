let certImageDataUrl = null;

// 1. Generate QR Code
function generateQRCode() {
    const qrContainer = document.getElementById("qrcode-container");
    if (!qrContainer) return;
    const qrText = qrContainer.getAttribute("data-qr-text") || "";
    qrContainer.innerHTML = "";
    new QRCode(qrContainer, {
        text: qrText, width: 80, height: 80,
        correctLevel: QRCode.CorrectLevel.H
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
        
        // Sinyal untuk Admin Panel bahwa loading selesai
        if (window.parent) {
            window.parent.dispatchEvent(new CustomEvent('preview-loaded'));
        }
    })
    .catch(function (error) {
        console.error('Render Error:', error);
    });
}

// 3. Listener Pesan dari Admin (Real-time Signature)
window.addEventListener('message', function(event) {
    if (event.data.type === 'UPDATE_SIGNATURE') {
        const sigImages = document.querySelectorAll('.sig-img');
        sigImages.forEach(img => {
            img.src = event.data.base64;
            img.parentElement.style.visibility = 'visible'; // Paksa tampil
        });
        // Render ulang setelah gambar berubah
        setTimeout(renderCertificateToImage, 300);
    }
});

// Jalankan saat load
window.addEventListener('load', function() {
    generateQRCode();
    setTimeout(renderCertificateToImage, 500); // Beri jeda agar font/gambar termuat
});
