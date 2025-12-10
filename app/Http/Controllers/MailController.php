<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;          // Mailable class for sending emails
use App\Models\MailLog;         // Mail log model
use Illuminate\Http\Request;    // HTTP request
use Illuminate\Support\Facades\Mail; // Facade for sending emails

class MailController extends Controller
{
    /**
     * Show the mail form
     */
    public function index()
    {
        return view('mail.form'); // Display the email form
    }

    /**
     * Show the create form (same as index)
     */
    public function create()
    {
        return view('mail.form'); // your form.blade.php
    }

    /**
     * Send email and save log
     */
    public function send(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email'   => 'required|email',  // Must be a valid email
            'subject' => 'required',        // Subject required
            'message' => 'required',        // Message required
        ]);

        // Save the mail log to database
        $log = MailLog::create([
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'created_by' => 1,   // Dummy user ID, replace with Auth::id() later
            'status'     => 1,   // 1 = Active
        ]);

        // Prepare mail details for Mailable
        $details = [
            'title' => $request->subject,
            'body'  => $request->message,
        ];

        // Send the email using TestMail Mailable
        Mail::to($request->email)->send(new TestMail($details));

        // Redirect to success page
        return view('mail.success');
    }

    /**
     * List all active mails with pagination
     */
    public function list()
    {
        $mails = MailLog::where('status', 1) // Only active mails
                        ->orderBy('id', 'ASC') 
                        ->paginate(10); // Paginate 10 per page

        return view('mail.index', compact('mails'));
    }

    /**
     * View a single mail
     */
    public function view($id)
    {
        $mail = MailLog::findOrFail($id); // Find by ID or fail
        return view('mail.view', compact('mail'));
    }

    /**
     * Soft delete a mail
     */
    public function delete($id)
    {
        $mail = MailLog::findOrFail($id); // Fetch mail
        $mail->delete(); // Soft delete (sets deleted_at)
        return redirect()->back()->with('success', 'Mail deleted successfully!');
    }

    /**
     * Restore a soft deleted mail
     */
    public function restore($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id); // Include trashed
        $mail->restore(); // Restore deleted mail
        return redirect()->back()->with('success', 'Email restored successfully!');
    }

    /**
     * Permanently delete a mail
     */
    public function forceDelete($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id); // Include trashed
        $mail->forceDelete(); // Permanently remove from DB
        return redirect()->back()->with('success', 'Email permanently deleted!');
    }

    /**
     * Change mail status (Active/Inactive)
     */
    public function changeStatus($id)
    {
        $mail = MailLog::findOrFail($id); // Fetch mail

        // Toggle status
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();

        return redirect()->back()->with('success', 'Status updated!');
    }
}

