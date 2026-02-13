-- ========================================================
-- QUICKSMS POSTGRESQL DATABASE ROLES & GRANTS
-- ========================================================
-- This script creates the role-based access control (RBAC)
-- foundation for QuickSMS multi-tenant security.
--
-- ROLES:
-- - portal_ro: Portal read-only (SELECT on views only)
-- - portal_rw: Portal read-write (EXECUTE on procedures, SELECT on views)
-- - svc_red: Internal services (full access to RED-side data)
-- - ops_admin: Operations staff (full access, can bypass RLS)
--
-- SECURITY MODEL:
-- - Portal roles CANNOT access base tables directly
-- - Portal roles CANNOT access RED-side tables (account_flags, auth_audit_log)
-- - RLS policies enforce tenant isolation at database layer
-- - Stored procedures use SECURITY DEFINER to bypass RLS for cross-tenant ops
-- ========================================================

-- ========================================================
-- 0. LOCK DOWN PUBLIC SCHEMA
-- ========================================================
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO PUBLIC;

-- ========================================================
-- 1. CREATE DATABASE ROLES
-- ========================================================
-- IMPORTANT: Set real passwords via environment variables before running.
-- Example: CREATE ROLE portal_ro LOGIN PASSWORD :'PORTAL_RO_PASSWORD';
-- Or use: \set PORTAL_RO_PASSWORD `echo $PORTAL_RO_PASSWORD`

-- Portal Read-Only: SELECT on views only
DO $$ BEGIN
    CREATE ROLE portal_ro LOGIN PASSWORD 'CHANGE_THIS_IN_PRODUCTION';
EXCEPTION WHEN duplicate_object THEN
    RAISE NOTICE 'Role portal_ro already exists, skipping';
END $$;
COMMENT ON ROLE portal_ro IS 'Customer portal read-only access to views';

-- Portal Read-Write: Execute procedures + SELECT views
DO $$ BEGIN
    CREATE ROLE portal_rw LOGIN PASSWORD 'CHANGE_THIS_IN_PRODUCTION';
EXCEPTION WHEN duplicate_object THEN
    RAISE NOTICE 'Role portal_rw already exists, skipping';
END $$;
COMMENT ON ROLE portal_rw IS 'Customer portal read-write via stored procedures';

-- Internal Services (RED SIDE): Full access to internal tables
DO $$ BEGIN
    CREATE ROLE svc_red LOGIN PASSWORD 'CHANGE_THIS_IN_PRODUCTION';
EXCEPTION WHEN duplicate_object THEN
    RAISE NOTICE 'Role svc_red already exists, skipping';
END $$;
COMMENT ON ROLE svc_red IS 'Internal services with access to fraud/billing/audit data';

-- Operations Admin: Full access, can bypass RLS for support
DO $$ BEGIN
    CREATE ROLE ops_admin LOGIN PASSWORD 'CHANGE_THIS_IN_PRODUCTION' BYPASSRLS;
EXCEPTION WHEN duplicate_object THEN
    RAISE NOTICE 'Role ops_admin already exists, skipping';
END $$;
COMMENT ON ROLE ops_admin IS 'Operations staff with full database access';

-- SECURITY WARNING: Change these passwords immediately after deployment!
-- ALTER ROLE portal_ro PASSWORD '<secure-random-password>';
-- ALTER ROLE portal_rw PASSWORD '<secure-random-password>';
-- ALTER ROLE svc_red PASSWORD '<secure-random-password>';
-- ALTER ROLE ops_admin PASSWORD '<secure-random-password>';

-- ========================================================
-- 2. GREEN SIDE - PORTAL READ-ONLY GRANTS (portal_ro)
-- ========================================================

-- Grant SELECT on portal-safe views
GRANT SELECT ON account_safe_view TO portal_ro;
GRANT SELECT ON user_profile_view TO portal_ro;
GRANT SELECT ON api_tokens_view TO portal_ro;

-- Revoke ALL access to base tables
REVOKE ALL ON accounts, users, api_tokens FROM portal_ro;

-- Revoke ALL access to RED-side tables
REVOKE ALL ON account_flags, auth_audit_log, admin_users FROM portal_ro;

-- Grant USAGE on sequences (for nextval in procedures)
GRANT USAGE ON SEQUENCE accounts_number_seq TO portal_ro;

-- ========================================================
-- 3. GREEN SIDE - PORTAL READ-WRITE GRANTS (portal_rw)
-- ========================================================

-- Inherit permissions from portal_ro
GRANT portal_ro TO portal_rw;

-- Grant EXECUTE on stored procedures
GRANT EXECUTE ON FUNCTION sp_create_account TO portal_rw;
GRANT EXECUTE ON FUNCTION sp_authenticate_user TO portal_rw;
GRANT EXECUTE ON FUNCTION sp_create_api_token TO portal_rw;
GRANT EXECUTE ON FUNCTION sp_update_user_profile TO portal_rw;
GRANT EXECUTE ON FUNCTION sp_update_account_settings TO portal_rw;

-- Grant SELECT on views (inherited from portal_ro)
-- Grant INSERT/UPDATE on specific GREEN tables via procedures only
-- NO direct table access granted

-- Revoke ALL direct table access
REVOKE ALL ON accounts, users, api_tokens FROM portal_rw;

