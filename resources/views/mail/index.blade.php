@extends('layouts.app') <!-- Extends the main layout file -->

@section('content')
<div class="card shadow-lg"> <!-- Card with shadow for visual emphasis -->

    <!-- Card Header with Title and Send Email Button -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Mail Logs</h4> <!-- Card title -->
        <!-- Button to navigate to Send Email page -->
        <a href="{{ url('/email') }}" class="btn btn-success btn-sm mb-3">
            Send Email
        </a>
    </div>

    <!-- Card Body -->
    <div class="card-body">

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div> <!-- Display success messages -->
        @endif

        <!-- Mail Logs Table -->
        <table class="table table-bordered table-striped"> <!-- Table with borders and striped rows -->
            <thead class="table-dark"> <!-- Table header with dark background -->
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th> <!-- Column for action buttons -->
            </tr>
            </thead>

            <tbody>
            <!-- Loop through mail logs -->
            @forelse($mails as $mail)
                <tr>
                    <td>{{ $mail->id }}</td> <!-- Mail ID -->
                    <td>{{ $mail->email }}</td> <!-- Recipient Email -->
                    <td>{{ $mail->subject }}</td> <!-- Email Subject -->
                    <td>
                        <!-- Status badge: Active (green) or Inactive (red) -->
                        @if($mail->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $mail->created_at->format('d-m-Y') }}</td> <!-- Created date formatted -->
                    <td>
                        <!-- Action buttons: View and Delete -->
                        <a href="{{ url('/mail/view/'.$mail->id) }}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ url('/mail/delete/'.$mail->id) }}" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this mail?');">
                            Delete
                        </a>
                    </td>
                </tr>
            @empty
                <!-- Display if no mail logs found -->
                <tr>
                    <td colspan="6" class="text-center">No Email Logs Found</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $mails->onEachSide(1)->links('pagination::bootstrap-5') }} <!-- Bootstrap 5 pagination links -->
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Form to send email -->
      <form action="{{ url('/mail/send') }}" method="POST">
        @csrf <!-- CSRF token for security -->
        <div class="modal-header">
          <h5 class="modal-title" id="sendEmailModalLabel">Send Email</h5> <!-- Modal title -->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> <!-- Close button -->
        </div>
        <div class="modal-body">
            <!-- Email input -->
            <div class="mb-3">
                <label for="email" class="form-label">Recipient Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <!-- Subject input -->
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <!-- Message textarea -->
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <!-- Modal action buttons -->
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Send Email</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
