<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Payroll calculation parameters
            $table->enum('pay_type', ['hourly', 'daily', 'weekly', 'monthly'])->default('monthly')->after('status');
            $table->decimal('base_rate', 10, 2)->nullable()->after('pay_type'); // Tarif de base (DH/heure, jour, semaine ou mois)
            $table->decimal('overtime_multiplier', 4, 2)->default(1.25)->after('base_rate'); // Multiplicateur HS (ex: 1.25 = 125%)
            $table->integer('normal_hours_per_day')->default(8)->after('overtime_multiplier'); // Heures normales par jour
            $table->string('rest_day')->default('dimanche')->after('normal_hours_per_day'); // Jour de repos
            $table->decimal('absence_penalty_rate', 4, 2)->default(1)->after('rest_day'); // Taux de retenue absence (pourcentage)
            $table->string('payment_method')->default('virement')->after('absence_penalty_rate'); // Mode de paiement par défaut
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'pay_type',
                'base_rate',
                'overtime_multiplier',
                'normal_hours_per_day',
                'rest_day',
                'absence_penalty_rate',
                'payment_method',
            ]);
        });
    }
};
