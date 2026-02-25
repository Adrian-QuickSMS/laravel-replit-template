<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            WITH inserted AS (
                INSERT INTO sender_ids (
                    uuid,
                    account_id,
                    sender_id_value,
                    sender_type,
                    brand_name,
                    country_code,
                    use_case,
                    use_case_description,
                    permission_confirmed,
                    workflow_status,
                    is_locked,
                    is_default,
                    submitted_at,
                    reviewed_at,
                    full_payload,
                    created_at,
                    updated_at
                )
                SELECT
                    gen_random_uuid(),
                    pn.account_id,
                    pn.number,
                    'NUMERIC',
                    pn.number,
                    pn.country_iso,
                    'transactional',
                    'Auto-registered from VMN purchase',
                    true,
                    'approved',
                    true,
                    false,
                    NOW(),
                    NOW(),
                    jsonb_build_object(
                        'source', 'vmn_purchase_backfill',
                        'purchased_number_id', pn.id::text
                    ),
                    NOW(),
                    NOW()
                FROM purchased_numbers pn
                WHERE pn.number_type = 'vmn'
                  AND pn.status = 'active'
                  AND pn.deleted_at IS NULL
                  AND pn.sender_id_id IS NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM sender_ids si
                      WHERE si.account_id = pn.account_id
                        AND si.sender_id_value = pn.number
                        AND si.deleted_at IS NULL
                  )
                RETURNING uuid, account_id, sender_id_value
            )
            UPDATE purchased_numbers pn
            SET sender_id_id = inserted.uuid
            FROM inserted
            WHERE pn.account_id = inserted.account_id
              AND pn.number = inserted.sender_id_value
              AND pn.sender_id_id IS NULL
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            UPDATE purchased_numbers pn
            SET sender_id_id = NULL
            FROM sender_ids si
            WHERE pn.sender_id_id = si.uuid
              AND si.full_payload->>'source' = 'vmn_purchase_backfill'
        ");

        DB::unprepared("
            DELETE FROM sender_ids
            WHERE full_payload->>'source' = 'vmn_purchase_backfill'
        ");
    }
};
