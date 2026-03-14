<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndTermUpdateStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public function rules()
    {
        return [
            'semester_id' => 'required|exists:semesters,id',
            'title' => 'nullable|string|max:255',
            'content_format' => 'required|in:plain_text,html',
            'content_body' => 'nullable|string',
            'newsletter_url' => 'nullable|url|max:500',
            'next_term_label' => 'nullable|string|max:120',
            'next_resumption_date' => 'nullable|date',
            'fee_deadline' => 'nullable|date',
            'resumption_note' => 'nullable|string|max:1000',
        ];
    }
}
