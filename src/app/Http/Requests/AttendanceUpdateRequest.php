<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'punch_in' => 'required',
            'punch_out' => 'required',
            'remarks' => 'required',
            'rests.*.start' => 'nullable',
            'rests.*.end' => 'nullable',
            'new_rests.*.start' => 'nullable',
            'new_rests.*.end' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'punch_in.required' => '出勤時間を記入してください',
            'punch_out.required' => '退勤時間を記入してください',
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $in = $this->punch_in;
            $out = $this->punch_out;

            if ($in && $out && $in >= $out) {
                $validator->errors()->add('punch_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($this->has('rests')) {
                foreach ($this->rests as $id => $rest) {
                    $rStart = $rest['start'] ?? null;
                    $rEnd = $rest['end'] ?? null;

                    if ($rStart && ($rStart < $in || ($out && $rStart > $out))) {
                        $validator->errors()->add("rests.$id.start", '休憩時間が不適切な値です');
                    }
                    if ($rEnd && $out && $rEnd > $out) {
                        $validator->errors()->add("rests.$id.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }

            if ($this->has('new_rests')) {
                foreach ($this->new_rests as $index => $rest) {
                    $rStart = $rest['start'] ?? null;
                    $rEnd = $rest['end'] ?? null;

                    if ($rStart && ($rStart < $in || ($out && $rStart > $out))) {
                        $validator->errors()->add("new_rests.$index.start", '休憩時間が不適切な値です');
                    }
                    if ($rEnd && $out && $rEnd > $out) {
                        $validator->errors()->add("new_rests.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
