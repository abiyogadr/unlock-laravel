<template>
  <div class="min-h-screen bg-gray-50 py-4">
    <div class="container mx-auto px-4 max-w-6xl">
      <!-- Header -->
      <button @click="goBack()" class="inline-flex items-center text-primary hover:text-purple-800 mb-6 cursor-pointer">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
      </button>

      <div v-if="statusMessage" :class="['mb-4 p-3 rounded', statusMessageType === 'error' ? 'bg-red-50 border border-red-100 text-red-800' : statusMessageType === 'success' ? 'bg-green-50 border border-green-100 text-green-800' : 'bg-primary/5 border border-primary/20 text-primary']">
        {{ statusMessage }}
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-24">
        <!-- Left Column: Course Details & Package Selection -->
        <div class="lg:col-span-2 space-y-4 md:space-y-6">
          <!-- Course Details -->
          <div v-if="type === 'course' && course" class="bg-white rounded-2xl shadow-lg px-6 py-4">
            <h2 class="text-lg md:text-lg font-bold text-gray-800 mb-4">Detail Pembelian</h2>
            <div class="flex items-start gap-4 mb-4">
              <img :src="course.thumbnail_url" :alt="course.title" class="w-24 h-24 object-cover rounded-lg bg-gradient-to-br from-gray-200/80 to-gray-100/80">
              <div class="flex-1">
                <h3 class="font-semibold text-sm md:text-md text-gray-800 mb-2 line-clamp-2">{{ course.title }}</h3>
                <!-- <p class="text-xs hidden md:block text-gray-600 mb-3">{{ course.short_description }}</p> -->
                <div class="flex items-center gap-4">
                  <div>
                    <p class="text-xs text-gray-500">Harga (IDR)</p>
                    <p class="text-primary font-bold text-sm md:text-lg">{{ formatPrice(course.price) }}</p>
                  </div>
                  <!-- <div v-if="course.credit_cost > 0">
                    <p class="text-xs text-gray-500">Atau (USTAR)</p>
                    <p class="text-yellow-600 font-bold text-sm md:text-lg flex items-center"><i class="fas fa-coins mr-1"></i> {{ course.credit_cost }}</p>
                  </div> -->
                </div>
              </div>
            </div>
          </div>

          <!-- Recommended Packages (Commented Out USTAR) -->
          <!-- <div v-if="type === 'course' && recommendedPackages && recommendedPackages.length > 0" class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-800">Paket USTAR Rekomendasi</h2>
              <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">Hemat!</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Beli paket USTAR untuk mengakses banyak kursus dengan harga lebih hemat</p>
            <div class="space-y-3">
              <div v-for="pkg in recommendedPackages" :key="pkg.id" @click="switchToPackage(pkg.id)" class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary transition">
                <div class="flex items-center justify-between">
                  <div><h3 class="font-bold text-gray-800">{{ pkg.name }}</h3><p class="text-sm text-gray-600">{{ pkg.ustar_credits }} USTAR Credits</p></div>
                  <div class="text-right"><p class="text-lg font-bold text-primary">{{ formatPrice(pkg.discount_price || pkg.price) }}</p><p class="text-xs text-gray-500">{{ pkg.duration_days }} hari</p></div>
                </div>
              </div>
            </div>
          </div> -->

          <!-- Package Selection (grid on desktop) (Commented Out USTAR) -->
          <!-- <div v-if="type === 'package' && packages" class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Pilih Paket USTAR</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div v-for="pkg in packages" :key="pkg.id" @click="selectedPackage = pkg" :class="['p-6 border-2 rounded-2xl cursor-pointer transition', selectedPackage?.id === pkg.id ? 'border-primary bg-purple-50' : 'border-gray-200 hover:border-primary']">
                <div class="flex items-start justify-between mb-3"><div class="flex-1"><h3 class="font-bold text-lg text-gray-800">{{ pkg.name }}</h3><p class="text-sm text-gray-600 mt-1">{{ pkg.ustar_credits }} USTAR Credits</p></div></div>
                <div class="flex items-baseline gap-2 mb-3"><span v-if="pkg.discount_price" class="text-sm text-gray-400 line-through">{{ formatPrice(pkg.price) }}</span><span class="text-3xl font-bold text-primary">{{ formatPrice(pkg.discount_price || pkg.price) }}</span><span class="text-sm text-gray-500">/ {{ pkg.duration_days }} hari</span></div>
                <div class="flex items-center text-sm text-gray-600"><i class="fas fa-check-circle text-green-500 mr-2"></i>Akses unlimited dengan kredit USTAR</div>
              </div>
            </div>
          </div> -->

          <!-- CoursePackage Details List: course first, suggestion, then active packages -->
          <div v-if="type === 'course'" class="bg-white rounded-2xl shadow-lg p-6 mb-18">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Detail Paket</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li @click="(function(){ purchaseMode = 'course'; selectedPackage = null; })()" :class="[{ 'ring-2 ring-primary bg-purple-50/50': purchaseMode === 'course' }, 'flex justify-between gap-4 p-3 border-2 focus:outline-none border-gray-200 rounded-md cursor-pointer']">
                <div>
                  <div class="font-medium text-gray-800 line-clamp-2">{{ course.title }}</div>
                  <div class="text-xs text-gray-500">E-course</div>
                </div>
                <div class="text-right whitespace-nowrap">
                  <div class="font-bold text-primary text-md leading-tight">{{ formatPrice(course.price) }}</div>
                  <!-- <div v-if="course.credit_cost > 0" class="text-xs text-yellow-600 flex items-center justify-end"><i class="fas fa-coins mr-1"></i> {{ course.credit_cost }} USTAR</div> -->
                </div>
              </li>
              <li class="text-sm text-gray-600 px-3">atau pilih paket langganan</li>

              <li v-if="subscriptionPackages && subscriptionPackages.length" class="space-y-2 px-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                  <div v-for="pkg in subscriptionPackages" :key="pkg.id" @click="(function(){ purchaseMode = 'package'; selectedPackage = pkg; selectedPaymentMethod = 'idr'; })()" :class="[(selectedPackage && selectedPackage.id === pkg.id) ? 'ring-2 ring-primary focus:outline-none bg-purple-50/50' : '', 'p-3 border-2 border-gray-200 rounded-md flex items-center justify-between cursor-pointer']">
                    <div>
                      <div class="font-semibold text-gray-800">{{ pkg.name }}</div>
                      <div v-if="pkg.package_type === 'ustar'" class="text-xs font-semibold text-gray-800">{{ pkg.ustar_credits }} USTAR</div>
                      <div v-else-if="pkg.package_type === 'monthly'" class="text-xs font-semibold text-gray-800">Monthly - {{ pkg.duration_days }} hari akses</div>
                      <div v-else-if="pkg.description" class="text-xs text-gray-500 mt-1">{{ pkg.description }}</div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                      <span v-if="Number(pkg.discount_price) && Number(pkg.discount_price) < Number(pkg.price)" 
                            class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded mb-1 uppercase">
                        Hemat {{ Math.round(((pkg.price - pkg.discount_price) / pkg.price) * 100) }}%
                      </span>

                      <div class="font-bold text-primary text-md leading-tight">
                        {{ formatPrice(pkg.discount_price || pkg.price) }}
                      </div>

                      <div v-if="Number(pkg.discount_price) && Number(pkg.discount_price) < Number(pkg.price)" 
                          class="text-xs text-gray-600 line-through decoration-gray-300">
                        {{ formatPrice(pkg.price) }}
                      </div>
                    </div>
                  </div>
                </div>
              </li>

              <li v-else class="text-xs text-gray-500 px-3">Belum ada paket aktif saat ini.</li>
            </ul>
          </div>

        </div>

        <!-- Right Column: Payment Method + Order Summary (Sticky) -->
        <div class="lg:col-span-1 hidden lg:block">
          <div class="sticky top-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <!-- <h2 class="text-lg font-bold text-gray-800 mb-4">Pilih Metode Pembayaran</h2> -->

              
              <!-- Payment Options as upward dropdown (Grab-like) -->
              <!-- <div class="relative mb-4 payment-dropdown">
                <button @click.stop="showPaymentDropdown = !showPaymentDropdown" class="w-full py-2 px-4 border-2 border-gray-200 rounded-xl bg-white hover:border-primary transition flex items-center justify-between cursor-pointer">
                  <div class="flex items-center"><i :class="selectedPaymentMethod === 'ustar' ? 'fas fa-coins text-yellow-500' : 'fas fa-credit-card text-primary'" class="text-xl mr-4"></i>
                    <div class="text-left"><p class="font-semibold text-gray-800">{{ selectedPaymentMethod === 'ustar' ? 'Bayar dengan USTAR' : 'Bayar dengan Rupiah' }}</p><p class="text-sm text-gray-600">{{ selectedPaymentMethod === 'ustar' ? (course.credit_cost + ' USTAR Credits') : formatPrice(purchaseMode === 'course' ? course.price : selectedPackage?.discount_price || selectedPackage?.price) }}</p></div>
                  </div>
                  <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': showPaymentDropdown }"></i>
                </button>

                <transition name="slide-up">
                  <div v-if="showPaymentDropdown" class="hidden lg:block absolute top-full left-0 right-0 bg-white border-2 border-gray-200 rounded-xl shadow-lg z-10 mt-2 overflow-hidden">
                    <div :class="[canPayWithUstar && purchaseMode === 'course' ? 'px-4 py-2 border-b border-gray-100 cursor-pointer hover:bg-yellow-50 transition flex items-center justify-between' : 'px-4 py-2 border-b border-gray-100 opacity-50 cursor-not-allowed flex items-center justify-between', selectedPaymentMethod === 'ustar' ? 'bg-yellow-100/50' : '']" @click.stop="canPayWithUstar && purchaseMode === 'course' && (selectedPaymentMethod = 'ustar', showPaymentDropdown = false)">
                      <div class="flex items-center"><i class="fas fa-coins text-2xl text-yellow-500 mr-4"></i>
                        <div><p class="font-semibold text-gray-800">Bayar dengan USTAR</p><p class="text-sm text-gray-600">{{ course.credit_cost }} USTAR Credits</p><p v-if="!canPayWithUstar || purchaseMode !== 'course'" class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ purchaseMode !== 'course' ? 'Tidak tersedia untuk pembelian paket' : 'USTAR tidak mencukupi' }}</p></div>
                      </div>
                      <div v-if="selectedPaymentMethod === 'ustar' && canPayWithUstar && purchaseMode === 'course'" class="w-6 h-6 rounded-full bg-yellow-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
                    </div>

                    <div @click.stop="selectedPaymentMethod = 'idr'; showPaymentDropdown = false" :class="['px-4 py-2 cursor-pointer hover:bg-purple-100/80 transition flex items-center justify-between', selectedPaymentMethod === 'idr' ? 'bg-purple-100/50' : '']">
                      <div class="flex items-center"><i class="fas fa-credit-card text-2xl text-primary mr-4"></i>
                        <div><p class="font-semibold text-gray-800">Bayar dengan Rupiah</p><p class="text-sm text-gray-600">{{ formatPrice(purchaseMode === 'course' ? course.price : selectedPackage?.discount_price || selectedPackage?.price) }}</p></div>
                      </div>
                      <div v-if="selectedPaymentMethod === 'idr'" class="w-6 h-6 rounded-full bg-primary flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
                    </div>

                    <div class="p-3 bg-gray-50 text-xs text-gray-600">
                      <div v-if="selectedPaymentMethod === 'idr'">
                        <p class="font-semibold text-gray-700">Cara pembayaran Rupiah (IDR)</p>
                        <p class="mt-1">Anda dapat membayar menggunakan Kartu Kredit, E-Wallet, Transfer Bank, atau QRIS. Setelah memilih, tekan "Bayar Sekarang" untuk melanjutkan ke langkah selanjutnya.</p>
                      </div>
                      <div v-else>
                        <p class="font-semibold text-gray-700">Cara pembayaran (USTAR)</p>
                        <p class="mt-1">Pembayaran akan langsung dipotong dari credit USTAR Anda jika mencukupi. Jika tidak mencukupi, silakan pilih Rupiah atau beli paket USTAR.</p>
                      </div>
                    </div>
                  </div>
                </transition>
                  <transition name="slide-up">
                    <div v-if="showMobilePaymentOptions" class="lg:hidden fixed left-0 right-0 bottom-16 bg-white border-t border-gray-200 rounded-t-xl shadow-xl z-50">
                      <div class="p-4">
                        <div v-if="type === 'course' && course.credit_cost > 0" :class="[canPayWithUstar ? 'p-4 border-b border-gray-100 cursor-pointer hover:bg-yellow-50 transition flex items-center justify-between' : 'p-4 border-b border-gray-100 opacity-50 cursor-not-allowed flex items-center justify-between', selectedPaymentMethod === 'ustar' ? 'bg-primary/20' : '']" @click.stop="canPayWithUstar && (selectedPaymentMethod = 'ustar', showMobilePaymentOptions = false)">
                            <div class="flex items-center"><i class="fas fa-coins text-2xl text-yellow-500 mr-4"></i>
                              <div><p class="font-semibold text-gray-800">Bayar dengan USTAR</p><p class="text-sm text-gray-600">{{ course.credit_cost }} USTAR Credits</p><p v-if="!canPayWithUstar" class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-circle mr-1"></i> USTAR tidak mencukupi</p></div>
                            </div>
                            <div v-if="selectedPaymentMethod === 'ustar' && canPayWithUstar" class="w-6 h-6 rounded-full bg-yellow-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
                          </div>

                        <div @click.stop="selectedPaymentMethod = 'idr'; showMobilePaymentOptions = false" :class="['p-4 cursor-pointer hover:bg-purple-50 transition flex items-center justify-between', selectedPaymentMethod === 'idr' ? 'bg-primary/20' : '']">
                          <div class="flex items-center"><i class="fas fa-credit-card text-2xl text-primary mr-4"></i>
                            <div><p class="font-semibold text-gray-800">Bayar dengan Rupiah</p><p class="text-sm text-gray-600">{{ formatPrice(course.price) }}</p></div>
                          </div>
                          <div v-if="selectedPaymentMethod === 'idr'" class="w-6 h-6 rounded-full bg-primary flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
                        </div>

                        <div class="p-3 bg-gray-50 text-xs text-gray-600">
                          <div v-if="selectedPaymentMethod === 'idr'">
                            <p class="font-semibold text-gray-700">Cara pembayaran (IDR)</p>
                            <p class="mt-1">Anda dapat membayar menggunakan Kartu Kredit, E-Wallet, Transfer Bank, atau QRIS. Setelah memilih, tekan "Bayar Sekarang" untuk melanjutkan ke Midtrans.</p>
                          </div>
                          <div v-else>
                            <p class="font-semibold text-gray-700">Cara pembayaran (USTAR)</p>
                            <p class="mt-1">Pembayaran akan langsung dipotong dari credit USTAR Anda jika mencukupi. Jika tidak mencukupi, silakan pilih Rupiah atau beli paket USTAR.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </transition>
              </div> -->
              <!-- USTAR Balance Info (Desktop) -->
              <!-- <div v-if="userUstarData.has_subscription" class="mb-4 px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-xl">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <i class="fas fa-coins text-yellow-600 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700">Credit USTAR:</span>
                  </div>
                  <span class="text-lg font-bold text-yellow-600">{{ userUstarData.remaining }}</span>
                </div>
              </div> -->
              <!-- Voucher input + order summary -->
              <div class="border-gray-100 bg-white rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gunakan Voucher</h3>
                <div class="flex gap-2 mb-2">
                  <input
                    v-model="voucherCode"
                    type="text"
                    placeholder="Kode voucher"
                    class="flex-1 px-3 py-2 text-sm rounded-lg focus:outline-none ring-2 ring-primary focus:bg-purple-50/50 disabled:cursor-not-allowed disabled:bg-gray-100"
                    :disabled="voucherLoading || paymentSummary.voucher"
                  />
                  <button
                    @click="applyVoucher"
                    :disabled="voucherLoading || !voucherCode || paymentSummary.voucher"
                    :class="[
                      'px-4 py-2 text-white rounded-lg disabled:opacity-50',
                      (voucherLoading || !voucherCode || paymentSummary.voucher)
                        ? 'bg-gray-400 cursor-not-allowed'
                        : 'bg-primary hover:bg-purple-900 transition cursor-pointer'
                    ]"
                  >
                    Apply
                  </button>
                </div>
                <div v-if="paymentSummary.voucher" class="flex items-center text-xs text-red-600 mb-2">
                  <button @click="removeVoucher" class="flex items-center space-x-1 hover:underline cursor-pointer">
                    <span class="font-bold">×</span>
                    <span>Hapus voucher</span>
                  </button>
                </div>
                <p v-if="voucherError" class="text-xs text-red-600 mb-2">{{ voucherError }}</p>

                <h3 class="text-lg font-bold text-gray-800 mb-4 mt-8">Ringkasan Pesanan</h3>
                <div class="space-y-3 mb-4">
                  <div class="flex justify-between items-start gap-4">
                    <div class="flex-1">
                      <p class="text-xs font-semibold text-primary uppercase tracking-wider mb-1">
                        {{ purchaseMode === 'course' ? 'E-Course' : 'Subscription Package' }}
                      </p>
                      <p v-if="purchaseMode === 'course'" class="text-xs font-bold text-gray-800 line-clamp-2">
                        {{ course.title }}
                      </p>
                      <p v-else class="text-sm font-bold text-gray-800">
                        {{ selectedPackage ? selectedPackage.name : 'Pilih paket terlebih dahulu' }}
                        <span v-if="selectedPackage" class="block text-xs font-normal text-gray-500 mt-0.5">
                          Berlaku selama {{ selectedPackage.duration_days }} hari
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span>{{ formatPrice(paymentSummary.subtotal) }}</span>
                  </div>
                  <div v-if="paymentSummary.discount > 0" class="flex justify-between text-sm text-green-600">
                    <span>Diskon</span>
                    <span>-{{ formatPrice(paymentSummary.discount) }}</span>
                  </div>

                  <div v-if="discountDetails.length" class="text-xs text-gray-600 space-y-1">
                    <div v-for="(item, idx) in discountDetails" :key="idx" class="flex justify-between">
                      <span>{{ item.label }}</span>
                      <span class="font-medium">-{{ formatPrice(item.amount) }}</span>
                    </div>
                  </div>

                  <div class="border-t border-gray-200 pt-3 flex justify-between font-bold">
                    <span>Grand Total</span>
                    <span class="text-primary text-xl">{{ formatPrice(paymentSummary.grand_total) }}</span>
                  </div>
                </div>
                <button @click="processPayment" :disabled="processing || !isPaymentReady" class="w-full mt-4 py-3 bg-primary text-white rounded-xl font-bold hover:bg-purple-900 transition disabled:opacity-50 disabled:cursor-not-allowed hidden lg:block cursor-pointer">
                  <span v-if="processing"><i class="fas fa-spinner fa-spin mr-2"></i> Memproses...</span>
                  <span v-else><i class="fas fa-lock mr-2"></i> Bayar Sekarang</span>
                </button>
                <p class="text-xs text-gray-500 text-center mt-4"><i class="fas fa-shield-alt mr-1"></i> Transaksi aman dan terenkripsi</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile sticky summary + pay button -->
      <div class="fixed bottom-0 left-0 right-0 bg-white lg:hidden shadow-lg">
        <div class="relative">
          <transition name="slide-up">
            <div v-if="showMobileDetails" class="absolute bottom-full left-0 right-0 mb-2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
              <div class="flex gap-2 mb-2">
                <input
                  v-model="voucherCode"
                  type="text"
                  placeholder="Masukkan kode voucher"
                  class="flex-1 px-3 py-2 rounded-lg focus:outline-none ring-2 ring-primary focus:bg-purple-50/50 disabled:cursor-not-allowed disabled:bg-gray-100"
                  :disabled="voucherLoading || paymentSummary.voucher"
                />
                <button
                  @click="applyVoucher"
                  :disabled="voucherLoading || !voucherCode || paymentSummary.voucher"
                  :class="[
                    'px-4 py-2 text-white rounded-lg disabled:opacity-50',
                    (voucherLoading || !voucherCode || paymentSummary.voucher)
                      ? 'bg-gray-400 cursor-not-allowed'
                      : 'bg-primary hover:bg-purple-900 transition cursor-pointer'
                  ]"
                >
                  Apply
                </button>
              </div>
              <div v-if="paymentSummary.voucher" class="flex items-center text-xs text-red-600 mb-2">
                <button @click="removeVoucher" class="flex items-center space-x-1 hover:underline cursor-pointer">
                  <span class="font-bold">×</span>
                  <span>Hapus voucher</span>
                </button>
              </div>
              <p v-if="voucherError" class="text-xs text-red-600 mb-2">{{ voucherError }}</p>

              <div class="space-y-2 text-xs text-gray-600">
                <div class="flex justify-between">
                  <span>Subtotal</span>
                  <span>{{ formatPrice(paymentSummary.subtotal) }}</span>
                </div>
                <div v-if="paymentSummary.discount > 0" class="flex justify-between text-green-600">
                  <span>Diskon</span>
                  <span>-{{ formatPrice(paymentSummary.discount) }}</span>
                </div>

                <div v-if="discountDetails.length" class="space-y-1">
                  <div v-for="(item, idx) in discountDetails" :key="idx" class="flex justify-between">
                    <span>{{ item.label }}</span>
                    <span class="font-medium">-{{ formatPrice(item.amount) }}</span>
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-2 flex justify-between font-bold">
                  <span>Grand Total</span>
                  <span class="text-primary">{{ formatPrice(paymentSummary.grand_total) }}</span>
                </div>
              </div>
            </div>
          </transition>

          <div class="p-3">
            <div class="flex items-center justify-between mb-2">
              <button @click="showMobileDetails = !showMobileDetails" class="flex items-center gap-1 text-sm font-semibold text-primary">
                <i :class="['fas', showMobileDetails ? 'fa-chevron-down' : 'fa-chevron-up', 'text-xs']"></i>
                <span>{{ showMobileDetails ? 'Sembunyikan detail' : 'Tampilkan detail' }}</span>
              </button>
              <button v-if="showMobileDetails" @click="showMobileDetails = false" class="text-xs text-gray-500 hover:text-gray-700">Tutup</button>
            </div>

            <div class="flex items-center justify-between gap-4">
              <div class="flex items-center space-x-3 flex-1 min-w-0">
                <!-- Thumbnail Section -->
                <img v-if="purchaseMode === 'course'" :src="course.thumbnail_url" class="w-12 h-12 flex-shrink-0 object-cover rounded-md bg-gray-100" />
                <div v-else class="grid place-items-center h-12 w-12 flex-shrink-0 bg-purple-100 rounded-md">
                  <i class="fas fa-crown text-primary"></i>
                </div>
                
                <!-- Text Section --> 
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-medium text-gray-800 line-clamp-2">
                    <span v-if="purchaseMode === 'course'">{{ course.title }}</span>
                    <span v-else>{{ selectedPackage ? selectedPackage.name : 'Pilih Paket' }}</span>
                  </div>
                  <div class="text-xs text-gray-500 truncate">
                    <span>{{ formatPrice(paymentSummary.grand_total) }}</span>
                    <span v-if="paymentSummary.discount > 0" class="block text-red-600 text-[10px]">
                      Diskon <span v-if="paymentSummary.voucher">({{ paymentSummary.voucher.code }})</span> : -{{ formatPrice(paymentSummary.discount) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Button Section -->
              <div class="flex flex-col items-end flex-shrink-0"> <!-- Gunakan flex-shrink-0 agar tombol tidak gepeng -->
                <button @click="processPayment" class="w-20 py-2 px-4 bg-primary text-white rounded-lg font-semibold flex items-center justify-center">
                  <span>Bayar</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile payment options panel (above sticky) (Commented Out USTAR) -->
      <!-- <transition name="slide-up">
        <div v-if="showMobilePaymentOptions" class="payment-dropdown-mobile lg:hidden fixed bottom-42 left-3 right-3 bg-white border border-gray-200 rounded-md shadow p-3 z-50">
            <div class="space-y-2">
            <div :class="[canPayWithUstar && purchaseMode === 'course' ? 'p-3 cursor-pointer hover:bg-yellow-50 rounded flex items-center justify-between' : 'p-3 opacity-50 cursor-not-allowed rounded flex items-center justify-between', selectedPaymentMethod === 'ustar' ? 'bg-yellow-100/50' : '']" @click.stop="canPayWithUstar && purchaseMode === 'course' && (selectedPaymentMethod = 'ustar', showMobilePaymentOptions = false)">
              <div class="flex items-center">
                <i class="fas fa-coins text-2xl text-yellow-500 mr-3"></i>
                <div>
                  <div class="font-semibold">Bayar dengan USTAR</div>
                  <div class="text-xs text-gray-500">{{ course.credit_cost }} USTAR Credits</div>
                  <div v-if="!canPayWithUstar || purchaseMode !== 'course'" class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ purchaseMode !== 'course' ? 'Tidak tersedia untuk pembelian paket' : 'USTAR tidak mencukupi' }}</div>
                </div>
              </div>
              <div v-if="selectedPaymentMethod === 'ustar' && canPayWithUstar && purchaseMode === 'course'" class="w-6 h-6 rounded-full bg-yellow-500 flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
            </div>

            <div :class="['p-3 cursor-pointer hover:bg-purple-100/80 rounded flex items-center justify-between', selectedPaymentMethod === 'idr' ? 'bg-purple-100/50' : '']" @click.stop="(selectedPaymentMethod = 'idr', showMobilePaymentOptions = false)">
              <div class="flex items-center">
                <i class="fas fa-credit-card text-2xl text-primary mr-3"></i>
                <div>
                  <div class="font-semibold">Bayar dengan Rupiah</div>
                  <div class="text-xs text-gray-500">{{ formatPrice(purchaseMode === 'course' ? course.price : selectedPackage?.discount_price || selectedPackage?.price) }}</div>
                </div>
              </div>
              <div v-if="selectedPaymentMethod === 'idr'" class="w-6 h-6 rounded-full bg-primary flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
            </div>
          </div>
        </div>
      </transition> -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  type: String,
  course: Object,
  packages: Array,
  recommendedPackages: Array,
  default_package_id: [Number, String], // allow numeric or string from server
  user_ustar: {
    type: Object,
    default: () => ({
      total: 0,
      used: 0,
      remaining: 0,
      has_subscription: false,
    })
  },
  defaultPaymentMethod: String,
  paymentContext: Object,
  summary: Object,
})

