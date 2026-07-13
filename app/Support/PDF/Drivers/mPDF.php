<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support\PDF\Drivers;

use Mpdf\Mpdf as PDF;
use FI\Support\PDF\PDFAbstract;

class mPDF extends PDFAbstract
{
    private function getPdf()
    {
        $pdf = new PDF([
            'tempDir'     => base_path('assets' . DIRECTORY_SEPARATOR),
            'orientation' => $this->paperOrientation,
            'format'      => $this->paperSize,
        ]);
        return $pdf;
    }

    public function getOutput($html, $filename, $downloadOption)
    {
        $pdf = $this->getPdf();

        $pdf->WriteHTML($html);
        return $pdf->Output($filename, $downloadOption);
    }

    public function save($html, $filename)
    {
        return $this->getOutput($html, $filename, 'F');
    }

    public function download($html, $filename)
    {
        return $this->getOutput($html, $filename, 'I');
    }
}