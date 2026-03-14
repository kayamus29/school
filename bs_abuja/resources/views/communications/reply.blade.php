@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-reply"></i> Reply Email</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.index') }}">Communication</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.show', $communication) }}">History Item</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reply</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="small text-muted">Replying to</div>
                                <div class="fw-semibold">{{ $recipient->recipient_name }}</div>
                                <div>{{ $recipient->destination }}</div>
                            </div>

                            <form method="POST" action="{{ route('communications.reply.send', [$communication, $recipient]) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="subject">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        value="{{ old('subject', 'Re: ' . ($communication->subject ?: 'Guardian Communication')) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="reply-editor">Message</label>
                                    <textarea class="form-control" id="reply-editor" name="message" rows="10" required>{{ old('message') }}</textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Send Reply
                                    </button>
                                    <a href="{{ route('communications.show', $communication) }}" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#reply-editor'), {
        toolbar: ['heading', 'bold', 'italic', '|', 'link', 'insertTable', 'numberedList', 'bulletedList', '|', 'undo', 'redo']
    }).catch((error) => {
        console.error(error);
    });
</script>
@endsection
