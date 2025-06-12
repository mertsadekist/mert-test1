<?php
// roles.php

/**
 * Application roles
 */
const ROLE_VIEWER = 'viewer';
const ROLE_EDITOR = 'editor';
const ROLE_ADMIN  = 'admin';

/**
 * List of all roles for convenience
 */
const ROLES = [
    ROLE_VIEWER,
    ROLE_EDITOR,
    ROLE_ADMIN,
];



/**
 * Capabilities assigned to each role
 */
const ROLE_CAPABILITIES = [
    ROLE_VIEWER => [],
    ROLE_EDITOR => [
        'manage_projects',
        'upload_apartments',
        'export_data',
    ],
    ROLE_ADMIN => [
        'manage_projects',
        'upload_apartments',
        'export_data',
        'manage_users',
    ],
];
