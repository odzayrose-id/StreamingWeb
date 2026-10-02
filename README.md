# 🎬 Web Streaming Platform

Platform *streaming* film dan serial TV berbasis **PHP dan MySQL** dengan antarmuka premium bernuansa gelap (*Dark Theme*) layaknya platform VOD modern. Proyek ini berfungsi sebagai sistem utama (Admin & *User Frontend*) sekaligus penyedia **REST API** (JSON) untuk aplikasi *native* Android.

## ✨ Fitur Utama

*   📱 **Desain Responsif & Premium:** Tampilan antarmuka *Dark Mode* yang mulus diakses melalui Desktop maupun *Mobile Browser*.
*   🗂️ **Manajemen Kategori & Tipe:** Mendukung pemisahan kategori spesifik (*Movies* dan *Series*) dengan sistem manajemen *Season* dan Episode yang terstruktur.
*   🎭 **Integrasi Ekosistem TMDB:** Menampilkan profil pemeran (aktor/aktris) dengan menarik foto dan biografi langsung dari The Movie Database (TMDB). Dilengkapi fitur *auto-translate* (Inggris ke Indonesia) melalui Google Translate API.
*   🎛️️ **Multi-Resolusi & Safelink:** Mendukung input multi-tautan video (1080p, 720p, 480p, 360p) dan tombol *Download* yang otomatis membungkus tautan ke layanan *Safelink* (`sfl.gl`).
*   📡 **Built-in REST API:** Dilengkapi *endpoint* JSON khusus yang siap digunakan untuk aplikasi *Native Mobile* (Android/iOS).

## 🛠 Teknologi yang Digunakan

*   **Backend:** PHP (Native/Procedural)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript (Vanilla/jQuery)
*   **API Eksternal:** 
    *   TMDB API (Data Pemeran & Biografi)
    *   Google Translate API (Translasi Biografi)
    *   UI-Avatars (Fallback Foto Profil Pemeran)

## 📂 Struktur Direktori Utama

```text
├── index.php          # Halaman utama (Frontend User)
├── detail.php         # Halaman pemutar video & detail film
├── koneksi.php        # Konfigurasi koneksi database MySQL
├── api_home.php       # [API] Endpoint data beranda untuk Mobile App
├── api_detail.php     # [API] Endpoint data detail & episode untuk Mobile App
├── /admin             # (Opsional) Folder dashboard admin panel
└── /assets            # Folder penyimpanan CSS, JS, dan gambar statis
