<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Course #{{ $progressTest->course_id }}</title>

@php
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png')));

    // --------- احصاء مبكر للصفوف (الحاضرين فقط) ----------
    $skills   = $progressTest->course->courseType->skills;
    $students = $progressTest->progressTestStudents()
        ->with(['student','grades'])
        ->where('status','present')
        ->get();

    $studentCount   = $students->count();
    $densityClass   = $studentCount > 14 ? 'dense' : ($studentCount > 7 ? 'normal' : 'spacious');
@endphp

<style>
@font-face{font-family:'cairo';src:url('{{ $fontRegular }}') format('truetype');font-weight:400}
@font-face{font-family:'cairo';src:url('{{ $fontBold }}') format('truetype');font-weight:700}
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:1024px;height:1024px;font-family:'cairo',sans-serif;color:#fff}
body{background:url('data:image/png;base64,{{ $bgData }}') no-repeat center center;background-size:cover}

/* ====== متغيرات عامة ====== */
:root{
  --title-top:160px;
  --meta-top:210px;
  --table-top:300px;
  --table-width:90%;
  --border:#333;
  --black:#000;
  --avg-bg:#2a2a2a;
  --hdr-bg:#333;
  --text:#fff;
  --ok:#0f0;
  --warn:#f90;
  --bad:#f00;
  --info:#00bfff;
}

/* ====== كثافات الجدول حسب عدد الطلاب ======
   spacious: <=7 طلاب
   normal  : 8 - 14
   dense   : >=15
*/
.table--spacious{
  --fz:20px;     /* حجم نص الخلايا */
  --fz-h:20px;   /* رأس الجدول */
  --py:12px;     /* حشوة عمودية */
  --px:10px;     /* حشوة أفقية */
  --name-w:260px;/* عرض عمود الاسم */
}
.table--normal{
  --fz:18px;
  --fz-h:18px;
  --py:10px;
  --px:8px;
  --name-w:230px;
}
.table--dense{
  --fz:15px;
  --fz-h:15px;
  --py:6px;
  --px:6px;
  --name-w:200px;
}

/* ====== هيكل الصفحة ====== */
.container{width:100%;height:100%;display:flex;flex-direction:column;justify-content:space-between}
.title{font-size:36px;font-weight:700;margin-bottom:20px;position:absolute;top:var(--title-top);left:200px}
.sub-details{font-size:18px}
.sub-right{position:absolute;top:var(--meta-top);right:50px}
.sub-left{position:absolute;top:var(--meta-top);left:200px;text-align:right}