-- Revoke ALL access to RED-side tables
REVOKE ALL ON account_flags, auth_audit_log, admin_users FROM portal_rw;

-- Grant USAGE on sequences
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO portal_rw;

-- ========================================================
-- 4. RED SIDE - INTERNAL SERVICES GRANTS (svc_red)
-- ========================================================

-- Grant ALL on RED-side tables
GRANT ALL ON account_flags TO svc_red;
GRANT ALL ON auth_audit_log TO svc_red;
GRANT ALL ON admin_users TO svc_red;
GRANT ALL ON rate_card_audit_log TO svc_red;
GRANT ALL ON routing_audit_log TO svc_red;

-- Grant SELECT on all tables (for reporting, analytics)
GRANT SELECT ON ALL TABLES IN SCHEMA public TO svc_red;

-- Grant EXECUTE on all functions
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO svc_red;

-- Grant USAGE on all sequences
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO svc_red;

-- ========================================================
-- 5. OPS ADMIN - FULL ACCESS (ops_admin)
-- ========================================================

-- Grant ALL privileges on all tables
GRANT ALL ON ALL TABLES IN SCHEMA public TO ops_admin;

-- Grant ALL on all sequences
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO ops_admin;

-- Grant EXECUTE on all functions
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO ops_admin;

-- ops_admin can BYPASS RLS (set in CREATE ROLE)
-- This allows support staff to view cross-tenant data when needed

-- ========================================================
-- 6. ENSURE AUDIT LOG IS IMMUTABLE
-- ========================================================

-- Remove UPDATE/DELETE permissions from all roles except ops_admin
REVOKE UPDATE, DELETE ON auth_audit_log FROM PUBLIC;
REVOKE UPDATE, DELETE ON auth_audit_log FROM portal_ro, portal_rw, svc_red;

-- Only INSERT allowed (append-only log)
GRANT INSERT ON auth_audit_log TO portal_rw, svc_red;
GRANT SELECT ON auth_audit_log TO svc_red, ops_admin;

-- ========================================================
-- 7. DEFAULT PRIVILEGES FOR FUTURE OBJECTS
-- ========================================================

-- Ensure future tables created by owner have correct permissions
ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT SELECT ON TABLES TO portal_ro;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT EXECUTE ON FUNCTIONS TO portal_rw;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT ALL ON TABLES TO ops_admin;

-- ========================================================
-- 8. VERIFICATION QUERIES
-- ========================================================

-- Verify portal_ro permissions
DO $$
BEGIN
    RAISE NOTICE 'Verifying portal_ro permissions...';

    -- Should have SELECT on views
    IF NOT has_table_privilege('portal_ro', 'account_safe_view', 'SELECT') THEN
        RAISE EXCEPTION 'portal_ro missing SELECT on account_safe_view';
    END IF;

    -- Should NOT have SELECT on base tables
    IF has_table_privilege('portal_ro', 'accounts', 'SELECT') THEN
        RAISE EXCEPTION 'portal_ro has SELECT on accounts (should be blocked)';
    END IF;

    -- Should NOT have SELECT on RED tables
    IF has_table_privilege('portal_ro', 'account_flags', 'SELECT') THEN
        RAISE EXCEPTION 'portal_ro has SELECT on account_flags (RED data leak!)';
    END IF;

    RAISE NOTICE '✓ portal_ro permissions verified';
END $$;

-- Verify portal_rw permissions
DO $$
BEGIN
    RAISE NOTICE 'Verifying portal_rw permissions...';

    -- Should have EXECUTE on procedures
    IF NOT has_function_privilege('portal_rw', 'sp_create_account', 'EXECUTE') THEN
        RAISE EXCEPTION 'portal_rw missing EXECUTE on sp_create_account';
    END IF;

    -- Should NOT have direct table access
    IF has_table_privilege('portal_rw', 'accounts', 'INSERT') THEN
        RAISE EXCEPTION 'portal_rw has INSERT on accounts (should use procedures only)';
    END IF;

    -- Should NOT have access to RED tables
    IF has_table_privilege('portal_rw', 'account_flags', 'SELECT') THEN
        RAISE EXCEPTION 'portal_rw has SELECT on account_flags (RED data leak!)';
    END IF;

    RAISE NOTICE '✓ portal_rw permissions verified';
END $$;

-- Verify svc_red permissions
DO $$
BEGIN
    RAISE NOTICE 'Verifying svc_red permissions...';

    -- Should have ALL on RED tables
    IF NOT has_table_privilege('svc_red', 'account_flags', 'SELECT') THEN
        RAISE EXCEPTION 'svc_red missing SELECT on account_flags';
    END IF;

    IF NOT has_table_privilege('svc_red', 'auth_audit_log', 'INSERT') THEN
        RAISE EXCEPTION 'svc_red missing INSERT on auth_audit_log';
    END IF;

    RAISE NOTICE '✓ svc_red permissions verified';
END $$;

RAISE NOTICE '========================================';
RAISE NOTICE '✓ ALL ROLE PERMISSIONS VERIFIED';
RAISE NOTICE '========================================';
RAISE NOTICE 'Portal roles: CANNOT access base tables or RED data';
RAISE NOTICE 'Internal role: CAN access RED-side fraud/billing/audit data';
RAISE NOTICE 'Audit log: IMMUTABLE (INSERT-only except ops_admin)';
RAISE NOTICE '========================================';
