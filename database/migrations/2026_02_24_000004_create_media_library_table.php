<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id');

            // File identification
            $table->string('filename', 255); // original filename
            $table->string('storage_path', 500); // path in storage system
            $table->string('storage_disk', 50)->default('local'); // local, s3, etc.
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size'); // bytes

            // Media classification
            // image (jpeg, png), video (mp4), document (pdf)
            $table->string('media_type', 20);

            // Image/video dimensions
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds, for video

            // Thumbnail (auto-generated for images/video)
            $table->string('thumbnail_path', 500)->nullable();

            // Accessibility / display
            $table->string('alt_text', 500)->nullable();
            $table->string('title', 255)->nullable();

            // Usage tracking
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            // Metadata (EXIF, processing info, etc.)
            $table->jsonb('metadata')->default('{}');

            // Audit
            $table->string('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('account_id');
            $table->index(['account_id', 'media_type']);
            $table->index(['account_id', 'created_at']);

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // CHECK constraint
        DB::statement("ALTER TABLE media_library ADD CONSTRAINT chk_media_type CHECK (media_type IN ('image', 'video', 'document'))");

        // RLS policy
        DB::statement("ALTER TABLE media_library ENABLE ROW LEVEL SECURITY");
        DB::statement("DO \$\$ BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'media_library' AND policyname = 'tenant_isolation_media_library') THEN
                EXECUTE 'CREATE POLICY tenant_isolation_media_library ON media_library
                    USING (account_id = current_setting(''app.current_tenant_id'', true)::uuid)';
            END IF;
        END \$\$");
    }

    public function down(): void
    {
        Schema::dropIfExists('media_library');
    }
};
