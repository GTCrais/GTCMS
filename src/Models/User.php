<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Str;

class User extends BaseModel implements
	AuthenticatableContract,
	AuthorizableContract,
	CanResetPasswordContract
{

	use Authenticatable, Authorizable, CanResetPassword, Notifiable;

	protected $table = 'users';
	protected $hidden = array('password', 'remember_token');
	protected $fillable = array('email', 'password', 'first_name', 'last_name', 'role');

	public static function userList() {
		return User::pluck('email', 'id');
	}

	public function getRoleNameAttribute() {
		$userRoles = self::getUserRoles();
		if (isset($userRoles[$this->role])) {
			return $userRoles[$this->role];
		}

		return null;
	}

	public static function getUserRoles() {
		return array(
			'user' => 'User',
			'admin' => 'Administrator'
		);
	}

	public function getFullNameAttribute() {
		return $this->first_name . " " . $this->last_name;
	}

	public static function usersList() {
		$users = User::all();
		if ($users->count()) {
			$params = array('itemName' => 'fullName');
			$list = array();
			$list = Tools::createItemList($users, $list, $params);
			return $list;
		} else return array();
	}

	public function isDeletable() {
		if (\Auth::user() && \Auth::user()->is_superadmin) {
			return true;
		}
		if (!$this->is_superadmin) {
			return true;
		}
		return false;
	}

	public function isEditable() {
		if (\Auth::user() && \Auth::user()->is_superadmin) {
			return true;
		}
		if (!$this->is_superadmin) {
			return true;
		}
		return false;
	}

	public function setPasswordAttribute($value) {
		if (!\Request::has('password')) {
			if ($value) {
				$this->attributes['password'] = $value;
			} else {
				$this->attributes['password'] = $this->password;
			}
		} else {
			$this->attributes['password'] = \Hash::make(\Request::get('password'));
		}
	}

}