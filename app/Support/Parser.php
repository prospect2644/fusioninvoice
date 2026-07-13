<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support;

use Throwable;

class Parser
{
    public function __construct($object)
    {
        $this->object = $object;

        $this->class = class_basename(get_class($object));
    }

    public function parse($template, $type = '')
    {
        try
        {
            if ($template == 'paymentReceiptBody')
            {
                $paymentReceiptBodyTemplate = ($type == 'custom') ? 'email_templates.payment.' . $template : 'payments.' . $template;
                return view($paymentReceiptBodyTemplate)
                    ->with(strtolower($this->class), $this->object)
                    ->render();
            }
            else
            {
                return view('app.email_templates.' . $template)
                    ->with(strtolower($this->class), $this->object)
                    ->render();
            }
        }
        catch (Throwable $e)
        {
            abort(500, $e->getMessage());
        }
    }
}
