<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Course #{{ $progressTest->course_id }}</title>

@php
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png')));
@endphp

<style>
@font-face{font-family:'cairo';src:url('{{ $fontRegular }}') format('truetype');font-weight:400}
@font-face{font-family:'cairo';src:url('{{ $fontBold }}') format('truetype');font-weight:700}
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:1024px;height:1024px;font-family:'cairo',sans-serif;color:#fff}
body{background:url('data:image/png;base64,{{ $bgData }}') no-repeat center center;background-size:cover}
.container{width:100%;height:100%;display:flex;flex-direction:column;justify-content:space-between}
.title{font-size:30px;font-weight:700;margin-bottom:15px;position:absolute;top:160px;left:200px}
.sub-details{font-size:18px}
.table-container{flex:1;display:flex;align-items:center;justify-content:center}
table{width:90%;margin:auto;border-collapse:collapse;position:absolute;top:300px}
th,td{font-size:15px;padding:10px;background:#000;border:1px solid #333;text-align:center}
</style>
</head>
<body>
<div class="container">
    <h1 class="title">PROGRESS TEST RESULTS - WEEK {{ $progressTest->week }} (#{{ $progressTest->course_id }})</h1>
    <div class="sub-details" style="position:absolute;top:210px;right:50px">
        Instructor: {{ optional($progressTest->course->instructor)->name ?? 'Unassigned' }}<br>
        Days: {{ $progressTest->course->days ?? '-' }}<br>
        Course: {{ $progressTest->course->courseType->name ?? '-' }}
    </div>
    <div class="sub-details" style="position:absolute;top:210px;left:200px;text-align:right">
        Date: {{ $progressTest->date ? \Carbon\Carbon::parse($progressTest->date)->format('Y-m-d') : '-' }}<br>
        Week: {{ $progressTest->week }}<br>
        Time: {{ $progressTest->time ? \Carbon\Carbon::parse($progressTest->time)->format('h:i A') : '-' }}
    </div>

    <div class="table-container">
        @php
            $skills   = $progressTest->course->courseType->skills;
            $students = $progressTest->progressTestStudents()->with(['student','grades'])->where('status','present')->get();
            
            // حساب المتوسطات
            $skillAverages = [];
            $totalPercentageSum = 0;
            $studentCount = $students->count();
            
            // حساب متوسط كل مهارة
            foreach ($skills as $skillIndex => $skill) {
                $skillGradeSum = 0;
                $skillCount = 0;
                foreach ($students as $student) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    if ($grade && $grade->progress_test_grade !== null) {
                        $skillGradeSum += $grade->progress_test_grade;
                        $skillCount++;
                    }
                }
                $skillAverages[$skillIndex] = $skillCount > 0 ? $skillGradeSum / $skillCount : 0;
            }
            
            // حساب متوسط النسبة المئوية
            foreach ($students as $student) {
                $total = 0;
                $max = 0;
                foreach ($skills as $skill) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    $val = $grade?->progress_test_grade;
                    $m = $grade?->max_grade ?? 0;
                    $total += ($val ?? 0);
                    $max += $m;
                }
                $percent = $max ? round(($total / $max) * 100) : 0;
                $totalPercentageSum += $percent;
            }
            
            $averagePercentage = $studentCount > 0 ? round($totalPercentageSum / $studentCount, 1) : 0;
            
            // حساب إحصائيات النجاح والرسوب
            $passCount = 0;
            foreach ($students as $student) {
                $total = 0;
                $max = 0;
                foreach ($skills as $skill) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    $val = $grade?->progress_test_grade;
                    $m = $grade?->max_grade ?? 0;
                    $total += ($val ?? 0);
                    $max += $m;
                }
                $percent = $max ? round(($total / $max) * 100) : 0;
                if ($percent >= 50) $passCount++;
            }
            $failCount = $studentCount - $passCount;
            
            // حساب العدد الإجمالي للطلاب المسجلين
            $totalEnrolled = $progressTest->course->students()->wherePivot('status', 'ongoing')->count();
            $absentCount = $totalEnrolled - $studentCount;
        @endphp
        
        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAME</th>
                    @foreach($skills as $s)
                        <th title="{{ $s->name }}">{{ mb_substr($s->name,0,2,'UTF-8') }}</th>
                    @endforeach
                    <th>TOTAL</th>
                    <th>PER</th>
                    <th>STATUS</th>
                </tr>
                <tr style="font-size: 14px; background: #333;">
                    <th colspan="2">MAX GRADES</th>
                    @foreach($skills as $s)
                        @php
                            $maxGrade = $students->first()?->grades->firstWhere('course_type_skill_id', $s->pivot->id)?->max_grade ?? 0;
                        @endphp
                        <th>{{ $maxGrade }}</th>
                    @endforeach
                    <th>{{ $students->first() ? $students->first()->grades->sum('max_grade') : 0 }}</th>
                    <th>100%</th>
                    <th>PASS/FAIL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i=>$rec)
                    @php 
                        $total=0;
                        $max=0; 
                        $grades = [];
                        $maxGrades = [];
                    @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td style="text-align: left; font-size: 16px;">{{ $rec->student->name }}</td>
                        @foreach($skills as $s)
                            @php
                                $grade=$rec->grades->firstWhere('course_type_skill_id',$s->pivot->id);
                                $val=$grade?->progress_test_grade;
                                $m=$grade?->max_grade ?? 0;
                                $total+=($val??0);
                                $max+=$m;
                                $grades[] = $val;
                                $maxGrades[] = $m;
                            @endphp
                            <td style="color: {{ ($val !== null && $m > 0 && $val >= ($m * 0.5)) ? '#0f0' : '#f90' }}">
                                {{ $val !== null ? number_format($val, 1) : '-' }}
                            </td>
                        @endforeach
                        @php $percent=$max?round(($total/$max)*100):0; @endphp
                        <td style="font-weight: bold;">{{ number_format($total, 1) }}</td>
                        <td style="color:{{ $percent<50?'#f00':'#0f0' }}; font-weight: bold;">{{ $percent }}%</td>
                        <td style="color:{{ $percent<50?'#f00':'#0f0' }}; font-weight: bold;">{{ $percent >= 50 ? 'PASS' : 'FAIL' }}</td>
                    </tr>
                @endforeach
            </tbody>
            
            <tfoot>
                <!-- صف المتوسط -->
                <tr style="background: #2a2a2a; font-weight: bold; border-top: 2px solid #666;">
                    <td style="background: #333; color: #ffd700;">AVG</td>
                    <td style="text-align: left; font-size: 16px; background: #333; color: #ffd700; font-style: italic;">Class Average</td>
                    @foreach($skillAverages as $avg)
                        <td style="color: #00bfff; background: #2a2a2a;">
                            {{ number_format($avg, 1) }}
                        </td>
                    @endforeach
                    @php
                        $averageTotal = array_sum($skillAverages);
                    @endphp
                    <td style="font-weight: bold; background: #2a2a2a; color: #fff;">{{ number_format($averageTotal, 1) }}</td>
                    <td style="color: {{ $averagePercentage >= 50 ? '#0f0' : '#f00' }}; font-weight: bold; background: #2a2a2a;">{{ $averagePercentage }}%</td>
                    <td style="color: {{ $averagePercentage >= 50 ? '#0f0' : '#f00' }}; font-weight: bold; background: #2a2a2a;">{{ $averagePercentage >= 50 ? 'PASS' : 'FAIL' }}</td>
                </tr>
                
                <!-- صف الإحصائيات -->
                <tr style="background: #444; font-weight: bold;">
                    <td colspan="2">STATISTICS (PRESENT ONLY)</td>
                    @if($skills->count() >= 3)
                        <td style="color: #0f0;">PASS: {{ $passCount }}</td>
                        <td style="color: #f00;">FAIL: {{ $failCount }}</td>
                        <td style="color: #ff0;">ABSENT: {{ $absentCount }}</td>
                        @if($skills->count() > 3)
                            <td colspan="{{ $skills->count() - 3 }}">-</td>
                        @endif
                    @else
                        @for($i = 0; $i < $skills->count(); $i++)
                            @if($i == 0)
                                <td style="color: #0f0;">PASS: {{ $passCount }}</td>
                            @elseif($i == 1)
                                <td style="color: #f00;">FAIL: {{ $failCount }}</td>
                            @else
                                <td>-</td>
                            @endif
                        @endfor
                    @endif
                    <td>{{ $studentCount }}/{{ $totalEnrolled }}</td>
                    <td>{{ $studentCount ? round($passCount / $studentCount * 100, 1) : 0 }}%</td>
                    <td>{{ round($passCount / max($studentCount, 1) * 100, 1) }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>