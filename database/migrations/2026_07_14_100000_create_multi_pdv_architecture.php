<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable()->unique();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('store_user')) {
            Schema::create('store_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role_in_store')->default('cashier');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['store_id', 'user_id']);
            });
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE users MODIFY role VARCHAR(30) NOT NULL DEFAULT 'cashier'");
            } catch (\Throwable $e) {
                // already widened
            }
        }

        if (! Schema::hasColumn('users', 'default_store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_store_id')->nullable()->after('is_active');
            });
        }

        foreach (['categories', 'articles', 'customers', 'sales', 'employees'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'store_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('id')->index();
                });
            }
        }

        if (Schema::hasTable('custom_lists') && ! Schema::hasColumn('custom_lists', 'store_id')) {
            Schema::table('custom_lists', function (Blueprint $table) {
                $table->unsignedBigInteger('store_id')->nullable()->after('id')->index();
            });
        }

        if (Schema::hasTable('custom_lists') && Schema::hasColumn('custom_lists', 'store_id')) {
            try {
                Schema::table('custom_lists', function (Blueprint $table) {
                    $table->dropUnique(['name']);
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('custom_lists', function (Blueprint $table) {
                    $table->unique(['store_id', 'name']);
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'store_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('store_id')->nullable()->after('id')->index();
            });
            try {
                Schema::table('settings', function (Blueprint $table) {
                    $table->dropUnique(['key']);
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('settings', function (Blueprint $table) {
                    $table->unique(['store_id', 'key']);
                });
            } catch (\Throwable $e) {
            }
        }

        // suppliers already existed in some DBs — extend instead of recreate
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->string('name');
                $table->string('contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->string('ice')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('suppliers', function (Blueprint $table) {
                if (! Schema::hasColumn('suppliers', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('suppliers', 'contact_name')) {
                    $table->string('contact_name')->nullable()->after('name');
                }
                if (! Schema::hasColumn('suppliers', 'ice')) {
                    $table->string('ice')->nullable();
                }
                if (! Schema::hasColumn('suppliers', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (! Schema::hasColumn('suppliers', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });

            if (Schema::hasColumn('suppliers', 'tax_id') && Schema::hasColumn('suppliers', 'ice')) {
                DB::table('suppliers')->whereNull('ice')->whereNotNull('tax_id')->update([
                    'ice' => DB::raw('tax_id'),
                ]);
            }
        }

        // expenses already existed — extend for multi-PDV charges
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('label');
                $table->string('category')->nullable();
                $table->string('expense_type')->default('variable');
                $table->decimal('amount', 12, 2);
                $table->string('payment_method')->nullable();
                $table->date('expense_date');
                $table->boolean('is_recurring')->default(false);
                $table->string('frequency')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('expenses', function (Blueprint $table) {
                if (! Schema::hasColumn('expenses', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('expenses', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                }
                if (! Schema::hasColumn('expenses', 'label')) {
                    $table->string('label')->nullable();
                }
                if (! Schema::hasColumn('expenses', 'category')) {
                    $table->string('category')->nullable();
                }
                if (! Schema::hasColumn('expenses', 'expense_type')) {
                    $table->string('expense_type')->default('variable');
                }
                if (! Schema::hasColumn('expenses', 'is_recurring')) {
                    $table->boolean('is_recurring')->default(false);
                }
                if (! Schema::hasColumn('expenses', 'frequency')) {
                    $table->string('frequency')->nullable();
                }
                if (! Schema::hasColumn('expenses', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });

            if (Schema::hasColumn('expenses', 'designation') && Schema::hasColumn('expenses', 'label')) {
                DB::table('expenses')->whereNull('label')->whereNotNull('designation')->update([
                    'label' => DB::raw('designation'),
                ]);
            }
            if (Schema::hasColumn('expenses', 'expense_category') && Schema::hasColumn('expenses', 'category')) {
                DB::table('expenses')->whereNull('category')->whereNotNull('expense_category')->update([
                    'category' => DB::raw('expense_category'),
                ]);
            }
        }

        // Seed default store once for existing superadmin (only if empty)
        if (Schema::hasTable('stores') && DB::table('stores')->count() === 0) {
            $ownerId = DB::table('users')->where('role', 'superadmin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            if ($ownerId) {
                $storeId = DB::table('stores')->insertGetId([
                    'name' => 'Point de Vente Principal',
                    'code' => 'PDV-001',
                    'owner_id' => $ownerId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('store_user')->insert([
                    'store_id' => $storeId,
                    'user_id' => $ownerId,
                    'role_in_store' => 'owner',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->where('id', $ownerId)->update([
                    'default_store_id' => $storeId,
                ]);

                foreach (['categories', 'articles', 'customers', 'sales', 'employees', 'custom_lists', 'settings', 'suppliers', 'expenses'] as $tableName) {
                    if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'store_id')) {
                        DB::table($tableName)->whereNull('store_id')->update(['store_id' => $storeId]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally minimal — multi-PDV columns may coexist with legacy rows
    }
};
