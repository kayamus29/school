@extends('layouts.app')

@section('title', 'Bulk Import & Export Center')

@section('content')
    <div class="bulk-import-container p-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-gradient">Bulk Import & Export Center</h2>
            <div class="template-downloads">
                @foreach($availableAdapters as $adapter)
                    <a href="{{ route('admin.bulk-import.template', $adapter) }}"
                        class="btn btn-outline-primary btn-sm rounded-pill shadow-sm me-2">
                        <i class="fas fa-download me-1"></i> {{ $adapter }} Template
                    </a>
                @endforeach
            </div>
        </div>

        <div class="row">
            {{-- Left: Upload Column --}}
            <div class="col-lg-4 mb-4">
                <div class="glass-card p-4 shadow-lg border-0 h-100">
                    <h5 class="fw-bold mb-4">Upload New Data</h5>

                    <form action="{{ route('admin.bulk-import.process') }}" method="POST" enctype="multipart/form-data"
                        id="importForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Import Type</label>
                            <select name="adapter_type" class="form-select custom-select" required>
                                <option value="">Choose an adapter...</option>
                                @foreach($availableAdapters as $adapter)
                                    <option value="{{ $adapter }}">{{ $adapter }} Importer</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload CSV File</label>
                            <div class="upload-zone position-relative border-dash rounded-3 p-4 text-center" id="dropZone">
                                <i class="fas fa-file-csv fa-3x text-primary mb-2"></i>
                                <p class="mb-0 text-muted small">Max file size: 10MB</p>
                                <input type="file" name="csv_file" class="hidden-input" id="csvFile" accept=".csv,.txt"
                                    required>
                                <div class="file-name-preview mt-2 fw-bold text-primary"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Import Mode</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="mode" id="mode_dry_run" value="dry_run" checked>
                                <label class="btn btn-outline-info rounded-start-pill" for="mode_dry_run">Dry-Run
                                    Preview</label>

                                <input type="radio" class="btn-check" name="mode" id="mode_real" value="real_import">
                                <label class="btn btn-outline-danger rounded-end-pill" for="mode_real">Real Import</label>
                            </div>
                            <small class="text-muted mt-2 d-block">Dry-run checks for errors without saving data.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-bold mt-2"
                            id="submitBtn">
                            <i class="fas fa-rocket me-2"></i> Start Operation
                        </button>

                        <div class="progress mt-4 d-none" id="importProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: 100%"></div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right: Preview & History Column --}}
            <div class="col-lg-8">
                {{-- Import Results Preview (Conditional) --}}
                @if(session('import_result'))
                    @php $result = session('import_result'); @endphp
                    <div class="glass-card p-4 shadow-lg border-0 mb-4 animate__animated animate__fadeIn">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0">Import Preview Results</h5>
                            <span
                                class="badge rounded-pill px-3 py-2 {{ $result['status'] === 'success' || $result['status'] === 'preview' ? 'bg-success' : 'bg-danger' }}">
                                {{ strtoupper($result['status']) }}
                            </span>
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-3">
                                <div class="stat-box p-3 rounded-3 bg-light">
                                    <h4 class="fw-bold mb-1">{{ $result['total_rows'] }}</h4>
                                    <small class="text-muted">Total Rows</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-box p-3 rounded-3 bg-soft-success">
                                    <h4 class="fw-bold mb-1 text-success">{{ $result['successful'] }}</h4>
                                    <small class="text-muted">Valid</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-box p-3 rounded-3 bg-soft-danger">
                                    <h4 class="fw-bold mb-1 text-danger">{{ $result['failed'] }}</h4>
                                    <small class="text-muted">Errors</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stat-box p-3 rounded-3 bg-soft-warning">
                                    <h4 class="fw-bold mb-1 text-warning">{{ count($result['warnings']) }}</h4>
                                    <small class="text-muted">Warnings</small>
                                </div>
                            </div>
                        </div>

                        @if(!empty($result['errors']) || !empty($result['warnings']))
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover border">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th width="80">Line</th>
                                            <th width="150">Field</th>
                                            <th>Message</th>
                                            <th width="100">Severity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- List errors first --}}
                                        @foreach($result['errors'] as $error)
                                            <tr class="table-danger">
                                                <td class="fw-bold">{{ $error['line'] }}</td>
                                                <td><code>{{ $error['field'] }}</code></td>
                                                <td>{{ $error['message'] }}</td>
                                                <td><span class="badge bg-danger">ERROR</span></td>
                                            </tr>
                                        @endforeach
                                        {{-- List warnings second --}}
                                        @foreach($result['warnings'] as $warning)
                                            <tr class="table-warning">
                                                <td class="fw-bold">{{ $warning['line'] }}</td>
                                                <td><code>{{ $warning['field'] }}</code></td>
                                                <td>{{ $warning['message'] }}</td>
                                                <td><span class="badge bg-warning text-dark">WARN</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success text-center border-0 py-4 mb-0">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                <strong>No internal errors found!</strong> This file is safe to import.
                            </div>
                        @endif
                    </div>
                @endif

                {{-- History Log Table --}}
                <div class="glass-card p-4 shadow-lg border-0 h-100">
                    <h5 class="fw-bold mb-4">Import History Log</h5>
                    <div class="table-responsive">
                        <table class="table table-custom hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Adapter</th>
                                    <th>Rows</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $item)
                                    <tr>
                                        <td class="small">{{ $item->created_at->format('M d, H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-soft-primary text-primary fw-bold text-center me-2"
                                                    style="width: 24px; height: 24px; font-size: 10px; line-height: 24px;">
                                                    {{ strtoupper(substr($item->user->first_name, 0, 1)) }}
                                                </div>
                                                <span class="small">{{ $item->user->first_name }}</span>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge bg-soft-secondary text-secondary">{{ $item->adapter_type }}</span>
                                        </td>
                                        <td class="small">
                                            <span class="text-success">{{ $item->successful_rows }}</span> /
                                            <span class="text-danger">{{ $item->failed_rows }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match ($item->status) {
                                                    'success' => 'bg-emerald',
                                                    'partial' => 'bg-amber',
                                                    'failed' => 'bg-rose',
                                                    'preview' => 'bg-sky',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $badgeClass }}">
                                                {{ strtoupper($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-link btn-sm p-0 text-decoration-none view-details-btn"
                                                data-id="{{ $item->id }}">
                                                Details
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No import history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Premium Dashboard Styles */
        :root {
            --primary: #4361ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --glass: rgba(255, 255, 255, 0.95);
        }

        .text-gradient {
            background: linear-gradient(45deg, var(--primary), #7209b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 20px;
        }

        .border-dash {
            border: 2px dashed #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .border-dash:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .hidden-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .bg-soft-success {
            background: rgba(16, 185, 129, 0.1);
        }

        .bg-soft-danger {
            background: rgba(239, 68, 68, 0.1);
        }

        .bg-soft-warning {
            background: rgba(245, 158, 11, 0.1);
        }

        .bg-soft-primary {
            background: rgba(67, 97, 238, 0.1);
        }

        .bg-soft-secondary {
            background: #f1f5f9;
        }

        /* Custom Status Badges */
        .status-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
            color: white;
        }

        .bg-emerald {
            background-color: #10b981;
        }

        .bg-amber {
            background-color: #f59e0b;
        }

        .bg-rose {
            background-color: #f43f5e;
        }

        .bg-sky {
            background-color: #0ea5e9;
        }

        .table-custom thead th {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            border-top: 0;
        }

        .table-custom tbody td {
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('csvFile');
            const fileNamePreview = dropZone.querySelector('.file-name-preview');
            const importForm = document.getElementById('importForm');
            const progress = document.getElementById('importProgress');
            const submitBtn = document.getElementById('submitBtn');

            // Drag and drop events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('bg-light'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('bg-light'), false);
            });

            dropZone.addEventListener('drop', (e) => {
                fileInput.files = e.dataTransfer.files;
                handleFiles(fileInput.files);
            });

            fileInput.addEventListener('change', function () {
                handleFiles(this.files);
            });

            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File is too large! Maximum size is 10MB.');
                        fileInput.value = '';
                        fileNamePreview.textContent = '';
                        return;
                    }
                    fileNamePreview.textContent = 'Selected: ' + file.name;
                }
            }

            importForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                progress.classList.remove('d-none');
            });
        });
    </script>
@endsection