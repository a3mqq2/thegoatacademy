<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Exam #{{ $exam->course_id }}</title>

@php
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png')));
@endphp

<style>
@font-face {
    font-family: 'cairo';
    src: url('{{ $fontRegular }}') format('truetype');
    font-weight: 400;
}
@font-face {
    font-family: 'cairo';
    src: url('{{ $fontBold }}') format('truetype');
    font-weight: 700;
}
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html, body {
    width: 1024px;
    height: 1024px;
    font-family: 'cairo', sans-serif;
    color: #fff;
}
body {
    background: url('data:image/png;base64,{{ $bgData }}') no-repeat center center;
    background-size: cover;
}
.container {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.header {
    text-align: center;
}
.title {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 20px;
}
.sub-details {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    margin-bottom: 30px;
}
.sub-details div {
    width: 48%;
}
.table-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
table {
    width: 90%;
    margin: auto;
    border-collapse: collapse;
    position: absolute;
    top: 300px;
}
th, td {
    font-size: 20px;
    padding: 10px;
    background: #000;
    border: 1px solid #333;
    text-align: center;
}
.title {
    position: absolute;
    top: 160px;
    left: 40px;
}
.exam-type-badge {
    background: #007bff;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 14px;
    margin-left: 10px;
}
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1 class="title">
            {{ strtoupper($exam->course->courseType->name) }} - {{ strtoupper($exam->exam_type) }} EXAM RESULTS (#{{ $exam->course_id }})
        </h1>
        <div class="sub-details">
            <div style="position: absolute; top:210px;">
                Instructor : {{ optional($exam->course->instructor)->name ?? 'Unassigned' }}<br>
                Days: {{ $exam->course->days ?? '-' }}<br>
            </div>
            <div style="text-align: right; position: absolute; top:210px; left:250px;">
                Time: {{ $exam->time ? \Carbon\Carbon::parse($exam->time)->format('h:i A') : '-' }}<br>
                Date: {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') : '-' }}<br>
            </div>
        </div>
    </div>

    <div class="table-container">
        @php
            // استخدام examSkills فقط للـ Mid و Final
            $skills  = $exam->course->courseType->examSkills;
            $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
            
            // فلترة الطلاب الحاضرين فقط
            $presentStudents = $ongoing->filter(function($student) use ($exam) {
                $examStudent = $exam->examStudents->firstWhere('student_id', $student->id);
                return !$examStudent || $examStudent->status !== 'absent';
            });

            // حساب المتوسطات لكل مهارة
            $skillAverages = [];
            $totalGradesSum = 0;
            $totalMaxSum = 0;
            $totalPercentageSum = 0;
            $studentCount = $presentStudents->count();

            foreach ($skills as $skillIndex => $skill) {
                $skillGradeSum = 0;
                foreach ($presentStudents as $student) {
                    $es = $exam->examStudents->firstWhere('student_id', $student->id);
                    $pivotId = $skill->pivot->id;
                    $gradeRow = $es?->grades->firstWhere('course_type_skill_id', $pivotId);
                    $grade = $gradeRow?->grade ?: 0;
                    $skillGradeSum += $grade;
                }
                $skillAverages[$skillIndex] = $studentCount > 0 ? $skillGradeSum / $studentCount : 0;
            }

            // حساب متوسط المجموع والنسبة المئوية
            foreach ($presentStudents as $student) {
                $es = $exam->examStudents->firstWhere('student_id', $student->id);
                $studentTotal = 0;
                $studentMax = 0;
                
                foreach ($skills as $sk) {
                    $pivotId = $sk->pivot->id;
                    $gradeRow = $es?->grades->firstWhere('course_type_skill_id', $pivotId);
                    $g = $gradeRow?->grade ?: 0;
                    $m = $exam->exam_type == 'mid' ? ($sk->pivot->mid_max ?? 0) : ($sk->pivot->final_max ?? 0);
                    $studentTotal += $g;
                    $studentMax += $m;
                }
                
                $totalGradesSum += $studentTotal;
                $totalMaxSum += $studentMax;
                $percentage = $studentMax ? round($studentTotal / $studentMax * 100, 1) : 0;
                $totalPercentageSum += $percentage;
            }

            $averageTotal = $studentCount > 0 ? $totalGradesSum / $studentCount : 0;
            $averagePercentage = $studentCount > 0 ? $totalPercentageSum / $studentCount : 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAME</th>
                    @foreach($skills as $s)
                        <th title="{{ $s->name }}">{{ mb_substr($s->name, 0, 3, 'UTF-8') }}</th>
                    @endforeach
                    <th>TOTAL</th>
                    <th>PER</th>
                    <th>STATUS</th>
                </tr>
                <tr style="font-size: 14px; background: #333;">
                    <th colspan="2">MAX GRADES</th>
                    @foreach($skills as $s)
                        <th>
                            @if($exam->exam_type == 'mid')
                                {{ $s->pivot->mid_max ?? 0 }}
                            @else
                                {{ $s->pivot->final_max ?? 0 }}
                            @endif
                        </th>
                    @endforeach
                    <th>{{ $skills->sum(function($s) use ($exam) { 
                        return $exam->exam_type == 'mid' ? ($s->pivot->mid_max ?? 0) : ($s->pivot->final_max ?? 0); 
                    }) }}</th>
                    <th>100%</th>
                    <th>PASS/FAIL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($presentStudents as $i => $student)
                    @php
                        $es      = $exam->examStudents->firstWhere('student_id', $student->id);
                        $grades  = [];
                        $maxes   = [];
                        $totalGrades = 0;
                        $totalMax = 0;
                
                        foreach ($skills as $sk) {
                            $pivotId  = $sk->pivot->id;
                            $gradeRow = $es?->grades->firstWhere('course_type_skill_id', $pivotId);

                            $g = $gradeRow?->grade ?: 0;

                            // استخدام منطق بسيط للـ Mid و Final فقط
                            $m = $exam->exam_type == 'mid' 
                                ? ($sk->pivot->mid_max ?? 0)
                                : ($sk->pivot->final_max ?? 0);

                            $grades[] = $g;
                            $maxes[]  = $m;
                            $totalGrades += $g;
                            $totalMax += $m;
                        }
                
                        $per = $totalMax ? round($totalGrades / $totalMax * 100, 1) : 0;
                        $status = $per >= 50 ? 'PASS' : 'FAIL';
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align: left; font-size: 16px;">{{ $student->name }}</td>
                        @foreach($grades as $j => $g)
                            <td style="color: {{ $g >= ($maxes[$j] * 0.5) ? '#0f0' : '#f90' }}">
                                {{ number_format($g, 1) }}
                            </td>
                        @endforeach
                        <td style="font-weight: bold;">{{ number_format($totalGrades, 1) }}</td>
                        <td style="color: {{ $per >= 50 ? '#0f0' : '#f00' }}; font-weight: bold;">{{ $per }}%</td>
                        <td style="color: {{ $per >= 50 ? '#0f0' : '#f00' }}; font-weight: bold;">{{ $status }}</td>
                    </tr>
                @endforeach
            </tbody>
            
            <tfoot>
                <!-- إضافة صف المتوسط -->
                <tr style="background: #555; font-weight: bold; color: #00ff00;">
                    <td colspan="2">AVERAGE</td>
                    @foreach($skillAverages as $avg)
                        <td>{{ number_format($avg, 1) }}</td>
                    @endforeach
                    <td>{{ number_format($averageTotal, 1) }}</td>
                    <td>{{ number_format($averagePercentage, 1) }}%</td>
                    <td>{{ $averagePercentage >= 50 ? 'PASS' : 'FAIL' }}</td>
                </tr>
                
                <!-- إضافة إحصائيات سريعة للطلاب الحاضرين فقط -->
                <tr style="background: #444; font-weight: bold;">
                    <td colspan="2">STATISTICS (PRESENT ONLY)</td>
                    @php
                        $passCount = $presentStudents->filter(function($student) use ($exam, $skills) {
                            $es = $exam->examStudents->firstWhere('student_id', $student->id);
                            $totalGrades = 0;
                            $totalMax = 0;
                            foreach ($skills as $sk) {
                                $pivotId = $sk->pivot->id;
                                $gradeRow = $es?->grades->firstWhere('course_type_skill_id', $pivotId);
                                $g = $gradeRow?->grade ?: 0;
                                $m = $exam->exam_type == 'mid' ? ($sk->pivot->mid_max ?? 0) : ($sk->pivot->final_max ?? 0);
                                $totalGrades += $g;
                                $totalMax += $m;
                            }
                            $per = $totalMax ? round($totalGrades / $totalMax * 100, 1) : 0;
                            return $per >= 50;
                        })->count();
                        
                        $presentStudentsCount = $presentStudents->count();
                        $failCount = $presentStudentsCount - $passCount;
                        $totalEnrolledStudents = $ongoing->count();
                        $absentCount = $totalEnrolledStudents - $presentStudentsCount;
                    @endphp
                    <td colspan="{{ $skills->count() - 1 }}">
                        PASS: {{ $passCount }} | FAIL: {{ $failCount }} | ABSENT: {{ $absentCount }}
                    </td>
                    <td>{{ $presentStudentsCount }}/{{ $totalEnrolledStudents }}</td>
                    <td>{{ $presentStudentsCount ? round($passCount / $presentStudentsCount * 100, 1) : 0 }}%</td>
                    <td>{{ round($passCount / max($presentStudentsCount, 1) * 100, 1) }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
</body>
</html>