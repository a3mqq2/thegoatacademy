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
.title{font-size:36px;font-weight:700;margin-bottom:20px;position:absolute;top:160px;left:260px}
.sub-details{font-size:18px}
.table-container{flex:1;display:flex;align-items:center;justify-content:center}
table{width:90%;margin:auto;border-collapse:collapse;position:absolute;top:300px}
th,td{font-size:20px;padding:10px;background:#000;border:1px solid #333;text-align:center}
</style>
</head>
<body>
<div class="container">
    <h1 class="title">Progress Test Results ({{ $progressTest->course_id }})</h1>
    <div class="sub-details" style="position:absolute;top:210px;right:50px">
        Instructor: {{ optional($progressTest->course->instructor)->name ?? 'Unassigned' }}<br>
        Days: {{ $progressTest->course->days ?? '-' }}
    </div>
    <div class="sub-details" style="position:absolute;top:210px;left:250px;text-align:right">
        Date: {{ $progressTest->date ? \Carbon\Carbon::parse($progressTest->date)->format('Y-m-d') : '-' }}<br>
        Week: {{ $progressTest->week }}
    </div>

    <div class="table-container">
        @php
            $skills   = $progressTest->course->courseType->skills;
            $students = $progressTest->progressTestStudents()->with(['student','grades'])->get();
        @endphp
        <table>
            <thead>
                <tr>
                    <th>NO</th><th>NAME</th>
                    @foreach($skills as $s)
                        <th>{{ mb_substr($s->name,0,1,'UTF-8') }}</th>
                    @endforeach
                    <th>PER</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i=>$rec)
                    @php $total=0;$max=0; @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $rec->student->name }}</td>
                        @foreach($skills as $s)
                            @php
                                $grade=$rec->grades->firstWhere('course_type_skill_id',$s->pivot->id);
                                $val=$grade?->progress_test_grade;
                                $m=$grade?->max_grade ?? 0;
                                $total+=($val??0);
                                $max+=$m;
                            @endphp
                            <td>{{ $val!=null?$val:'-' }}</td>
                        @endforeach
                        @php $percent=$max?round(($total/$max)*100):0; @endphp
                        <td style="color:{{ $percent<50?'#dc3545':'#28a745' }}">{{ $percent }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
