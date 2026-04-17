<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Display the email sending form.
     */
   public function index()
    {
        return view('mail.form');
    }

    /**
     * Handle the email sending process.
     */
    public function send(Request $request)
    {
        // 1. Validation
        $request->validate([
            'email'         => 'required|email',
            'subject'       => 'required|string|max:255',
            'message'       => 'required',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120'
        ]);

        try {
            // 2. Process attachments
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Files are stored in storage/app/public/attachments
                    $attachmentPaths[] = $file->store('attachments', 'public');
                }
            }

            // 3. Prepare data for the email
            $details = [
                'title'       => $request->subject,
                'body'        => $request->message,
                'attachments' => $attachmentPaths,
            ];

            // 4. Create database log
            MailLog::create([
                'email'      => $request->email,
                'subject'    => $request->subject,
                'message'    => $request->message,
                'created_by' => 1,
                'status'     => 1
            ]);

            // 5. Attempt to send the email
            Mail::to($request->email)->send(new TestMail($details));

            // If successful, return success view
            return view('mail.success');

        } catch (Exception $e) {
            // IF MAIL FAILS, IT WILL SHOW THE REAL ERROR HERE
            return "Mail Sending Failed! Error: " . $e->getMessage();
        }
    }

    /**
     * List all sent mail logs with search functionality.
     */
    public function list(Request $request)
    {
        $query = MailLog::where('status', 1);

        // Filter logs based on search query (email or subject)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        // Paginate results (10 per page)
        $mails = $query->orderBy('id', 'ASC')->paginate(10);

        return view('mail.index', compact('mails'));
    }

    /**
     * View details of a single mail log entry.
     */
    public function view($id)
    {
        $mail = MailLog::findOrFail($id);
        return view('mail.view', compact('mail'));
    }

    /**
     * Soft delete a mail log.
     */
    public function delete($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->delete();

        return redirect()->back()->with('success', 'Mail deleted successfully!');
    }

    /**
     * Restore a soft-deleted mail log.
     */
    public function restore($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->restore();
        return redirect()->back()->with('success', 'Email restored successfully!');
    }

    /**
     * Permanently delete a mail log entry.
     */
    public function forceDelete($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->forceDelete();
        return redirect()->back()->with('success', 'Email permanently deleted!');
    }

    /**
     * Toggle the status of a mail log (Active/Inactive).
     */
    public function changeStatus($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();

        return redirect()->back()->with('success', 'Status updated!');
    }
}