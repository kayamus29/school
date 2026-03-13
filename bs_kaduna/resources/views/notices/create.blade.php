@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-megaphone"></i> Notice Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Notices</li>
                        </ol>
                    </nav>
                    @include('session-messages')
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $notice ? 'Edit Notice' : 'Create Notice' }}</h5>
                                    <form action="{{ $notice ? route('notice.update', $notice) : route('notice.store') }}" method="POST">
                                        @csrf
                                        @if($notice)
                                            @method('PUT')
                                        @endif
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mb-3">
                                            <label for="notice-editor" class="form-label">Write Notice</label>
                                            <textarea name="notice" class="form-control" id="notice-editor" rows="10" placeholder="Write here...">{{ old('notice', $notice->notice ?? '') }}</textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-outline-primary">
                                                <i class="bi bi-check2"></i> {{ $notice ? 'Update' : 'Save' }}
                                            </button>
                                            @if($notice)
                                                <a href="{{ route('notice.create') }}" class="btn btn-light">Cancel Edit</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title">Current Session Notices</h5>
                                    @forelse($notices as $savedNotice)
                                        <div class="border rounded p-3 mb-3">
                                            <div class="small text-muted mb-2">{{ $savedNotice->created_at->format('d M Y, h:i A') }}</div>
                                            <div class="small mb-3">{!! \Illuminate\Support\Str::limit(strip_tags($savedNotice->notice), 180) !!}</div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('notice.create', ['notice_id' => $savedNotice->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="{{ route('notice.destroy', $savedNotice) }}" method="POST" onsubmit="return confirm('Delete this notice?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">No notices created for this session yet.</p>
                                    @endforelse
                                </div>
                            </div>
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
    function DisallowNestingTables(editor) {
        editor.model.schema.addChildCheck((context, childDefinition) => {
            if (childDefinition.name == 'table' && Array.from(context.getNames()).includes('table')) {
                return false;
            }
        });
    }

    ClassicEditor.create(document.querySelector('#notice-editor'), {
        extraPlugins: [DisallowNestingTables],
        toolbar: ['heading', 'bold', 'italic', '|', 'link', 'insertTable', 'numberedList', 'bulletedList', '|', 'undo', 'redo'],
        table: {
            toolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
        }
    }).catch(error => {
        console.error(error);
    });
</script>
@endsection
