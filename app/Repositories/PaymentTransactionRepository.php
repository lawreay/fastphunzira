<?php

namespace App\Repositories;

use PDO;

final class PaymentTransactionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $transaction): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO payment_transactions
                (user_id, provider, tx_ref, purpose, amount, currency, status, metadata)
             VALUES
                (:user_id, :provider, :tx_ref, :purpose, :amount, :currency, :status, :metadata)'
        );

        $statement->execute([
            ':user_id' => (int) $transaction['user_id'],
            ':provider' => (string) ($transaction['provider'] ?? 'paychangu'),
            ':tx_ref' => (string) $transaction['tx_ref'],
            ':purpose' => (string) $transaction['purpose'],
            ':amount' => (float) $transaction['amount'],
            ':currency' => strtoupper((string) ($transaction['currency'] ?? 'MWK')),
            ':status' => (string) ($transaction['status'] ?? 'pending'),
            ':metadata' => isset($transaction['metadata'])
                ? json_encode($transaction['metadata'], JSON_UNESCAPED_SLASHES)
                : null,
        ]);

        $transaction['id'] = (int) $this->pdo->lastInsertId();

        return $transaction;
    }

    public function findByTxRef(string $txRef): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM payment_transactions WHERE tx_ref = :tx_ref LIMIT 1'
        );
        $statement->execute([':tx_ref' => $txRef]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function updateStatus(string $txRef, string $status, ?string $providerTransactionId = null): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE payment_transactions
             SET status = :status, provider_transaction_id = COALESCE(:provider_transaction_id, provider_transaction_id)
             WHERE tx_ref = :tx_ref'
        );

        $statement->execute([
            ':status' => $status,
            ':provider_transaction_id' => $providerTransactionId,
            ':tx_ref' => $txRef,
        ]);

        return $statement->rowCount() > 0;
    }
}
