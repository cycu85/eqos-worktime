<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class NotInFuture implements ValidationRule
{
    protected $dateField;
    protected $timeField;

    public function __construct(?string $dateField = null, ?string $timeField = null)
    {
        $this->dateField = $dateField;
        $this->timeField = $timeField;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Skip validation if empty (use 'required' rule separately)
        }

        // Get the date and time from the request
        $request = request();
        $date = $this->dateField ? $request->input($this->dateField) : $value;
        $time = $this->timeField ? $request->input($this->timeField) : null;

        if (empty($date)) {
            return; // Skip if no date provided
        }

        try {
            // Parse the date
            $checkDate = Carbon::parse($date);
            $now = Carbon::now();

            // If date is in the future, fail
            if ($checkDate->isAfter($now->startOfDay())) {
                if ($attribute === 'order_date') {
                    $fail('Data polecenia wyjazdu nie może być w przyszłości.');
                } elseif ($attribute === 'departure_date') {
                    $fail('Data wyjazdu nie może być w przyszłości.');
                } elseif ($attribute === 'arrival_date') {
                    $fail('Data przyjazdu nie może być w przyszłości.');
                } else {
                    $fail('Data nie może być w przyszłości.');
                }
                return;
            }

            // If date is today and time is provided, check if time is in future
            if ($checkDate->isToday() && !empty($time)) {
                try {
                    $checkDateTime = Carbon::parse($date . ' ' . $time);

                    if ($checkDateTime->isFuture()) {
                        if ($attribute === 'departure_date' || $this->timeField === 'departure_time') {
                            $fail('Godzina wyjazdu nie może być w przyszłości.');
                        } elseif ($attribute === 'arrival_date' || $this->timeField === 'arrival_time') {
                            $fail('Godzina przyjazdu nie może być w przyszłości.');
                        } else {
                            $fail('Godzina nie może być w przyszłości.');
                        }
                    }
                } catch (\Exception $e) {
                    // Invalid time format, let date_format rule handle it
                }
            }
        } catch (\Exception $e) {
            // Invalid date format, let date rule handle it
        }
    }
}
