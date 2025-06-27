<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: أولاً نتحقق من بنية الجدول الحالي
        if (!Schema::hasColumn('course_type_skill', 'skill_type')) {
            Schema::table('course_type_skill', function (Blueprint $table) {
                $table->enum('skill_type', ['progress', 'exam', 'legacy'])->default('legacy')->after('skill_id');
            });
        }

        // Step 2: تحديث البيانات الموجودة
        DB::transaction(function () {
            // تحديث السجلات التي تحتوي على mid_max أو final_max لتصبح exam skills
            DB::table('course_type_skill')
                ->where('skill_type', 'legacy')
                ->where(function ($query) {
                    $query->whereNotNull('mid_max')
                          ->orWhereNotNull('final_max');
                })
                ->update(['skill_type' => 'exam']);

            // تحديث السجلات التي تحتوي على progress_test_max فقط لتصبح progress skills
            DB::table('course_type_skill')
                ->where('skill_type', 'legacy')
                ->whereNotNull('progress_test_max')
                ->where(function ($query) {
                    $query->whereNull('mid_max')
                          ->whereNull('final_max');
                })
                ->update(['skill_type' => 'progress']);

            // معالجة السجلات التي تحتوي على كل أنواع الدرجات
            $mixedRecords = DB::table('course_type_skill')
                ->where('skill_type', 'legacy')
                ->whereNotNull('progress_test_max')
                ->where(function ($query) {
                    $query->whereNotNull('mid_max')
                          ->orWhereNotNull('final_max');
                })
                ->get();

            foreach ($mixedRecords as $record) {
                // إنشاء سجل جديد للـ progress test
                DB::table('course_type_skill')->insert([
                    'course_type_id' => $record->course_type_id,
                    'skill_id' => $record->skill_id,
                    'skill_type' => 'progress',
                    'progress_test_max' => $record->progress_test_max,
                    'mid_max' => null,
                    'final_max' => null,
                    'created_at' => $record->created_at ?? now(),
                    'updated_at' => now(),
                ]);

                // تحديث السجل الأصلي ليصبح exam
                DB::table('course_type_skill')
                    ->where('id', $record->id)
                    ->update([
                        'skill_type' => 'exam',
                        'progress_test_max' => null,
                        'updated_at' => now(),
                    ]);
            }
        });

        // Step 3: إضافة timestamps إذا لم تكن موجودة
        if (!Schema::hasColumn('course_type_skill', 'created_at')) {
            Schema::table('course_type_skill', function (Blueprint $table) {
                $table->timestamps();
            });
            
            DB::table('course_type_skill')
                ->whereNull('created_at')
                ->update([
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        }

        // Step 4: محاولة إضافة الفهرس الجديد
        try {
            // أولاً نحذف أي فهارس قديمة محتملة
            $this->dropExistingIndexes();
            
            // ثم نضيف الفهرس الجديد
            Schema::table('course_type_skill', function (Blueprint $table) {
                $table->index(['course_type_id', 'skill_id', 'skill_type'], 'course_type_skill_composite_idx');
            });
        } catch (Exception $e) {
            // في حالة فشل إضافة الفهرس، نتجاهل الخطأ ونواصل
            \Log::warning('Could not add composite index: ' . $e->getMessage());
        }
    }

    /**
     * حذف الفهارس الموجودة
     */
    private function dropExistingIndexes()
    {
        $indexes = DB::select("SHOW INDEX FROM course_type_skill WHERE Key_name != 'PRIMARY'");
        $indexNames = collect($indexes)->pluck('Key_name')->unique();

        foreach ($indexNames as $indexName) {
            try {
                DB::statement("DROP INDEX `{$indexName}` ON course_type_skill");
            } catch (Exception $e) {
                // تجاهل الأخطاء
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الفهرس الجديد
        try {
            Schema::table('course_type_skill', function (Blueprint $table) {
                $table->dropIndex('course_type_skill_composite_idx');
            });
        } catch (Exception $e) {
            // تجاهل الخطأ
        }

        // حذف السجلات المكررة (التي أنشأناها للـ progress)
        DB::table('course_type_skill')
            ->where('skill_type', 'progress')
            ->delete();

        // إعادة تعيين skill_type إلى legacy
        DB::table('course_type_skill')
            ->update(['skill_type' => 'legacy']);

        // حذف العمود
        if (Schema::hasColumn('course_type_skill', 'skill_type')) {
            Schema::table('course_type_skill', function (Blueprint $table) {
                $table->dropColumn('skill_type');
            });
        }
    }
};