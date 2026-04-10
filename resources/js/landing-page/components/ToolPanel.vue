<template>
  <div class="h-full flex flex-col bg-white text-sm">
    <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 bg-gray-50 shrink-0">
      <div class="flex items-center gap-2">
        <span class="text-[11px] text-gray-400">{{ data.links?.length || 0 }} elemen</span>
        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium"
              :class="data.status === 'published' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'">
          {{ data.status }}
        </span>
      </div>
      <div class="flex gap-1">
        <button @click="$emit('duplicate-page')" title="Duplikasi"
                class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition">
          <i class="fas fa-copy text-[11px]"></i>
        </button>
        <button @click="$emit('delete-page')" title="Hapus"
                class="w-7 h-7 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
          <i class="fas fa-trash text-[11px]"></i>
        </button>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div class="border-b border-gray-50">
        <SectionHeader title="Profil" icon="fa-id-card" v-model="open.profil" />
        <div v-if="open.profil" class="px-4 pb-4 pt-1 space-y-3">
          <FieldRow label="Nama Halaman"><input v-model="data.name" type="text" class="fi w-full" /></FieldRow>
          <FieldRow label="Slug"><input v-model="data.slug" type="text" class="fi w-full" /></FieldRow>
          <FieldRow label="Judul"><input v-model="data.title" type="text" class="fi w-full" /></FieldRow>
          <FieldRow label="Bio"><textarea v-model="data.bio" rows="3" class="fi w-full resize-none"></textarea></FieldRow>
          <FieldRow label="Font">
            <select v-model="data.style.font_family" class="fi w-full">
              <option v-for="font in fontOptions" :key="font.v" :value="font.v">{{ font.l }}</option>
            </select>
          </FieldRow>
          <FieldRow label="Lebar Halaman">
            <select v-model="data.style.page_max_width" class="fi w-full">
              <option v-for="opt in widthOptions" :key="opt.v" :value="opt.v">{{ opt.l }}</option>
            </select>
          </FieldRow>
          <FieldRow label="Status">
            <div class="flex gap-1.5">
              <button @click="data.status = 'draft'" class="flex-1 py-1.5 rounded-lg border text-xs transition"
                      :class="data.status === 'draft' ? 'bg-amber-50 border-amber-300 text-amber-700 font-medium' : 'border-gray-200 text-gray-500 hover:border-gray-300'">Draft</button>
              <button @click="data.status = 'published'" class="flex-1 py-1.5 rounded-lg border text-xs transition"
                      :class="data.status === 'published' ? 'bg-emerald-50 border-emerald-300 text-emerald-700 font-medium' : 'border-gray-200 text-gray-500 hover:border-gray-300'">Published</button>
            </div>
          </FieldRow>
        </div>
      </div>

      <div class="border-b border-gray-50">
        <SectionHeader title="Tampilan" icon="fa-paint-brush" v-model="open.tampilan" />
        <div v-if="open.tampilan" class="px-4 pb-4 pt-1 space-y-4">
          <div class="rounded-xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50">
              <span class="text-xs font-medium text-gray-600 flex items-center gap-1.5"><i class="fas fa-user-circle text-gray-400 text-[11px]"></i> Avatar</span>
              <Toggle v-model="data.style.use_avatar" />
            </div>
            <div v-if="data.style.use_avatar" class="p-3 space-y-3">
              <div class="flex items-center gap-3">
                <div class="shrink-0 border-2 border-white shadow overflow-hidden"
                     :style="{ width: '52px', height: '52px', borderRadius: data.style.avatar_rounded === 'full' ? '9999px' : '12px' }">
                  <img v-if="data.avatar" :src="storageUrl(data.avatar)" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full bg-gradient-to-br from-violet-100 to-pink-100 flex items-center justify-center"><i class="fas fa-user text-violet-300 text-base"></i></div>
                </div>
                <div class="flex-1 space-y-1.5">
                  <input type="file" ref="avatarInput" accept="image/*" class="hidden" @change="onAvatarChange" />
                  <button @click="avatarInput?.click()" class="w-full btn-outline-sm"><i class="fas fa-upload text-gray-400 mr-1"></i> Upload Foto</button>
                  <button v-if="data.avatar" @click="$emit('remove-image', 'avatar')" class="w-full btn-danger-sm"><i class="fas fa-times mr-1"></i> Hapus</button>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <FieldRow label="Ukuran">
                  <select v-model="data.style.avatar_size" class="fi w-full">
                    <option value="sm">Kecil</option>
                    <option value="md">Sedang</option>
                    <option value="lg">Besar</option>
                  </select>
                </FieldRow>
                <FieldRow label="Bentuk">
                  <select v-model="data.style.avatar_rounded" class="fi w-full">
                    <option value="full">Bulat</option>
                    <option value="xl">Rounded</option>
                  </select>
                </FieldRow>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50">
              <span class="text-xs font-medium text-gray-600 flex items-center gap-1.5"><i class="fas fa-panorama text-gray-400 text-[11px]"></i> Cover</span>
              <Toggle v-model="data.style.use_cover" />
            </div>
            <div v-if="data.style.use_cover" class="p-3 space-y-2">
              <div class="w-full h-20 rounded-lg overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                <img v-if="data.cover_image" :src="storageUrl(data.cover_image)" class="w-full h-full object-cover" />
                <i v-else class="fas fa-image text-gray-200 text-2xl"></i>
              </div>
              <div class="flex gap-2">
                <input type="file" ref="coverInput" accept="image/*" class="hidden" @change="onCoverChange" />
                <button @click="coverInput?.click()" class="flex-1 btn-outline-sm"><i class="fas fa-upload text-gray-400 mr-1"></i> Upload</button>
                <button v-if="data.cover_image" @click="$emit('remove-image', 'cover')" class="btn-danger-sm px-3"><i class="fas fa-times"></i></button>
              </div>
            </div>
          </div>

          <div>
            <p class="fl mb-2">Background</p>
            <div class="flex gap-1 bg-gray-100 p-0.5 rounded-lg mb-3">
              <button v-for="t in ['solid','gradient']" :key="t" @click="data.style.bg_type = t"
                      class="flex-1 py-1.5 text-xs rounded-md transition capitalize"
                      :class="data.style.bg_type === t ? 'bg-white shadow font-medium text-gray-700' : 'text-gray-400 hover:text-gray-600'">{{ t }}</button>
            </div>
            <ColorRow v-if="data.style.bg_type === 'solid'" label="Warna" v-model="data.style.bg_color" />
            <template v-else>
              <ColorRow label="Dari" v-model="data.style.bg_gradient_from" class="mb-2" />
              <ColorRow label="Ke" v-model="data.style.bg_gradient_to" />
            </template>
          </div>
        </div>
      </div>

      <div class="border-b border-gray-50">
        <SectionHeader title="Gaya Teks" icon="fa-font" v-model="open.gaya" />
        <div v-if="open.gaya" class="px-4 pb-4 pt-1 space-y-4">
          <div>
            <p class="fl mb-2">Judul</p>
            <TextControls prefix="title" :s="data.style" />
          </div>
          <div>
            <p class="fl mb-2">Bio</p>
            <TextControls prefix="bio" :s="data.style" />
          </div>
        </div>
      </div>

      <div class="border-b border-gray-50">
        <SectionHeader title="Elemen" icon="fa-th-list" v-model="open.elemen" />
        <div v-if="open.elemen" class="px-3 pb-4 pt-1 space-y-2">
          <div v-for="(el, idx) in data.links" :key="idx"
               class="rounded-xl border transition-all"
               :class="expandedIdx === idx ? 'border-violet-200 shadow-sm' : 'border-gray-100'">
            <div class="flex items-center gap-2 px-2.5 py-2 cursor-pointer select-none"
                 :class="expandedIdx === idx ? 'bg-violet-50 rounded-t-xl' : 'hover:bg-gray-50 rounded-xl'"
                 @click="expandedIdx = expandedIdx === idx ? null : idx">
              <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                   :class="el.type === 'text' ? 'bg-blue-100' : el.type === 'image' ? 'bg-emerald-100' : el.type === 'catalog' ? 'bg-orange-100' : 'bg-violet-100'">
                <i class="text-[10px]"
                   :class="el.type === 'text' ? 'fas fa-font text-blue-600' : el.type === 'image' ? 'fas fa-image text-emerald-600' : el.type === 'catalog' ? 'fas fa-table-cells-large text-orange-600' : 'fas fa-link text-violet-600'"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-medium text-gray-700 truncate">
                  {{ el.type === 'text' ? stripHtml(el.content || '') || 'Teks kosong' : el.type === 'image' ? 'Blok gambar' : el.type === 'catalog' ? (el.label || 'Catalog baru') : (el.label || 'Link baru') }}
                </div>
              </div>
              <div class="flex flex-col gap-px shrink-0">
                <button @click.stop="move(idx, -1)" :disabled="idx === 0" class="text-gray-300 hover:text-gray-500 disabled:opacity-20 leading-none py-0.5 px-1"><i class="fas fa-chevron-up text-[8px]"></i></button>
                <button @click.stop="move(idx, 1)" :disabled="idx === data.links.length - 1" class="text-gray-300 hover:text-gray-500 disabled:opacity-20 leading-none py-0.5 px-1"><i class="fas fa-chevron-down text-[8px]"></i></button>
              </div>
              <button @click.stop="el.is_active = !el.is_active" class="shrink-0 p-1"><span class="inline-block w-2 h-2 rounded-full transition" :class="el.is_active !== false ? 'bg-emerald-400' : 'bg-gray-200'"></span></button>
              <button @click.stop="remove(idx)" class="w-5 h-5 flex items-center justify-center text-gray-300 hover:text-red-400 rounded transition shrink-0"><i class="fas fa-times text-[10px]"></i></button>
              <i class="fas fa-chevron-down text-gray-300 text-[9px] transition-transform shrink-0" :class="expandedIdx === idx ? 'rotate-180' : ''"></i>
            </div>

            <div v-if="expandedIdx === idx" class="border-t border-violet-100 px-3 py-3 space-y-3">
              <template v-if="!el.type || el.type === 'link'">
                <FieldRow label="Label">
                  <input v-model="el.label" type="text" class="fi w-full" placeholder="Contoh: Kunjungi Website" />
                </FieldRow>
                <FieldRow label="URL"><input v-model="el.url" type="url" class="fi w-full" placeholder="https://" /></FieldRow>
                <FieldRow label="Icon">
                  <div class="grid grid-cols-2 gap-2">
                    <select v-model="el.icon" class="fi w-full col-span-1">
                      <option value="">Tanpa icon</option>
                      <option v-for="icon in iconOptions" :key="icon.v" :value="icon.v">{{ icon.l }}</option>
                    </select>
                  </div>
                </FieldRow>
                <label class="flex items-center gap-2 cursor-pointer"><Toggle v-model="el.opens_in_new_tab" /><span class="text-xs text-gray-600">Buka tab baru</span></label>
                <div class="space-y-3 pt-1">
                  <FieldRow label="Gaya Link">
                    <div class="grid grid-cols-4 gap-2">
                      <button v-for="opt in linkVariantOptions" :key="opt.v" @click="setES(el, 'variant', opt.v)" class="p-2.5 rounded-xl border transition text-center" :class="getES(el, 'variant', 'solid') === opt.v ? 'ring-2 ring-violet-400 border-violet-300 bg-violet-50' : 'border-gray-200 hover:border-gray-300'">
                        <div class="text-[11px] font-semibold text-gray-700">{{ opt.l }}</div>
                      </button>
                    </div>
                  </FieldRow>
                  <div class="grid grid-cols-2 gap-2">
                    <ColorRow label="Background" v-model="el.elem_style.bg_color" />
                    <ColorRow label="Teks" v-model="el.elem_style.text_color" />
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <FieldRow label="Sudut">
                      <select v-model="el.elem_style.rounded" class="fi w-full">
                        <option v-for="r in roundeds" :key="r.v" :value="r.v">{{ r.l }}</option>
                      </select>
                    </FieldRow>
                    <FieldRow label="Shadow">
                      <label class="flex items-center gap-2 pt-2"><input type="checkbox" v-model="el.elem_style.shadow" class="rounded border-gray-300 text-violet-600" /> <span class="text-xs text-gray-600">Aktif</span></label>
                    </FieldRow>
                  </div>
                </div>
              </template>

              <template v-else-if="el.type === 'catalog'">
                <FieldRow label="Judul Catalog">
                  <input v-model="el.label" type="text" class="fi w-full" placeholder="Contoh: Produk Unggulan" />
                </FieldRow>
                <div class="grid grid-cols-2 gap-2">
                  <ColorRow label="Background" v-model="el.elem_style.bg_color" />
                  <ColorRow label="Teks" v-model="el.elem_style.text_color" />
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                  <Toggle v-model="el.elem_style.catalog_main_card" />
                  <span class="text-xs text-gray-600">Gunakan card utama</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <Toggle v-model="el.elem_style.catalog_show_price" />
                  <span class="text-xs text-gray-600">Tampilkan harga</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                  <FieldRow label="Sudut">
                    <select v-model="el.elem_style.rounded" class="fi w-full">
                      <option v-for="r in roundeds" :key="r.v" :value="r.v">{{ r.l }}</option>
                    </select>
                  </FieldRow>
                  <FieldRow label="Shadow">
                    <label class="flex items-center gap-2 pt-2"><input type="checkbox" v-model="el.elem_style.shadow" class="rounded border-gray-300 text-violet-600" /> <span class="text-xs text-gray-600">Aktif</span></label>
                  </FieldRow>
                </div>
                <FieldRow label="Layout Item">
                  <div class="grid grid-cols-4 gap-2">
                    <button v-for="opt in catalogLayoutOptions" :key="opt.v" @click="setES(el, 'catalog_layout', Number(opt.v))" class="p-2.5 rounded-xl border transition text-center" :class="String(getES(el, 'catalog_layout', 4)) === opt.v ? 'ring-2 ring-orange-400 border-orange-300 bg-orange-50' : 'border-gray-200 hover:border-gray-300'">
                      <div class="text-[11px] font-semibold text-gray-700">{{ opt.l }}</div>
                    </button>
                  </div>
                </FieldRow>
                <div class="space-y-3">
                  <div v-for="(item, itemIdx) in catalogPreviewItems(el)" :key="itemIdx" class="rounded-xl border border-gray-100 bg-white/70 p-3 space-y-3">
                    <div class="flex items-center justify-between">
                      <div class="text-xs font-medium text-gray-600">Item {{ itemIdx + 1 }}</div>
                      <div class="text-[10px] text-gray-400">{{ item.url ? 'Aktif' : 'Kosong' }}</div>
                    </div>
                    <div class="grid grid-cols-[88px,1fr] gap-3">
                      <div class="space-y-2">
                        <div class="w-full h-24 rounded-xl overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                          <img v-if="item.image_path" :src="storageUrl(item.image_path)" class="w-full h-full object-cover" />
                          <div v-else class="text-center text-gray-200"><i class="fas fa-box text-2xl block mb-1"></i><span class="text-[10px]">Gambar</span></div>
                        </div>
                        <input type="file" :ref="el => setCatalogImageRef(idx, itemIdx, el)" accept="image/*" class="hidden" @change="e => onCatalogImageChange(e, idx, itemIdx)" />
                        <button @click="clickCatalogImageRef(idx, itemIdx)" class="w-full btn-outline-sm"><i class="fas fa-upload text-gray-400 mr-1"></i> Upload</button>
                        <button v-if="item.image_path" @click="item.image_path = null" class="w-full btn-danger-sm"><i class="fas fa-times mr-1"></i> Hapus</button>
                      </div>
                      <div class="space-y-2">
                        <FieldRow label="Nama"><input v-model="item.label" type="text" class="fi w-full" placeholder="Nama produk" /></FieldRow>
                        <FieldRow label="Harga"><input v-model="item.price" type="text" class="fi w-full" placeholder="Rp 99.000" /></FieldRow>
                        <FieldRow label="URL"><input v-model="item.url" type="url" class="fi w-full" placeholder="https://" /></FieldRow>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <template v-else-if="el.type === 'text'">
                <FieldRow label="Konten Teks">
                  <div class="space-y-2">
                    <div class="flex flex-wrap gap-1.5 border border-gray-200 rounded-lg p-2 bg-gray-50">
                      <button v-for="cmd in textCommands" :key="cmd.v" @mousedown.prevent="applyTextCommand(idx, cmd.v)" class="px-2 py-1 rounded text-[11px] bg-white border border-gray-200 hover:bg-gray-50">{{ cmd.l }}</button>
                      <button @mousedown.prevent="promptLink(idx)" class="px-2 py-1 rounded text-[11px] bg-white border border-gray-200 hover:bg-gray-50">Link</button>
                      <input type="color" class="w-8 h-7 p-0 border-0 bg-transparent" :value="getES(el, 'color', '#374151')" @input="setES(el, 'color', $event.target.value)" />
                      <input type="color" class="w-8 h-7 p-0 border-0 bg-transparent" :value="highlightColor" @input="applyHighlight(idx, $event.target.value)" />
                    </div>
                    <div :ref="el => setTextRef(idx, el)" contenteditable="true" class="fi min-h-32 w-full leading-6" @input="syncText(idx, $event)" @mouseup="captureSelection(idx)" @keyup="captureSelection(idx)"></div>
                  </div>
                </FieldRow>
                <div class="pt-1 space-y-3">
                  <p class="fl">Gaya Teks</p>
                  <div>
                    <p class="fl mb-1">Rata</p>
                    <div class="flex gap-1">
                      <button v-for="a in aligns" :key="a.v" @click="setES(el, 'align', a.v)" class="flex-1 py-1.5 rounded-lg text-xs transition" :class="getES(el, 'align', 'left') === a.v ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"><i :class="`fas fa-align-${a.v}`"></i></button>
                    </div>
                  </div>
                  <div>
                    <p class="fl mb-1">Ukuran</p>
                    <div class="flex gap-1 flex-wrap">
                      <button v-for="sz in sizes" :key="sz.v" @click="setES(el, 'size', sz.v)" class="px-2.5 py-1 rounded-lg text-[10px] transition" :class="getES(el, 'size', 'sm') === sz.v ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">{{ sz.l }}</button>
                    </div>
                  </div>
                  <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="el.elem_style.bold" class="rounded border-gray-300 text-violet-600" /><span class="text-xs text-gray-600">Tebal</span></label>
                </div>
              </template>

              <template v-else-if="el.type === 'image'">
                <div class="w-full h-28 rounded-xl overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                  <img v-if="el.image_path"
                       :src="storageUrl(el.image_path)"
                       class="max-h-full object-contain"
                       :style="{ width: `${Number(getES(el, 'scale', 100))}%`, maxWidth: 'none' }" />
                  <div v-else class="text-center text-gray-200"><i class="fas fa-image text-3xl block mb-1"></i><span class="text-[10px]">Belum ada gambar</span></div>
                </div>
                <div class="flex gap-2">
                  <input type="file" :ref="el => setImageRef(idx, el)" accept="image/*" class="hidden" @change="e => onImageChange(e, idx)" />
                  <button @click="clickImageRef(idx)" class="flex-1 btn-outline-sm"><i class="fas fa-upload text-gray-400 mr-1"></i> Upload Gambar</button>
                  <button v-if="el.image_path" @click="el.image_path = null" class="btn-danger-sm px-3"><i class="fas fa-times"></i></button>
                </div>
                <div class="pt-1 space-y-3">
                  <p class="fl">Gaya Gambar</p>
                  <div class="grid grid-cols-2 gap-2">
                    <FieldRow label="Rata">
                      <select v-model="el.elem_style.align" class="fi w-full">
                        <option value="left">Kiri</option>
                        <option value="center">Tengah</option>
                        <option value="right">Kanan</option>
                      </select>
                    </FieldRow>
                    <FieldRow label="Skala">
                      <select v-model="el.elem_style.scale" class="fi w-full">
                        <option v-for="scale in [25,40,50,60,75,100]" :key="scale" :value="scale">{{ scale }}%</option>
                      </select>
                    </FieldRow>
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <FieldRow label="Sudut">
                      <select v-model="el.elem_style.rounded" class="fi w-full">
                        <option value="none">Tidak</option>
                        <option value="md">Sedikit</option>
                        <option value="lg">Sedang</option>
                        <option value="xl">Besar</option>
                      </select>
                    </FieldRow>
                    <FieldRow label="Border/Shadow">
                      <div class="flex items-center gap-2 pt-2">
                        <label class="flex items-center gap-1 text-[11px] text-gray-600"><input type="checkbox" v-model="el.elem_style.border" class="rounded border-gray-300 text-violet-600" /> Border</label>
                        <label class="flex items-center gap-1 text-[11px] text-gray-600"><input type="checkbox" v-model="el.elem_style.shadow" class="rounded border-gray-300 text-violet-600" /> Shadow</label>
                      </div>
                    </FieldRow>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <div class="relative mt-1" @click.stop>
            <button @click="showAdd = !showAdd" class="w-full py-2.5 border-2 border-dashed border-gray-200 rounded-xl text-xs text-gray-400 hover:border-violet-400 hover:text-violet-500 transition flex items-center justify-center gap-1.5"><i class="fas fa-plus"></i> Tambah Elemen</button>
            <div v-if="showAdd" class="absolute z-20 bottom-full mb-2 left-0 right-0 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
              <button v-for="t in addTypes" :key="t.v" @click="addElement(t.v)" class="w-full flex items-center gap-3 px-3.5 py-3 hover:bg-gray-50 transition">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" :class="t.bg"><i :class="t.icon + ' text-sm'"></i></div>
                <div class="text-left"><div class="text-xs font-medium text-gray-700">{{ t.label }}</div><div class="text-[10px] text-gray-400">{{ t.desc }}</div></div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="border-b border-gray-50">
        <SectionHeader title="SEO" icon="fa-search-plus" v-model="open.seo" />
        <div v-if="open.seo" class="px-4 pb-4 pt-1 space-y-3">
          <FieldRow label="Meta Title"><input v-model="data.meta_title" type="text" class="fi w-full" /></FieldRow>
          <FieldRow label="Meta Description"><textarea v-model="data.meta_description" rows="2" class="fi w-full resize-none"></textarea></FieldRow>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, nextTick, onMounted, onUnmounted, defineComponent, h } from 'vue'
