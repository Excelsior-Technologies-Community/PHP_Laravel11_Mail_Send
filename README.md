# Laravel 11 Mail Send Project

A beginner-friendly Laravel 11 project that allows users to send emails via a form, log sent emails, and manage them. It includes Laravel Mailables, soft deletes, status management, and a clean Bootstrap 5 frontend.

---

##  Features
- Send emails using a form
- Save email logs in the database
- View all email logs with pagination
- Soft delete, restore, and permanently delete email logs
- Toggle email log status (Active/Inactive)
- View details of a single email
- Responsive UI with Bootstrap 5
- Beginner-friendly, fully commented code

---

##  Requirements
- PHP 8.1+
- Laravel 11
- MySQL
- Composer
- SMTP account (e.g., Mailtrap for testing)

---

##  Installation Steps

### Step 1: Install Laravel 11
```
composer create-project laravel/laravel laravel11-mail "^11.0"
cd laravel11-mail
```
Step 2: Configure Database
Edit .env:
```
env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mail_app
DB_USERNAME=root
DB_PASSWORD=
Create the database:

sql
```
CREATE DATABASE mail_app;
Step 3: Create Migration for Mail Logs
```


php artisan make:migration create_mail_logs_table --create=mails
```

Migration file (database/migrations/..._create_mail_logs_table.php):

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('subject');
            $table->longText('message');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
```
Run migration:

```

php artisan migrate
```
Step 4: Configure Mail Settings
Edit .env:
```
env

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="from@example.com"
MAIL_FROM_NAME="Laravel11-Mail"
```
Step 5: Create Model, Controller & Mailable
```
php artisan make:model MailLog -m
php artisan make:controller MailController --resource --model=MailLog
php artisan make:mail TestMail
```
MailLog Model (app/Models/MailLog.php)
```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email','subject','message','created_by','updated_by','status'
    ];
}
```
MailController (app/Http/Controllers/MailController.php)
```

<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function index() {
        return view('mails.form');
    }

    public function create() {
        return view('mails.form');
    }

    public function send(Request $request) {
        $request->validate([
            'email'=>'required|email',
            'subject'=>'required',
            'message'=>'required',
        ]);

        $log = MailLog::create([
            'email'=>$request->email,
            'subject'=>$request->subject,
            'message'=>$request->message,
            'created_by'=>1,
            'status'=>1,
        ]);

        $details = [
            'title'=>$request->subject,
            'body'=>$request->message
        ];

        Mail::to($request->email)->send(new TestMail($details));

        return view('mails.success');
    }

    public function list() {
        $mails = MailLog::where('status',1)->orderBy('id','ASC')->paginate(10);
        return view('mails.index', compact('mails'));
    }

    public function view($id) {
        $mail = MailLog::findOrFail($id);
        return view('mails.view', compact('mail'));
    }

    public function delete($id) {
        MailLog::findOrFail($id)->delete();
        return redirect()->back()->with('success','Mail deleted successfully!');
    }

    public function restore($id) {
        MailLog::withTrashed()->findOrFail($id)->restore();
        return redirect()->back()->with('success','Mail restored successfully!');
    }

    public function forceDelete($id) {
        MailLog::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->back()->with('success','Mail permanently deleted!');
    }

    public function changeStatus($id) {
        $mail = MailLog::findOrFail($id);
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();
        return redirect()->back()->with('success','Status updated!');
    }
}
```
TestMail Mailable (app/Mail/TestMail.php)
```

<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public $details;

    public function __construct($details) {
        $this->details = $details;
    }

    public function build() {
        return $this->subject($this->details['title'])
                    ->view('emails.test');
    }
}
```
Step 6: Routes (routes/web.php)
```

use App\Http\Controllers\MailController;

Route::get('/email', [MailController::class,'index']);
Route::post('/send-email', [MailController::class,'send']);

Route::get('/mail', [MailController::class,'list']);
Route::get('/mail/view/{id}', [MailController::class,'view']);
Route::get('/mail/delete/{id}', [MailController::class,'delete']);
Route::get('/mail/restore/{id}', [MailController::class,'restore']);
Route::get('/mail/force-delete/{id}', [MailController::class,'forceDelete']);
Route::get('/mail/status/{id}', [MailController::class,'changeStatus']);
```
Step 7: Blade Views
Layout (resources/views/layouts/app.blade.php)
```

<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark w-100 shadow-sm text-center">
    <span class="navbar-brand mb-0 h1">Mail Sender</span>
</nav>
<div class="container">@yield('content')</div>
</body>
</html>
```
Send Form (resources/views/mails/form.blade.php)
```

