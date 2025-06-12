<?php
// auth.php
require_once 'roles.php';

function require_role(array $allowedRoles)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $_SESSION['role'] ?? null;
    if (!$role || !in_array($role, $allowedRoles, true)) {
        header('Location: index.php');
        exit();
    }
}

function has_role(array $allowedRoles): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $_SESSION['role'] ?? null;
    return $role && in_array($role, $allowedRoles, true);
}

function can(string $capability): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $_SESSION['role'] ?? null;
    return $role && in_array($capability, ROLE_CAPABILITIES[$role] ?? [], true);
}

function require_capability(string $capability)
{
    if (!can($capability)) {
        header('Location: index.php');
        exit();
    }
}
