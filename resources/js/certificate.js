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

    // 1. Validasi awal: Pastikan elemen ada di DOM
    if (!element) {
        console.error("Gagal Render: Elemen #certificate tidak ditemukan.");
        return;
    }
    if (!wrapper) {
        console.warn("Peringatan: Elemen #cert-image-wrapper tidak ditemukan.");
        return;
    }

    // 2. Eksekusi render dengan opsi stabil
    htmlToImage.toPng(element, { 
        quality: 0.9, 
        pixelRatio: 2, // Lebih tajam untuk sertifikat
        cacheBust: true, // Menghindari masalah cache gambar
    })
    .then(function (dataUrl) {
        certImageDataUrl = dataUrl;
        wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg" id="rendered-cert-img">`;
        
        if (window.parent) {
            window.parent.dispatchEvent(new CustomEvent('preview-loaded'));
        }
        console.log("Render Berhasil: Gambar sertifikat telah diperbarui.");
    })
    .catch(function (error) {
        // 3. Penangkapan Error Detail
        let errorMessage = "Terjadi kesalahan yang tidak diketahui.";

        if (error instanceof Error) {
            // Jika error adalah objek Error standar (memiliki message & stack)
            errorMessage = `Error: ${error.message}`;
            console.error("Stack Trace:", error.stack);
        } else if (typeof error === 'string') {
            // Jika library melempar error dalam bentuk string
            errorMessage = error;
        } else if (error && error.isTrusted) {
            // Menjelaskan kenapa muncul 'isTrusted: true'
            errorMessage = "Error dipicu oleh interaksi sistem/browser (mungkin masalah CORS atau aset belum terload).";
        }

        console.error('Render Error Detail:', errorMessage);
        console.dir(error); // Menampilkan struktur objek error lengkap di konsol

        // Opsi: Tampilkan peringatan visual ke user
        // alert("Gagal memuat gambar sertifikat. Silakan cek koneksi atau format gambar.");
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

// Copy URL functionality
document.getElementById('btnCopy').addEventListener('click', function() {
    const certUrlInput = document.getElementById('cert-url');
    certUrlInput.select();
    certUrlInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        // Visual feedback
        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
        this.classList.add('bg-green-600');
        setTimeout(() => {
            this.innerHTML = '<i class="far fa-copy"></i> Copy';
            this.classList.remove('bg-green-600');
        }, 2000);
    } catch (err) {
        console.error('Copy failed:', err);
        // Fallback for older browsers
        prompt("Copy this URL:", certUrlInput.value);
    }
});

// Download PNG functionality
document.getElementById('downloadBtn').addEventListener('click', function() {
    if (!certImageDataUrl) {
        alert('Certificate is still rendering. Please wait...');
        return;
    }
    
    // Create temporary link element
    const link = document.createElement('a');
    link.href = certImageDataUrl;
    link.download = 'certificate_' + Date.now() + '.png';
    
    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});
