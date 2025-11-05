# **Laporan Modul 5: Form Submission & Data Validation**

**Mata Kuliah:** Workshop Web Lanjut  
**Nama:** Adha Gusti Harmadhan  
**NIM:** 2024573010009  
**Kelas:** 2B TI  

---

## **Abstrak**

Laporan ini menjelaskan hasil praktikum pada **Modul 5: Form Submission & Data Validation** menggunakan Laravel 12. Fokus dari modul ini adalah bagaimana Laravel menangani proses pengiriman form (request → controller → response), bagaimana cara mengamankan form dengan CSRF token, serta bagaimana melakukan **validasi data** baik yang sederhana maupun yang bersifat kustom.  
Pada praktikum ini dibuat tiga skenario utama:  
1. Form registrasi dasar dengan validasi dan tampilan ulang data.  
2. Form dengan **pesan error kustom** untuk memberi feedback yang lebih ramah.  
3. **Multi-step form** (form bertahap) yang menyimpan data di session sehingga input panjang bisa dipecah menjadi beberapa halaman.  
Dari praktikum ini dapat dilihat bahwa Laravel sudah menyediakan alur lengkap: tampilkan form → kirim → validasi → redirect → tampilkan hasil/error ke user.

---

## **1. Dasar Teori**

- **Arsitektur Request–Response di Laravel**  
  Form dikirim dari **view**, diterima oleh **route** dan **controller**, lalu controller melakukan validasi dan mengembalikan **response** (berupa view lain atau redirect). Data user dibawa dalam bentuk `Request` dan bisa diambil dengan `$request->input('field')` atau `$request->validate(...)`.

- **CSRF Protection**  
  Semua form POST/PUT/PATCH/DELETE harus menyertakan `@csrf`. Ini akan membuat `<input type="hidden" name="_token" ...>` yang dicek oleh middleware. Tanpa token yang valid, Laravel akan menolak request dan mengembalikan error 419.

- **Validasi Data**  
  Validasi di Laravel bisa dilakukan dengan:
  1. `$request->validate([...])` langsung di controller (paling cepat dan sering dipakai).
  2. **Form Request** (class terpisah) untuk validasi yang lebih rapi.
  3. **Validator facade** untuk validasi manual dan kondisi khusus.  
  Validasi dipakai untuk menjamin data yang masuk:  
  - tidak kosong (`required`)  
  - format benar (`email`, `url`, `date`)  
  - sesuai bisnis (`confirmed`, `unique`, `exists`)  
  - tidak merusak database.

- **Menampilkan Error ke View**  
  Laravel otomatis mengirim error ke view saat validasi gagal. Di Blade kita bisa pakai:
  ```blade
  @error('email')
      <div class="text-danger">{{ $message }}</div>
  @enderror
  ```
  atau semua error sekali gus:
  ```blade
  @if ($errors->any())
      @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
      @endforeach
  @endif
  ```

- **Old Input**  
  `old('name')` dipakai supaya input user tidak hilang setelah validasi gagal. Laravel otomatis mem-*flash* data ke session.

---

## **2. Langkah-Langkah Praktikum**

### **2.1 Praktikum 1 — Form Dasar dengan Validasi & Redirect**

1. Buat project:  
   ```bash
   laravel new form-app
   ```
2. Tambahkan route:
   ```php
   Route::get('/form', [FormController::class, 'showForm'])->name('form.show');
   Route::post('/form', [FormController::class, 'handleForm'])->name('form.handle');
   Route::get('/result', [FormController::class, 'showResult'])->name('form.result');
   ```
3. Buat controller `FormController` dengan validasi:
   ```php
   public function handleForm(Request $request)
   {
       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|email',
           'age' => 'required|integer|min:1',
           'password' => 'required|min:6',
           'gender' => 'required',
           'role' => 'required',
           'bio' => 'required',
           'confirm' => 'accepted',
       ]);

       return redirect()->route('form.result')->with('data', $validated);
   }
   ```
