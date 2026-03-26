<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicantCleanupService
{
    /**
     * Get all postgraduate applicants (not admitted students)
     */
    public function getPostgraduateApplicants(): Collection
    {
        return User::where('role', Role::APPLICANT->value)
            ->where('programme_id', ProgrammesEnum::PG->value)
            ->with([
                'proposedCourse',
                'academicDetail',
                'transactions',
                'studentTransactions',
                'olevelExams',
                'olevelsubjectGrades',
                'certificateUploads',
                'schools',
                'postUtmeUpload',
            ])
            ->get();
    }

    /**
     * Get preview of what will be deleted
     */
    public function getDeletionPreview(array $userIds): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users */
        $users = User::whereIn('id', $userIds)
            ->with([
                'proposedCourse',
                'academicDetail',
                'transactions',
                'studentTransactions',
                'olevelExams',
                'olevelsubjectGrades',
                'certificateUploads',
                'schools',
                'postUtmeUpload',
            ])
            ->get();

        $preview = [
            'total_users' => $users->count(),
            'users' => $users->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'jamb_no' => $user->jamb_no,
            ]),
            'record_counts' => [
                'proposed_courses' => $users->sum(fn ($u) => $u->proposedCourse ? 1 : 0),
                'academic_details' => $users->sum(fn ($u) => $u->academicDetail ? 1 : 0),
                'transactions' => $users->sum(fn ($u) => $u->transactions->count()),
                'student_transactions' => $users->sum(fn ($u) => $u->studentTransactions->count()),
                'olevel_exams' => $users->sum(fn ($u) => $u->olevelExams->count()),
                'olevel_subject_grades' => $users->sum(fn ($u) => $u->olevelsubjectGrades->count()),
                'certificate_uploads' => $users->sum(fn ($u) => $u->certificateUploads->count()),
                'schools' => $users->sum(fn ($u) => $u->schools->count()),
                'post_utme_uploads' => $users->sum(fn ($u) => $u->postUtmeUpload ? 1 : 0),
            ],
        ];

        $preview['total_records'] = array_sum($preview['record_counts']);

        return $preview;
    }

    /**
     * Delete applicant and all related records within a transaction
     */
    public function deleteApplicant(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Delete child records first to avoid FK errors

            // 0. Delete profile and id card processing records that reference users.id
            DB::table('profiles')->where('user_id', $user->id)->delete();
            DB::table('id_card_processings')->where('user_id', $user->id)->delete();

            // 1. Delete olevel_subject_grades (directly on user)
            $user->olevelsubjectGrades()->delete();

            // 2. Delete olevel_exams
            $user->olevelExams()->delete();

            // 3. Delete certificate_uploads
            $user->certificateUploads()->delete();

            // 4. Delete schools
            $user->schools()->delete();

            // 5. Delete post_utme_uploads
            $user->postUtmeUpload()->delete();

            // 6. Delete proposed_courses
            $user->proposedCourse()->delete();

            // 7. Delete academic_details
            $user->academicDetail()->delete();

            // 8. Delete student_transactions
            $user->studentTransactions()->delete();

            // 9. Delete transactions
            $user->transactions()->delete();

            // 10. Finally delete the user
            $user->delete();

            return true;
        });
    }

    /**
     * Bulk delete multiple applicants
     */
    public function bulkDeleteApplicants(array $userIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'counts' => [
                'proposed_courses' => 0,
                'academic_details' => 0,
                'transactions' => 0,
                'student_transactions' => 0,
                'olevel_exams' => 0,
                'olevel_subject_grades' => 0,
                'certificate_uploads' => 0,
                'schools' => 0,
                'post_utme_uploads' => 0,
                'users' => 0,
            ],
        ];

        $users = User::whereIn('id', $userIds)
            ->with([
                'proposedCourse',
                'academicDetail',
                'transactions',
                'studentTransactions',
                'olevelExams',
                'olevelsubjectGrades',
                'certificateUploads',
                'schools',
                'postUtmeUpload',
            ])
            ->get();

        foreach ($users as $user) {
            try {
                // Count records before deletion
                $results['counts']['proposed_courses'] += $user->proposedCourse ? 1 : 0;
                $results['counts']['academic_details'] += $user->academicDetail ? 1 : 0;
                $results['counts']['transactions'] += $user->transactions->count();
                $results['counts']['student_transactions'] += $user->studentTransactions->count();
                $results['counts']['olevel_exams'] += $user->olevelExams->count();
                $results['counts']['olevel_subject_grades'] += $user->olevelsubjectGrades->count();
                $results['counts']['certificate_uploads'] += $user->certificateUploads->count();
                $results['counts']['schools'] += $user->schools->count();
                $results['counts']['post_utme_uploads'] += $user->postUtmeUpload ? 1 : 0;

                if ($this->deleteApplicant($user)) {
                    $results['counts']['users']++;
                    $results['success'][] = $user->id;
                }
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Export selected applicants to CSV before deletion
     */
    public function exportApplicants(array $userIds): string
    {
        $users = User::whereIn('id', $userIds)
            ->with([
                'proposedCourse',
                'academicDetail',
                'transactions',
                'studentTransactions',
                'olevelExams',
                'olevelsubjectGrades',
                'certificateUploads',
                'schools',
            ])
            ->get();

        $filename = 'applicants_backup_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $path = 'backups/' . $filename;

        Storage::disk('local')->makeDirectory('backups');

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            throw new \RuntimeException('Unable to create temporary export stream.');
        }

        // Headers
        fputcsv($stream, [
            'ID', 'Name', 'Email', 'JAMB No', 'Phone', 'Programme',
            'Created At', 'Proposed Courses', 'Academic Details',
            'Transactions', 'Student Transactions', 'Olevel Exams',
            'Certificate Uploads', 'Schools'
        ]);

        foreach ($users as $user) {
            fputcsv($stream, [
                $user->id,
                $user->full_name,
                $user->email,
                $user->jamb_no,
                $user->phone,
                'Postgraduate',
                $user->created_at,
                $user->proposedCourse ? 1 : 0,
                $user->academicDetail ? 1 : 0,
                $user->transactions->count(),
                $user->studentTransactions->count(),
                $user->olevelExams->count(),
                $user->certificateUploads->count(),
                $user->schools->count(),
            ]);
        }

        rewind($stream);

        $csvContents = stream_get_contents($stream);

        fclose($stream);

        if ($csvContents === false) {
            throw new \RuntimeException('Unable to read generated export data.');
        }

        Storage::disk('local')->put($path, $csvContents);

        return $path;
    }

    /**
     * Log deletion to audit table
     */
    public function logDeletion(array $userIds, ?string $adminId, array $counts, ?string $exportPath = null): void
    {
        DB::table('applicant_deletion_logs')->insert([
            'admin_user_id' => $adminId,
            'deleted_user_ids' => json_encode($userIds),
            'deletion_summary' => json_encode($counts),
            'export_file_path' => $exportPath,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
