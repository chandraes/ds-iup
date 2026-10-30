<?php

namespace App\Http\Controllers;

use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class HistoriController extends Controller
{
    public function index(Request $request)
    {
        // Jika request dari Datatables (AJAX)
        if ($request->ajax()) {
            $data = PesanWa::leftJoin('group_was', 'pesan_was.tujuan', '=', 'group_was.nama_group')
                ->select('pesan_was.*', 'group_was.group_id')
                ->orderBy('pesan_was.id', 'desc');

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('created_at_display', fn (PesanWa $row): string => $row->created_at ? $row->created_at->copy()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB' : 'Belum tercatat')
                ->addColumn('last_attempt_at_display', fn (PesanWa $row): string => $row->last_attempt_at ? $row->last_attempt_at->copy()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB' : 'Belum tercatat')
                ->editColumn('pesan', function ($row) {
                    // Potong pesan agar tabel tetap rapi
                    return mb_strimwidth($row->pesan, 0, 60, '…');
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        return '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">Belum Terkirim</span>';
                    }

                    return '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">Terkirim</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-center gap-2">';

                    // Gunakan htmlspecialchars agar karakter unik WA aman dimasukkan ke dalam atribut HTML
                    $pesanAman = htmlspecialchars($row->pesan, ENT_QUOTES, 'UTF-8');

                    // Hapus onclick, ganti dengan class btn-detail dan atribut data-pesan
                    $btn .= '<button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm btn-detail" data-pesan="'.$pesanAman.'"><i class="fa fa-eye me-1"></i> Detail</button>';

                    // Tombol Kirim Ulang (Hanya jika belum terkirim)
                    if ($row->status == 0) {
                        $resendUrl = route('histori.resend', $row->id);
                        $btn .= '<form action="'.$resendUrl.'" method="post" class="d-inline text-nowrap form-resend">'.
                                csrf_field().
                                '<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm btn-resend"><i class="fa fa-paper-plane me-1"></i> Kirim Ulang</button>'.
                                '</form>';
                    }

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pengaturan.histori.index');
    }

    public function resend(Request $request, PesanWa $pesanWa): JsonResponse|RedirectResponse
    {
        $lock = Cache::lock('whatsapp-resend:'.$pesanWa->id, 90);
        if (! $lock->get()) {
            return $this->resendResult($request, false, 'Pesan sedang diproses. Tunggu sampai proses selesai.', 409);
        }
        try {
            $pesanWa->refresh();
            if ((int) $pesanWa->status === 1) {
                return $this->resendResult($request, true, 'Pesan sudah tercatat terkirim; tidak dikirim ulang.');
            }
            $pesanWa->update(['last_attempt_at' => now()]);
            try {
                $starSender = app(StarSender::class, ['tujuan' => $pesanWa->tujuan, 'pesan' => $pesanWa->pesan]);
                $sent = $starSender->sendGroup();
            } catch (Throwable $exception) {
                report($exception);

                return $this->resendResult($request, false, 'Pengiriman belum berhasil. Periksa koneksi layanan WhatsApp sebelum mencoba kembali.', 502);
            }
            if (! $sent) {
                return $this->resendResult($request, false, 'Layanan WhatsApp belum mengonfirmasi pengiriman. Silakan periksa kembali.', 422);
            }
            $pesanWa->update(['status' => 1]);

            return $this->resendResult($request, true, 'Berhasil mengirim ulang pesan.');
        } finally {
            $lock->release();
        }
    }

    private function resendResult(Request $request, bool $success, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['success' => $success, 'message' => $message], $status)
            : back()->with($success ? 'success' : 'error', $message);
    }

    public function delete_sended()
    {
        PesanWa::where('status', 1)->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus pesan yang sudah terkirim');
    }
}