// convert to Number so prop validation passes and comparisons work
const defaultPackageId = computed(() => Number(props.default_package_id || 0))

const selectedPackage = ref(null)
const selectedPaymentMethod = ref('idr') // Always default to idr
const processing = ref(false)
const purchaseMode = ref(props.type || 'course')

// voucher state
const voucherCode = ref('')
const voucherLoading = ref(false)
const voucherError = ref(null)
// summary (subtotal/discount/grand_total) reactive copy
const paymentSummary = ref({
  subtotal: props.summary?.subtotal || 0,
  discount: props.summary?.discount || 0,
  grand_total: props.summary?.grand_total || 0,
  voucher: props.summary?.voucher || null,
})

const discountDetails = computed(() => {
  const details = []
  const voucher = paymentSummary.value.voucher
  if (voucher) {
    const amount = voucher.amount ?? voucher.discount ?? paymentSummary.value.discount ?? 0
    details.push({ label: `Voucher (${voucher.code || '—'})`, amount })
  }
  return details
})

const showPaymentDropdown = ref(false)
const showMobilePaymentOptions = ref(false)
const showMobileDetails = ref(false)

const subscriptionPackages = computed(() => {
  return (props.packages || []).filter(pkg => ['subscription', 'monthly', 'ustar'].includes(pkg.package_type))
})

