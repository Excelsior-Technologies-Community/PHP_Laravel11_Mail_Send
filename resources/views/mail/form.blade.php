@extends('layouts.app')

@section('content')
<br>
<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Send Email</h4>
            </div>

            <div class="card-body">
                <form action="/send-email" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" id="editor" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Attachments (PDF/Image)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Send Email</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

<script>
    // Initialize CKEditor on the element with id "editor"
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>

@endsection