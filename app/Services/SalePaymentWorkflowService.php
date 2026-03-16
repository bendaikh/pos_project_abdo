<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Collection;

class SalePaymentWorkflowService
{
    public const STATUS_TO_PAY = 'to_pay';
    public const STATUS_TO_COLLECT = 'to_collect';
    public const STATUS_PAID = 'paid';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_UNPAID = 'unpaid';

    private const EPSILON = 0.00001;

    public function decorateSale(Sale $sale, bool $decoratePayments = true): Sale
    {
        $summary = $this->computeSaleSummary($sale);

        $sale->setAttribute('payment_status_code', $summary['payment_status_code']);
        $sale->setAttribute('payment_status_label', $summary['payment_status_label']);
        $sale->setAttribute('paid_confirmed_amount', $summary['paid_confirmed_amount']);
        $sale->setAttribute('pending_collection_amount', $summary['pending_collection_amount']);
        $sale->setAttribute('remaining_amount', $summary['remaining_amount']);
        $sale->setAttribute('failed_amount', $summary['failed_amount']);
        $sale->setAttribute('payment_summary', $summary);

        if ($decoratePayments && $sale->relationLoaded('payments')) {
            $sale->setRelation(
                'payments',
                $sale->payments->map(fn (Payment $payment) => $this->decoratePayment($payment))
            );
        }

        return $sale;
    }

    public function decoratePayment(Payment $payment): Payment
    {
        $payment->setAttribute('payment_method_label', $this->paymentMethodLabel($payment));
        $payment->setAttribute('reference_number', $this->paymentReferenceNumber($payment));
        $payment->setAttribute('requires_confirmation', $this->requiresConfirmation(
            $payment->payment_type,
            $this->resolveTransferMode($payment->payment_type, $payment->transfer_mode, $payment->notes)
        ));

        $statusCode = $this->paymentWorkflowStatus($payment);
        $payment->setAttribute('workflow_status_code', $statusCode);
        $payment->setAttribute('workflow_status_label', $this->saleStatusLabel($statusCode));

        return $payment;
    }

    public function computeSaleSummary(Sale $sale): array
    {
        $sale->loadMissing('payments');

        $totalAmount = $this->amount($sale->total);
        $confirmedImmediateAmount = 0.0;
        $confirmedDeferredAmount = 0.0;
        $pendingCollectionAmount = 0.0;
        $failedAmount = 0.0;

        /** @var Collection<int, Payment> $payments */
        $payments = $sale->payments ?? collect();

        foreach ($payments as $payment) {
            $amount = $this->amount($payment->amount);

            if ($this->isFailedPayment($payment)) {
                $failedAmount += $amount;
                continue;
            }

            if ($this->isPendingCollectionPayment($payment)) {
                $pendingCollectionAmount += $amount;
                continue;
            }

            if ($this->isConfirmedPayment($payment)) {
                if ($this->requiresConfirmationForPayment($payment)) {
                    $confirmedDeferredAmount += $amount;
                } else {
                    $confirmedImmediateAmount += $amount;
                }
            }
        }

        $paidConfirmedAmount = $this->amount($confirmedImmediateAmount + $confirmedDeferredAmount);
        $pendingCollectionAmount = $this->amount($pendingCollectionAmount);
        $failedAmount = $this->amount($failedAmount);
        $remainingAmount = $this->amount(max(0, $totalAmount - $paidConfirmedAmount - $pendingCollectionAmount));

        $statusCode = $this->determineSaleStatus(
            $totalAmount,
            $confirmedImmediateAmount,
            $confirmedDeferredAmount,
            $pendingCollectionAmount,
            $failedAmount,
            $remainingAmount
        );

        return [
            'total_amount' => $totalAmount,
            'paid_confirmed_amount' => $paidConfirmedAmount,
            'pending_collection_amount' => $pendingCollectionAmount,
            'remaining_amount' => $remainingAmount,
            'failed_amount' => $failedAmount,
            'payment_status_code' => $statusCode,
            'payment_status_label' => $this->saleStatusLabel($statusCode),
        ];
    }

    public function syncSalePaymentStatus(Sale $sale): array
    {
        $summary = $this->computeSaleSummary($sale->fresh(['payments']));
        $legacyStatus = $this->toLegacySaleStatus($summary['payment_status_code'], $summary);

        $updates = [
            'payment_status' => $legacyStatus,
        ];

        if (Sale::supportsColumn('payment_status_code')) {
            $updates['payment_status_code'] = $summary['payment_status_code'];
        }

        $sale->forceFill($updates);

        if ($sale->isDirty(array_keys($updates))) {
            $sale->saveQuietly();
        }

        return $summary;
    }

    public function normalizePaymentType(?string $paymentType): ?string
    {
        return match ($paymentType) {
            'check' => 'cheque',
            'simple_transfer', 'instant_transfer', 'transfer' => 'virement',
            default => $paymentType,
        };
    }

    public function resolveTransferMode(?string $paymentType, ?string $transferMode, ?string $notes = null): ?string
    {
        $paymentType = $this->normalizePaymentType($paymentType);

        if ($paymentType !== 'virement') {
            return null;
        }

        if (in_array($transferMode, ['simple', 'instant'], true)) {
            return $transferMode;
        }

        $notes = (string) $notes;

        if (str_contains($notes, '[VIREMENT_INSTANT]')) {
            return 'instant';
        }

        if (str_contains($notes, '[VIREMENT_SIMPLE]')) {
            return 'simple';
        }

        return 'simple';
    }

