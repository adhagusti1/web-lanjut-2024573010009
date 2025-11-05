# Laporan Modul 6: Model dan Laravel Eloquent

**Mata Kuliah:** Workshop Web Lanjut  
**Nama:** Adha Gusti Harmadhan  
**NIM:** 2024573010009  
**Kelas:** 2B TI

---

## Abstrak

Laporan ini menjelaskan hasil praktikum pada Modul 6: *Model dan Laravel Eloquent* dalam mata kuliah Workshop Web Lanjut. Fokus praktikum adalah memahami bagaimana Laravel memetakan data ke dalam model, bagaimana Eloquent ORM bekerja untuk operasi CRUD, serta bagaimana pola-pola pendukung seperti DTO dan Repository dapat dipakai untuk merapikan kode. Praktikum dibagi menjadi tiga: (1) binding form ke model sederhana tanpa database, (2) penggunaan Data Transfer Object (DTO) dan service layer, dan (3) membangun aplikasi Todo CRUD menggunakan Eloquent dan MySQL. Melalui percobaan ini mahasiswa diharapkan mampu menghubungkan teori MVC dengan implementasi Laravel yang riil, khususnya pada sisi *Model* dan interaksinya dengan database.

---

## 1. Dasar Teori

### 1.1 Model dalam Laravel
Dalam arsitektur MVC, **Model** adalah bagian yang mewakili data dan aturan bisnis. Di Laravel, model biasanya berada di folder `app/Models` dan secara default akan terhubung ke tabel yang namanya jamak dari nama model — misalnya model `Product` ke tabel `products`. Model inilah yang berkomunikasi dengan database menggunakan **Eloquent ORM** sehingga kita tidak perlu menulis query SQL mentah setiap kali ingin mengambil atau menyimpan data.

