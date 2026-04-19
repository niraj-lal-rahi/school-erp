<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentDocumentRepository implements StudentDocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $documentQuery) use ($search): void {
                    $documentQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['document_type'] ?? null, fn (Builder $query, string $value) => $query->where('document_type', $value))
            ->when($filters['verification_status'] ?? null, fn (Builder $query, string $value) => $query->where('verification_status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StudentDocument
    {
        return $this->query()->findOrFail($id);
    }

    public function create(Student $student, array $attributes): StudentDocument
    {
        return $student->documents()->create($attributes);
    }

    public function update(StudentDocument $document, array $attributes): StudentDocument
    {
        $document->update($attributes);

        return $this->findOrFail($document->id);
    }

    public function delete(StudentDocument $document): void
    {
        $document->delete();
    }

    public function allForStudent(Student $student): Collection
    {
        return $this->query()
            ->where('student_id', $student->id)
            ->get();
    }

    protected function query(): Builder
    {
        return StudentDocument::query()->with(['student', 'uploader']);
    }
}
