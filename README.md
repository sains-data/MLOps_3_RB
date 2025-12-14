# 📦 Perancangan Website File Manager untuk Manajemen Modul Praktikum Sains Data
_(Implementasi MLOps-Based Intelligent Repository System)_

---

## 📌 Overview
Proyek perancangan dan implementasi **Website File Manager** yang digunakan untuk mengelola modul praktikum Sains Data dalam format PDF secara terstruktur dan terpusat. Sistem akan dikembangkan dengan pendekatan **Machine Learning Operations (MLOps)** serta memanfaatkan **Generative Artificial Intelligence (Large Language Model / LLM)** untuk melakukan ekstraksi metadata dokumen secara otomatis.

Metadata yang diekstraksi meliputi **mata kuliah, judul topik praktikum, dan deskripsi modul**, yang kemudian disimpan ke dalam basis data dan disajikan melalui antarmuka web berbasis PHP. Dengan pendekatan ini, website tidak hanya berfungsi sebagai media penyimpanan file, tetapi juga sebagai **repositori cerdas** yang mendukung pencarian modul berdasarkan topik dan mata kuliah.

---

## 🎯 Objectives
Tujuan dari proyek ini adalah:
1. Merancang **Website File Manager** untuk manajemen modul praktikum Sains Data.
2. Mengimplementasikan **workflow MLOps end-to-end** yang mencakup tahap Build, Deploy, dan Monitor.
3. Mengintegrasikan **Generative AI (LLM)** untuk otomatisasi ekstraksi metadata dari dokumen PDF.
4. Menyediakan sistem repositori modul praktikum yang berkelanjutan dan dapat digunakan lintas semester.
5. Menerapkan mekanisme **Human-in-the-Loop** untuk menjaga akurasi dan tata kelola data.

---

## 🧩 Background (Latar Belakang)
Dalam kegiatan praktikum Sains Data, modul praktikum sering mengalami perubahan setiap semester. Modul lama kerap tidak digunakan kembali meskipun secara konsep masih relevan. Selain itu, penyimpanan modul yang tersebar di berbagai media menyebabkan kesulitan dalam pencarian materi, duplikasi dokumen, serta meningkatnya beban administrasi bagi pengelola.

Berdasarkan konsep **Document Management System** dan **Knowledge Management System**, pengelolaan dokumen yang baik memerlukan repositori terpusat yang mendukung pencarian berbasis metadata. Oleh karena itu, sistem ini dikembangkan sebagai **Website File Manager berbasis AI**, sehingga pengelolaan modul tidak hanya bersifat administratif, tetapi juga mampu memahami isi dokumen secara otomatis.

---

## 💡 Value Proposition
### Untuk Administrator
- Mengurangi proses input metadata secara manual
- Mempercepat pengelolaan dan dokumentasi modul praktikum
- Menyediakan kontrol kualitas data melalui mekanisme koreksi manual

### Untuk Mahasiswa
- Memudahkan pencarian modul berdasarkan topik atau mata kuliah
- Mengakses modul lama dan baru yang masih relevan
- Mendukung proses belajar mandiri berbasis praktikum

---

## 🏗️ System Architecture
Sistem dirancang menggunakan **arsitektur hybrid** yang memisahkan proses komputasi berat dan penyajian data.

### 1. AI Backend (Python – Google Colab)
Lingkungan ini digunakan untuk menjalankan pipeline AI, yang meliputi:
- Pembacaan file PDF menggunakan **PyMuPDF (fitz)**
- Pengambilan teks dari dua halaman awal dokumen
- Pembersihan teks menggunakan **Regular Expression**
- Inferensi menggunakan **Large Language Model**
- Validasi dan penyimpanan hasil ke dalam file CSV

Google Colab digunakan karena menyediakan GPU (T4) yang mendukung eksekusi model LLM dengan teknik optimasi memori.

### 2. Web Application / File Manager (PHP – XAMPP)
Website berfungsi sebagai:
- File manager modul praktikum
- Mesin pencarian metadata modul
- Antarmuka admin untuk monitoring dan koreksi data

### 3. Database (MySQL)
Database menyimpan metadata hasil ekstraksi AI dan berfungsi sebagai **single source of truth** untuk sistem. Setiap data memiliki informasi waktu pembuatan dan pembaruan untuk mendukung versioning.

---

## 🔄 MLOps Workflow

### A. Build Stage (Membangun Pipeline AI)
Tahap Build diimplementasikan pada file `mlops.ipynb`.

#### 1. Data Ingestion
Sistem membaca seluruh file PDF modul praktikum dari folder Google Drive yang telah ditentukan. Proses pembacaan dilakukan menggunakan library PyMuPDF. Untuk efisiensi komputasi, hanya **dua halaman pertama** yang diambil karena umumnya memuat informasi utama modul.

