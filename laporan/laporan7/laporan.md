# Laporan Modul 7: Eloquent Relationship & Pagination

**Mata Kuliah:** Workshop Web Lanjut  
**Nama:** Adha Gusti Harmadhan  
**NIM:** 2024573010009  
**Kelas:** 2B TI

---

## Abstrak

Laporan ini menjelaskan hasil praktikum pada Modul 7: _Eloquent Relationship & Pagination_ dalam mata kuliah Workshop Web Lanjut. Fokus praktikum adalah memahami bagaimana Laravel mendefinisikan relasi antar tabel menggunakan Eloquent ORM (One-to-One, One-to-Many, dan Many-to-Many), serta bagaimana membagi data menjadi beberapa halaman menggunakan fitur pagination bawaan Eloquent. Praktikum dibagi menjadi dua: (1) membangun aplikasi _complex relationships_ yang menghubungkan model User, Profile, Post, dan Tag dengan berbagai jenis relasi; (2) membangun aplikasi sederhana daftar produk yang menampilkan data Product dengan pagination menggunakan Tailwind. Melalui praktikum ini mahasiswa diharapkan mampu memetakan konsep relasi pada ERD ke dalam Eloquent Relationship, serta memahami pentingnya pagination untuk performa aplikasi dan pengalaman pengguna.

---

## 1. Dasar Teori

### 1.1 Eloquent Relationship

Dalam aplikasi berbasis database, tabel-tabel biasanya saling berhubungan. Di Laravel, hubungan antar tabel tersebut direpresentasikan melalui **Eloquent Relationship** di level model. Dengan relationship, kita bisa mengakses data terkait cukup dengan memanggil properti pada objek model tanpa menulis query SQL manual.

Beberapa jenis relasi yang umum:

- **One-to-One** → satu data di tabel A hanya berpasangan dengan satu data di tabel B.  
- **One-to-Many** → satu data di tabel A berpasangan dengan banyak data di tabel B.  
- **Many-to-Many** → banyak data di tabel A bisa berpasangan dengan banyak data di tabel B melalui tabel pivot.

Relationship memudahkan pengembangan karena:

- Sintaks lebih deklaratif dan mudah dibaca.  
- Mendukung _eager loading_ (`with()`) untuk menghemat query.  
- Mengurangi penggunaan query mentah atau join yang rumit.

### 1.2 Relasi One-to-One

Relasi **One-to-One** digunakan ketika satu baris di sebuah tabel hanya terkait dengan satu baris di tabel lain. Contoh klasik: `users` dan `profiles` — satu user hanya punya satu profile, dan sebaliknya.

Skema:

- `users` → menyimpan data user  
- `profiles` → menyimpan data profil, memiliki kolom `user_id` unik

Contoh di model:

```php
// User.php
public function profile()
{
    return $this->hasOne(Profile::class);
}

// Profile.php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Dengan ini, cukup memanggil `$user->profile` untuk mendapatkan profil user tersebut.

### 1.3 Relasi One-to-Many

Relasi **One-to-Many** dipakai ketika sebuah data di tabel A bisa memiliki banyak data di tabel B. Contoh: `users` dan `posts` — satu user bisa menulis banyak postingan, sedangkan satu post hanya dimiliki satu user.

Skema:

- `users`  
- `posts` (punya `user_id` sebagai foreign key)

Contoh di model:

```php
// User.php
public function posts()
{
    return $this->hasMany(Post::class);
}

// Post.php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Di view kita bisa looping `@foreach($user->posts as $post)` untuk menampilkan semua post milik user.

### 1.4 Relasi Many-to-Many dan Tabel Pivot

Relasi **Many-to-Many** terjadi saat banyak data di tabel A bisa berhubungan dengan banyak data di tabel B. Contoh: `posts` dan `tags` — satu post bisa punya banyak tag, dan satu tag bisa digunakan di banyak post.

Untuk ini dibutuhkan **tabel pivot**, misalnya `post_tag` yang menyimpan pasangan `post_id` dan `tag_id`. Konvensi Laravel: nama tabel pivot menggunakan **bentuk tunggal** dan urut alfabetis, misalnya `post_tag` (bukan `posts_tags`).

Contoh di model:

