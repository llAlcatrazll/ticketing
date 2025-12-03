<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ViewQrCodeAction extends Action
{
    use \Filament\Actions\Concerns\InteractsWithRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('view-qr-code');

        $this->label('View QR Code');

        $this->icon('gmdi-qr-code-o');

        $this->modalIcon('gmdi-qr-code-o');

        $this->modalWidth(\Filament\Support\Enums\MaxWidth::Small);

        $this->modalDescription('You can download this QR code and share it to access the feedback form for your office.');

        $this->modalHeading(request()->user()->organization->code . ' QR Code');

        $this->modalSubmitActionLabel('Download');

        $this->modalCancelAction(false);

        $this->closeModalByClickingAway(false);

        $this->modalFooterActionsAlignment(Alignment::Center);

        $this->modalContent(function () {
            return view('filament.panels.admin.clusters.organization.view-qrCode', [
                'qr' => QrCode::size(200)->generate(url('/') . '/feedback/' . request()->user()->organization_id . '/feedback'),
            ]);
        });

        $this->action(function () {
                $filename = 'QR-' . request()->user()->organization->code . '.png';
                $qrCode = QrCode::format('png')
                    ->size(1024)
                    ->generate(url('/') . '/feedback/' . request()->user()->organization_id . '/feedback');

                return response()->streamDownload(
                    fn () => print($qrCode),
                    $filename,
                    ['Content-Type' => 'image/png']
                );
            });
    }
}