/* ====== الجدول ====== */
.table-wrap{
  position:absolute;top:var(--table-top);left:0;right:0;
  display:flex;justify-content:center;align-items:flex-start;
}
table{
  width:var(--table-width);
  margin:auto;
  border-collapse:collapse;
  background:transparent;
}
th,td{
  font-size:var(--fz);
  padding:var(--py) var(--px);
  background:var(--black);
  border:1px solid var(--border);
  text-align:center;
  color:var(--text);
  vertical-align:middle;
}
thead th{font-size:var(--fz-h)}
th.name, td.name{
  text-align:left;
  max-width:var(--name-w);
  width:var(--name-w);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
thead .subhdr{font-size:14px;background:var(--hdr-bg)}
tfoot .avg-row{background:var(--avg-bg);font-weight:bold;border-top:2px solid #666}
tfoot .stats-row{background:#444;font-weight:bold}

.green{color:var(--ok)}
.orange{color:var(--warn)}
.red{color:var(--bad)}
.cyan{color:var(--info)}
</style>
</head>
<body>
<div class="container">
    <h1 class="title">PROGRESS TEST RESULTS - WEEK {{ $progressTest->week }} (#{{ $progressTest->course_id }})</h1>

    <div class="sub-details sub-right">
        Instructor: {{ optional($progressTest->course->instructor)->name ?? 'Unassigned' }}<br>
        Days: {{ $progressTest->course->days ?? '-' }}<br>
        Course: {{ $progressTest->course->courseType->name ?? '-' }}
    </div>
    <div class="sub-details sub-left">
        Date: {{ $progressTest->date ? \Carbon\Carbon::parse($progressTest->date)->format('Y-m-d') : '-' }}<br>
        Week: {{ $progressTest->week }}<br>
        Time: {{ $progressTest->time ? \Carbon\Carbon::parse($progressTest->time)->format('h:i A') : '-' }}
    </div>

    <div class="table-wrap {{ 'table--' . $densityClass }}">
        @php
            // ---- حساب المتوسطات و الإحصائيات كما في كودك الأصلي ----
            $skillAverages = [];
            $totalPercentageSum = 0;

            foreach ($skills as $skillIndex => $skill) {
                $skillGradeSum = 0; $skillCount = 0;
                foreach ($students as $student) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    if ($grade && $grade->progress_test_grade !== null) {
                        $skillGradeSum += $grade->progress_test_grade;
                        $skillCount++;
                    }
                }
                $skillAverages[$skillIndex] = $skillCount > 0 ? $skillGradeSum / $skillCount : 0;
            }

            foreach ($students as $student) {
                $total = 0; $max = 0;
                foreach ($skills as $skill) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    $val = $grade?->progress_test_grade;
                    $m = $grade?->max_grade ?? 0;
                    $total += ($val ?? 0); $max += $m;
                }
                $percent = $max ? round(($total / $max) * 100) : 0;
                $totalPercentageSum += $percent;
            }
            $averagePercentage = $studentCount > 0 ? round($totalPercentageSum / $studentCount, 1) : 0;

            $passCount = 0;
            foreach ($students as $student) {
                $total = 0; $max = 0;
                foreach ($skills as $skill) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    $val = $grade?->progress_test_grade;
                    $m = $grade?->max_grade ?? 0;
                    $total += ($val ?? 0); $max += $m;
                }
                $percent = $max ? round(($total / $max) * 100) : 0;
                if ($percent >= 50) $passCount++;
            }
            $failCount      = $studentCount - $passCount;
            $totalEnrolled  = $progressTest->course->students()->wherePivot('status', 'ongoing')->count();
            $absentCount    = $totalEnrolled - $studentCount;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th class="name">NAME</th>
                    @foreach($skills as $s)
                        <th title="{{ $s->name }}">{{ mb_substr($s->name,0,3,'UTF-8') }}</th>
                    @endforeach
                    <th>TOTAL</th>
                    <th>PER</th>
                    <th>STATUS</th>
                </tr>
                <tr class="subhdr">
                    <th colspan="2">MAX GRADES</th>
                    @foreach($skills as $s)
                        @php
                            $maxGrade = $students->first()?->grades->firstWhere('course_type_skill_id', $s->pivot->id)?->max_grade ?? 0;
                        @endphp
                        <th>{{ number_format($maxGrade,1) }}</th>
                    @endforeach
                    <th>{{ $students->first() ? number_format($students->first()->grades->sum('max_grade'),1) : 0 }}</th>
                    <th>100%</th>
                    <th>PASS/FAIL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i=>$rec)
                    @php $total=0; $max=0; @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td class="name">{{ $rec->student->name }}</td>
                        @foreach($skills as $s)
                            @php
                                $grade=$rec->grades->firstWhere('course_type_skill_id',$s->pivot->id);
                                $val=$grade?->progress_test_grade;
                                $m=$grade?->max_grade ?? 0;
                                $total+=($val??0); $max+=$m;
                                $ok = ($val !== null && $m > 0 && $val >= ($m * 0.5));
                            @endphp
                            <td class="{{ $ok ? 'green' : 'orange' }}">
                                {{ $val !== null ? number_format($val, 1) : '-' }}
                            </td>
                        @endforeach
                        @php $percent=$max?round(($total/$max)*100):0; @endphp
                        <td style="font-weight:700">{{ number_format($total, 1) }}</td>
                        <td class="{{ $percent<50?'red':'green' }}" style="font-weight:700">{{ $percent }}%</td>
                        <td class="{{ $percent<50?'red':'green' }}" style="font-weight:700">
                            {{ $percent >= 50 ? 'PASS' : 'FAIL' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="avg-row">
                    <td>AVG</td>
                    <td class="name" style="font-style:italic;color:#ffd700">Class Average</td>
                    @foreach($skillAverages as $avg)
                        <td class="cyan">{{ number_format($avg, 1) }}</td>
                    @endforeach
                    @php $averageTotal = array_sum($skillAverages); @endphp
                    <td style="font-weight:700">{{ number_format($averageTotal, 1) }}</td>
                    <td class="{{ $averagePercentage>=50?'green':'red' }}" style="font-weight:700">{{ $averagePercentage }}%</td>
                    <td class="{{ $averagePercentage>=50?'green':'red' }}" style="font-weight:700">
                        {{ $averagePercentage >= 50 ? 'PASS' : 'FAIL' }}
                    </td>
                </tr>

                <tr class="stats-row">
                    <td colspan="2">STATISTICS (PRESENT ONLY)</td>
                    @if($skills->count() >= 3)
                        <td class="green">PASS: {{ $passCount }}</td>
                        <td class="red">FAIL: {{ $failCount }}</td>
                        <td style="color:#ff0">ABSENT: {{ $absentCount }}</td>
                        @if($skills->count() > 3)
                            <td colspan="{{ $skills->count() - 3 }}">-</td>
                        @endif
                    @else
                        @for($i = 0; $i < $skills->count(); $i++)
                            @if($i == 0)
                                <td class="green">PASS: {{ $passCount }}</td>
                            @elseif($i == 1)
                                <td class="red">FAIL: {{ $failCount }}</td>
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