4. Buat view `form.blade.php` berisi form Bootstrap + `@csrf` + `@error`.
5. Buat view `result.blade.php` untuk menampilkan data yang dikirim (diambil dari session).

---

### **2.2 Praktikum 2 — Validasi Kustom & Pesan Error Sendiri**

1. Tambahkan route:
   ```php
   Route::get('/register', [RegisterController::class, 'showForm'])->name('register.show');
   Route::post('/register', [RegisterController::class, 'handleForm'])->name('register.handle');
   ```
2. Buat controller `RegisterController`:
   ```php
   public function handleForm(Request $request)
   {
       $customMessages = [
           'name.required' => 'Kami perlu tahu nama Anda!',
           'email.required' => 'Email Anda penting bagi kami.',
           'email.email' => 'Hmm... itu tidak terlihat seperti email yang valid.',
           'password.required' => 'Jangan lupa untuk set password.',
           'password.min' => 'Password harus minimal :min karakter.',
           'username.regex' => 'Username hanya boleh berisi huruf dan angka.',
       ];

       $request->validate([
           'name' => 'required|string|max:100',
           'email' => 'required|email',
           'username' => ['required', 'regex:/^[a-zA-Z0-9]+$/'],
           'password' => 'required|min:6',
       ], $customMessages);

       return redirect()->route('register.show')->with('success', 'Registrasi berhasil!');
   }
   ```
3. Buat view `register.blade.php` yang menampilkan pesan sukses dan error per field.

---

### **2.3 Praktikum 3 — Multi-Step Form dengan Session**

1. Tambahkan route untuk step 1–3 + summary + complete.
2. Buat controller `MultiStepFormController` yang pada setiap step:
   - validasi data
   - simpan ke session
   - redirect ke step berikutnya
3. Buat view Blade per step (`multistep/step1.blade.php`, `step2`, `step3`, `summary`, `complete`) dengan layout Bootstrap dan progress bar.

---

## **3. Hasil Pengujian**

> Format hasil dibuat seperti yang kamu minta sebelumnya: **URL → output** lalu diikuti **penjelasan umum**.

### **3.1 Praktikum 1 — Form Dasar**

**Hasil Pengujian:**
- `http://127.0.0.1:8000/form` → menampilkan **Form Registrasi** berisi input: nama, email, umur, password, jenis kelamin (radio), role (select), bio (textarea), dan checkbox konfirmasi.  
- Submit form **tanpa isi apa pun** → kembali ke halaman form yang sama dan muncul pesan error merah di bawah setiap field yang kosong (**“The name field is required.”**, dll).  
- Submit form **tanpa centang checkbox konfirmasi** → tombol submit tetap nonaktif (tidak bisa dikirim) sampai checkbox dicentang.  
- Submit form **dengan data lengkap dan benar** → diarahkan ke `http://127.0.0.1:8000/result` dan halaman menampilkan daftar data yang tadi dikirim.  

**Penjelasan Umum:**  
Pengujian ini menunjukkan bahwa validasi Laravel di controller bekerja normal. Saat data tidak valid, Laravel otomatis:
1. me-*redirect* kembali ke halaman form,
2. mengirim error ke view,
3. dan meng-*flash* old input sehingga field terisi ulang.  
Selain itu, penggunaan `@csrf` di form membuat request POST diterima oleh Laravel (tidak error 419). Mekanisme ini sudah sesuai lifecycle form Laravel: **form → request → validasi → redirect → view**.

---

### **3.2 Praktikum 2 — Validasi & Pesan Error Kustom**

**Hasil Pengujian:**
- `http://127.0.0.1:8000/register` → menampilkan **Form Register** dengan field: nama, email, username, password.  
- Submit form **kosong** → muncul pesan:
  - **“Kami perlu tahu nama Anda!”**  
  - **“Email Anda penting bagi kami.”**  
  - **“Username hanya boleh berisi huruf dan angka.”** (kalau diisi karakter aneh)
  - **“Jangan lupa untuk set password.”**
