-- =====================================================
-- Lead Management System - Database Schema
-- Version: 2.0 (PHP Migration)
-- Created: 2025-10-29
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS lead_management_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lead_management_system;

-- =====================================================
-- Table: users
-- Description: User accounts with role-based access
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
    role ENUM('superadmin', 'admin', 'agent') NOT NULL DEFAULT 'agent',
    status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,

    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: refresh_tokens
-- Description: JWT Refresh Tokens
-- =====================================================
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE COMMENT 'Hashed refresh token',
    expires_at TIMESTAMP NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: login_attempts
-- Description: Track login attempts for rate limiting
-- =====================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,

    INDEX idx_email_ip (email, ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: password_resets
-- Description: Password reset tokens
-- =====================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: leads
-- Description: Lead information and management
-- =====================================================
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    company VARCHAR(255) NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NULL,
    status ENUM('new', 'contacted', 'qualified', 'unqualified', 'converted') NOT NULL DEFAULT 'new',
    qualification_score INT NULL COMMENT '0-100',
    notes TEXT NULL,
    assigned_to INT NULL COMMENT 'Assigned user ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX idx_fulltext_search (first_name, last_name, company, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: lead_status_history
-- Description: Track lead status changes
-- =====================================================
CREATE TABLE IF NOT EXISTS lead_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    old_status ENUM('new', 'contacted', 'qualified', 'unqualified', 'converted') NULL,
    new_status ENUM('new', 'contacted', 'qualified', 'unqualified', 'converted') NOT NULL,
    changed_by INT NULL COMMENT 'User who made the change',
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_lead_id (lead_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: campaigns
-- Description: Campaign configurations
-- =====================================================
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    voice_provider ENUM('openai', 'elevenlabs') NOT NULL,
    agent_prompt TEXT NOT NULL,
    qualification_questions JSON NULL COMMENT 'Array of questions',
    status ENUM('draft', 'active', 'paused', 'completed') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: campaign_leads
-- Description: Many-to-Many relationship between campaigns and leads
-- =====================================================
CREATE TABLE IF NOT EXISTS campaign_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    lead_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_lead (campaign_id, lead_id),
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_lead_id (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: calls
-- Description: Call records and history
-- =====================================================
CREATE TABLE IF NOT EXISTS calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    campaign_id INT NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'failed', 'no_answer') NOT NULL DEFAULT 'scheduled',
    outcome ENUM('answered', 'no_answer', 'voicemail', 'busy', 'failed') NULL,
    duration INT NULL COMMENT 'Duration in seconds',
    recording_url VARCHAR(500) NULL,
    notes TEXT NULL,
    called_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    INDEX idx_lead_id (lead_id),
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_status (status),
    INDEX idx_called_at (called_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: user_preferences
-- Description: User-specific settings and preferences
-- =====================================================
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: audit_log
-- Description: Audit trail for important actions
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