#### 2. Data Preprocessing
Teks hasil ekstraksi dibersihkan menggunakan **Regular Expression (regex)** untuk menghilangkan spasi berlebih, baris kosong, dan karakter non-standar. Teks kemudian dipotong maksimal **2500 karakter** agar sesuai dengan batas konteks model LLM.

#### 3. Modeling / Inference
Pendekatan pemodelan menggunakan **Generative AI dengan In-Context Learning**, tanpa proses training ulang. Model diarahkan menggunakan **Prompt Engineering** agar menghasilkan output dalam format JSON yang berisi mata kuliah, judul topik, dan deskripsi modul.

#### 4. Output Packaging
Output model divalidasi dengan proses parsing JSON. Data yang valid dikemas ke dalam file CSV (`indexing_final_db.csv`) agar mudah diintegrasikan dengan sistem web dan database.

---

### B. Deploy Stage (Production Release)
Tahap deploy dilakukan dengan:
- Mengimpor file CSV ke database MySQL menggunakan `import_data.php`
- Menyajikan metadata modul melalui website berbasis PHP
- Mahasiswa dapat mengakses hasil klasifikasi modul melalui fitur pencarian di `index.php`

Pendekatan ini menggunakan **batch inference dan batch serving**, karena server web tidak menjalankan inferensi LLM secara real-time.

---

### C. Monitor Stage (Monitoring & Governance)
Monitoring sistem dilakukan menggunakan pendekatan **Human-in-the-Loop (HITL)**:
- Admin memantau hasil ekstraksi melalui halaman `admin.php`
- Kesalahan klasifikasi dapat diperbaiki melalui `update.php`
- Data yang tidak relevan dapat dihapus melalui `delete_document.php`

Pendekatan ini memastikan hasil AI tetap akurat dan dapat dikontrol secara manual.

---

## 🧰 MLOps Components & Tools

| Komponen | Tools | Fungsi |
|--------|------|--------|
| Data Ingestion | Python, PyMuPDF | Membaca dan mengekstraksi PDF |
| Data Preprocessing | Regex, JSON | Membersihkan teks |
| Model Framework | PyTorch, Transformers | Menjalankan LLM |
| Model Optimization | BitsAndBytes (4-bit) | Efisiensi memori GPU |
| Deployment | XAMPP (PHP, MySQL) | Penyajian web |
| Monitoring | Admin Dashboard | Human-in-the-Loop |

---

## 🧠 Modeling Approach

### Model yang Digunakan
- **Qwen/Qwen2.5-1.5B-Instruct**

Model ini dipilih karena memiliki performa bahasa yang baik, ukuran relatif ringan, serta mendukung instruction-based inference. Model dijalankan menggunakan teknik **4-bit quantization** agar dapat berjalan pada GPU T4 Google Colab.

### Pengaturan Inferensi
- `temperature = 0.1` untuk menghasilkan output yang konsisten
- `max_new_tokens = 450` agar deskripsi tidak terpotong

Pendekatan ini sesuai dengan praktik **document understanding menggunakan LLM**.

---

## 📊 Evaluation Strategy
Evaluasi sistem dilakukan menggunakan:
1. **Format Validity Rate**: keberhasilan model menghasilkan JSON yang valid
2. **Human Correction Rate**: frekuensi koreksi manual oleh admin

Evaluasi dilakukan secara offline (validasi JSON otomatis) dan online (validasi manual melalui dashboard).

---

## 🚀 Inference Strategy
- **Offline (Batch Inference)**: seluruh dokumen PDF diproses di Google Colab
- **Online Serving**: website hanya menyajikan hasil inferensi

Pendekatan ini menjaga performa website tetap ringan dan stabil.

---

## 🔁 Reproducibility & Versioning
- Code versioning menggunakan GitHub
- Model versioning dikunci pada `Qwen/Qwen2.5-1.5B-Instruct`
- Data versioning menggunakan kolom `created_at` dan `updated_at` di database

---

## 🧪 Testing & Reliability
Pengujian dilakukan dengan memproses seluruh file PDF secara batch. Sistem menggunakan mekanisme `try-except` untuk memastikan proses tetap berjalan meskipun terdapat file yang gagal diproses oleh model.

---

## 📂 Key Files
- `mlops.ipynb` – Pipeline AI
- `indexing_final_db.csv` – Output metadata
- `import_data.php` – Import data ke database
- `index.php` – Search engine
- `admin.php` – Monitoring data
- `update.php` – Koreksi metadata

---

## 📄 License
Proyek ini dikembangkan untuk **keperluan pendidikan dan akademik**.
