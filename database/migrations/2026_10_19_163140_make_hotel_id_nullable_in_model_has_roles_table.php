
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            DB::transaction(function () {
                DB::statement('
                    CREATE TABLE model_has_roles_new (
                        role_id INTEGER NOT NULL,
                        model_type VARCHAR NOT NULL,
                        model_id INTEGER NOT NULL,
                        hotel_id INTEGER,
                        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                        PRIMARY KEY (hotel_id, role_id, model_id, model_type)
                    )
                ');

                DB::statement('
                    INSERT INTO model_has_roles_new
                        (role_id, model_type, model_id, hotel_id)
                    SELECT role_id, model_type, model_id, hotel_id
                    FROM model_has_roles
                ');

                DB::statement('DROP TABLE model_has_roles');

                DB::statement('
                    ALTER TABLE model_has_roles_new
                    RENAME TO model_has_roles
                ');

                DB::statement('
                    CREATE INDEX model_has_roles_model_id_model_type_index
                    ON model_has_roles (model_id, model_type)
                ');

                DB::statement('
                    CREATE INDEX model_has_roles_team_foreign_key_index
                    ON model_has_roles (hotel_id)
                ');
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    public function down(): void
    {
        // Keep hotel_id nullable for global super-admin assignments.
    }
};
