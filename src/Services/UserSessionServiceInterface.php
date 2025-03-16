<?php

namespace App\Services;

use App\Entities\User;

interface UserSessionServiceInterface
{
	public function start();

	public function login(User $user);
	public function logout();

	public function getUser(): ?User;

	public function getCsrfToken(): string;
	public function verifyCsrfToken(string $token): bool;
}
