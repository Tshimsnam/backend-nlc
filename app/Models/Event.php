<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonImmutable;
use Illuminate\Http\Exceptions\HttpResponseException;

class Event extends Model
{
    use HasFactory;

    protected $with = ['event_prices'];

    protected $appends = ['registration_open'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'full_description',
        'date',
        'end_date',
        'time',
        'end_time',
        'location',
        'type',
        'status',
        'image',
        'agenda',
        'capacity',
        'registered',
        'contact_phone',
        'contact_email',
        'venue_details',
        'sponsors',
        'organizer',
        'registration_deadline',
    ];

    protected $casts = [
        'agenda' => 'array',
        'sponsors' => 'array',
        'registration_deadline' => 'date',
    ];

    /** Invalid legacy dates are left for an administrator to correct. */
    public function endsAt(): ?CarbonImmutable
    {
        $date = trim((string) ($this->end_date ?: $this->date));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }
        if ($this->end_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $this->date) && $date < $this->date) {
            return null;
        }

        try {
            $end = CarbonImmutable::createFromFormat('!Y-m-d', $date, config('events.timezone'));
            if (!$end || $end->format('Y-m-d') !== $date) {
                return null;
            }

            $time = trim((string) $this->end_time);
            if ($time === '') {
                return $end->endOfDay();
            }

            // Existing events use both 16h00 and 16:00 (optionally seconds).
            if (!preg_match('/^(\d{1,2})(?:h|:)(\d{2})(?::(\d{2}))?$/i', $time, $parts)) {
                return null;
            }
            $hour = (int) $parts[1];
            $minute = (int) $parts[2];
            $second = (int) ($parts[3] ?? 0);
            if ($hour > 23 || $minute > 59 || $second > 59) {
                return null;
            }

            return $end->setTime($hour, $minute, $second);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasEnded(): bool
    {
        return $this->endsAt()?->lessThanOrEqualTo(CarbonImmutable::now(config('events.timezone'))) ?? false;
    }

    public function getRegistrationOpenAttribute(): bool
    {
        return $this->status === 'upcoming' && !$this->hasEnded();
    }

    public function assertRegistrationOpen(): void
    {
        if (!$this->registration_open) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'code' => 'event_registration_closed',
                'message' => 'Les inscriptions sont closes pour cet événement terminé.',
            ], 409));
        }
    }

    public function event_prices(): HasMany
    {
        return $this->hasMany(EventPrice::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
