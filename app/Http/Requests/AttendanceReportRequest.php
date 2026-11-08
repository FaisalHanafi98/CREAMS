<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'centre_id' => $this->input('centre_id') ?? session('centre_id'),
            'start_date' => $this->input('start_date', $this->input('date_from', now()->subMonth()->toDateString())),
            'end_date' => $this->input('end_date', $this->input('date_to', now()->toDateString())),
            'format' => $this->input('format', 'csv'),
        ]);
    }

    public function authorize(): bool
    {
        if (!session()->has('id')) {
            return false;
        }

        return match (session('role')) {
            'admin' => true,
            'supervisor' => session('centre_id') && $this->input('centre_id') === session('centre_id'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'centre_id' => 'bail|nullable|string|max:10|exists:centres,centre_id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'format' => 'required|in:csv,excel,pdf',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('start_date') || $validator->errors()->has('end_date')) {
                return;
            }
            if (Carbon::parse($this->input('start_date'))->diffInDays(Carbon::parse($this->input('end_date'))) > 365) {
                $validator->errors()->add('end_date', 'Choose a date range of 366 days or fewer.');
            }
        });
    }
}
