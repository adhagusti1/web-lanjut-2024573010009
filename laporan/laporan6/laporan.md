# Laporan Modul 6: Model dan Laravel Eloquent

**Mata Kuliah:** Workshop Web Lanjut  
**Nama:** Adha Gusti Harmadhan  
**NIM:** 2024573010009  
**Kelas:** 2B TI

---

## Abstrak

Laporan ini menjelaskan hasil praktikum pada Modul 6: _Model dan Laravel Eloquent_ dalam mata kuliah Workshop Web Lanjut. Fokus praktikum adalah memahami bagaimana Laravel memetakan data ke dalam model, bagaimana Eloquent ORM bekerja untuk operasi CRUD, serta bagaimana pola-pola pendukung seperti DTO dan Repository dapat dipakai untuk merapikan kode. Praktikum dibagi menjadi tiga: (1) binding form ke model sederhana tanpa database, (2) penggunaan Data Transfer Object (DTO) dan service layer, dan (3) membangun aplikasi Todo CRUD menggunakan Eloquent dan MySQL. Melalui percobaan ini mahasiswa diharapkan mampu menghubungkan teori MVC dengan implementasi Laravel yang riil, khususnya pada sisi _Model_ dan interaksinya dengan database.

---

## 1. Dasar Teori

### 1.1 Model dalam Laravel

Dalam arsitektur MVC, **Model** adalah bagian yang mewakili data dan aturan bisnis. Di Laravel, model biasanya berada di folder `app/Models` dan secara default akan terhubung ke tabel yang namanya jamak dari nama model — misalnya model `Product` ke tabel `products`. Model inilah yang berkomunikasi dengan database menggunakan **Eloquent ORM** sehingga kita tidak perlu menulis query SQL mentah setiap kali ingin mengambil atau menyimpan data.

### 1.2 Eloquent ORM

