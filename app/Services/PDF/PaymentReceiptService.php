<?php

declare(strict_types=1);

namespace App\Services\PDF;

use App\Models\Setting;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\URL;

class PaymentReceiptService
{
    /**
     * Generate a PDF receipt for a payment transaction
     *
     * @param PaymentTransaction $payment
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePDF(PaymentTransaction $payment)
    {
        // Get system settings
        $settings = $this->getSettings();

        // Convert company logo to base64 for embedding in PDF
        $companyLogo = $settings['company_logo'];
        $logoPath = public_path('assets/images/logo/'.$companyLogo);
        $settings['logo'] = '';
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $settings['logo'] = 'data:image/png;base64,' . base64_encode($imageData);
        }

        // Generate PDF
        $pdf = PDF::loadView('payments.receipts.payment_receipt', [
            'payment' => $payment,
            'settings' => $settings
        ]);

        return $pdf;
    }

    public function generateHTML(PaymentTransaction $payment)
    {
        // Get system settings
        $settings = $this->getSettings();

        // Convert company logo to base64 for embedding in HTML
        $companyLogo = $settings['company_logo'];
        $logoUrl = URL::to('assets/images/logo/'.$companyLogo);
        $settings['logo'] = $logoUrl;

        // Generate HTML directly by rendering the view
        $html = view('payments.receipts.payment_receipt', [
            'payment' => $payment,
            'settings' => $settings
        ])->render();

        return $html;
    }

    /**
     * Download a PDF receipt for a payment transaction
     *
     * @param PaymentTransaction $payment
     * @return Response
     */
    public function downloadPDF(PaymentTransaction $payment)
    {
        $pdf = $this->generatePDF($payment);
        return $pdf->download($this->getFileName($payment));
    }

    /**
     * Stream a PDF receipt for a payment transaction
     *
     * @param PaymentTransaction $payment
     * @return Response
     */
    public function streamPDF(PaymentTransaction $payment)
    {
        $pdf = $this->generatePDF($payment);
        return $pdf->stream($this->getFileName($payment));
    }

    /**
     * Get the encoded PDF for a payment transaction
     *
     * @param PaymentTransaction $payment
     * @return string The encoded PDF
     */
    public function getReceiptEncodedPDF(PaymentTransaction $payment): string
    {
        $pdf = $this->generatePDF($payment);
        return base64_encode($pdf->output());
    }

    public function getHtmlOutput(PaymentTransaction $payment)
    {
        $html = $this->generateHTML($payment);
        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Get the filename for the PDF receipt
     *
     * @param PaymentTransaction $payment
     * @return string
     */
    private function getFileName(PaymentTransaction $payment): string
    {
        return 'payment_receipt_' . $payment->id . '_' . $payment->transaction_id . '.pdf';
    }

    /**
     * Get system settings
     *
     * @return array
     */
    private function getSettings(): array
    {
        $types = ['company_name', 'company_address', 'company_phone', 'company_email', 'company_logo', 'company_tel1', 'company_tel2', 'currency_symbol', 'currency_code'];
        $settings = Setting::whereIn('type', $types)->get()->pluck('data', 'type')->toArray();
        return $settings;
    }
}
