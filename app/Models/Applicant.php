<?php

namespace App\Models;

use App\Enums\ApplicantStatus;
use App\Support\ApplicantEditToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    use HasFactory;

    /**
     * Marker stored in rejection_reason for Approved records whose ID card was delivered.
     * Reuses an existing column — no migration required.
     */
    public const CARD_DELIVERED_REASON = 'Card Delivered';

    protected $fillable = [
        'application_id',
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
            'edit_token_expires_at' => 'datetime',
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
            ->where(function ($builder) {
                $builder->where('status', ApplicantStatus::Rejected)
                    ->orWhere(function ($delivered) {
                        $delivered->where('status', ApplicantStatus::Approved)
                            ->where('rejection_reason', self::CARD_DELIVERED_REASON);
                    });
            })
            ->orderByDesc('verified_at');
    }

    public function scopeCardDelivered($query)
    {
        return $query
            ->where('status', ApplicantStatus::Approved)
            ->where('rejection_reason', self::CARD_DELIVERED_REASON);
    }

    public function scopeFinalized($query)
    {
        return $query
            ->where('status', ApplicantStatus::Approved)
            ->where(function ($builder) {
                $builder->whereNull('rejection_reason')
                    ->orWhere('rejection_reason', '!=', self::CARD_DELIVERED_REASON);
            })
            ->orderBy('verified_at')
            ->orderBy('id');
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
            $builder->where('application_id', 'like', $like)
                ->orWhere('full_name', 'like', $like)
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
        return filled($this->passport_photo)
            ? route('admin.applications.document', ['applicant' => $this, 'type' => 'passport'])
            : null;
    }

    public function gcashScreenshotUrl(): ?string
    {
        return filled($this->gcash_screenshot)
            ? route('admin.applications.document', ['applicant' => $this, 'type' => 'gcash'])
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

    public function isCardDelivered(): bool
    {
        return $this->isApproved()
            && $this->rejection_reason === self::CARD_DELIVERED_REASON;
    }

    /**
     * Full names already used by finalized (approved) or card-delivered archive records.
     * Used to flag duplicate-name risk on New Applicants only.
     *
     * @return list<string>
     */
    public static function finalizedOrDeliveredFullNames(): array
    {
        return static::query()
            ->where('status', ApplicantStatus::Approved)
            ->whereNotNull('full_name')
            ->where('full_name', '!=', '')
            ->distinct()
            ->orderBy('full_name')
            ->pluck('full_name')
            ->all();
    }

    public function awaitsDocumentCorrection(): bool
    {
        return $this->isPending()
            && filled($this->rejection_reason)
            && filled($this->edit_token_hash);
    }

    public function canBeEditedWithToken(string $plainToken): bool
    {
        return ApplicantEditToken::isValid($this, $plainToken);
    }
}