```php
// Post.php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}

// Tag.php
public function posts()
{
    return $this->belongsToMany(Post::class);
}
```

Dengan ini, `$post->tags` akan mengembalikan semua tag milik sebuah post.

### 1.5 Eager Loading dengan `with()`

Jika kita memanggil relasi di dalam loop (misalnya `$user->profile` berulang-ulang), bisa terjadi masalah **N+1 query**. Untuk mencegah ini, Laravel menyediakan _eager loading_ dengan method `with()`.

Contoh:

```php
$users = User::with('profile', 'posts')->get();
```

Perintah ini mengambil data user sekaligus data profile dan posts-nya dalam beberapa query yang lebih efisien.

### 1.6 Pagination di Laravel

**Pagination** adalah proses membagi data menjadi beberapa halaman, misalnya 10 item per halaman. Di Laravel, pagination bisa dilakukan dengan method `paginate()` pada query Eloquent.

Contoh:

```php
$products = Product::orderBy('id', 'desc')->paginate(10);
```

Di view, kita cukup memanggil:

```blade
{{ $products->links() }}
```

Laravel akan otomatis menghasilkan link pagination dengan styling Tailwind CSS. Pagination membantu:

- Mengurangi beban server dan waktu loading karena data tidak diambil semua.  
- Membuat tampilan lebih rapi dan mudah dinavigasi.  
- Meningkatkan UX terutama pada data yang jumlahnya besar.

---

## 2. Langkah-Langkah Praktikum

### 2.1 Praktikum 1 — Eloquent ORM Relationships: One-to-One, One-to-Many, Many-to-Many


**Langkah-langkah:**

1. Buat dan buka proyek Laravel baru:

   ![gambar1](./gambar/gambar1.png)

2. Pastikan ekstensi MySQL aktif (`mysqli`, `pdo_mysql`) dan buat database baru `eloquentrelation_db` di MySQL atau phpMyAdmin.  
   ![gambar2](./gambar/gambar2.png)

3. Install dependency database dan konfigurasi `.env`:

   ![gambar3](./gambar/gambar3.png)

   Atur koneksi database di `.env`:

   ![gambar3](./gambar/gambar3.1.png)

4. Buat migration untuk `profiles`, `posts`, `tags`, dan `post_tag`:

   ```bash
   php artisan make:migration create_profiles_table
   php artisan make:migration create_posts_table
   php artisan make:migration create_tags_table
   php artisan make:migration create_post_tag_table
   ```

   Lalu perbarui masing-masing file migration untuk menambahkan foreign key dan struktur tabel.  
   
   profiles_table

   ![gambar4](./gambar/gambar4.png) 

   posts_table  

   ![gambar4](./gambar/gambar4.1.png)  

   tags_table

   ![gambar4](./gambar/gambar4.2.png)  

   post_tag_table

   ![gambar4](./gambar/gambar4.3.png)

   
5. Jalankan migrasi untuk membuat tabel di database:

   ![gambar5](./gambar/gambar5.png)

6. Buat model `Profile`, `Post`, dan `Tag`:

   ![gambar6](./gambar/gambar6.png)

   `profiles.php`

   ![gambar4](./gambar/gambar6.1.png) 

   `posts.php`  

   ![gambar4](./gambar/gambar6.2.png)  

   `tag.php`

   ![gambar4](./gambar/gambar6.3.png) 

7. Buat dan atur seeder di `DatabaseSeeder` untuk mengisi data awal:

   ![gambarseeder](./gambar/seeder1.png)

      Jalankan seeder:

   ```bash
   php artisan db:seed
   ```