import axios from 'axios'

const props = defineProps({ data: Object, pageId: Number })
const emit = defineEmits(['upload-image', 'remove-image', 'delete-page', 'duplicate-page'])

const open = reactive({ profil: true, tampilan: true, gaya: false, elemen: true, seo: false })
const expandedIdx = ref(null)
const showAdd = ref(false)
const textEditorRefs = reactive({})
const thumbRefs = reactive({})
const catalogImageRefs = reactive({})
const imageRefs = reactive({})
const savedRange = reactive({})
const api = axios.create({ headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } })

const fontOptions = [
  { v: 'sans', l: 'Sans Modern' },
  { v: 'serif', l: 'Serif Elegan' },
  { v: 'mono', l: 'Mono Tech' },
  { v: 'rounded', l: 'Rounded Friendly' },
]
const widthOptions = [
  { v: 'sm', l: 'Kecil' },
  { v: 'md', l: 'Sedang' },
  { v: 'lg', l: 'Lebar' },
]
const roundeds = [
  { v: 'none', l: 'None', cls: 'rounded-none' },
  { v: 'sm', l: 'Sm', cls: 'rounded-sm' },
  { v: 'md', l: 'Md', cls: 'rounded' },
  { v: 'lg', l: 'Lg', cls: 'rounded-lg' },
  { v: 'xl', l: 'Xl', cls: 'rounded-xl' },
  { v: 'full', l: 'Full', cls: 'rounded-full' },
]
const aligns = [{ v: 'left' }, { v: 'center' }, { v: 'right' }]
const sizes = [{ v: 'xs', l: 'XS' }, { v: 'sm', l: 'S' }, { v: 'base', l: 'M' }, { v: 'lg', l: 'L' }, { v: 'xl', l: 'XL' }, { v: '2xl', l: '2XL' }, { v: '3xl', l: '3XL' }]
const iconOptions = [
  { v: 'fas fa-link', l: 'Link' },
  { v: 'fas fa-globe', l: 'Website' },
  { v: 'fas fa-house', l: 'Home' },
  { v: 'fas fa-bag-shopping', l: 'Belanja' },
  { v: 'fas fa-shop', l: 'Shop' },
  { v: 'fas fa-phone', l: 'Telepon' },
  { v: 'fas fa-envelope', l: 'Email' },
  { v: 'fas fa-circle-info', l: 'Info' },
  { v: 'fas fa-message', l: 'Chat' },
  { v: 'fas fa-star', l: 'Favorit' },
]
const linkVariantOptions = [
  { v: 'solid', l: 'Solid' },
  { v: 'outline', l: 'Outline' },
  { v: 'soft', l: 'Soft' },
  { v: 'ghost', l: 'Ghost' },
]
const catalogLayoutOptions = [
  { v: '1', l: '1' },
  { v: '2', l: '2' },
  { v: '4', l: '4' },
  { v: '6', l: '6' },
]
const addTypes = [
  { v: 'link', label: 'Link', desc: 'Full width link/button', icon: 'fas fa-link text-violet-600', bg: 'bg-violet-100' },
  { v: 'text', label: 'Teks', desc: 'Rich text block', icon: 'fas fa-font text-blue-600', bg: 'bg-blue-100' },
  { v: 'image', label: 'Gambar', desc: 'Image block', icon: 'fas fa-image text-emerald-600', bg: 'bg-emerald-100' },
  { v: 'catalog', label: 'Catalog', desc: 'Shop-style product grid', icon: 'fas fa-table-cells-large text-orange-600', bg: 'bg-orange-100' },
]
const textCommands = [
  { v: 'bold', l: 'B' },
  { v: 'italic', l: 'I' },
  { v: 'underline', l: 'U' },
  { v: 'removeFormat', l: 'Clear' },
]
const highlightColor = ref('#fff59d')

