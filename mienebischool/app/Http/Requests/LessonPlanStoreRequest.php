<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonPlanStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && (auth()->user()->hasRole('Teacher') || auth()->user()->hasRole('Admin'));
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasContent = filled($this->input('content'));
            $hasFile = $this->hasFile('file');

            if (!$hasContent && !$hasFile) {
                $validator->errors()->add('content', 'Provide lesson plan text or upload a file.');
            }
        });
    }
}