function closeDropdown(event) {
  const root = document.querySelector('.payment-dropdown')
  const mobile = document.querySelector('.payment-dropdown-mobile')
  if (root && !root.contains(event.target)) showPaymentDropdown.value = false
  if (mobile && !mobile.contains(event.target)) showMobilePaymentOptions.value = false
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
  
  // Auto-default to USTAR if user has enough credits (Commented out)
  /* if (props.type === 'course' && props.course && props.course.credit_cost > 0) {
    if (userUstarData.value.remaining >= props.course.credit_cost) {
      selectedPaymentMethod.value = 'ustar'
    }
  } */
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})

const userUstarData = computed(() => props.user_ustar || {
  total: 0,
  used: 0,
  remaining: 0,
  has_subscription: false,
})

const canPayWithUstar = computed(() => {
  // if (!props.course || !props.course.credit_cost) return false
  // return userUstarData.value.remaining >= props.course.credit_cost
  return false // Disabled
})

// auto-switch to IDR if USTAR becomes insufficient (Commented out)
/* watch(canPayWithUstar, (available) => {
  if (!available && selectedPaymentMethod.value === 'ustar') {
    selectedPaymentMethod.value = 'idr'
  }
}) */

// auto-switch to IDR when selecting a package
watch(selectedPackage, (pkg) => {
  if (pkg) {
    selectedPaymentMethod.value = 'idr'
  }
  revalidateVoucherOnSwitch()
})

