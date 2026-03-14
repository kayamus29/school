<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunicationRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Teacher']);
    }

    public function rules()
    {
        return [
            'scope' => ['required', Rule::in(['bulk', 'single'])],
            'channel' => ['required', Rule::in(['email', 'sms'])],
            'session_id' => ['required', 'integer', 'exists:school_sessions,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            $scope = $this->input('scope');
            $channel = $this->input('channel');

            if ($user->hasRole('Admin') && !in_array($scope, ['bulk', 'single'], true)) {
                $validator->errors()->add('scope', 'Invalid communication scope selected.');
            }

            if ($user->hasRole('Teacher') && $scope !== 'single') {
                $validator->errors()->add('scope', 'Teachers can only send single communication to their assigned students.');
            }

            if ($scope === 'single' && !$this->filled('student_id')) {
                $validator->errors()->add('student_id', 'Select a student to continue.');
            }

            if ($scope === 'bulk' && $this->filled('section_id') && !$this->filled('class_id')) {
                $validator->errors()->add('class_id', 'Select a class when filtering by section.');
            }

            if ($channel === 'email' && trim((string) $this->input('subject')) === '') {
                $validator->errors()->add('subject', 'Email subject is required.');
            }

            if ($channel === 'sms' && mb_strlen(trim(strip_tags((string) $this->input('message')))) > 1000) {
                $validator->errors()->add('message', 'SMS message should not exceed 1000 characters.');
            }
        });
    }
}
