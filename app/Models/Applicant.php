<?php

namespace App\Models;

use App\Enums\ApplicantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'birthday',
        'gcash_number',
        'province',
        'barangay',
        'address',
        'blood_type',
        'emergency_contact_person',
        'emergency_contact_number',
        'passport_photo',
        'gcash_screenshot',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicantStatus::class,
            'birthday' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', ApplicantStatus::Pending);
    }

    public function scopeInVerificationQueue($query)
    {
        return $query->pending()->orderBy('created_at');
    }

    public function scopeArchived($query)
    {
        return $query
            ->where('status', ApplicantStatus::Rejected)
            ->orderByDesc('verified_at');
    }

    public function scopeFinalized($query)
    {
        return $query
            ->where('status', ApplicantStatus::Approved)
            ->orderByDesc('verified_at');
    }

    public function scopeInBarangay($query, ?string $barangay)
    {
        if (blank($barangay)) {
            return $query;
        }

        return $query->where('barangay', $barangay);
    }

    public function scopeApprovedBetween($query, ?string $from, ?string $to)
    {
        if (filled($from)) {
            $query->whereDate('verified_at', '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate('verified_at', '<=', $to);
        }

        return $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function ($builder) use ($like) {
            $builder->where('full_name', 'like', $like)
                ->orWhere('first_name', 'like', $like)
                ->orWhere('middle_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('barangay', 'like', $like)
                ->orWhere('rejection_reason', 'like', $like);
        });
    }

    public function passportPhotoUrl(): ?string
    {
        return $this->passport_photo
            ? asset('storage/'.$this->passport_photo)
            : null;
    }

    public function gcashScreenshotUrl(): ?string
    {
        return $this->gcash_screenshot
            ? asset('storage/'.$this->gcash_screenshot)
            : null;
    }

    public function isGcashPdf(): bool
    {
        return str_ends_with(strtolower($this->gcash_screenshot ?? ''), '.pdf');
    }

    public function isPending(): bool
    {
        return $this->status === ApplicantStatus::Pending;
    }

    public function isRejected(): bool
    {
        return $this->status === ApplicantStatus::Rejected;
    }

    public function isApproved(): bool
    {
        return $this->status === ApplicantStatus::Approved;
    }
}