### 1.2 Eloquent ORM
**Eloquent** adalah ORM bawaan Laravel yang menyediakan cara berinteraksi dengan database secara *object-oriented*. Setiap baris pada tabel direpresentasikan sebagai objek model. Operasi umum seperti `all()`, `find()`, `create()`, `update()`, dan `delete()` sudah disediakan sehingga kode jadi lebih singkat dan mudah dibaca.
Contoh model sederhana:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'stock'];
}
```

Properti `$fillable` digunakan agar field tersebut boleh di-*mass assign* saat pemanggilan `Product::create([...])`.

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

Dengan cara ini, alur *Model layer* Laravel bisa dilihat utuh dari atas ke bawah.

---

## 2. Langkah-Langkah Praktikum

### 2.1 Praktikum 1 — Binding Form ke Model Sederhana (tanpa DB)

**Tujuan praktik (implisit):** latihan mengikat data form ke objek model sederhana agar alur request → controller → view dipahami dulu sebelum pakai Eloquent.

**Langkah-langkah:**

1. **Buat proyek baru** (opsional kalau gabung dengan modul sebelumnya):

   ```bash
   laravel new model-app
   cd model-app
   ```

2. **Buat ViewModel** untuk menampung data produk: `app/ViewModels/ProductViewModel.php`

   ```php
   <?php
   namespace App\ViewModels;

   class ProductViewModel
   {
       public string $name;
       public float $price;
       public string $description;

       public function __construct(string $name = '', float $price = 0, string $description = '')
       {
           $this->name = $name;
           $this->price = $price;
           $this->description = $description;
       }

       public static function fromRequest(array $data): self
       {
           return new self(
               $data['name'] ?? '',
               (float)($data['price'] ?? 0),
               $data['description'] ?? ''
           );
       }
   }
   ```

3. **Buat controller**: `php artisan make:controller ProductController` lalu isi:

   ```php
   namespace App\Http\Controllers;

   use Illuminate\Http\Request;
   use App\ViewModels\ProductViewModel;

   class ProductController extends Controller
   {
       public function create()
       {
           return view('product.create');
       }

       public function result(Request $request)
       {
           $product = ProductViewModel::fromRequest($request->all());
           return view('product.result', compact('product'));
       }
   }
   ```

4. **Daftarkan route** di `routes/web.php`:

   ```php
   use App\Http\Controllers\ProductController;

   Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
   Route::post('/product/result', [ProductController::class, 'result'])->name('product.result');
   ```

5. **Buat view form**: `resources/views/product/create.blade.php`

   ```html
   <!DOCTYPE html>
   <html>
   <head>
       <title>Create Product</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   </head>
   <body class="container py-5">
       <h2>Create Product (No Database)</h2>
       <form method="POST" action="{{ route('product.result') }}">
           @csrf
           <div class="mb-3">
               <label class="form-label">Name</label>
               <input name="name" class="form-control" required>
           </div>
           <div class="mb-3">
               <label class="form-label">Price</label>
               <input name="price" type="number" step="0.01" class="form-control" required>
           </div>
           <div class="mb-3">
               <label class="form-label">Description</label>
               <textarea name="description" class="form-control"></textarea>
           </div>
           <button type="submit" class="btn btn-primary">Submit Product</button>
       </form>
   </body>
   </html>
   ```

6. **Buat view hasil**: `resources/views/product/result.blade.php`

   ```html
   <!DOCTYPE html>
   <html>
   <head>
       <title>Product Result</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   </head>
   <body class="container py-5">
       <h2>Submitted Product Details</h2>
       <ul class="list-group">
           <li class="list-group-item"><strong>Name:</strong> {{ $product->name }}</li>
           <li class="list-group-item"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</li>
           <li class="list-group-item"><strong>Description:</strong> {{ $product->description }}</li>
       </ul>
       <a href="{{ route('product.create') }}" class="btn btn-link mt-3">Submit Another Product</a>
   </body>
   </html>
   ```

---

### 2.2 Praktikum 2 — Menggunakan DTO dan Service

Pada praktik kedua, alurnya mirip praktikum 1, tetapi datanya tidak langsung dipakai oleh view. Data yang dikirim form dimasukkan dulu ke **DTO**, lalu diproses oleh **service** supaya controller tetap tipis.

**Langkah-langkah:**

1. **Buat folder DTO** dan kelas: `app/DTO/ProductDTO.php`

   ```php
   <?php

   namespace App\DTO;

   class ProductDTO
   {
       public string $name;
       public float $price;
       public string $description;

       public function __construct(string $name, float $price, string $description)
       {
           $this->name = $name;
           $this->price = $price;
           $this->description = $description;
       }

       public static function fromRequest(array $data): self
       {
           return new self(
               $data['name'] ?? '',
               (float)($data['price'] ?? 0),
               $data['description'] ?? ''
           );
       }
   }
   ```

2. **Buat service**: `app/Services/ProductService.php`

   ```php
   <?php

   namespace App\Services;

   use App\DTO\ProductDTO;

   class ProductService
   {
       public function display(ProductDTO $product): array
       {
           return [
               'name' => $product->name,
               'price' => $product->price,
               'description' => $product->description,
           ];
       }
   }
   ```

3. **Controller** (boleh pakai `ProductController` yang lain / dipisah):  

   ```php
   namespace App\Http\Controllers;

   use Illuminate\Http\Request;
   use App\DTO\ProductDTO;
   use App\Services\ProductService;

   class ProductController extends Controller
   {
       public function create()
       {
           return view('product.create');
       }

       public function result(Request $request)
       {
           $dto = ProductDTO::fromRequest($request->all());
           $service = new ProductService();
           $product = $service->display($dto);

           return view('product.result', compact('product'));
       }
   }
   ```

4. **Rute** tetap sama:

   ```php
   Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
   Route::post('/product/result', [ProductController::class, 'result'])->name('product.result');
   ```

5. **View** bisa pakai view dari praktikum 1 (form) dan hasilnya sedikit beda karena yang dikirim array:

   ```html
   <!-- resources/views/product/result.blade.php -->
   <!DOCTYPE html>
   <html>
   <head>
       <title>Product Result</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   </head>
   <body class="container py-5">
       <h2>Product DTO Result</h2>
       <div class="card">
           <div class="card-header">Product Details</div>
           <ul class="list-group list-group-flush">
               <li class="list-group-item"><strong>Name:</strong> {{ $product['name'] }}</li>
               <li class="list-group-item"><strong>Price:</strong> ${{ number_format($product['price'], 2) }}</li>
               <li class="list-group-item"><strong>Description:</strong> {{ $product['description'] }}</li>
           </ul>
       </div>
   </body>
   </html>
   ```

---

### 2.3 Praktikum 3 — Todo CRUD dengan Eloquent dan MySQL

Bagian ini sudah mulai memakai **model Eloquent beneran** plus **migration + seeder**.

**Langkah-langkah utama:**

1. **Buat project**

   ```bash
   laravel new todo-app-mysql
   cd todo-app-mysql
   ```

2. **Set database MySQL** di file `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tododb
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Buat migration** untuk tabel `todos`:

   ```bash
   php artisan make:migration create_todos_table
   ```

   Lalu isi:

   ```php
   Schema::create('todos', function (Blueprint $table) {
       $table->id();
       $table->string('task');
       $table->boolean('completed')->default(false);
       $table->timestamps();
   });
   ```

   Jalankan:

   ```bash
   php artisan migrate
   ```