function createCatalogItems() {
  return Array.from({ length: 6 }, (_, index) => ({
    label: `Produk ${index + 1}`,
    price: 'Rp 99.000',
    url: '',
    image_path: null,
  }))
}

function createDefaultElemStyle(type = 'link') {
  if (type === 'text') {
    return { align: 'left', size: 'sm', color: '#374151', bold: false }
  }
  if (type === 'image') {
    return { align: 'center', scale: 100, border: false, shadow: false, rounded: 'lg' }
  }
  if (type === 'link') {
    return { variant: 'solid', bg_color: '#111827', text_color: '#ffffff', rounded: 'lg', shadow: false }
  }
  if (type === 'catalog') {
    return {
      bg_color: '#f8fafc',
      text_color: '#111827',
      rounded: 'xl',
      shadow: false,
      catalog_layout: 4,
      catalog_main_card: true,
      catalog_show_price: true,
      catalog_items: createCatalogItems(),
    }
  }
  return {}
}

const avatarInput = ref(null)
const coverInput = ref(null)

function storageUrl(path) {
  if (!path) return null
  if (path.startsWith('http') || path.startsWith('blob:')) return path
  return `/storage/${path}`
}
function stripHtml(html) {
  return String(html || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}
function ensureES(el) {
  const type = el.type || 'link'
  if (!el.elem_style || typeof el.elem_style !== 'object') el.elem_style = { ...createDefaultElemStyle(type) }
  if (type === 'catalog') {
    el.elem_style.catalog_layout = Number(el.elem_style.catalog_layout) || 4
    if (el.elem_style.catalog_main_card === undefined) el.elem_style.catalog_main_card = true
    if (el.elem_style.catalog_show_price === undefined) el.elem_style.catalog_show_price = true
    if (!Array.isArray(el.elem_style.catalog_items)) {
      el.elem_style.catalog_items = createCatalogItems()
    } else {
      while (el.elem_style.catalog_items.length < 6) {
        el.elem_style.catalog_items.push({
          label: `Produk ${el.elem_style.catalog_items.length + 1}`,
          price: 'Rp 99.000',
          url: '',
          image_path: null,
        })
      }
    }
  }
  return el.elem_style
}
function getES(el, key, fallback) {
  const style = ensureES(el)
  return style[key] !== undefined ? style[key] : fallback
}
function setES(el, key, val) { ensureES(el)[key] = val }
function onAvatarChange(e) { const file = e.target.files[0]; if (file) emit('upload-image', { type: 'avatar', file }) }
function onCoverChange(e) { const file = e.target.files[0]; if (file) emit('upload-image', { type: 'cover', file }) }
function setThumbRef(idx, el) { if (el) thumbRefs[idx] = el }
function clickThumbRef(idx) { thumbRefs[idx]?.click() }
function setCatalogImageRef(idx, itemIdx, el) { if (el) catalogImageRefs[`${idx}:${itemIdx}`] = el }
function clickCatalogImageRef(idx, itemIdx) { catalogImageRefs[`${idx}:${itemIdx}`]?.click() }
function setImageRef(idx, el) { if (el) imageRefs[idx] = el }
function clickImageRef(idx) { imageRefs[idx]?.click() }
async function onThumbChange(e, idx) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('image', file)
  fd.append('type', 'element')
  const { data } = await api.post(`/upanel/landing-pages/${props.pageId}/upload`, fd)
  props.data.links[idx].thumbnail = data.path
}
async function onCatalogImageChange(e, idx, itemIdx) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('image', file)
  fd.append('type', 'element')
  const { data } = await api.post(`/upanel/landing-pages/${props.pageId}/upload`, fd)
  ensureES(props.data.links[idx]).catalog_items[itemIdx].image_path = data.path
}
async function onImageChange(e, idx) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('image', file)
  fd.append('type', 'element')
  const { data } = await api.post(`/upanel/landing-pages/${props.pageId}/upload`, fd)
  props.data.links[idx].image_path = data.path
}
function addElement(type) {
  showAdd.value = false
  const defaults = {
    link: { type: 'link', label: '', url: '', icon: '', thumbnail: null, is_active: true, opens_in_new_tab: true, elem_style: { ...createDefaultElemStyle('link') } },
    text: { type: 'text', content: '<p>Tulis teks di sini</p>', is_active: true, elem_style: { ...createDefaultElemStyle('text') } },
    image: { type: 'image', image_path: null, is_active: true, elem_style: { ...createDefaultElemStyle('image') } },
    catalog: { type: 'catalog', label: 'Catalog', is_active: true, opens_in_new_tab: true, elem_style: { ...createDefaultElemStyle('catalog') } },
  }
  props.data.links.push({ id: null, ...defaults[type] })
  expandedIdx.value = props.data.links.length - 1
}
function remove(idx) {
  props.data.links.splice(idx, 1)
  if (expandedIdx.value === idx) expandedIdx.value = null
}
function move(idx, dir) {
  const next = idx + dir
  if (next < 0 || next >= props.data.links.length) return
  ;[props.data.links[idx], props.data.links[next]] = [props.data.links[next], props.data.links[idx]]
}
function setTextRef(idx, el) { if (el) textEditorRefs[idx] = el }
function syncText(idx, e) { props.data.links[idx].content = e.target.innerHTML }
function saveSelection(idx) {
  const sel = window.getSelection()
  if (sel && sel.rangeCount) savedRange[idx] = sel.getRangeAt(0)
}
function restoreSelection(idx) {
  const range = savedRange[idx]
  const editor = textEditorRefs[idx]
  if (!range || !editor) return
  const sel = window.getSelection()
  sel.removeAllRanges()
  sel.addRange(range)
  editor.focus()
}
function applyTextCommand(idx, command) {
  const editor = textEditorRefs[idx]
  if (!editor) return
  editor.focus()
  restoreSelection(idx)
  document.execCommand(command, false, null)
  syncText(idx, { target: editor })
}
function applyHighlight(idx, color) {
  const editor = textEditorRefs[idx]
  if (!editor) return
  editor.focus()
  restoreSelection(idx)
  document.execCommand('hiliteColor', false, color)
  syncText(idx, { target: editor })
}
function promptLink(idx) {
  const url = window.prompt('Masukkan URL link')
  if (!url) return
  const editor = textEditorRefs[idx]
  if (!editor) return
  editor.focus()
  restoreSelection(idx)
  document.execCommand('createLink', false, url)
  editor.querySelectorAll('a').forEach(a => {
    a.target = '_blank'
    a.rel = 'noopener noreferrer'
  })
  syncText(idx, { target: editor })
}
function captureSelection(idx) { saveSelection(idx) }
function catalogPreviewItems(el) {
  const style = ensureES(el)
  const count = Math.min(6, Math.max(1, Number(style.catalog_layout) || 4))
  return style.catalog_items.slice(0, count)
}
watch(expandedIdx, async idx => {
  await nextTick()
  if (idx === null) return
  const el = props.data.links[idx]
  const editor = textEditorRefs[idx]
  if (el?.type === 'text' && editor) {
    editor.innerHTML = el.content || '<p></p>'
  }
})
function closeAdd() { showAdd.value = false }
onMounted(() => document.addEventListener('click', closeAdd))
onUnmounted(() => document.removeEventListener('click', closeAdd))

