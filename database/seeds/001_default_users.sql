-- =====================================================
-- Lead Management System - Initial Data Seeds
-- Version: 2.0
-- =====================================================

USE lead_management_system;

-- =====================================================
-- Insert Default Superadmin User
-- Credentials:
--   Email: admin@leadmanager.com
--   Password: admin123
-- =====================================================
INSERT INTO users (username, email, password, role, status, created_at)
VALUES (
    'admin',
    'admin@leadmanager.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- bcrypt hash of 'admin123'
    'superadmin',
    'active',
    CURRENT_TIMESTAMP
);

-- =====================================================
-- Insert Demo Users for Testing
-- =====================================================
INSERT INTO users (username, email, password, role, status, created_at)
VALUES
    (
        'manager',
        'manager@leadmanager.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: admin123
        'admin',
        'active',
        CURRENT_TIMESTAMP
    ),
    (
        'agent_user',
        'agent@leadmanager.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: admin123
        'agent',
        'active',
        CURRENT_TIMESTAMP
    );

-- =====================================================
-- Insert Demo Leads
-- =====================================================
INSERT INTO leads (first_name, last_name, company, phone, email, status, qualification_score, notes, assigned_to)
VALUES
    ('Max', 'Mustermann', 'Musterfirma GmbH', '+49 30 12345678', 'max.mustermann@example.com', 'new', 75, 'Sehr interessiert an unserem Produkt', 2),
    ('Anna', 'Schmidt', 'Tech Solutions AG', '+49 89 98765432', 'anna.schmidt@techsolutions.de', 'contacted', 85, 'Erstkontakt erfolgreich, Follow-up geplant', 2),
    ('Peter', 'Müller', 'Innovation Labs', '+49 40 55566677', 'peter.mueller@innolabs.com', 'qualified', 90, 'Hohe Kaufabsicht, Budget vorhanden', 2),
    ('Sarah', 'Weber', 'Digital Marketing Pro', '+49 69 33344455', 'sarah.weber@digimarketing.de', 'contacted', 60, 'Benötigt mehr Informationen', 3),
    ('Thomas', 'Fischer', 'StartUp GmbH', '+49 30 77788899', 'thomas.fischer@startup.io', 'new', NULL, 'Lead aus Webformular', NULL),
    ('Julia', 'Becker', 'Enterprise Solutions', '+49 89 11122233', 'julia.becker@enterprise.com', 'converted', 95, 'Deal abgeschlossen!', 2),
    ('Michael', 'Wagner', 'Consulting Group', '+49 40 44455566', 'michael.wagner@consulting.de', 'unqualified', 20, 'Kein aktueller Bedarf', 2),
    ('Laura', 'Hoffmann', 'Creative Agency', '+49 30 22233344', 'laura.hoffmann@creative.de', 'contacted', 70, 'Interessiert, Termin vereinbart', 3);

-- =====================================================
-- Insert Demo Campaigns
-- =====================================================
INSERT INTO campaigns (name, voice_provider, agent_prompt, qualification_questions, status)
VALUES
    (
        'Q4 2025 Outreach',
        'openai',
        'Du bist ein freundlicher Vertriebsmitarbeiter für ein Lead Management System. Stelle dich vor und frage nach dem Interesse des Kunden.',
        '["Haben Sie aktuell ein CRM-System?", "Wie viele Mitarbeiter nutzen das System?", "Was ist Ihr Budget für neue Software?"]',
        'active'
    ),
    (
        'Holiday Special Campaign',
        'elevenlabs',
        'Du bist ein enthusiastischer Sales Agent. Informiere über unser Weihnachtsangebot mit 20% Rabatt.',
        '["Interessieren Sie sich für unsere Weihnachtsaktion?", "Wann möchten Sie starten?"]',
        'draft'
    ),
    (
        'Reactivation Campaign',
        'openai',
        'Du kontaktierst ehemalige Interessenten. Sei höflich und frage nach erneutem Interesse.',
        '["Hat sich Ihre Situation geändert?", "Möchten Sie mehr über neue Features erfahren?"]',
        'paused'
    );

-- =====================================================
-- Assign Leads to Campaigns
-- =====================================================
INSERT INTO campaign_leads (campaign_id, lead_id)
VALUES
    (1, 1),
    (1, 2),
    (1, 3),
    (1, 4),
    (2, 5),
    (2, 6),
    (3, 7),
    (3, 8);

-- =====================================================
-- Insert Demo Calls
-- =====================================================
INSERT INTO calls (lead_id, campaign_id, status, outcome, duration, notes, called_at)
VALUES
    (1, 1, 'completed', 'answered', 180, 'Gutes Gespräch, Interesse bekundet', DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (2, 1, 'completed', 'answered', 240, 'Follow-up nötig', DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (3, 1, 'completed', 'answered', 360, 'Demo vereinbart', NOW()),
    (4, 1, 'completed', 'no_answer', NULL, 'Nicht erreicht, Mailbox', DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (5, 2, 'scheduled', NULL, NULL, 'Geplant für morgen', DATE_ADD(NOW(), INTERVAL 1 DAY)),
    (6, 2, 'completed', 'answered', 420, 'Deal abgeschlossen!', DATE_SUB(NOW(), INTERVAL 5 DAY)),
    (7, 3, 'completed', 'answered', 120, 'Kein Interesse', DATE_SUB(NOW(), INTERVAL 7 DAY)),
    (8, 3, 'in_progress', NULL, NULL, 'Aktuell im Gespräch', NOW());

-- =====================================================
-- Insert Lead Status History
-- =====================================================
INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by, changed_at)
VALUES
    (1, NULL, 'new', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
    (2, 'new', 'contacted', 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (3, 'new', 'contacted', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
    (3, 'contacted', 'qualified', 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (6, 'qualified', 'converted', 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
    (7, 'contacted', 'unqualified', 2, DATE_SUB(NOW(), INTERVAL 7 DAY));

-- =====================================================
-- Insert User Preferences
-- =====================================================
INSERT INTO user_preferences (user_id, notifications_enabled, email_notifications)
VALUES
    (1, TRUE, TRUE),
    (2, TRUE, TRUE),
    (3, TRUE, FALSE);
