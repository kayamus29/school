<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRuleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create exams rule');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'total_marks' => 'required|numeric',
            'pass_marks' => 'required|numeric',
            'names' => 'required|array',
            'names.*' => 'required|string',
            'weights' => 'required|array',
            'weights.*' => 'required|numeric|min:0|max:100',
            'marks_distribution_note' => 'required',
            'exam_id' => 'required|integer',
            'session_id' => 'required|integer'
        ];
    }
}