8. Buat controller `UserController` dan `PostController`:

   ![gambar8](./gambar/gambar7.png) `

   Tambahkan method `index` dan `show` untuk masing-masing:  
   - `UserController` memanggil `User::with('profile','posts')->get()`.  
   ![gambar8](./gambar/gambar8.png) 

   - `PostController` memanggil `Post::with('user','tags')->get()`.  
   ![gambar8](./gambar/gambar8.1.png)  
  
9. Daftarkan route di `routes/web.php`:

   ![gambar9](./gambar/gambar9.png)

10. Buat layout `resources/views/layouts/app.blade.php` yang memuat Bootstrap dan menu navigasi Users & Posts.  
    ![gambar10](./gambar/gambar10.png)

11. Buat view untuk users:  

    - `resources/views/users/index.blade.php` untuk daftar user.  
      ![gambar11](./gambar/gambar11.png)

    - `resources/views/users/show.blade.php` untuk detail user, profile, dan daftar post miliknya.  
      ![gambar11](./gambar/gambar11.1.png)

12. Buat view untuk posts:  

    - `resources/views/posts/index.blade.php` untuk daftar post dan nama penulisnya.  
      ![gambar12](./gambar/gambar12.png)

    - `resources/views/posts/show.blade.php` untuk detail post, author, dan daftar tag.  
      ![gambar12](./gambar/gambar12.1.png)

---

**Hasil Pengujian:**

- `http://127.0.0.1:8000/users` → menampilkan daftar user dan emailnya.  
  ![hasil1](./gambar/gambarhasil1.png)

- Klik salah satu user → `http://127.0.0.1:8000/users/{user}` menampilkan informasi user, bio & website dari `profile`, serta daftar post milik user tersebut.  
  ![hasil1_detail](./gambar/gambarhasil2.png)

- `http://127.0.0.1:8000/posts` → menampilkan daftar post dan nama user yang menjadi author.  
  ![hasil1_posts](./gambar/gambarhasil3.png)

- Klik salah satu post → `http://127.0.0.1:8000/posts/{post}` menampilkan detail post, author, dan daftar tag Many-to-Many.  
  ![hasil1_post_detail](./gambar/gambarhasil4.png)

---

### 2.2 Praktikum 2 — Pagination dengan Eloquent ORM

Pada praktik kedua, fokusnya adalah cara melakukan pagination daftar produk menggunakan model `Product` dan method `paginate()` dari Eloquent, dengan tampilan yang memanfaatkan Tailwind CSS.

**Tujuan praktik (implisit):**  
Latihan menampilkan data dalam jumlah banyak dengan pagination Laravel, agar tampilan lebih rapi, loading lebih cepat, dan UX tetap nyaman.

---

**Langkah-langkah:**

1. Buat proyek baru:

   ![gambar13](./gambar/gambar13.png)

2. Buat database `pagination_db` di MySQL atau phpMyAdmin dan konfigurasi koneksi di `.env`, mirip praktikum pertama:

   ```env
   DB_DATABASE=pagination_db
   ```
   ![gambar14](./gambar/gambar14.png)

   Install dependency database dan clear config:

   ```bash
   composer require doctrine/dbal
   php artisan config:clear
   ```

3. Buat model dan migration untuk `Product`:

   ![gambar15](./gambar/gambar15.png)

   Perbarui migration `create_products_table` dengan kolom `name` dan `price`, kemudian jalankan migrasi:

   ```bash
   php artisan migrate
   ```
   ![gambar15](./gambar/gambar15.1.png)

4. Buat seeder dan factory untuk menghasilkan data dummy:

   ![gambarseeder](./gambar/seeder2.png)

   ![gambarfactory](./gambar/factory.png)

   - Di `Product.php`, tambahkan `HasFactory` dan `$fillable`.  

   ![gambar15](./gambar/gambar15.2.png)

   - Di `ProductFactory`, generate nama produk dan harga acak.  

   ![gambarfactory](./gambar/factory2.png)
   
   - Di `ProductSeeder`, panggil `Product::factory()->count(50)->create();` 

   ![gambarseeder](./gambar/seeder3.png)

   - Di `DatabaseSeeder`, panggil `ProductSeeder`.

   ![gambarseeder](./gambar/seeder4.png)

   Jalankan seeder:

   ![gambar16](./gambar/gambar16.png)  

5. Buat `ProductController`:

  ![gambar17](./gambar/gambar17.png)

   Tambahkan method `index` untuk mengambil data produk dengan pagination:

   ![gambar17](./gambar/gambar17.1.png)

6. Daftarkan route di `routes/web.php`:

   ![gambar17](./gambar/gambar17.2.png)

