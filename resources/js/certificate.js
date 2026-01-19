let certImageDataUrl = null;
let renderAttemptCount = 0;

// Deteksi Mobile Browser
function isMobileDevice() {
    return /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent) && 
           !window.matchMedia('(min-width: 1024px)').matches;
}

// Deteksi Mode Desktop (user aktifkan "Request Desktop Site")
function isDesktopMode() {
    return window.screen.width > 1024 || 
           (window.innerWidth > 1024 && !/Mobile/i.test(navigator.userAgent));
}

// 1. Generate QR Code
function generateQRCode() {
    const qrContainer = document.getElementById("qrcode-container");
    if (!qrContainer) return;
    
    const qrText = qrContainer.getAttribute("data-qr-text") || "";
    qrContainer.innerHTML = "";
    
    try {
        new QRCode(qrContainer, {
            text: qrText,
            width: 80,
            height: 80,
            correctLevel: QRCode.CorrectLevel.H,
            useSVG: false // PNG lebih kompatibel di mobile
        });
    } catch (error) {
        console.warn('QR Code generation failed:', error);
        qrContainer.innerHTML = '<div class="text-xs text-gray-500">QR Code unavailable</div>';
    }
}

// 2. Render Certificate dengan strategi berbeda untuk mobile/desktop
function renderCertificateToImage() {
    const element = document.getElementById('certificate');
    const wrapper = document.getElementById('cert-image-wrapper');
    if (!element || !wrapper) {
        console.error('Certificate element not found');
        return;
    }

    renderAttemptCount++;
    
    // Tentukan konfigurasi berdasarkan device
    const isMobile = isMobileDevice();
    const isDesktop = isDesktopMode();
    
    // Konfigurasi adaptive
    const config = {
        quality: 0.8,
        pixelRatio: isDesktop ? 1 : (isMobile ? 0.5 : 0.7),
        skipFonts: false,
        fontEmbedCSS: '',
        cacheBust: true, // Hindari cache issues di mobile
        backgroundColor: '#ffffff',
        imagePlaceholder: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY2NjY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIGxvYWRpbmcuLi48L3RleHQ+PC9zdmc+' // Fallback untuk gambar gagal load
    };

    // Log untuk debugging
    console.log(`Render attempt #${renderAttemptCount}`, {
        isMobile: isMobile,
        isDesktop: isDesktop,
        pixelRatio: config.pixelRatio,
        userAgent: navigator.userAgent.substring(0, 50) + '...'
    });

    htmlToImage.toPng(element, config)
        .then(function (dataUrl) {
            certImageDataUrl = dataUrl;
            wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg" id="rendered-cert-img" alt="Certificate Preview">`;
            
            // Sinyal ke parent window
            if (window.parent) {
                window.parent.postMessage({
                    type: 'preview-loaded',
                    timestamp: Date.now(),
                    success: true,
                    device: isMobile ? 'mobile' : 'desktop'
                }, '*');
            }
            
            // Reset counter on success
            renderAttemptCount = 0;
        })
        .catch(function (error) {
            console.error('Render Error:', error);
            
            // Fallback strategy berdasarkan attempt count
            if (renderAttemptCount === 1) {
                // Attempt 1 gagal, coba dengan更低 resolusi
                console.log('Retrying with ultra-low resolution...');
                setTimeout(() => renderCertificateToImageLowRes(), 500);
            } else if (renderAttemptCount === 2) {
                // Attempt 2 gagal, coba skip fonts
                console.log('Retrying with font embedding disabled...');
                setTimeout(() => renderCertificateToImageNoFonts(), 500);
            } else {
                // Semua attempt gagal, tampilkan error
                wrapper.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-red-700 font-medium">Certificate rendering failed</p>
                        <p class="text-red-600 text-sm mt-1">Please enable "Desktop Mode" in your browser</p>
                        <button onclick="renderCertificateToImage()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `;
                
                // Sinyal gagal ke parent
                if (window.parent) {
                    window.parent.postMessage({
                        type: 'preview-loaded',
                        timestamp: Date.now(),
                        success: false,
                        error: error.message
                    }, '*');
                }
            }
        });
}

// 3. Ultra-low resolution fallback
function renderCertificateToImageLowRes() {
    const element = document.getElementById('certificate');
    const wrapper = document.getElementById('cert-image-wrapper');
    
    console.log('Using ultra-low resolution mode...');
    
    htmlToImage.toPng(element, { 
        quality: 0.6, 
        pixelRatio: 0.3,
        skipFonts: false,
        cacheBust: true,
        backgroundColor: '#ffffff'
    })
    .then(function (dataUrl) {
        certImageDataUrl = dataUrl;
        wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg" id="rendered-cert-img" alt="Certificate Preview (Low Res)">`;
        
        // Tampilkan warning ke user
        const warning = document.createElement('div');
        warning.className = 'bg-yellow-50 border border-yellow-200 rounded p-2 mt-2 text-sm text-yellow-800';
        warning.innerHTML = '<i class="fas fa-info-circle"></i> Using low-quality mode for mobile compatibility';
        wrapper.appendChild(warning);
    })
    .catch(function (error) {
        console.error('Low-res render also failed:', error);
        // Lanjutkan ke no-fonts fallback
        renderAttemptCount = 1; // Force next fallback
        renderCertificateToImageNoFonts();
    });
}

