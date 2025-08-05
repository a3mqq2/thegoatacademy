@php     
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');     
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');     
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png'))); 
@endphp     
     
<div style="font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right;">
    <h2>Progress Test Results ({{ $progressTest->course_id }})</h2>
    
    <div style="margin-bottom: 20px;">
        <p><strong>Instructor:</strong> {{ optional($progressTest->course->instructor)->name ?? 'Unassigned' }}</p>
        <p><strong>Days:</strong> {{ $progressTest->course->days ?? '-' }}</p>
        <p><strong>Date:</strong> {{ $progressTest->date ? \Carbon\Carbon::parse($progressTest->date)->format('Y-m-d') : '-' }}</p>
        <p><strong>Week:</strong> {{ $progressTest->week }}</p>
    </div>
    
    @php             
        $skills = $progressTest->course->courseType->skills;             
        // Filter for PRESENT students only
        $students = $progressTest->progressTestStudents()
                                ->with(['student','grades'])
                                ->where('status', 'present')
                                ->get();
        
        $presentCount = $students->count();
    @endphp                              

    @if($presentCount > 0)
        <div style="margin-bottom: 10px;">
            <p><strong>Present Students:</strong> {{ $presentCount }}</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">#</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Student</th>
                    @foreach($skills as $s)
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                            {{ mb_substr($s->name, 0, 3, 'UTF-8') }}
                        </th>
                    @endforeach
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Total</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $rec)                     
                    @php 
                        $total = 0;
                        $max = 0; 
                    @endphp                                              
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $i+1 }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $rec->student->name }}</td>
                        
                        @foreach($skills as $s)                             
                            @php                                 
                                $grade = $rec->grades->firstWhere('course_type_skill_id', $s->pivot->id);                                 
                                $val = $grade?->progress_test_grade;                                 
                                $m = $grade?->max_grade ?? 0;                                 
                                $total += ($val ?? 0);                                 
                                $max += $m;                             
                            @endphp                                                      
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                {{ $val != null ? $val : '-' }}
                            </td>
                        @endforeach                         
                        
                        @php 
                            $percent = $max ? round(($total/$max)*100) : 0; 
                        @endphp                                          
                        
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold;">
                            {{ $total }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold;">
                            {{ $percent }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        @php
            // Calculate class average for present students only
            $totalScores = $students->sum(function($student) use ($skills) {
                $total = 0;
                foreach($skills as $skill) {
                    $grade = $student->grades->firstWhere('course_type_skill_id', $skill->pivot->id);
                    $total += $grade?->progress_test_grade ?? 0;
                }
                return $total;
            });
            
            $maxPossible = $students->count() * $skills->sum('pivot.progress_test_max');
            $classAverage = $maxPossible > 0 ? round(($totalScores / $maxPossible) * 100) : 0;
        @endphp
        
        <div style="margin-top: 20px; padding: 10px; background-color: #e9ecef; border-radius: 5px;">
            <p><strong>Class Average (Present Students):</strong> {{ $classAverage }}%</p>
            <p><strong>Total Present Students:</strong> {{ $presentCount }}</p>
        </div>
    @else
        <div style="padding: 20px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
            <p><strong>No present students found in this progress test.</strong></p>
        </div>
    @endif
</div>