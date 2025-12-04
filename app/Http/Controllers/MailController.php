<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;

use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function index()
    {
        return view('mail.form');
    }
      public function create()
    {
        return view('mail.form'); // your form.blade.php
    }

    public function send(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        // Save log
        $log = MailLog::create([
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'created_by' => 1,   // dummy (you can use Auth later)
            'status'     => 1
        ]);

        // Mail Details
        $details = [
            'title' => $request->subject,
            'body'  => $request->message,
        ];

        // Send Email
        Mail::to($request->email)->send(new TestMail($details));

        return view('mail.success');
    }

  

    public function list()
{
    $mails = MailLog::where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->paginate(10);

    return view('mail.index', compact('mails'));
}


public function view($id)
{
    $mail = MailLog::findOrFail($id);
    return view('mail.view', compact('mail'));
}

public function delete($id)
{
    $mail = MailLog::findOrFail($id); // ✅ Use your MailLog model
    $mail->delete(); // Soft delete if using SoftDeletes

    return redirect()->back()->with('success', 'Mail deleted successfully!');
}


public function restore($id)
{
    $mail = MailLog::withTrashed()->findOrFail($id);
    $mail->restore();
    return redirect()->back()->with('success', 'Email restored successfully!');
}

public function forceDelete($id)
{
    $mail = MailLog::withTrashed()->findOrFail($id);
    $mail->forceDelete();
    return redirect()->back()->with('success', 'Email permanently deleted!');
}

public function changeStatus($id)
{
    $mail = MailLog::findOrFail($id);

    $mail->status = $mail->status == 1 ? 0 : 1;
    $mail->save();

    return redirect()->back()->with('success', 'Status updated!');
}

}