@extends('layouts.app')
@section('content')
<br>
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-lg">
<div class="card-header bg-primary text-white"><h4>Send Email</h4></div>
<div class="card-body">
<form action="/send-email" method="POST">@csrf
<div class="mb-3"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label>Subject</label><input type="text" name="subject" class="form-control" required></div>
<div class="mb-3"><label>Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
<button type="submit" class="btn btn-success w-100">Send Email</button>
</form>
</div>
</div>
</div>
</div>
@endsection
```
Email Logs (resources/views/mails/index.blade.php)
```

@extends('layouts.app')
@section('content')
<div class="card shadow-lg">
<div class="card-header bg-primary text-white d-flex justify-content-between">
<h4>Mail Logs</h4>
<a href="/email" class="btn btn-success btn-sm">Send Email</a>
</div>
<div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered table-striped">
<thead class="table-dark"><tr><th>ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Created At</th><th>Action</th></tr></thead>
<tbody>
@forelse($mails as $mail)
<tr>
<td>{{ $mail->id }}</td>
<td>{{ $mail->email }}</td>
<td>{{ $mail->subject }}</td>
<td>@if($mail->status==1)<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Inactive</span>@endif</td>
<td>{{ $mail->created_at->format('d-m-Y') }}</td>
<td>
<a href="/mail/view/{{ $mail->id }}" class="btn btn-sm btn-primary">View</a>
<a href="/mail/delete/{{ $mail->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
</td>
</tr>
@empty
<tr><td colspan="6" class="text-center">No Email Logs Found</td></tr>
@endforelse
</tbody>
</table>
<div class="d-flex justify-content-center mt-3">{{ $mails->links('pagination::bootstrap-5') }}</div>
</div>
</div>
@endsection
```
View Single Email (resources/views/mails/view.blade.php)
```

@extends('layouts.app')
@section('content')
<div class="container my-5">
<div class="row justify-content-center">
<div class="col-lg-7 col-md-9">
<div class="card shadow-lg rounded-4">
<div class="card-header text-white text-center py-4 rounded-top-4" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
<h3>Email Details</h3>
</div>
<div class="card-body p-4">
<p><strong>Recipient:</strong> {{ $mail->email }}</p>
<p><strong>Subject:</strong> {{ $mail->subject }}</p>
<p><strong>Message:</strong></p>
<div style="white-space: pre-wrap;">{{ $mail->message }}</div>
<p><strong>Status:</strong> @if($mail->status==1)Active @else Inactive @endif</p>
<p><strong>Created At:</strong> {{ $mail->created_at->format('d-m-Y H:i A') }}</p>
<a href="/mail" class="btn btn-secondary">Back</a>
</div>
</div>
</div>
</div>
@endsection
```
Success Page (resources/views/mails/success.blade.php)
```

@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-lg">
<div class="card-header bg-success text-white"><h4>Success!</h4></div>
<div class="card-body text-center">
<h3 class="text-success">✔ Email Sent Successfully</h3>
<p>Your message was delivered to the recipient.</p>
<a href="/email" class="btn btn-primary mt-3">Send Another Email</a>
<a href="/mail" class="btn btn-primary mt-3">List Emails</a>
</div>
</div>
</div>
</div>
@endsection
```
Email Template (resources/views/emails/test.blade.php)
```

<!DOCTYPE html>
<html>
<head><title>{{ $details['title'] }}</title></head>
<body>
<h2>{{ $details['title'] }}</h2>
<p>{{ $details['body'] }}</p>
</body>
</html>
```
Step 8: Run the Application
bash
```
php artisan serve
```
Visit in browser:
```
Email Form: http://localhost:8000/email

Mail Logs: http://localhost:8000/mail
```

You can see this type Output :

Email Form: http://localhost:8000/email :
<img width="1913" height="959" alt="image" src="https://github.com/user-attachments/assets/7c228df4-5957-46ef-8404-72ccc6b37373" />
<img width="1917" height="972" alt="Screenshot 2025-12-04 113812" src="https://github.com/user-attachments/assets/addf6504-0c4d-49a4-91d0-b0076920b4c8" />

Mail Logs: http://localhost:8000/mail :
<img width="1919" height="968" alt="image" src="https://github.com/user-attachments/assets/6d65959c-8dbb-44fb-905b-ae608a86c314" />
<img width="1894" height="964" alt="Screenshot 2025-12-04 114747" src="https://github.com/user-attachments/assets/71b00090-9125-4f86-9a36-970a5b3ce56e" />


 Features Now Working
```
Send emails via form

View email logs with pagination

Soft delete, restore, and permanently delete emails

Toggle status (Active/Inactive)

Mobile responsive with Bootstrap 5
```