watch(purchaseMode, () => {
  revalidateVoucherOnSwitch()
})

// keep summary in sync if server sends new props (rare)
watch(() => props.summary, (s) => {
  if (s) {
    paymentSummary.value = { ...s };
  }
});

// If paymentContext changes we should reset voucher state
watch(() => props.paymentContext, () => {
  paymentSummary.value = {
    subtotal: props.summary?.subtotal || 0,
    discount: props.summary?.discount || 0,
    grand_total: props.summary?.grand_total || 0,
    voucher: props.summary?.voucher || null,
  };
  voucherCode.value = '';
  voucherError.value = null;
});

// Auto-select 1-month subscription package on load (select subscription when arriving from course)
watch(subscriptionPackages, (list) => {
  if (!list || !list.length) return
  if (selectedPackage.value) return

  // If default_package_id provided pref from query, choose that first
  if (defaultPackageId.value) {
    const match = list.find(p => p.id === defaultPackageId.value)
    if (match) {
      selectedPackage.value = match
      purchaseMode.value = 'package'
      selectedPaymentMethod.value = 'idr'
      return
    }
  }

  // Prefer explicit plan_duration flag '1_month'
  let pkg = list.find(p => p.plan_duration === '1_month')
  // Fallback to duration_days approximately 30
  if (!pkg) pkg = list.find(p => Number(p.duration_days) && Number(p.duration_days) <= 31)

  if (pkg) {
    selectedPackage.value = pkg
    purchaseMode.value = 'package'
    selectedPaymentMethod.value = 'idr'
  }
}, { immediate: true })