const SectionHeader = defineComponent({
  props: { title: String, icon: String, modelValue: Boolean },
  emits: ['update:modelValue'],
  setup(p, { emit }) {
    return () => h('button', {
      class: 'w-full flex items-center justify-between px-4 py-3 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition',
      onClick: () => emit('update:modelValue', !p.modelValue),
    }, [
      h('span', { class: 'flex items-center gap-2' }, [h('i', { class: `fas ${p.icon} text-gray-400 w-3.5 text-center text-[11px]` }), p.title]),
      h('i', { class: `fas fa-chevron-down text-gray-300 text-[9px] transition-transform ${p.modelValue ? 'rotate-180' : ''}` }),
    ])
  },
})

const FieldRow = defineComponent({
  props: { label: String },
  setup(p, { slots }) { return () => h('div', {}, [p.label ? h('label', { class: 'fl' }, p.label) : null, slots.default?.()]) }
})

const ColorRow = defineComponent({
  props: { label: String, modelValue: String },
  emits: ['update:modelValue'],
  setup(p, { emit }) {
    return () => h('div', { class: 'flex items-center gap-2' }, [
      h('span', { class: 'text-[10px] text-gray-400 w-16 shrink-0 truncate' }, p.label),
      h('label', { class: 'relative cursor-pointer shrink-0' }, [
        h('span', { class: 'block w-7 h-7 rounded-lg border border-gray-200 shadow-sm', style: { background: p.modelValue || '#ffffff' } }),
        h('input', { type: 'color', value: p.modelValue, class: 'sr-only', onInput: e => emit('update:modelValue', e.target.value) }),
      ]),
      h('input', { type: 'text', value: p.modelValue, class: 'fi flex-1', onInput: e => emit('update:modelValue', e.target.value) }),
    ])
  },
})

