{{--
    ADS PAYMENT PAGE — Bootcamp Coretax
    Route: GET /ad/payment/{registration}
    Auto-open Midtrans Snap, tampilkan status inline.
--}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran — Bootcamp Coretax</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --c-900: #2D004A;
            --c-800: #46006D;
            --c-700: #5B1A87;
            --c-600: #7c3aed;
            --orange: #F97316;
            --font: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            font-family: var(--font);
            background: linear-gradient(135deg, var(--c-900) 0%, #1a0033 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            box-shadow: 0 32px 80px -16px rgba(0,0,0,.55);
        }
        .card-header {
            background: linear-gradient(135deg, var(--c-900), var(--c-700));
            padding: 1.5rem 1.75rem;
            display: flex;
            align-items: center;
            gap: .875rem;
        }
        .card-header-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--orange), #fb923c);
            border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .card-header-icon i { color: #fff; font-size: 1.1rem; }
        .card-header-text .sub  { color: #fda675; font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .card-header-text .main { color: #fff; font-size: 1rem; font-weight: 800; }
        .card-body { padding: 1.5rem 1.75rem; }

        /* States */
        .state { display: none; flex-direction: column; align-items: center; text-align: center; gap: 1rem; }
        .state.active { display: flex; }

        /* Loading */
        .spinner {
            width: 56px; height: 56px;
            border: 5px solid #f3e8ff;
            border-top-color: var(--c-600);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Icons */
        .state-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
        }
        .icon-success { background: #dcfce7; color: #16a34a; }
        .icon-pending { background: #fef9c3; color: #ca8a04; }
        .icon-error   { background: #fee2e2; color: #dc2626; }
        .icon-closed  { background: #f3e8ff; color: var(--c-600); }

        /* Text */
        .state-title { font-size: 1.2rem; font-weight: 800; color: #1f2937; }
        .state-desc  { font-size: .875rem; color: #6b7280; line-height: 1.6; }

        /* Code box */
        .code-box {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: .875rem;
            padding: 1rem 1.25rem;
            width: 100%;
        }
        .code-label { font-size: .7rem; color: #9ca3af; margin-bottom: .25rem; }
        .code-value { font-family: monospace; font-size: 1.1rem; font-weight: 900; color: var(--c-700); letter-spacing: .08em; }
        .code-hint  { font-size: .68rem; color: #9ca3af; margin-top: .25rem; }

        /* Amount box */
        .amount-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: .875rem;
            padding: .875rem 1.25rem;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-label { font-size: .8rem; color: #9a3412; font-weight: 600; }
        .amount-value { font-size: 1.05rem; font-weight: 800; color: var(--orange); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            font-weight: 700; font-size: .9rem; padding: .875rem 1.5rem;
            border-radius: .875rem; border: none; cursor: pointer;
            transition: transform .15s, box-shadow .15s;
            text-decoration: none; width: 100%;
        }
        .btn:hover  { transform: translateY(-2px); }
        .btn:active { transform: scale(.97); }

        .btn-primary {
            background: linear-gradient(90deg, #f97316, #fb923c 50%, #f97316);
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
            color: #fff;
            box-shadow: 0 8px 24px -4px rgba(249,115,22,.45);
        }
        @keyframes shimmer { from { background-position: -200% center; } to { background-position: 200% center; } }

        .btn-ghost {
            background: transparent;
            color: #6b7280;
            font-size: .8rem;
            padding: .5rem;
        }
        .btn-ghost:hover { color: #374151; transform: none; }

        /* WhatsApp button */
        .btn-wa { background: #1eb257; color: #fff; box-shadow: 0 8px 24px -4px rgba(30,178,87,.35); }
        .btn-wa:hover { background: #17a04e; }

        /* Footer */
        .card-footer {
            border-top: 1px solid #f3f4f6;
            padding: 1rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            font-size: .7rem;
            color: #9ca3af;
        }
        .card-footer i { font-size: .65rem; }
    </style>
</head>
<body>

<div class="card">

    {{-- Header --}}
    <div class="card-header">
        <div class="card-header-icon">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="card-header-text">
            <p class="sub">Pembayaran Bootcamp</p>
            <p class="main">{{ $event->event_title }}</p>
        </div>
    </div>

    <div class="card-body" style="gap: 0;">

        {{-- ── STATE: LOADING (auto-open Snap) ─────────────────── --}}
        <div id="state-loading" class="state active" style="padding: 2rem 0;">
            <div class="spinner"></div>
            <p class="state-title" style="font-size: 1rem;">Membuka Pembayaran…</p>
            <p class="state-desc" style="font-size: .8rem;">Harap tunggu, jangan tutup halaman ini.</p>
        </div>

        {{-- ── STATE: SUCCESS ────────────────────────────────────── --}}
        <div id="state-success" class="state" style="padding: 1.5rem 0; gap: 1.1rem;">
            <div class="state-icon icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="state-title">Pembayaran Berhasil! 🎉</p>
                <p class="state-desc" style="margin-top: .4rem;">
                    Terima kasih! Pendaftaran kamu sudah terkonfirmasi.<br>
                    Detail kelas akan dikirim ke email kamu sebelum sesi dimulai.
                </p>
            </div>
            <div class="code-box">
                <p class="code-label">Kode Pendaftaran</p>
                <p class="code-value">{{ $registration->registration_code }}</p>
                <p class="code-hint">Screenshot ini sebagai bukti pembayaran kamu</p>
            </div>
            <a href="{{ 'https://wa.me/6285176767623?text=' . urlencode('Halo, saya sudah berhasil bayar Bootcamp Coretax dengan kode ' . $registration->registration_code . '. Mohon konfirmasi pendaftaran saya.') }}"
               target="_blank" rel="noopener" class="btn btn-wa">
                <i class="fab fa-whatsapp" style="font-size:1.1rem;"></i>
                Konfirmasi via WhatsApp
            </a>
        </div>

        {{-- ── STATE: PENDING ────────────────────────────────────── --}}
        <div id="state-pending" class="state" style="padding: 1.5rem 0; gap: 1.1rem;">
            <div class="state-icon icon-pending">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="state-title">Pembayaran Dalam Proses</p>
                <p class="state-desc" style="margin-top: .4rem;">
                    Pembayaranmu sedang diverifikasi. Jika sudah berhasil,
                    status akan otomatis diperbarui dan kamu akan mendapat konfirmasi via email.
                </p>
            </div>
            <div class="code-box">
                <p class="code-label">Kode Pendaftaran</p>
                <p class="code-value">{{ $registration->registration_code }}</p>
                <p class="code-hint">Gunakan kode ini jika menghubungi tim kami</p>
            </div>
            <div class="amount-box">
                <span class="amount-label">Total yang harus dibayar</span>
                <span class="amount-value">Rp {{ number_format($grossAmount, 0, ',', '.') }}</span>
            </div>
            <button id="btn-retry" class="btn btn-primary" type="button">
                <i class="fas fa-redo"></i> Selesaikan Pembayaran
            </button>
            <a href="{{ 'https://wa.me/' . preg_replace('/\D/', '', '6285176767623') . '?text=' . urlencode('Halo, saya sudah daftar Bootcamp Coretax dengan kode ' . $registration->registration_code . '. Mohon konfirmasi pembayaran saya.') }}"
               target="_blank" rel="noopener" class="btn btn-wa">
                <i class="fab fa-whatsapp" style="font-size:1.1rem;"></i>
                Chat Admin untuk Konfirmasi
            </a>
        </div>

        {{-- ── STATE: ERROR ───────────────────────────────────────── --}}
        <div id="state-error" class="state" style="padding: 1.5rem 0; gap: 1.1rem;">
            <div class="state-icon icon-error">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                <p class="state-title">Pembayaran Gagal</p>
                <p class="state-desc" style="margin-top: .4rem;">
                    Terjadi kendala saat memproses pembayaran. Kamu bisa mencoba lagi
                    atau hubungi kami untuk bantuan.
                </p>
            </div>
            <div class="code-box">
                <p class="code-label">Kode Pendaftaran</p>
                <p class="code-value">{{ $registration->registration_code }}</p>
            </div>
            <button id="btn-retry-error" class="btn btn-primary" type="button">
                <i class="fas fa-redo"></i> Coba Bayar Lagi
            </button>
        </div>

    </div>

    {{-- Footer --}}
    <div class="card-footer">
        <i class="fas fa-lock"></i>
        <span>Transaksi aman &amp; terenkripsi oleh Midtrans</span>
    </div>

</div>

{{-- Midtrans Snap --}}
<script
    type="text/javascript"
    src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script type="text/javascript">
    var snapToken   = '{{ $snapToken }}';
    var stateEls    = { loading: null, success: null, pending: null, error: null };

    function showState(name) {
        Object.keys(stateEls).forEach(function(k) {
            stateEls[k].classList.remove('active');
        });
        if (stateEls[name]) stateEls[name].classList.add('active');
    }

    function openSnap() {
        window.snap.pay(snapToken, {
            onSuccess: function () { showState('success'); },
            onPending: function () { showState('pending'); },
            onError:   function () { showState('error'); },
            onClose:   function () { showState('pending'); }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        stateEls.loading = document.getElementById('state-loading');
        stateEls.success = document.getElementById('state-success');
        stateEls.pending = document.getElementById('state-pending');
        stateEls.error   = document.getElementById('state-error');

        // Tombol retry (pending / error)
        var retryBtns = document.querySelectorAll('#btn-retry, #btn-retry-error');
        retryBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                showState('loading');
                openSnap();
            });
        });

        // Jika ada status di query string (redirect dari Snap), tunjukkan state tersebut
        var initialStatus = '{{ request()->get('status', '') }}';
        if (initialStatus) {
            // Pastikan nama state valid (success|pending|error)
            if (['success','pending','error'].includes(initialStatus)) {
                showState(initialStatus);
            } else {
                showState('pending');
            }
        } else {
            // Auto-open Snap setelah sedikit delay agar skrip selesai load
            setTimeout(function () {
                showState('loading');
                openSnap();
            }, 600);
        }
    });
</script>

</body>
</html>
