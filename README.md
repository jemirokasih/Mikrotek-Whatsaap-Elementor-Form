# Mikrotek WhatsApp Action for Elementor Form

Plugin WordPress untuk menambahkan aksi **WhatsApp** pada *Actions After Submit* di widget Form Elementor Pro. Setelah form dikirimkan, user langsung diarahkan ke WhatsApp membawa format pesan otomatis dari data form.

---

## Fitur Utama

1. **Integrasi Resmi Elementor Pro Form**: Muncul otomatis di dropdown *Actions After Submit*.
2. **Fleksibilitas Nomor Tujuan**:
   - Nomor kustom/tetap (CS / Admin).
   - Nomor dinamis dari input field form (telepon pengguna).
3. **Format Pesan Fleksibel**:
   - Gunakan `[all-fields]` untuk rekap otomatis semua isian form.
   - Gunakan `[field id="nama_field"]` untuk kustomisasi posisi text tertentu.
   - Mendukung metadata `[form:name]`, `[form:id]`, dan `[page:url]`.
4. **Opsi Buka di Tab Baru**: Pilihan buka WhatsApp di tab baru atau redirect di tab yang sama.
5. **Dukungan Endpoint**:
   - `api.whatsapp.com/send`
   - `wa.me`
6. **Auto Normalisasi Nomor**: Mengubah awalan `08xxx` menjadi kode negara `628xxx` secara otomatis.

---

## Cara Instalasi

1. Unduh folder/zip plugin ini.
2. Masuk ke Dashboard WordPress -> **Plugins** -> **Add New** -> **Upload Plugin**.
3. Pilih file zip atau letakkan folder `mikrotek-whatsapp-elementor-form` di direktori `wp-content/plugins/`.
4. Klik **Activate**.

---

## Cara Menggunakan di Elementor

1. Buka halaman di **Elementor Editor**.
2. Tambahkan atau pilih widget **Form** (Elementor Pro).
3. Buka tab **Content** -> bagian **Actions After Submit**.
4. Tambahkan opsi **WhatsApp**.
5. Buka tab baru **WhatsApp** yang muncul di bawahnya:
   - Tentukan **Tipe Nomor Tujuan** (Nomor CS atau Input Form).
   - Masukkan **Nomor WhatsApp** (contoh: `628123456789`).
   - Atur **Format Pesan** sesuai kebutuhan.
   - Pilih opsi **Buka di Tab Baru** jika ingin membuka di window baru.
6. Simpan / Publish halaman.

---

## Tag / Placeholder Pesan

| Tag | Keterangan |
| --- | --- |
| `[all-fields]` | Semua field form beserta labelnya |
| `[field id="name"]` | Nilai field spesifik berdasarkan ID Field |
| `[form:name]` | Nama Form di pengaturan Elementor |
| `[form:id]` | ID Form |
| `[page:url]` | URL halaman tempat form berada |

