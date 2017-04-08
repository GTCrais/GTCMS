<?php

namespace App\Models;

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
	protected $hidden = array('password', 'remember_token');
	protected $fillable = array('email', 'password', 'first_name', 'last_name', 'role', 'is_superadmin');

	public static function userList()
	{
		return User::pluck('email', 'id');
	}

	public function getRoleNameAttribute()
	{
		$userRoles = self::getUserRoles();
		if (isset($userRoles[$this->role])) {
			return $userRoles[$this->role];
		}

		return null;
	}

	public static function getUserRoles()
	{
		return array(
			'user' => 'User',
			'admin' => 'Administrator'
		);
	}

	public function getFullNameAttribute()
	{
		return $this->first_name . " " . $this->last_name;
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
		if (!request()->has('password')) {
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