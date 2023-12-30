<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function invoice_download($id)
    {
        $direction = 'ltr';
        $text_align = 'left';
        $not_text_align = 'right';

        $font_family = "'Roboto','sans-serif'";
        $order = Order::findOrFail($id);

        set_time_limit(300);

        // return view('backend.invoices.invoice', [
        //     'order' => $order,
        //     'font_family' => $font_family,
        //     'direction' => $direction,
        //     'text_align' => $text_align,
        //     'not_text_align' => $not_text_align
        // ]);
        // die;

        $pdf = Pdf::loadView('backend.invoices.invoice', [
            'order' => $order,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align,
            'imagePath' => asset('/admin_assets/assets/img/logo.svg')
        ]);
        // $pdf->render();
        // return $pdf->output();
        // return $pdf->output('order-' . $order->code . '.pdf',array('Attachment'=>0));
        return $pdf->download('order-' . $order->code . '.pdf');
    }
}
