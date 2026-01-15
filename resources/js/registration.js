document.addEventListener('alpine:init', () => {
    Alpine.data('registrationForm', (initialData) => ({
        currentStep: 1,
        
        // Data Options
        options: {
            events: initialData.events || [],
            professions: initialData.professions || [],
            provinces: initialData.provinces || [],
            allCities: initialData.allCities || []
        },

        // Data Form
        form: {
            event_id: initialData.old.event_id || '',
            packet_id: initialData.old.packet_id || '',
            name: initialData.old.name || '',
            email: initialData.old.email || '',
            whatsapp: initialData.old.whatsapp || '',
            age: initialData.old.age || '',
            gender: initialData.old.gender || '',
            profession: initialData.old.profession || '',
            province: initialData.old.province || '',
            city: initialData.old.city || '',
            channel_information: initialData.old.channel_information || []
        },

        // State & Data Dinamis
        packets: [],
        cities: [],
        priceDisplay: 'Rp 0',
        packetDescription: '',
        errors: {},
        
        // State Validasi Upload (BARU)
        uploads: {}, 

        // Config Requirements
        requirementsConfig: {
            follow_ig: { label: 'Follow IG unlock.indonesia', icon: 'fab fa-instagram text-pink-600' },
            follow_yt: { label: 'Subscribe YouTube', icon: 'fab fa-youtube text-red-600' },
            follow_tt: { label: 'Follow TikTok', icon: 'fab fa-tiktok text-black' },
            tag_friends: { label: 'Tag 5 Teman', icon: 'fas fa-users text-primary' },
            repost_story: { label: 'Repost IG Story', icon: 'fas fa-retweet text-primary' },
            repost_groups: { label: 'Repost WA Groups', icon: 'fas fa-comments text-primary' },
            repost_wa_story: { label: 'Repost WA Story', icon: 'fas fa-bullhorn text-primary' }
        },
        activeRequirements: {}, 

        init() {
            this.$watch('form.event_id', (val) => {
                this.form.packet_id = '';
                this.packets = [];
                this.priceDisplay = 'Rp 0';
                this.packetDescription = '';
                this.activeRequirements = {};
                this.uploads = {}; // Reset upload jika event ganti
                if(val) this.fetchPackets(val);
            });

            this.$watch('form.packet_id', (val) => {
                this.updatePacketDetails(val);
                this.uploads = {}; // Reset upload jika paket ganti
            });

            this.$watch('form.province', (val) => {
                this.form.city = '';
                this.filterCities(val);
            });

            if (this.form.event_id) this.fetchPackets(this.form.event_id);
            if (this.form.province) {
                this.filterCities(this.form.province);
                if(initialData.old.city) {
                    this.form.city = initialData.old.city;
                }
            }
        },

        filterCities(provCode) {
            if (!provCode) {
                this.cities = [];
                return;
            }
            const prefix = provCode + '.';
            this.cities = this.options.allCities.filter(city => 
                city.value.startsWith(prefix)
            );
        },

        async fetchPackets(eventId) {
            this.packets = [{ value: '', label: 'Memuat...', disabled: true }];
            try {
                const res = await fetch(`registration/packets/${eventId}`);
                if(!res.ok) throw new Error('Gagal');
                const data = await res.json();
                this.packets = data.map(p => ({
                    value: String(p.id),
                    label: p.packet_name,
                    price: p.price,
                    desc: p.description,
                    reqs: p.requirements
                }));
                // Restore old packet
                if(this.form.packet_id && !this.packets.find(p => p.value == this.form.packet_id)) {
                    this.form.packet_id = '';
                } else if(this.form.packet_id) {
                    this.updatePacketDetails(this.form.packet_id);
                }
            } catch (e) {
                this.packets = [{ value: '', label: 'Gagal memuat', disabled: true }];
            }
        },

        async fetchCities(provCode) {
            this.cities = [{ value: '', label: 'Memuat...', disabled: true }];
            try {
                const res = await fetch(`registration/regencies/${provCode}`);
                if(!res.ok) throw new Error('Gagal');
                const data = await res.json();
                this.cities = data.map(c => ({ value: c.kode, label: c.nama }));
            } catch (e) {
                this.cities = [{ value: '', label: 'Gagal memuat', disabled: true }];
            }
        },

        updatePacketDetails(packetId) {
            const packet = this.packets.find(p => p.value == packetId);
            if (!packet) return;
            this.priceDisplay = `Rp ${Number(packet.price).toLocaleString('id-ID')}`;
            this.packetDescription = packet.desc ? `<strong>Detail Paket:</strong><br>${packet.desc}` : '';

            // Map Requirements
            const mapKeys = {
                'followig': 'follow_ig', 'followyt': 'follow_yt', 'followtt': 'follow_tt',
                'tagfriends': 'tag_friends', 'repoststory': 'repost_story',
                'repostgroups': 'repost_groups', 'repostwastory': 'repost_wa_story'
            };
            const newReqs = {};
            if(packet.reqs) {
                Object.keys(packet.reqs).forEach(backendKey => {
                    const frontendKey = mapKeys[backendKey] || backendKey;
                    if(packet.reqs[backendKey] === true && this.requirementsConfig[frontendKey]) {
                        newReqs[frontendKey] = {
                            ...this.requirementsConfig[frontendKey],
                            key: backendKey
                        };
                    }
                });
            }
            this.activeRequirements = newReqs;
        },

        getLabel(value, list) {
            if (!list) return '';
            const item = list.find(i => i.value == value);
            return item ? item.label : '';
        },

        // --- VALIDASI DIPERBARUI ---
        validateStep(step) {
            this.errors = {}; 
            let isValid = true;

            // STEP 1
            if (step === 1) {
                if (!this.form.event_id) { this.errors['event_id'] = 'Wajib dipilih'; isValid = false; }
                if (!this.form.packet_id) { this.errors['packet_id'] = 'Wajib dipilih'; isValid = false; }
            }

            // STEP 2
            if (step === 2) {
                const fields = ['name', 'email', 'whatsapp', 'age', 'gender', 'profession', 'province', 'city'];
                fields.forEach(field => {
                    if (!this.form[field]) {
                        this.errors[field] = 'Wajib diisi';
                        isValid = false;
                    }
                });

                // TAMBAHAN: Validasi Checkbox (Minimal pilih 1)
                if (this.form.channel_information.length === 0) {
                    this.errors['channel_information'] = 'Pilih minimal satu sumber informasi';
                    isValid = false;
                }
            }

            // STEP 3
            if (step === 3) {
                Object.values(this.activeRequirements).forEach(req => {
                    if (!this.uploads[req.key]) {
                        this.errors['upload_' + req.key] = 'Bukti ini wajib diupload';
                        isValid = false;
                    }
                });
            }

            return isValid;
        },

        nextStep() {
            if (!this.validateStep(this.currentStep)) {
                this.$nextTick(() => {
                    const firstErrorKey = Object.keys(this.errors)[0];
                    
                    if (firstErrorKey) {
                        // Logic Selector yang lebih pintar untuk handle array []
                        let selector = `[name="${firstErrorKey}"]`;
                        
                        // Jika key adalah channel_information, target name-nya ada []
                        if (firstErrorKey === 'channel_information') {
                            selector = `[name="channel_information[]"]`;
                        }

                        const element = document.querySelector(selector);
                        
                        if (element) {
                            if (element.type === 'hidden') {
                                // Scroll ke dropdown wrapper
                                element.closest('.relative')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            } else if (element.type === 'checkbox') {
                                // Scroll ke container checkbox (biar lebih enak dilihat)
                                element.closest('.grid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            } else {
                                // Scroll input biasa
                                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                element.focus({ preventScroll: true }); 
                            }
                        }
                    }
                });
                return;
            }

            if (this.currentStep === 2 && Object.keys(this.activeRequirements).length === 0) {
                this.currentStep = 4;
            } else {
                this.currentStep++;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        prevStep() {
            if (this.currentStep === 4 && Object.keys(this.activeRequirements).length === 0) {
                this.currentStep = 2;
            } else {
                this.currentStep--;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }));
});


