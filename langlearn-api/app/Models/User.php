<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Find or create a user by Firebase UID
     */
    public static function findOrCreateByFirebaseUid(string $uid, array $userData = []): User
    {
        $user = static::where('firebase_uid', $uid)->first();
        
        if (!$user) {
            $user = static::create([
                'firebase_uid' => $uid,
                'name' => $userData['name'] ?? '',
                'email' => $userData['email'] ?? '',
            ]);
        }
        
        return $user;
    }
}