    public function requiresConfirmation(?string $paymentType, ?string $transferMode = null): bool
    {
        $paymentType = $this->normalizePaymentType($paymentType);

        if (in_array($paymentType, ['cheque', 'credit'], true)) {
            return true;
        }

        if ($paymentType === 'virement') {
            return $transferMode !== 'instant';
        }

        return false;
    }

    public function paymentMethodLabel(Payment $payment): string
    {
        $type = $this->normalizePaymentType($payment->payment_type);
        $transferMode = $this->resolveTransferMode($type, $payment->transfer_mode, $payment->notes);

        if ($type === 'virement' && $transferMode === 'instant') {
            return 'Virement instantané';
        }

        return match ($type) {
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'mobile' => 'Paiement mobile',
            'cheque' => 'Chèque',
            'virement' => 'Virement standard',
            'credit' => 'Crédit / LCN',
            default => ucfirst((string) $type),
        };
    }

    public function paymentReferenceNumber(Payment $payment): ?string
    {
        return $payment->transaction_number
            ?: $payment->piece_number
            ?: $payment->reference;
    }

    public function normalizeWorkflowStatus(?string $status): ?string
    {
        if (!$status) {
            return null;
        }

        $normalized = mb_strtolower(trim($status));
        $normalized = str_replace(
            ['à', 'â', 'é', 'è', 'ê', 'î', 'ï', 'ô', 'ù', 'û', 'ç'],
            ['a', 'a', 'e', 'e', 'e', 'i', 'i', 'o', 'u', 'u', 'c'],
            $normalized
        );

        return match ($normalized) {
            'a payer', 'a_payer', 'to_pay' => self::STATUS_TO_PAY,
            'a encaisser', 'a_encaisser', 'to_collect', 'pending' => self::STATUS_TO_COLLECT,
            'paye', 'paid' => self::STATUS_PAID,
            'encaisse', 'collected' => self::STATUS_COLLECTED,
            'impaye', 'unpaid', 'failed', 'cancelled' => self::STATUS_UNPAID,
            default => null,
        };
    }

    public function saleStatusLabel(string $statusCode): string
    {
        return match ($statusCode) {
            self::STATUS_TO_COLLECT => 'À encaisser',
            self::STATUS_PAID => 'Payé',
            self::STATUS_COLLECTED => 'Encaissé',
            self::STATUS_UNPAID => 'Impayé',
            default => 'À payer',
        };
    }

    public function paymentWorkflowStatus(Payment $payment): string
    {
        if ($this->isFailedPayment($payment)) {
            return self::STATUS_UNPAID;
        }

        if ($this->requiresConfirmationForPayment($payment)) {
            return $this->isConfirmedPayment($payment)
                ? self::STATUS_COLLECTED
                : self::STATUS_TO_COLLECT;
        }

        return self::STATUS_PAID;
    }

    private function determineSaleStatus(
        float $totalAmount,
        float $confirmedImmediateAmount,
        float $confirmedDeferredAmount,
        float $pendingCollectionAmount,
        float $failedAmount,
        float $remainingAmount
    ): string {
        if ($totalAmount <= self::EPSILON) {
            return self::STATUS_PAID;
        }

        $confirmedAmount = $confirmedImmediateAmount + $confirmedDeferredAmount;

        if ($confirmedAmount + self::EPSILON >= $totalAmount) {
            return $confirmedDeferredAmount > self::EPSILON
                ? self::STATUS_COLLECTED
                : self::STATUS_PAID;
        }

        if ($confirmedAmount + $pendingCollectionAmount + self::EPSILON >= $totalAmount) {
            return self::STATUS_TO_COLLECT;
        }

        if ($failedAmount > self::EPSILON && $remainingAmount > self::EPSILON) {
            return self::STATUS_UNPAID;
        }

        return self::STATUS_TO_PAY;
    }

    private function toLegacySaleStatus(string $statusCode, array $summary): string
    {
        return match ($statusCode) {
            self::STATUS_PAID, self::STATUS_COLLECTED => 'paid',
            default => ($summary['paid_confirmed_amount'] > self::EPSILON || $summary['pending_collection_amount'] > self::EPSILON)
                ? 'partial'
                : 'unpaid',
        };
    }

    private function requiresConfirmationForPayment(Payment $payment): bool
    {
        return $this->requiresConfirmation(
            $payment->payment_type,
            $this->resolveTransferMode($payment->payment_type, $payment->transfer_mode, $payment->notes)
        );
    }

    private function isConfirmedPayment(Payment $payment): bool
    {
        if ($payment->payment_status !== 'completed') {
            return false;
        }

        if (!$this->requiresConfirmationForPayment($payment)) {
            return true;
        }

        return $payment->collection_status === 'collected';
    }

    private function isPendingCollectionPayment(Payment $payment): bool
    {
        return $this->requiresConfirmationForPayment($payment)
            && $payment->payment_status === 'pending'
            && $payment->collection_status === 'pending';
    }

    private function isFailedPayment(Payment $payment): bool
    {
        return $payment->payment_status === 'failed'
            || $payment->collection_status === 'cancelled';
    }

    private function amount(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
