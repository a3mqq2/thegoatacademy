@extends('layouts.app')
@section('title', 'Record Grades')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-pen-to-square me-2"></i>
                    Record Grades for Exam #{{ $exam->id }} ({{ $exam->exam_type }})
                </h4>
                <small class="text-muted">
                    Course: #{{ $exam->course->id }} /
                    {{ optional($exam->course->courseType)->name }} /
                    {{ optional($exam->course->groupType)->name }}
                </small>
            </div>
            <div>
                <!-- A small table or list for max grades -->
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="pe-2 text-end">
                                <i class="fas fa-book-open text-info"></i> Grammar:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->grammar_max_grade, 2), '0'), '.') }}</td>
                            <th class="pe-2 text-end">
                                <i class="fas fa-book text-info"></i> Vocab:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->vocabulary_max_grade, 2), '0'), '.') }}</td>
                        </tr>
                        <tr>
                            <th class="pe-2 text-end">
                                <i class="fas fa-chalkboard text-info"></i> Practical:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->practical_english_max_grade, 2), '0'), '.') }}</td>
                            <th class="pe-2 text-end">
                                <i class="fas fa-book-reader text-info"></i> Reading:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->reading_max_grade, 2), '0'), '.') }}</td>
                        </tr>
                        <tr>
                            <th class="pe-2 text-end">
                                <i class="fas fa-pencil-alt text-info"></i> Writing:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->writing_max_grade, 2), '0'), '.') }}</td>
                            <th class="pe-2 text-end">
                                <i class="fas fa-headphones text-info"></i> Listening:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->listening_max_grade, 2), '0'), '.') }}</td>
                        </tr>
                        <tr>
                            <th class="pe-2 text-end">
                                <i class="fas fa-comments text-info"></i> Speaking:
                            </th>
                            <td>{{ rtrim(rtrim(number_format($exam->speaking_max_grade, 2), '0'), '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- card-header -->

        <div class="card-body">
            <form action="{{ route('exam_officer.exams.grades.store', $exam->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Grammar</th>
                                <th>Vocabulary</th>
                                <th>Practical English</th>
                                <th>Reading</th>
                                <th>Writing</th>
                                <th>Listening</th>
                                <th>Speaking</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exam->students as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-user-graduate me-1 text-secondary"></i>
                                        {{ $student->name }}
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][grammar_grade]" 
                                               placeholder="0 - {{ $exam->grammar_max_grade }}"
                                               value="{{ old("students.$index.grammar_grade", $student->pivot->grammar_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][vocabulary_grade]" 
                                               placeholder="0 - {{ $exam->vocabulary_max_grade }}"
                                               value="{{ old("students.$index.vocabulary_grade", $student->pivot->vocabulary_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][practical_english_grade]"
                                               placeholder="0 - {{ $exam->practical_english_max_grade }}"
                                               value="{{ old("students.$index.practical_english_grade", $student->pivot->practical_english_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][reading_grade]"
                                               placeholder="0 - {{ $exam->reading_max_grade }}"
                                               value="{{ old("students.$index.reading_grade", $student->pivot->reading_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][writing_grade]" 
                                               placeholder="0 - {{ $exam->writing_max_grade }}"
                                               value="{{ old("students.$index.writing_grade", $student->pivot->writing_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][listening_grade]" 
                                               placeholder="0 - {{ $exam->listening_max_grade }}"
                                               value="{{ old("students.$index.listening_grade", $student->pivot->listening_grade ?? 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.5" class="form-control"
                                               name="students[{{ $index }}][speaking_grade]" 
                                               placeholder="0 - {{ $exam->speaking_max_grade }}"
                                               value="{{ old("students.$index.speaking_grade", $student->pivot->speaking_grade ?? 0) }}">
                                    </td>
                                    <!-- Hidden field for student_id -->
                                    <input type="hidden" name="students[{{ $index }}][student_id]" value="{{ $student->id }}">
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- table-responsive -->

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Save Grades
                    </button>
                </div>
            </form>
        </div> <!-- card-body -->
    </div>
</div>
@endsection
