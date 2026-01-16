<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendBlastEmail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ToolsController extends Controller
{
    /**
     * Halaman Utama Kumpulan Tools
     */
    public function index() 
    {
        // Nantinya bisa berisi daftar card tools yang tersedia
        return view('tools.index'); 
    }

    /**
     * Halaman Fitur Email Blast
     */
    public function blastIndex() 
    {
        // Sesuai dengan permintaan Anda: view/tools/blast.blade.php [web:12]
        return view('tools.blast');
    }

    /**
     * Eksekusi Pengiriman Blast
     */
    public function send(Request $request)
    {
        $request->validate([
            'subject'    => 'required|string',
            'content'    => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*.email' => 'required|email'
        ]);

        $recipients = $request->input('recipients');
        
        // 1. Buat Batch kosong terlebih dahulu untuk mendapatkan ID-nya [web:1]
        $batch = Bus::batch([])
            ->name('Mail Blast: ' . $request->subject)
            ->dispatch();

        $jobs = [];
        $logs = [];

        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            
            // 2. Siapkan data untuk tabel log
            $logs[] = [
                'batch_id'   => $batch->id,
                'email'      => $email,
                'is_opened'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 3. Masukkan batch_id ke dalam Job agar Job bisa buat Tracking URL
            $jobs[] = new SendBlastEmail(
                (object)$recipient, 
                $request->subject, 
                $this->convertTextToButtons($request->content),
                $batch->id // Tambahkan parameter ini
            );
        }

        // 4. Simpan log secara massal (bulk insert) agar cepat
        DB::table('email_blast_logs')->insert($logs);

        // 5. Tambahkan jobs ke dalam batch yang sudah dibuat tadi
        $batch->add($jobs);

        return response()->json([
            'batch_id' => $batch->id,
            'total'    => count($jobs)
        ]);
    }

    /**
     * Cek Progress Batch secara Real-time
     */
    public function progress($batchId)
    {
        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        // Mengembalikan data standar Batch Laravel untuk diolah Alpine.js [web:12][web:25]
        return response()->json([
            'progress'      => $batch->progress(),
            'processedJobs' => $batch->processedJobs(),
            'failedJobs'    => $batch->failedJobs,
            'totalJobs'     => $batch->totalJobs,
            'finishedAt'    => $batch->finishedAt,
        ]);
    }

    public function getHistory()
    {
        $batches = DB::table('job_batches')
            ->where('name', 'like', 'Mail Blast:%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $history = $batches->map(function ($batch) {
            // Hitung pengiriman: processed - failed
            $processed = $batch->total_jobs - $batch->pending_jobs;
            $success_count = $processed - $batch->failed_jobs;
            
            // Logika Status
            $status = 'Sedang Mengirim';
            if ($batch->cancelled_at) {
                $status = 'Dibatalkan';
            } elseif ($batch->finished_at) {
                $status = $batch->failed_jobs > 0 ? 'Gagal' : 'Selesai';
            }
            
            // Hitung Statistik Open Rate dari tabel log
            $total_logged = DB::table('email_blast_logs')->where('batch_id', $batch->id)->count();
            $opened_count = DB::table('email_blast_logs')->where('batch_id', $batch->id)->where('is_opened', true)->count();
            $open_rate = $total_logged > 0 ? round(($opened_count / $total_logged) * 100) : 0;

            // Datetime selesai: prioritas cancelled_at atau finished_at
            $datetime_selesai = $batch->cancelled_at ?: $batch->finished_at;

            return [
                'id' => $batch->id,
                'batch_id' => $batch->id,
                'subject' => str_replace('Mail Blast: ', '', $batch->name),
                'date' => date('d/m/Y H:i', $batch->created_at),
                'datetime_selesai' => $datetime_selesai 
                    ? date('d/m/Y H:i', $datetime_selesai)
                    : null,
                'success' => $success_count,
                'failed' => $batch->failed_jobs,
                'total' => $batch->total_jobs,
                'status' => $status,
                'progress_percent' => $batch->total_jobs > 0 
                    ? round(($processed / $batch->total_jobs) * 100) 
                    : 0,
                // Data baru untuk tracking open
                'open_rate' => $open_rate,
                'opened' => $opened_count
            ];
        });

        return response()->json($history);
    }

    private function convertTextToButtons($content)
    {
        return preg_replace_callback(
            '/button\[([^\]]+)\]/i',
            function ($matches) use ($content) {
                $fullMatch = $matches[0];
                
                // 1. button[URL|TEXT|COLOR]
                if (preg_match('/button\[([^\]]+)\|([^\]]+)\|([^\]]+)\]/i', $fullMatch, $full)) {
                    $url = $full[1];
                    $text = htmlspecialchars($full[2]);
                    $colorCode = $this->getColorCode($full[3]);
                }
                // 2. button[URL|TEXT]
                elseif (preg_match('/button\[([^\]]+)\|([^\]]+)\]/i', $fullMatch, $partial)) {
                    $url = $partial[1];
                    $text = htmlspecialchars($partial[2]);
                    $colorCode = '#6366f1'; // Ungu default
                }
                // 3. button[URL]
                else {
                    $url = $matches[1];
                    $text = 'Klik Disini';
                    $colorCode = '#6366f1';
                }
                
                return "
                    <table cellpadding='0' cellspacing='0' border='0' style='display: inline-block; margin: 12px 0;'>
                        <tr>
                            <td bgcolor='$colorCode' style='background-color: $colorCode; border-radius: 12px; padding: 16px 32px;'>
                                <a href='$url' style='color: #ffffff; text-decoration: none; font-weight: 700; font-size: 16px; display: block;'>$text</a>
                            </td>
                        </tr>
                    </table>
                ";
            },
            $content
        );
    }

    /**
     * Mapping nama warna ke kode HEX (aman untuk email)
     */
    private function getColorCode($colorName)
    {
        $colors = [
            'ungu' => '#4c1d95',
            'purple' => '#4c1d95',
            'orange' => '#f97316',
            'hijau' => '#10b981',
            'green' => '#10b981',
            'merah' => '#ef4444',
            'red' => '#ef4444',
            'biru' => '#3b82f6',
            'blue' => '#3b82f6',
            'pink' => '#ec4899'
        ];
        
        return $colors[strtolower(trim($colorName))] ?? '#4c1d95'; // Default ungu
    }

    public function cancelBatch($batchId)
    {
        $batch = Bus::findBatch($batchId);
        if ($batch) {
            $batch->cancel();
        }
        
        return response()->json(['success' => true]);
    }

    public function trackOpen(Request $request)
    {
        $batchId = $request->query('b');
        $email = base64_decode($request->query('e'));

        if ($batchId && $email) {
            DB::table('email_blast_logs')
                ->where('batch_id', $batchId)
                ->where('email', $email)
                ->where('is_opened', false)
                ->update([
                    'is_opened' => true,
                    'opened_at' => now()
                ]);
        }

        // Return gambar pixel transparan 1x1
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel)->header('Content-Type', 'image/gif')->header('Cache-Control', 'no-cache');
    }

}