- Submit form **dengan email tidak valid** (misal: `adha@abc`) → muncul pesan: **“Hmm... itu tidak terlihat seperti email yang valid.”**  
- Submit form **dengan semua data benar** → redirect ke halaman yang sama dan muncul alert hijau: **“Registrasi berhasil!”**

**Penjelasan Umum:**  
Bagian ini membuktikan bahwa pesan error Laravel bisa di-*override* sesuai kebutuhan aplikasi/kampus. Bukan hanya itu, kita juga bisa pakai **regex** di validasi untuk membatasi format username. Ini sering dipakai pada aplikasi real (misal: username hanya huruf+angka). Dengan cara ini, pengalaman pengguna jadi lebih jelas karena pesan error tidak kaku seperti bawaan Laravel.

---

### **3.3 Praktikum 3 — Multi-Step Form (Session Based)**

**Hasil Pengujian:**
- `http://127.0.0.1:8000/multistep` → menampilkan **Step 1: Informasi Pribadi** dengan progress bar dan form isian nama, email, telepon, dan alamat.  
- Submit Step 1 tanpa isi → tetap di Step 1 dan muncul pesan error di field yang kosong.  
- Submit Step 1 dengan benar → **redirect ke** `http://127.0.0.1:8000/multistep/step2`.  
- `http://127.0.0.1:8000/multistep/step2` → menampilkan **Step 2: Informasi Pendidikan**. Jika pengguna langsung akses step 2 tanpa step 1 → diarahkan balik ke step 1 (berhasil mencegah loncat step).  
- Submit Step 2 dengan data valid → **redirect ke** `http://127.0.0.1:8000/multistep/step3`.  
- `http://127.0.0.1:8000/multistep/step3` → menampilkan form pengalaman kerja.  
- Submit Step 3 → **redirect ke** `http://127.0.0.1:8000/multistep/summary` → halaman ringkasan menampilkan **gabungan semua data dari step 1, 2, dan 3**.  
- Klik tombol **Kirim Data** → menampilkan halaman sukses (`/multistep/complete`) dengan pesan bahwa pendaftaran berhasil.

**Penjelasan Umum:**  
Hasil ini menunjukkan bahwa:
1. **session Laravel** bisa dipakai untuk menyimpan data antar langkah,
2. **validasi per-langkah** tetap bisa dilakukan,
3. user tidak bisa meloncat ke step berikutnya tanpa menyelesaikan step sebelumnya,
4. tampilan ringkasan (summary) membantu user mengecek ulang sebelum data benar-benar disimpan.  
Pola ini cocok untuk form panjang (pendaftaran kerja, beasiswa, data mahasiswa baru, dsb).

---

## **4. Kesimpulan**

1. Laravel menyediakan alur form submission yang **lengkap dan aman**: ada CSRF, validasi, old input, dan error bag.
2. Validasi bisa dibuat **sangat fleksibel**: dari yang paling sederhana (`required`, `email`) sampai yang kustom dengan pesan sendiri.
3. Menampilkan error di Blade sangat mudah karena Laravel otomatis mengirim objek `$errors` setelah validasi gagal.
4. Multi-step form menunjukkan bahwa Laravel bisa menangani form panjang tanpa harus langsung menyimpan ke database — cukup pakai session dulu.
5. Dengan kombinasi **form + validasi + session + redirect** seperti di modul ini, mahasiswa sudah siap ke materi berikutnya: **CRUD yang form-nya benar-benar disimpan ke database**.

---

## **5. Referensi**

- Dokumentasi Resmi Laravel 12 — *Validation*, *Requests*, *CSRF Protection*  
- Modul 5 — *Form Submission & Data Validation* — HackMD (Muhammad Reza Zulman)  
- Laravel Docs: Redirecting & Flashing Input  