// 4. No-fonts fallback (terakhir)
function renderCertificateToImageNoFonts() {
    const element = document.getElementById('certificate');
    const wrapper = document.getElementById('cert-image-wrapper');
    
    console.log('Disabling font embedding...');
    
    htmlToImage.toPng(element, { 
        quality: 0.6, 
        pixelRatio: 0.3,
        skipFonts: true, // Kunci: skip font embedding
        cacheBust: true,
        backgroundColor: '#ffffff'
    })
    .then(function (dataUrl) {
        certImageDataUrl = dataUrl;
        wrapper.innerHTML = `<img src="${dataUrl}" class="w-full h-auto shadow-lg" id="rendered-cert-img" alt="Certificate Preview (Basic)">`;
        
        // Tampilkan warning
        const warning = document.createElement('div');
        warning.className = 'bg-orange-50 border border-orange-200 rounded p-2 mt-2 text-sm text-orange-800';
        warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Fonts disabled for mobile compatibility. Quality reduced.';
        wrapper.appendChild(warning);
    })
    .catch(function (error) {
        console.error('No-fonts render also failed:', error);
        // Final fallback: tampilkan pesan error
        wrapper.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                <p class="text-red-700 font-semibold">Unable to render certificate</p>
                <p class="text-red-600 text-sm mt-2 mb-3">Your browser has limited canvas memory. Please:</p>
                <ul class="text-left text-red-600 text-sm space-y-1">
                    <li>1. Enable "Desktop Mode" in browser menu</li>
                    <li>2. Use Chrome/Firefox instead of stock browser</li>
                    <li>3. Close other tabs to free memory</li>
                </ul>
                <button onclick="renderCertificateToImage()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
    });
}

// 5. Listener Pesan dari Admin (Real-time Signature)
window.addEventListener('message', function(event) {
    // ✅ FIXED: Validasi data sebelum proses
    if (!event.data || typeof event.data !== 'object') {
        console.warn('Invalid message format received', event.data);
        return;
    }
    
    if (event.data.type === 'UPDATE_SIGNATURE') {
        console.log('Signature update received', event.data);
        
        const sigImages = document.querySelectorAll('.sig-img');
        if (sigImages.length === 0) {
            console.warn('No signature images found');
            return;
        }
        
        sigImages.forEach(img => {
            img.src = event.data.base64;
            img.parentElement.style.visibility = 'visible';
            
            // Pastikan image loaded sebelum render ulang
            img.onload = () => {
                console.log('Signature image loaded');
            };
            img.onerror = () => {
                console.error('Signature image failed to load');
            };
        });
        
        // Render ulang dengan delay yang cukup
        setTimeout(() => {
            renderAttemptCount = 0; // Reset counter
            renderCertificateToImage();
        }, 500);
    }
});

// 6. Initialize on load
window.addEventListener('load', function() {
    console.log('Certificate page loaded', {
        mobile: isMobileDevice(),
        desktopMode: isDesktopMode(),
        userAgent: navigator.userAgent
    });
    
    generateQRCode();
    
    // Delay render untuk memastikan semua assets loaded
    const delay = isMobileDevice() ? 1000 : 500;
    setTimeout(() => renderCertificateToImage(), delay);
});

// 7. Copy URL functionality (modern + fallback)
document.getElementById('btnCopy')?.addEventListener('click', async function() {
    const certUrlInput = document.getElementById('cert-url');
    const btn = this;
    
    if (!certUrlInput) {
        console.error('URL input not found');
        return;
    }
    
    try {
        // 🔒 Secure context check
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(certUrlInput.value);
            console.log('URL copied via Clipboard API');
        } else {
            // Fallback untuk HTTP atau browser lama
            certUrlInput.select();
            certUrlInput.setSelectionRange(0, 99999);
            const successful = document.execCommand('copy');
            
            if (!successful) {
                throw new Error('execCommand failed');
            }
            console.log('URL copied via execCommand fallback');
        }
        
        // Visual feedback
        const originalHTML = btn.innerHTML;
        const originalClasses = btn.className;
        
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.className = originalClasses + ' bg-green-600';
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.className = originalClasses;
        }, 2000);
        
    } catch (err) {
        console.error('Copy failed:', err);
        
        // Last resort: show prompt
        const userCopied = prompt("Copy this URL manually:", certUrlInput.value);
        
        if (userCopied !== null) {
            btn.innerHTML = '<i class="fas fa-check"></i> Ready to paste!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }
    }
});

// 8. Download PNG functionality
document.getElementById('downloadBtn')?.addEventListener('click', function() {
    if (!certImageDataUrl) {
        alert('Certificate is still rendering. Please wait...');
        return;
    }
    
    try {
        const link = document.createElement('a');
        link.href = certImageDataUrl;
        link.download = `certificate_${Date.now()}.png`;
        
        // Tambahkan ke DOM sementara
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        setTimeout(() => {
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }, 100);
        
        console.log('Certificate download initiated');
        
    } catch (error) {
        console.error('Download failed:', error);
        alert('Download failed. Please try again or use desktop mode.');
    }
});

// 9. Resize handler (re-render on orientation change)
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        console.log('Window resized, re-rendering certificate...');
        renderAttemptCount = 0;
        renderCertificateToImage();
    }, 250);
});

// 10. Error handler global untuk mobile debugging
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    
    // Tampilkan error di console mobile
    if (isMobileDevice()) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed bottom-0 left-0 right-0 bg-red-600 text-white p-2 text-xs z-50';
        errorDiv.innerHTML = `<i class="fas fa-bug"></i> Debug: ${event.error.message.substring(0, 50)}...`;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => errorDiv.remove(), 5000);
    }
});

// 11. Performance monitoring
if (window.performance && isMobileDevice()) {
    window.addEventListener('load', () => {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page load performance:', {
                domContentLoaded: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
                loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
                totalTime: perfData.loadEventEnd - perfData.fetchStart
            });
        }, 1000);
    });
}