const currentOrderId = ref(null)
const statusMessage = ref(null)
const statusMessageType = ref('info')
function setMessage(msg, type = 'info') {
  statusMessage.value = msg
  statusMessageType.value = type
  setTimeout(() => {
    if (statusMessage.value === msg) statusMessage.value = null
  }, 6000)
}

const isPaymentReady = computed(() => {
  if (purchaseMode.value === 'course') {
    if (selectedPaymentMethod.value === 'ustar') {
      return canPayWithUstar.value
    }
    return true
  }
  return !!selectedPackage.value
})

async function updateTransactionStatus(orderId, status = 'failed') {
  processing.value = false
  try {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

    const res = await fetch('/ecourse/api/payments/update-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify({ order_id: orderId, status }),
    })

    const body = await res.json().catch(() => ({}))
    if (!res.ok) throw body

    setMessage(body.message || `Transaksi diperbarui: ${status}`, 'success')
    // keep stored order id so user can continue later
    // clear stored order id only when status is final
    if (status !== 'pending') {
      currentOrderId.value = null
    }

  } catch (err) {
    console.error('Update transaction status error:', err)
    setMessage(err?.message || 'Gagal memperbarui status transaksi', 'error')
  } finally {
    processing.value = false
  }
}

function formatPrice(value) {
  return `Rp ${new Intl.NumberFormat('id-ID').format(value || 0)}`
}

