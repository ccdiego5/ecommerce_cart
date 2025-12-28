<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuid, HasPublicId, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
    ];
    
    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['formatted_public_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
    
    /**
     * Get the cart items for the user.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
