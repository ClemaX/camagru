<?php

namespace App\Services;

use App\Entities\User;

interface UserSessionServiceInterface
{
	public function start(): void;

	public function login(User $user): void;
	public function logout(): void;

	public function getUser(): ?User;

	public function getCsrfToken(): string;
	public function verifyCsrfToken(string $token): bool;
}
