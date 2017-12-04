<?php

namespace App\Models;

use App\Classes\Mailer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends BaseModel implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword, Notifiable;

	protected $table = 'users';
	protected $hidden = ['password', 'remember_token'];
	protected $fillable = ['email', 'password', 'name', 'role', 'is_superadmin'];

	public static function userList()
	{
		return User::pluck('email', 'id');
	}

	public function getRoleNameAttribute()
	{
		return self::getUserRoles()[$this->role] ?? null;
	}

	public static function getUserRoles()
	{
		return [
			'user' => 'User',
			'admin' => 'Administrator'
		];
	}

	public function sendPasswordResetNotification($token)
	{
		try {
			Mailer::sendPasswordResetLink($this, $token);
		} catch (\Exception $e) {
			\Log::error("Error while sending password reset token: " . $e->getMessage());
			\Log::error($e);
		}
	}

	public function isDeletable()
	{
		if (auth()->user() && auth()->user()->is_superadmin) {
			return true;
		}
		if (!$this->is_superadmin) {
			return true;
		}

		return false;
	}

	public function isEditable()
	{
		if (auth()->user() && auth()->user()->is_superadmin) {
			return true;
		}
		if (!$this->is_superadmin) {
			return true;
		}

		return false;
	}

	public function setPasswordAttribute($value)
	{
		if (!request()->filled('password')) {
			if ($value) {
				$this->attributes['password'] = $value;
			} else {
				$this->attributes['password'] = $this->password;
			}
		} else {
			$this->attributes['password'] = \Hash::make(request()->get('password'));
		}
	}
}