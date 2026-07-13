<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Jobs;

use Carbon\Carbon;
use FI\Modules\Clients\Models\Client;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Notes\Models\Note;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Settings\Models\Setting;
use FI\Modules\TaskList\Models\Task;
use FI\Modules\Transitions\Models\Transitions;
use FI\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateTimelineHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $clientTimelines = [];
    public $expenseTimelines = [];
    public $invoiceTimelines = [];
    public $noteTimelines = [];
    public $paymentInvoiceTimelines = [];
    public $paymentTimelines = [];
    public $quoteTimelines = [];
    public $recurringInvoiceTimelines = [];
    public $taskTimelines = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lastTransition = Transitions::query()->orderBy('id', 'asc')->first();
        $userId         = User::whereUserType('system')->first()->id;
        $userId         = $userId ? $userId : auth()->user()->id;

        if ($lastTransition)
        {
            $date = $lastTransition->created_at;
        }
        else
        {
            $date = Carbon::now();
        }
        /*
         * Client Timelines
         */
        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasClientTransitionCreated = Setting::getByKey('clientTransitionHistoryCreated');
                if (!$hasClientTransitionCreated)
                {
                    $clientRecords = Client::query()
                        ->where('created_at', '<', $date)
                        ->get();
                    if ($clientRecords->count())
                    {
                        foreach ($clientRecords as $clientRecord)
                        {
                            $this->clientTimelines[] = [
                                'user_id'             => $clientRecord->user_id ? $clientRecord->user_id : $userId,
                                'client_id'           => $clientRecord->id,
                                'transitionable_id'   => $clientRecord->id,
                                'transitionable_type' => 'FI\Modules\Clients\Models\Client',
                                'action_type'         => 'client_created',
                                'detail'              => json_encode([
                                    'id'   => $clientRecord->id,
                                    'name' => $clientRecord->name,
                                    'type' => $clientRecord->type,
                                ]),
                                'previous_value'      => null,
                                'current_value'       => null,
                                'created_at'          => $clientRecord->created_at,
                                'updated_at'          => $clientRecord->updated_at,
                            ];
                        }
                        if (!empty($this->clientTimelines))
                        {
                            foreach (array_chunk($this->clientTimelines, 50) as $clientTimelineChunk)
                            {
                                Transitions::insert($clientTimelineChunk);
                            }
                            Setting::saveByKey('clientTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('clientTransitionHistoryCreated', 1);
                    }
                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('clientTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Expense Timelines
         */
        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasExpenseTransitionCreated = Setting::getByKey('expenseTransitionHistoryCreated');
                if (!$hasExpenseTransitionCreated)
                {
                    $expenseRecords = Expense::query()
                        ->where([
                            ['created_at', '<', $date],
                            ['client_id', '!=', 0],
                        ])
                        ->get();
                    if ($expenseRecords->count())
                    {
                        foreach ($expenseRecords as $expenseRecord)
                        {
                            if ($expenseRecord->client_id)
                            {
                                $this->expenseTimelines[] = [
                                    'user_id'             => $expenseRecord->user_id,
                                    'client_id'           => $expenseRecord->client_id,
                                    'transitionable_id'   => $expenseRecord->id,
                                    'transitionable_type' => 'FI\Modules\Expenses\Models\Expense',
                                    'action_type'         => 'created',
                                    'detail'              => json_encode([
                                        'amount' => $expenseRecord->formatted_amount,
                                    ]),
                                    'previous_value'      => null,
                                    'current_value'       => null,
                                    'created_at'          => $expenseRecord->created_at,
                                    'updated_at'          => $expenseRecord->updated_at,
                                ];
                            }
                        }
                        if (!empty($this->expenseTimelines))
                        {
                            foreach (array_chunk($this->expenseTimelines, 50) as $expenseTimelineChunk)
                            {
                                Transitions::insert($expenseTimelineChunk);
                            }
                            Setting::saveByKey('expenseTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('expenseTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('expenseTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Invoice Timelines
         */
        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasInvoiceTransitionCreated = Setting::getByKey('invoiceTransitionHistoryCreated');
                if (!$hasInvoiceTransitionCreated)
                {
                    $invoiceRecords = Invoice::query()
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($invoiceRecords->count())
                    {
                        foreach ($invoiceRecords as $invoiceRecord)
                        {
                            $tempDetail = [
                                'user_id'             => $invoiceRecord->user_id,
                                'client_id'           => $invoiceRecord->client_id,
                                'transitionable_id'   => $invoiceRecord->id,
                                'transitionable_type' => 'FI\Modules\Invoices\Models\Invoice',
                                'previous_value'      => null,
                                'current_value'       => null,
                                'created_at'          => $invoiceRecord->created_at,
                                'updated_at'          => $invoiceRecord->updated_at,
                            ];
                            if ($invoiceRecord->type == 'credit_memo')
                            {
                                $tempDetail['action_type'] = 'credit_memo_created';
                                $tempDetail['detail']      = json_encode([
                                    'number' => $invoiceRecord->number,
                                ]);
                            }
                            elseif ($invoiceRecord->recurring_invoice_id)
                            {
                                $tempDetail['action_type'] = 'created_from_recurring';
                                $tempDetail['detail']      = json_encode([
                                    'number'               => $invoiceRecord->number,
                                    'recurring_invoice_id' => $invoiceRecord->recurring_invoice_id,
                                ]);
                            }
                            else
                            {
                                $tempDetail['action_type'] = 'created';
                                $tempDetail['detail']      = json_encode([
                                    'number' => $invoiceRecord->number,
                                ]);
                            }
                            $this->invoiceTimelines[] = $tempDetail;
                        }
                        if (!empty($this->invoiceTimelines))
                        {
                            foreach (array_chunk($this->invoiceTimelines, 50) as $invoiceTimelineChunk)
                            {
                                Transitions::insert($invoiceTimelineChunk);
                            }
                            Setting::saveByKey('invoiceTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('invoiceTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('invoiceTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * PaymentInvoice Timelines
         */
        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasPaymentInvoiceTransitionCreated = Setting::getByKey('paymentInvoiceTransitionHistoryCreated');
                if (!$hasPaymentInvoiceTransitionCreated)
                {
                    $paymentInvoiceRecords = PaymentInvoice::query()
                        ->with(['payment', 'invoice'])
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($paymentInvoiceRecords->count())
                    {
                        foreach ($paymentInvoiceRecords as $paymentInvoiceRecord)
                        {
                            $this->paymentInvoiceTimelines[] = [
                                'user_id'             => isset($paymentInvoiceRecord->payment->user_id) ? $paymentInvoiceRecord->payment->user_id : $userId,
                                'client_id'           => $paymentInvoiceRecord->invoice->client_id,
                                'transitionable_id'   => $paymentInvoiceRecord->id,
                                'transitionable_type' => 'FI\Modules\Payments\Models\PaymentInvoice',
                                'action_type'         => 'payment_received',
                                'detail'              => json_encode([
                                    'payment_id'          => $paymentInvoiceRecord->payment_id,
                                    'invoice_number'      => $paymentInvoiceRecord->invoice->number,
                                    'invoice_amount_paid' => $paymentInvoiceRecord->formatted_invoice_amount_paid,
                                    'is_full_amount'      => ($paymentInvoiceRecord->invoice->amount->balance == 0) ? 1 : 0,
                                ]),
                                'previous_value'      => null,
                                'current_value'       => null,
                                'created_at'          => $paymentInvoiceRecord->created_at,
                                'updated_at'          => $paymentInvoiceRecord->updated_at,
                            ];
                        }
                        if (!empty($this->paymentInvoiceTimelines))
                        {
                            foreach (array_chunk($this->paymentInvoiceTimelines, 50) as $paymentInvoiceTimelineChunk)
                            {
                                Transitions::insert($paymentInvoiceTimelineChunk);
                            }
                            Setting::saveByKey('paymentInvoiceTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('paymentInvoiceTransitionHistoryCreated', 1);
                    }
                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('paymentInvoiceTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Payment Timelines (Pre-Payment)
         */
        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasPaymentTransitionCreated = Setting::getByKey('paymentTransitionHistoryCreated');
                if (!$hasPaymentTransitionCreated)
                {
                    $paymentRecords = Payment::query()
                        ->doesntHave('paymentInvoice')
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($paymentRecords->count())
                    {
                        foreach ($paymentRecords as $paymentRecord)
                        {
                            $this->paymentTimelines[] = [
                                'user_id'             => $paymentRecord->user_id ? $paymentRecord->user_id : $userId,
                                'client_id'           => $paymentRecord->client_id,
                                'transitionable_id'   => $paymentRecord->id,
                                'transitionable_type' => 'FI\Modules\Payments\Models\Payment',
                                'action_type'         => 'prepayment_created',
                                'detail'              => json_encode([
                                    'id'     => $paymentRecord->id,
                                    'amount' => getCurrencySign(config('fi.baseCurrency')) . ' ' . $paymentRecord->formatted_numeric_amount,
                                ]),
                                'previous_value'      => null,
                                'current_value'       => null,
                                'created_at'          => $paymentRecord->created_at,
                                'updated_at'          => $paymentRecord->updated_at,
                            ];
                        }
                        if (!empty($this->paymentTimelines))
                        {
                            foreach (array_chunk($this->paymentTimelines, 50) as $paymentTimelineChunk)
                            {
                                Transitions::insert($paymentTimelineChunk);
                            }
                            Setting::saveByKey('paymentTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('paymentTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('paymentTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Quote Timelines
         */

        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $hasquoteTransitionCreated = Setting::getByKey('quoteTransitionHistoryCreated');
                if (!$hasquoteTransitionCreated)
                {
                    $quoteRecords = Quote::query()
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($quoteRecords->count())
                    {
                        foreach ($quoteRecords as $quoteRecord)
                        {
                            $this->quoteTimelines[] = [
                                'user_id'             => $quoteRecord->user_id,
                                'client_id'           => $quoteRecord->client_id,
                                'transitionable_id'   => $quoteRecord->id,
                                'transitionable_type' => 'FI\Modules\Quotes\Models\Quote',
                                'action_type'         => 'created',
                                'detail'              => json_encode([
                                    'number' => $quoteRecord->number,
                                ]),
                                'previous_value'      => null,
                                'current_value'       => null,
                                'created_at'          => $quoteRecord->created_at,
                                'updated_at'          => $quoteRecord->updated_at,
                            ];
                        }
                        if (!empty($this->quoteTimelines))
                        {
                            foreach (array_chunk($this->quoteTimelines, 50) as $quoteTimelineChunk)
                            {
                                Transitions::insert($quoteTimelineChunk);
                            }
                            Setting::saveByKey('quoteTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('quoteTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('quoteTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Note Timelines
         */

        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $noteTransitionCreated = Setting::getByKey('noteTransitionHistoryCreated');
                if (!$noteTransitionCreated)
                {
                    $noteRecords = Note::query()
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($noteRecords->count())
                    {
                        foreach ($noteRecords as $noteRecord)
                        {
                            $notable = $noteRecord->notable;
                            if ($noteRecord->notable_type == 'FI\Modules\Clients\Models\Client')
                            {
                                $clientId = $notable && isset($notable->id) ? $notable->id : null;
                            }
                            else
                            {
                                $clientId = (isset($notable->client_id)) ? $notable->client_id : null;
                            }
                            if ($clientId)
                            {
                                $this->noteTimelines[] = [
                                    'user_id'             => $noteRecord->user_id,
                                    'client_id'           => $clientId,
                                    'transitionable_id'   => $noteRecord->id,
                                    'action_type'         => 'created',
                                    'transitionable_type' => 'FI\Modules\Notes\Models\Note',
                                    'detail'              => json_encode([
                                        'short_text' => $noteRecord->note,
                                    ]),
                                    'previous_value'      => null,
                                    'current_value'       => null,
                                    'created_at'          => $noteRecord->created_at,
                                    'updated_at'          => $noteRecord->updated_at,
                                ];
                            }
                        }
                        if (!empty($this->noteTimelines))
                        {
                            foreach (array_chunk($this->noteTimelines, 50) as $noteTimelineChunk)
                            {
                                Transitions::insert($noteTimelineChunk);
                            }
                            Setting::saveByKey('noteTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('noteTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('noteTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        /*
         * Task Timelines
         */

        try
        {
            DB::transaction(function () use ($userId, $date)
            {
                $taskTransitionCreated = Setting::getByKey('taskTransitionHistoryCreated');
                if (!$taskTransitionCreated)
                {
                    $taskRecords = Task::query()
                        ->where([
                            ['created_at', '<', $date],
                        ])
                        ->get();
                    if ($taskRecords->count())
                    {
                        foreach ($taskRecords as $taskRecord)
                        {
                            if ($taskRecord->client_id)
                            {
                                $this->taskTimelines[] = [
                                    'user_id'             => $taskRecord->user_id,
                                    'client_id'           => $taskRecord->client_id,
                                    'transitionable_id'   => $taskRecord->id,
                                    'transitionable_type' => 'FI\Modules\TaskList\Models\Task',
                                    'action_type'         => 'created',
                                    'detail'              => json_encode([
                                        'short_title' => $taskRecord->formatted_short_title,
                                    ]),
                                    'previous_value'      => null,
                                    'current_value'       => null,
                                    'created_at'          => $taskRecord->created_at,
                                    'updated_at'          => $taskRecord->updated_at,
                                ];
                            }
                        }
                        if (!empty($this->taskTimelines))
                        {
                            foreach (array_chunk($this->taskTimelines, 50) as $taskTimelineChunk)
                            {
                                Transitions::insert($taskTimelineChunk);
                            }
                            Setting::saveByKey('taskTransitionHistoryCreated', 1);
                        }
                    }
                    else
                    {
                        Setting::saveByKey('taskTransitionHistoryCreated', 1);
                    }

                }
            });
        }
        catch (\Exception $e)
        {
            Setting::saveByKey('taskTransitionHistoryCreated', 0);
            Log::error($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }
}
