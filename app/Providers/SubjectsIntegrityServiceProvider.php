<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class SubjectsIntegrityServiceProvider extends ServiceProvider
{
    /**
     * Path to the snapshot file that records the approved subjects state.
     * Created automatically on first boot — edit it manually ONLY when you
     * intentionally change config/subjects.php through an approved process.
     */
    private const SNAPSHOT_PATH = 'subjects_snapshot.json';

    public function boot(): void
    {
        $current  = config('subjects', []);
        $snapshot = $this->loadSnapshot();

        if ($snapshot === null) {
            // First boot — no snapshot yet. Write one and mark as clean.
            $this->saveSnapshot($current);
            cache()->forget('subjects_integrity_violations');
            return;
        }

        $violations = $this->diff($snapshot, $current);

        if (empty($violations)) {
            cache()->forget('subjects_integrity_violations');
            return;
        }

        // Store violations in cache so the login view can read them.
        cache()->put('subjects_integrity_violations', $violations, now()->addHours(24));

        // Log every violation at CRITICAL level.
        foreach ($violations as $v) {
            Log::critical('[SUBJECTS INTEGRITY] ' . $v['message'], $v);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function loadSnapshot(): ?array
    {
        $path = storage_path('app/' . self::SNAPSHOT_PATH);
        if (!file_exists($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    private function saveSnapshot(array $subjects): void
    {
        $path = storage_path('app/' . self::SNAPSHOT_PATH);
        file_put_contents($path, json_encode($subjects, JSON_PRETTY_PRINT));
    }

    /**
     * Compare snapshot vs current config and return a flat list of violations.
     */
    private function diff(array $snapshot, array $current): array
    {
        $violations = [];

        $allClasses = array_unique(array_merge(array_keys($snapshot), array_keys($current)));

        foreach ($allClasses as $classKey) {
            $approved = $snapshot[$classKey] ?? null;
            $live     = $current[$classKey]  ?? null;

            if ($approved === null && $live !== null) {
                $violations[] = [
                    'class'   => $classKey,
                    'type'    => 'CLASS_ADDED',
                    'message' => "config/subjects.php: class key '{$classKey}' was ADDED (not in snapshot). Subjects: [" . implode(', ', $live) . "]",
                    'approved' => [],
                    'current'  => $live,
                ];
                continue;
            }

            if ($approved !== null && $live === null) {
                $violations[] = [
                    'class'   => $classKey,
                    'type'    => 'CLASS_REMOVED',
                    'message' => "config/subjects.php: class key '{$classKey}' was REMOVED from config.",
                    'approved' => $approved,
                    'current'  => [],
                ];
                continue;
            }

            $removed = array_values(array_diff($approved, $live));
            $added   = array_values(array_diff($live, $approved));

            if (!empty($removed)) {
                $violations[] = [
                    'class'    => $classKey,
                    'type'     => 'SUBJECTS_REMOVED',
                    'message'  => "config/subjects.php key '{$classKey}': subjects REMOVED: [" . implode(', ', $removed) . "]",
                    'approved' => $approved,
                    'current'  => $live,
                    'removed'  => $removed,
                ];
            }

            if (!empty($added)) {
                $violations[] = [
                    'class'    => $classKey,
                    'type'     => 'SUBJECTS_ADDED',
                    'message'  => "config/subjects.php key '{$classKey}': subjects ADDED: [" . implode(', ', $added) . "]. Ensure these columns exist in the marks table.",
                    'approved' => $approved,
                    'current'  => $live,
                    'added'    => $added,
                ];
            }
        }

        return $violations;
    }

    public function register(): void {}
}