const Toggle = defineComponent({
  props: { modelValue: Boolean },
  emits: ['update:modelValue'],
  setup(p, { emit }) {
    return () => h('button', { type: 'button', role: 'switch', class: `relative inline-flex h-5 w-9 items-center rounded-full transition-colors ${p.modelValue ? 'bg-violet-600' : 'bg-gray-200'}`, onClick: () => emit('update:modelValue', !p.modelValue) }, [
      h('span', { class: `inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform ${p.modelValue ? 'translate-x-4' : 'translate-x-0.5'}` }),
    ])
  },
})

const TextControls = defineComponent({
  props: { prefix: String, s: Object },
  setup(p) {
    const k = x => `${p.prefix}_${x}`
    const aligns = ['left', 'center', 'right']
    const sizes = [{ v: 'xs', l: 'XS' }, { v: 'sm', l: 'S' }, { v: 'base', l: 'M' }, { v: 'lg', l: 'L' }, { v: 'xl', l: 'XL' }, { v: '2xl', l: '2XL' }, { v: '3xl', l: '3XL' }]
    return () => h('div', { class: 'space-y-3' }, [
      h('div', {}, [h('p', { class: 'fl mb-1' }, 'Rata'), h('div', { class: 'flex gap-1' }, aligns.map(a => h('button', { class: `flex-1 py-1.5 rounded-lg text-xs transition ${p.s[k('align')] === a ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`, onClick: () => { p.s[k('align')] = a } }, [h('i', { class: `fas fa-align-${a}` })]))) ]),
      h('div', {}, [h('p', { class: 'fl mb-1' }, 'Ukuran'), h('div', { class: 'flex gap-1 flex-wrap' }, sizes.map(sz => h('button', { class: `px-2.5 py-1 rounded-lg text-[10px] transition ${p.s[k('size')] === sz.v ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}`, onClick: () => { p.s[k('size')] = sz.v } }, sz.l)))]),
      h(ColorRow, { label: 'Warna', modelValue: p.s[k('color')], 'onUpdate:modelValue': v => { p.s[k('color')] = v } }),
      h('label', { class: 'flex items-center gap-2 cursor-pointer' }, [h('input', { type: 'checkbox', checked: p.s[k('bold')], class: 'rounded border-gray-300 text-violet-600', onChange: e => { p.s[k('bold')] = e.target.checked } }), h('span', { class: 'text-xs text-gray-600' }, 'Tebal')]),
    ])
  },
})
</script>

<style scoped>
.fl {
  display: block;
  font-size: 10px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  margin-bottom: 4px;
}
.fi {
  display: block;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 12px;
  color: #374151;
  background: #fff;
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
}
.fi:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.1); }
.btn-outline-sm {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 11px;
  color: #6b7280;
  background: #fff;
  transition: background .15s, border-color .15s;
  cursor: pointer;
}
.btn-outline-sm:hover { background: #f9fafb; border-color: #d1d5db; }
.btn-danger-sm {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border: 1px solid #fecaca;
  border-radius: 8px;
  font-size: 11px;
  color: #ef4444;
  background: #fff5f5;
  transition: background .15s;
  cursor: pointer;
}
.btn-danger-sm:hover { background: #fee2e2; }
</style>
