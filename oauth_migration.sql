-- ============================================================
-- Migration OAuth 2.0 — ApiUseritium
-- À exécuter UNE SEULE FOIS dans la DB gamenituser
-- ============================================================

CREATE TABLE IF NOT EXISTS oauth_clients (
    id             VARCHAR(64)  NOT NULL PRIMARY KEY,
    redirect_uri   VARCHAR(512) NOT NULL,
    name           VARCHAR(128) NOT NULL,
    allowed_roles  VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si la table existe déjà :
-- ALTER TABLE oauth_clients ADD COLUMN allowed_roles VARCHAR(255) NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS oauth_codes (
    code         CHAR(64)     NOT NULL PRIMARY KEY,
    client_id    VARCHAR(64)  NOT NULL,
    user_id      INT          NOT NULL,
    redirect_uri VARCHAR(512) NOT NULL,
    expires_at   DATETIME     NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS oauth_tokens (
    access_token CHAR(96)     NOT NULL PRIMARY KEY,
    user_id      INT          NOT NULL,
    client_id    VARCHAR(64)  NOT NULL,
    expires_at   DATETIME     NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Ajout du client Odoo (adapte la redirect_uri)
-- ============================================================

-- INSERT INTO oauth_clients (id, redirect_uri, name, allowed_roles) VALUES (
--     'odoo',
--     'https://ton-odoo.fr/web/login',
--     'Odoo',
--     'admin,staff'
-- );