7. Buat view `resources/views/products/index.blade.php` untuk menampilkan daftar produk dengan Tailwind dan link pagination:

   ![gambar18](./gambar/gambar18.png)

   View ini menampilkan tabel produk dan memanggil `{{ $products->links() }}` untuk menampilkan navigasi pagination.

---

**Hasil Pengujian:**

- `http://127.0.0.1:8000/products` → menampilkan daftar produk dari database dalam bentuk tabel, 10 item per halaman.  
  ![hasil2](./gambar/gambar2hasil1.png)

- Link pagination (nomor halaman, Next, Previous) muncul otomatis di bawah tabel dan dapat diklik untuk berpindah halaman.  
  ![hasil2_pagination](./gambar/gambar2hasil2.png)

---

## 3. Hasil dan Pembahasan

1. **Relasi One-to-One (User–Profile)** pada praktikum pertama memperlihatkan bagaimana satu user dikaitkan dengan tepat satu profile melalui foreign key `user_id` yang unik di tabel `profiles`. Dengan `hasOne()` dan `belongsTo()`, pengambilan data menjadi sangat sederhana dan tidak membutuhkan join manual.

2. **Relasi One-to-Many (User–Posts)** menunjukkan bahwa satu user dapat memiliki banyak post. Di controller dan view, kita cukup memanfaatkan `$user->posts` untuk menampilkan seluruh tulisan user. Ini sangat mirip dengan kasus nyata seperti sistem blog atau forum.

3. **Relasi Many-to-Many (Post–Tags)** memperkenalkan penggunaan tabel pivot `post_tag`. Dengan `belongsToMany()` di kedua model, proses menempelkan tag ke post dan mengambil ulang data tag menjadi jauh lebih mudah. Praktikum ini juga menegaskan pentingnya konvensi penamaan tabel pivot di Laravel.

4. Penggunaan **_eager loading_** (`with('profile', 'posts')` dan `with('user', 'tags')`) membuat akses data relasi menjadi lebih efisien, menghindari masalah N+1 query yang sering muncul jika relasi dipanggil berulang di dalam loop.

5. Pada **pratikum pagination**, mahasiswa melihat bagaimana data yang jumlahnya banyak sebaiknya tidak ditampilkan sekaligus. Dengan `Product::orderBy(...)->paginate(10)`, sistem otomatis membagi data per halaman dan menghasilkan link navigasi. Ini tidak hanya meningkatkan performa, tetapi juga membuat tampilan lebih user-friendly.

6. Secara keseluruhan, kedua praktikum ini memperlihatkan bagaimana **relasi data** dan **pagination** saling melengkapi pada aplikasi Laravel yang riil: relasi untuk memetakan struktur data yang kompleks, dan pagination untuk mengatur cara penyajian data tersebut kepada pengguna.

---

## 4. Kesimpulan

Dari praktikum Modul 7 ini dapat disimpulkan bahwa:

1. **Eloquent Relationship** menyediakan cara yang deklaratif dan rapi untuk mendefinisikan hubungan antar model seperti One-to-One, One-to-Many, dan Many-to-Many tanpa perlu query SQL yang rumit.
2. **Tabel pivot** adalah komponen penting dalam relasi Many-to-Many dan Laravel menyediakan dukungan langsung melalui method `belongsToMany()`.
3. Penggunaan **_eager loading_** dengan `with()` sangat membantu mengoptimasi query dan mencegah terjadinya N+1 query problem ketika bekerja dengan banyak relasi.
4. **Pagination** dengan `paginate()` mempermudah pembagian data ke beberapa halaman, meningkatkan performa dan pengalaman pengguna, serta otomatis terintegrasi dengan Tailwind CSS untuk tampilan link pagination.
5. Dengan menggabungkan relasi Eloquent dan pagination, mahasiswa dapat membangun aplikasi Laravel yang _scalable_, efisien, dan tetap mudah dipelihara baik dari sisi struktur kode maupun dari sisi tampilan data.

---

## 5. Referensi

- Modul 7 - Eloquent Relationship & Pagination — (https://hackmd.io/@mohdrzu/ryIIM1a0ll)  
- Dokumentasi Resmi Laravel 12 — (https://laravel.com/docs/12.x/eloquent-relationships)  
- Dokumentasi Pagination Laravel — (https://laravel.com/docs/12.x/pagination)