**Eloquent** adalah ORM bawaan Laravel yang menyediakan cara berinteraksi dengan database secara _object-oriented_. Setiap baris pada tabel direpresentasikan sebagai objek model. Operasi umum seperti `all()`, `find()`, `create()`, `update()`, dan `delete()` sudah disediakan sehingga kode jadi lebih singkat dan mudah dibaca.
Contoh model sederhana:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'stock'];
}
```

Properti `$fillable` digunakan agar field tersebut boleh di-_mass assign_ saat pemanggilan `Product::create([...])`.

### 1.3 POCO / ViewModel

Sebelum masuk ke database, kadang kita hanya butuh “wadah data” dari form. Pada praktikum pertama, kita memakai kelas PHP biasa (gaya **POCO / ViewModel**) untuk menampung data produk tanpa menyimpannya ke database. Ini berguna kalau kita ingin latihan alur request → controller → view tapi database belum disiapkan.

### 1.4 Data Transfer Object (DTO)

**DTO** dipakai untuk memindahkan data dari lapisan request ke lapisan service atau ke controller lain dalam bentuk yang sudah rapi. Keuntungan memakai DTO:

- data yang masuk lebih terkontrol,
- memisahkan data mentah dari logika bisnis,
- kode controller jadi lebih pendek.

### 1.5 Repository Pattern (sekilas)

Repository dipakai untuk mengabstraksi akses data. Di Laravel, ini cocok kalau nanti aplikasi mulai besar dan kita ingin ganti sumber data (MySQL → API, dsb.) tanpa mengubah controller.

### 1.6 Migrasi, Seeder, dan Eloquent

Untuk praktikum Todo, kita sudah mulai pakai fitur database Laravel:

- **Migration** → membuat struktur tabel dengan kode
- **Seeder** → mengisi data awal
- **Model Eloquent** → mengakses tabel memakai class
- **Controller** → memanggil model dan melempar ke view

Dengan cara ini, alur _Model layer_ Laravel bisa dilihat utuh dari atas ke bawah.

---

## 2. Langkah-Langkah Praktikum

### 2.1 Praktikum 1 — Binding Form ke Model Sederhana (tanpa DB)

**Tujuan praktik (implisit):** latihan mengikat data form ke objek model sederhana agar alur request → controller → view dipahami dulu sebelum pakai Eloquent.

**Langkah-langkah:**

1. Buat proyek baru:  
   ![gambar1](./gambar/gambar1.png)

2. Buat ViewModel: `app/ViewModels/ProductViewModel.php` (kode di laporan utama).  
   ![gambar2](./gambar/gambar2.png)

3. Buat `ProductController`
   ![gambar3](./gambar/gambar3.png)

   dan tambah method `create` & `result`.  
   ![gambar3](./gambar/gambar3.1.png)

4. Daftarkan route di `routes/web.php` untuk form & result.  
   ![gambar4](./gambar/gambar4.png)

5. Buat view form `resources/views/product/create.blade.php`.  
   ![gambar5](./gambar/gambar5.png)

6. Buat view hasil `resources/views/product/result.blade.php`.  
   ![gambar6](./gambar/gambar6.png)

**Hasil Pengujian:**

- `/product/create` → menampilkan form input produk.  
  ![hasil1](./gambar/gambarhasil1.png)
- Submit → `/product/result` menampilkan data yang dikirim (tanpa penyimpanan DB).  
  ![hasil1_result](./gambar/gambarhasil2.png)

### 2.2 Praktikum 2 — Menggunakan DTO dan Service

Pada praktik kedua, alurnya mirip praktikum 1, tetapi datanya tidak langsung dipakai oleh view. Data yang dikirim form dimasukkan dulu ke **DTO**, lalu diproses oleh **service** supaya controller tetap tipis.

**Langkah-langkah:**

1. Buat proyek baru :  
   ![gambar7](./gambar/gambar7.png)

2. Buat DTO: `app/DTO/ProductDTO.php`.
   ![gambar8](./gambar/gambar8.png)
3. Buat service: `app/Services/ProductService.php`.  
   ![gambar8](./gambar/gambar8.1.png)
4. Buat `ProductController` Update controller untuk menggunakan DTO dan Service.  
   ![gambar3](./gambar/gambar9.png)

5. Route dan view dapat tetap seperti praktikum 1, hanya hasil yang diolah oleh service.  
   ![gambar10](./gambar/gambar10.png)
   ![gambar10](./gambar/gambar10.1.png)
   ![gambar10](./gambar/gambar10.2.png)

**Hasil Pengujian:**

- `/product/create` → form sama.  
  ![hasil2_create](./gambar/gambar2hasil1.png)
- Submit → hasil ditampilkan menggunakan data dari `ProductService`.  
  ![hasil2_result](./gambar/gambar2hasil2.png)

---

### 2.3 Praktikum 3 — Todo CRUD dengan Eloquent dan MySQL

Bagian ini sudah mulai memakai **model Eloquent** plus **migration + seeder**.

**Langkah-langkah utama:**

1. Buat project baru :  
   ![gambar11](./gambar/gambar11.png)

   kemudian Install dependency MySQL:
   ```bash 
   composer require doctrine/dbal
   ```
   Tak lupa juga membuat database tododb

2. Atur database di `.env`.  
   ![gambar12](./gambar/gambar12.png)

3. Buat migration `create_todos_table` 
   ```bash
   php artisan make:migration create_todos_table
   ```
   Lalu isi file yang dihasilkan di database/migrations/YYYY_MM_DD_create_todos_table.php dan perbarui
   ![gambar13](./gambar/gambar13.png)

   lalu jalankan 
   ```bash
   php artisan migrate
   ```
4. Buat seeder `TodoSeeder` Jalankan perintah ini untuk membuat seeder: 
   ```bash
   php artisan make:seeder TodoSeeder
   ```
   Buka file yang dihasilkan di database/seeders/TodoSeeder.php dan perbarui:
   ![gambar14](./gambar/gambar14.png)

   Lalu jalankan seeder  
   ```bash
   php artisan db:seed --class=TodoSeeder
   ```

5. Buat model `Todo` 
   ```bash
   php artisan make:model Todo
   ```
   Buka file yang dihasilkan di app/Models/Todo.php dan perbarui:
   ![gambar15](./gambar/gambar15.png)

6. Buat `TodoController`     
   ![gambar16](./gambar/gambar16.png)

   Dan tambhakan controller tersebut ke route
   ![gambar16](./gambar/gambar16.1.png)

7. Buat Layout lewat file `app.blade.php` di folder layout
   ![gambarlayout](./gambar/gambarlayout.png)

   Kemudian buat view di `resources/views/todos/*` (index, create, edit, show).  
   
   index
   ![gambar17](./gambar/gambar17.png)

   create
   ![gambar17](./gambar/gambar17.1.png)

   edit
   ![gambar17](./gambar/gambar17.2.png)

   show
   ![gambar17](./gambar/gambar17.3.png)

**Hasil Pengujian:**

- `/` → menampilkan daftar todo dari database.  
  ![hasil3_index](./gambar/gambar3hasil1.png)
- `/todos/create` → form tambah task, submit menyimpan ke DB.  
  ![hasil3_create](./gambar/gambar3hasil2.png)
- Edit / Delete bekerja sesuai ekspektasi.  
  ![hasil3_edit](./gambar/gambar3hasil3.png)

## 3. Hasil dan Pembahasan

1. **Praktikum 1 (tanpa database)** memperkenalkan konsep model sebagai _data holder_ sederhana. `ProductViewModel` membantu memahami bahwa model tidak selalu harus terhubung ke tabel database — fungsinya bisa sekadar menampung data yang dikirim dari form.

2. **Praktikum 2 (DTO dan Service)** memperlihatkan penerapan pola _clean architecture_ di Laravel. Dengan memisahkan data ke `ProductDTO` dan logika ke `ProductService`, controller jadi lebih ringkas, mudah diuji, dan mudah dikembangkan jika nanti logikanya bertambah.

3. **Praktikum 3 (CRUD Eloquent dan MySQL)** adalah penerapan konsep _Model_ yang sesungguhnya di Laravel. Semua operasi database seperti tambah, ubah, hapus, dan tampil dilakukan lewat Eloquent ORM, tanpa perlu menulis query SQL secara manual.

4. Secara keseluruhan, dari ketiga praktikum ini mahasiswa dapat melihat transisi yang jelas dari model sederhana tanpa database, ke model dengan arsitektur DTO–Service, hingga ke model penuh dengan Eloquent ORM yang terhubung langsung ke database MySQL. Hal ini memperkuat pemahaman konsep MVC Laravel dari sisi Model secara menyeluruh.

---

## 4. Kesimpulan

Dari praktikum Modul 6 ini dapat disimpulkan bahwa:

1. **Model di Laravel tidak selalu berarti Eloquent** — kita bisa mulai dari kelas PHP biasa (ViewModel/POCO) untuk menampung data form.
2. **DTO membantu merapikan alur data** dari request ke lapisan lain sehingga controller lebih bersih dan mudah diuji.
3. **Eloquent ORM mempermudah CRUD** karena kita cukup memanggil method bawaan tanpa menulis SQL manual.
4. **Migration dan seeder** membuat struktur database dan data awal bisa dikelola lewat kode dan mudah diulang di lingkungan lain.
5. Dengan menggabungkan controller, route, view, dan model, mahasiswa jadi melihat alur penuh MVC Laravel dari form sampai tersimpan ke database.

---

## 5. Referensi

- Modul 6 - Model dan Laravel Eloquent — (https://hackmd.io/@mohdrzu/ryIIM1a0ll)
- Dokumentasi Resmi Laravel 12 — (https://laravel.com/docs/12.x/eloquent)


