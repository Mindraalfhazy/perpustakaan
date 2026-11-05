<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSiswaAccounts extends Command
{
    protected $signature = 'siswa:generate-accounts';
    protected $description = 'Membuat akun login otomatis untuk semua member yang belum punya user_id';

    public function handle(): int
    {
        $members = Member::whereNull('user_id')->get();

        if ($members->isEmpty()) {
            $this->info('Semua member sudah punya akun. Tidak ada yang perlu dibuat.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$members->count()} member tanpa akun. Memulai proses...");
        $bar = $this->output->createProgressBar($members->count());

        $skipped = [];

        foreach ($members as $member) {
            $email = $member->nisn . '@mtsn3bone.sch.id';

            // Lewati kalau email sudah dipakai (jaga-jaga NISN kosong/duplikat)
            if (empty($member->nisn) || User::where('email', $email)->exists()) {
                $skipped[] = $member->nama . ' (NISN: ' . $member->nisn . ')';
                $bar->advance();
                continue;
            }

            DB::transaction(function () use ($member, $email) {
                $user = User::create([
                    'name' => $member->nama,
                    'email' => $email,
                    'password' => bcrypt($member->nisn),
                    'role' => 'siswa',
                    'nisn' => $member->nisn,
                    'kelas' => $member->kelas,
                ]);

                $member->update([
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Selesai! Akun berhasil dibuat untuk ' . ($members->count() - count($skipped)) . ' siswa.');

        if (! empty($skipped)) {
            $this->warn('Dilewati (' . count($skipped) . '): NISN kosong atau email sudah dipakai.');
            foreach ($skipped as $s) {
                $this->line(' - ' . $s);
            }
        }

        return self::SUCCESS;
    }
}