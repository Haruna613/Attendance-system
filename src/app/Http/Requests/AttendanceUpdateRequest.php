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
                $validator->errors()->add('punch_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $checkRest = function ($restData, $isNew = false) use ($validator, $in, $out) {
                foreach ($restData as $rest) {
                    $rStart = $rest['start'] ?? null;
                    $rEnd = $rest['end'] ?? null;

                    if ($rStart) {
                        if ($rStart < $in || ($out && $rStart > $out)) {
                            $validator->errors()->add('rests', '休憩時間が不適切な値です');
                        }
                    }

                    if ($rEnd) {
                        if ($out && $rEnd > $out) {
                            $validator->errors()->add('rests', '休憩時間もしくは退勤時間が不適切な値です');
                        }
                        if ($rStart && $rStart >= $rEnd) {
                            $validator->errors()->add('rests', '休憩時間の開始と終了が不適切です');
                        }
                    }
                }
            };
            if ($this->has('rests')) $checkRest($this->rests);
            if ($this->has('new_rests')) $checkRest($this->new_rests, true);
        });
    }
}
