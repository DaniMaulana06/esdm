<?php

namespace App\Mail;

use App\Models\Bku;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengingatLaporanHarianMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, string> $missingDates Daftar tanggal laporan yang belum dikirim (format: YYYY-MM-DD)
     */
    public function __construct(
        public User $user,
        public Bku $bku,
        public array $missingDates
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'esdm@sumselprov.go.id');
        $fromName = config('mail.from.name', 'SUMAS - Dinas ESDM');

        $tanggalCount = count($this->missingDates);
        $subject = $tanggalCount === 1
            ? "[SUMAS] Pengingat Laporan Produksi Harian Belum Disampaikan ({$this->bku->nama})"
            : "[SUMAS] Pengingat {$tanggalCount} Laporan Produksi Harian Belum Disampaikan ({$this->bku->nama})";

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pengingat_laporan_harian',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
