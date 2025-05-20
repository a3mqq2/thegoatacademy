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
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1 class="title">
            {{ strtoupper($exam->course->courseType->name) }} - {{ strtoupper($exam->exam_type) }} – EXAM RESULTS (#{{ $exam->course_id }})
        </h1>
        <div class="sub-details">
            <div style="position: absolute; top:210px;">
                Examiner: {{ optional($exam->examiner)->name ?? 'Unassigned' }}<br>
                Days: {{ $exam->course->days ?? '-' }}
            </div>
            <div style="text-align: right; position: absolute; top:210px; left:250px;">
                Time: {{ $exam->time ? \Carbon\Carbon::parse($exam->time)->format('h:i A') : '-' }}<br>
                Date: {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') : '-' }}
            </div>
        </div>
    </div>

    <div class="table-container">
        @php
            $skills  = $exam->course->courseType->skills;
            $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
        @endphp

        <table>
            <thead>
                <tr>
                    <th>NO</th><th>NAME</th>
                    @foreach($skills as $s)
                        <th>{{ mb_substr($s->name, 0, 1, 'UTF-8') }}</th>
                    @endforeach
                    <th>PER</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ongoing as $i => $student)
                    @php
                        $es      = $exam->examStudents->firstWhere('student_id', $student->id);
                        $grades  = [];
                        $maxes   = [];

                        foreach ($skills as $sk) {
                            $pivotId = $sk->pivot->id;  // ⬅️ يجب استخدام id من Pivot
                            $grade   = optional($es?->grades->firstWhere('course_type_skill_id', $pivotId))->grade ?? 0;

                            $max     = $exam->exam_type === 'pre'   ? $sk->pivot->pre_max  :
                                       ($exam->exam_type === 'mid' ? $sk->pivot->mid_max  : $sk->pivot->final_max);

                            $grades[] = $grade;
                            $maxes[]  = $max;
                        }

                        $per = array_sum($maxes) ? round(array_sum($grades) / array_sum($maxes) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $student->name }}</td>
                        @foreach($grades as $g)
                            <td>{{ number_format($g, 2) }}</td>
                        @endforeach
                        <td style="color: {{ $per >= 50 ? '#0f0' : '#f00' }}">{{ $per }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