// voucher helpers
async function applyVoucher() {
  if (!voucherCode.value) return;
  voucherLoading.value = true;
  voucherError.value = null;

  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
  const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

  // Match the same logic as processPayment: use purchaseMode to determine type
  const payload = {
    code: voucherCode.value.trim(),
  }

  if (purchaseMode.value === 'course') {
    payload.type = 'course'
    payload.course_slug = props.course?.slug
    // Don't include package_id for course type
  } else {
    payload.type = 'package'
    // Don't include course_slug for package type
    payload.package_id = selectedPackage.value?.id
  }

  try {
    const res = await fetch('/ecourse/api/payments/apply-voucher', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const body = await res.json().catch(() => ({}));
    if (!res.ok) throw body;
    const data = body.data;
    paymentSummary.value = data;
  } catch (err) {
    voucherError.value = err?.message || 'Kode voucher tidak valid';
  } finally {
    voucherLoading.value = false;
  }
}

function removeVoucher() {
  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
  const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

  // local reset first
  paymentSummary.value = {
    subtotal: props.summary?.subtotal || 0,
    discount: 0,
    grand_total: props.summary?.subtotal || 0,
    voucher: null,
  };
  voucherCode.value = '';
  voucherError.value = null;
  
  // notify server with conditional payload (same logic as applyVoucher)
  const payload = {}
  if (purchaseMode.value === 'course') {
    payload.type = 'course'
    payload.course_slug = props.course?.slug
    // Don't include package_id for course type
  } else {
    payload.type = 'package'
    // Don't include course_slug for package type
    payload.package_id = selectedPackage.value?.id
  }

  fetch('/ecourse/api/payments/remove-voucher', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
}

async function revalidateVoucherOnSwitch() {
  // nothing to revalidate if no voucher is applied
  if (!paymentSummary.value.voucher) return

  const code = paymentSummary.value.voucher.code
  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
  const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

  const payload = { code }
  if (purchaseMode.value === 'course') {
    payload.type = 'course'
    payload.course_slug = props.course?.slug
    // Don't include package_id for course type
  } else {
    payload.type = 'package'
    // Don't include course_slug for package type
    payload.package_id = selectedPackage.value?.id
  }

  try {
    const res = await fetch('/ecourse/api/payments/apply-voucher', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    })
    const body = await res.json().catch(() => ({}))
    if (!res.ok) {
      // voucher not valid for new product – reset and warn
      paymentSummary.value = {
        subtotal: props.summary?.subtotal || 0,
        discount: 0,
        grand_total: props.summary?.subtotal || 0,
        voucher: null,
      }
      voucherError.value = `Voucher "${code}" tidak berlaku untuk produk ini.`
    } else {
      // update summary with new discount calculation for new product
      paymentSummary.value = body.data
      voucherError.value = null
    }
  } catch {
    // on network error just silently reset
    paymentSummary.value = {
      subtotal: props.summary?.subtotal || 0,
      discount: 0,
      grand_total: props.summary?.subtotal || 0,
      voucher: null,
    }
    voucherError.value = null
  }
}

function switchToPackage(packageId) {
  router.visit(`/ecourse/payment?type=package&package_id=${packageId}`)
}

function goBack() {
  try {
    if (window && window.history && window.history.length > 1) {
      window.history.back()
    } else {
      router.visit('/ecourse/catalog')
    }
  } catch (e) {
    router.visit('/ecourse/catalog')
  }
}

function processPayment() {
  if (!isPaymentReady.value) return
  
  processing.value = true
  
  const base = purchaseMode.value === 'course'
    ? {
        type: 'course',
        course_id: props.course?.id,
        payment_method: selectedPaymentMethod.value,
      }
    : {
        type: 'package',
        package_id: selectedPackage.value?.id,
        course_id: props.course?.id, // include course_id if we are on a course's payment page
      }
  const data = {
      ...base,
      voucher_code: paymentSummary.value.voucher?.code || null,
  }

  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
  const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null

  fetch('/ecourse/api/payments/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
    credentials: 'same-origin',
    body: JSON.stringify(data),
  })
    .then(async (res) => {
      const body = await res.json().catch(() => ({}))
      if (!res.ok) throw body

      const result = body.data || body

      if (result?.payment_method === 'ustar') {
        setMessage('Pembayaran berhasil dengan USTAR!', 'success')
        router.visit(`/ecourse/payment?status=success&order_id=${result.order_id}&course_name=${encodeURIComponent(result.course_name || '')}&purchase_time=${encodeURIComponent(result.purchase_time || '')}&redirect_url=${encodeURIComponent(result.redirect_url || '')}`)
        return
      }

      if (result?.payment_method === 'voucher') {
        setMessage('Pembayaran berhasil dengan voucher!', 'success')
        router.visit(`/ecourse/payment?status=success&order_id=${result.order_id}&course_name=${encodeURIComponent(result.course_name || '')}&purchase_time=${encodeURIComponent(result.purchase_time || '')}&redirect_url=${encodeURIComponent(result.redirect_url || '')}`)
        return
      }

      if (result?.snap_token) {
        // store order id so we can cancel if user closes popup
        currentOrderId.value = result.order_id || null

        window.snap.pay(result.snap_token, {
          onSuccess: function(resSnap) {
            router.visit(`/ecourse/payment?status=success&order_id=${resSnap.order_id}`)
          },
          onPending: function(resSnap) {
            router.visit(`/ecourse/payment?status=pending&order_id=${resSnap.order_id}`)
          },
          onError: function(err) {
            console.error('Midtrans onError:', err)
            setMessage('Pembayaran gagal. Silakan coba lagi.', 'error')
            // If there's a pending order, update it to 'failed' so it matches Midtrans response
            if (currentOrderId.value) {
              updateTransactionStatus(currentOrderId.value, 'failed')
            } else {
              processing.value = false
            }
          },
          onClose: function() {
            // User closed the payment popup without completing payment -> show pending status so they can continue later
            if (currentOrderId.value) {
              router.visit(`/ecourse/payment?status=pending&order_id=${currentOrderId.value}`)
            } else {
              processing.value = false
            }
          }
        })
      }
    })
    .catch((err) => {
      console.error('Payment error:', err)
      if (err && err.exception && err.exception.includes('HttpException') && err.message && err.message.includes('CSRF')) {
        setMessage('CSRF token mismatch. Silakan refresh halaman dan coba lagi.', 'error')
      } else if (err && err.message && typeof err.message === 'string') {
        setMessage(err.message, 'error')
      } else {
        setMessage(err?.msg || 'Terjadi kesalahan saat memproses pembayaran', 'error')
      }
      processing.value = false
    })
}
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
  transition: transform 180ms ease, opacity 180ms ease;
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(8px);
  opacity: 0;
}
.slide-up-enter-to, .slide-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}

.payment-dropdown > button:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(124,58,237,0.08);
}

</style>