4. **Buat seeder** untuk data awal:

   ```bash
   php artisan make:seeder TodoSeeder
   ```

   Isi dengan 3 data dummy, lalu jalankan:

   ```bash
   php artisan db:seed --class=TodoSeeder
   ```

5. **Buat model Eloquent**

   ```bash
   php artisan make:model Todo
   ```

   dan isi:

   ```php
   class Todo extends Model
   {
       protected $fillable = ['task', 'completed'];
   }
   ```

6. **Buat controller CRUD**

   ```bash
   php artisan make:controller TodoController
   ```

   lalu isi method: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` seperti di modul.

7. **Definisikan route** di `routes/web.php`:

   ```php
   use App\Http\Controllers\TodoController;

   Route::get('/', [TodoController::class, 'index'])->name('todos.index');
   Route::get('/todos/create', [TodoController::class, 'create'])->name('todos.create');
   Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
   Route::get('/todos/{todo}', [TodoController::class, 'show'])->name('todos.show');
   Route::get('/todos/{todo}/edit', [TodoController::class, 'edit'])->name('todos.edit');
   Route::patch('/todos/{todo}', [TodoController::class, 'update'])->name('todos.update');
   Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

   // Route bawaan dikomentari agar tidak bentrok
   // Route::get('/', function () { return view('welcome'); });
   ```

8. **Buat layout dan view** di `resources/views/layouts/app.blade.php` dan folder `resources/views/todos/` (index, create, edit, show) dengan Bootstrap seperti di modul.

---

## 3. Hasil dan Pembahasan

### Hasil Pengujian:

**Praktikum 1 – Binding Form ke Model Sederhana**  
- `http://127.0.0.1:8000/product/create` → menampilkan halaman **form input produk** berisi kolom *Name*, *Price*, dan *Description*.  
- Saat form disubmit ke `POST http://127.0.0.1:8000/product/result` → muncul halaman hasil yang menampilkan **Name**, **Price**, dan **Description** sesuai data yang dimasukkan.  
- Tidak ada penyimpanan ke database; data hanya dikirim melalui controller dan ditampilkan kembali ke view.

**Praktikum 2 – Menggunakan DTO dan Service**  
- `http://127.0.0.1:8000/product/create` → menampilkan form input produk sama seperti sebelumnya.  
- Setelah disubmit ke `POST /product/result`, data form diproses oleh **ProductDTO** dan diteruskan ke **ProductService** untuk diolah.  
- Hasil akhirnya ditampilkan pada halaman **Product DTO Result** dalam bentuk *Bootstrap card* berisi nama, harga, dan deskripsi produk.

**Praktikum 3 – Todo CRUD dengan Eloquent dan MySQL**  
- `http://127.0.0.1:8000/` → menampilkan **daftar todo** dari tabel `todos` di database MySQL.  
- `http://127.0.0.1:8000/todos/create` → menampilkan **form tambah task**. Setelah disubmit, data tersimpan ke database melalui **Model Eloquent Todo** dan muncul pesan “Task added successfully!”.  
- `http://127.0.0.1:8000/todos/{id}` → menampilkan **detail salah satu task**.  
- `http://127.0.0.1:8000/todos/{id}/edit` → menampilkan **form edit task** untuk memperbarui data, setelah disimpan muncul pesan sukses.  
- Tombol **Hapus** pada daftar todo → menghapus data dari database dengan metode `DELETE`, dan data langsung hilang dari daftar.

---

### Penjelasan Umum

1. **Praktikum 1 (tanpa database)** memperkenalkan konsep model sebagai *data holder* sederhana. `ProductViewModel` membantu memahami bahwa model tidak selalu harus terhubung ke tabel database — fungsinya bisa sekadar menampung data yang dikirim dari form.  

2. **Praktikum 2 (DTO dan Service)** memperlihatkan penerapan pola *clean architecture* di Laravel. Dengan memisahkan data ke `ProductDTO` dan logika ke `ProductService`, controller jadi lebih ringkas, mudah diuji, dan mudah dikembangkan jika nanti logikanya bertambah.  

3. **Praktikum 3 (CRUD Eloquent dan MySQL)** adalah penerapan konsep *Model* yang sesungguhnya di Laravel. Semua operasi database seperti tambah, ubah, hapus, dan tampil dilakukan lewat Eloquent ORM, tanpa perlu menulis query SQL secara manual.  

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

- Dokumentasi Resmi Laravel 12 — *Eloquent ORM, Migrations, Database*  
- Modul 6 - Model dan Laravel Eloquent — (HackMD, Muhammad Reza Zulman)  
- Laravel Docs: Validation, Controllers, Responses — https://laravel.com/docs
