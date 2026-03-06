<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE payments_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                sale_id INTEGER NOT NULL,
                payment_type VARCHAR NOT NULL DEFAULT \'cash\'
                    CHECK (payment_type IN (\'cash\', \'card\', \'check\', \'cheque\', \'virement\', \'credit\', \'mobile\', \'other\')),
                amount NUMERIC NOT NULL,
                received_amount NUMERIC NULL,
                change_amount NUMERIC NOT NULL DEFAULT 0,
                reference VARCHAR NULL,
                notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                transaction_number VARCHAR NULL,
                piece_number VARCHAR NULL,
                issue_date DATE NULL,
                bank_name VARCHAR NULL,
                due_date DATE NULL,
                payment_status VARCHAR NOT NULL DEFAULT \'completed\'
                    CHECK (payment_status IN (\'pending\', \'completed\', \'failed\')),
                is_deferred TINYINT NOT NULL DEFAULT 0,
                collection_status VARCHAR NOT NULL DEFAULT \'pending\',
                collected_at DATETIME NULL,
                collected_by VARCHAR NULL,
                collection_notes TEXT NULL,
                FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO payments_new (
                id, sale_id, payment_type, amount, received_amount, change_amount, reference, notes,
                created_at, updated_at, transaction_number, piece_number, issue_date, bank_name, due_date,
                payment_status, is_deferred, collection_status, collected_at, collected_by, collection_notes
            )
            SELECT
                id, sale_id, payment_type, amount, received_amount, change_amount, reference, notes,
                created_at, updated_at, transaction_number, piece_number, issue_date, bank_name, due_date,
                payment_status, is_deferred, collection_status, collected_at, collected_by, collection_notes
            FROM payments
        ');

        DB::statement('DROP TABLE payments');
        DB::statement('ALTER TABLE payments_new RENAME TO payments');

        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE payments_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                sale_id INTEGER NOT NULL,
                payment_type VARCHAR NOT NULL DEFAULT \'cash\'
                    CHECK (payment_type IN (\'cash\', \'card\', \'check\', \'mobile\', \'other\')),
                amount NUMERIC NOT NULL,
                received_amount NUMERIC NULL,
                change_amount NUMERIC NOT NULL DEFAULT 0,
                reference VARCHAR NULL,
                notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                transaction_number VARCHAR NULL,
                piece_number VARCHAR NULL,
                issue_date DATE NULL,
                bank_name VARCHAR NULL,
                due_date DATE NULL,
                payment_status VARCHAR NOT NULL DEFAULT \'completed\'
                    CHECK (payment_status IN (\'pending\', \'completed\', \'failed\')),
                is_deferred TINYINT NOT NULL DEFAULT 0,
                collection_status VARCHAR NOT NULL DEFAULT \'pending\',
                collected_at DATETIME NULL,
                collected_by VARCHAR NULL,
                collection_notes TEXT NULL,
                FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO payments_old (
                id, sale_id, payment_type, amount, received_amount, change_amount, reference, notes,
                created_at, updated_at, transaction_number, piece_number, issue_date, bank_name, due_date,
                payment_status, is_deferred, collection_status, collected_at, collected_by, collection_notes
            )
            SELECT
                id, sale_id,
                CASE
                    WHEN payment_type IN (\'cheque\', \'virement\', \'credit\') THEN \'other\'
                    ELSE payment_type
                END,
                amount, received_amount, change_amount, reference, notes,
                created_at, updated_at, transaction_number, piece_number, issue_date, bank_name, due_date,
                payment_status, is_deferred, collection_status, collected_at, collected_by, collection_notes
            FROM payments
        ');

        DB::statement('DROP TABLE payments');
        DB::statement('ALTER TABLE payments_old RENAME TO payments');

        DB::statement('PRAGMA foreign_keys=ON');
    }
};
