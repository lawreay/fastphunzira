INSERT INTO roles (name, description) VALUES
    ('student', 'Default learner role'),
    ('admin', 'Administrative user role')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO permissions (permission_key, description) VALUES
    ('dashboard.view', 'View the dashboard'),
    ('users.view', 'View users'),
    ('users.create', 'Create users'),
    ('users.update', 'Update users'),
    ('users.delete', 'Delete users'),
    ('courses.view', 'View courses'),
    ('courses.manage', 'Manage courses'),
    ('exams.manage', 'Manage exams'),
    ('certificates.manage', 'Manage certificates')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'dashboard.view',
    'courses.view',
    'users.view'
)
WHERE r.name = 'student'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_key IN (
    'dashboard.view',
    'users.view',
    'users.create',
    'users.update',
    'users.delete',
    'courses.view',
    'courses.manage',
    'exams.manage',
    'certificates.manage'
)
WHERE r.name = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;
