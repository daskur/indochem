<?php
declare(strict_types=1);

function issue_process_certificate(PDO $pdo, int $userId, int $processId, string $processTitle): array
{
    $existing = $pdo->prepare(
        'SELECT * FROM certificates WHERE user_id=? AND process_id=? LIMIT 1'
    );
    $existing->execute([$userId,$processId]);
    $certificate = $existing->fetch();
    if ($certificate) {
        return $certificate;
    }

    do {
        $certificateNumber = 'IPLA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $check = $pdo->prepare('SELECT id FROM certificates WHERE certificate_number=? LIMIT 1');
        $check->execute([$certificateNumber]);
    } while ($check->fetch());

    $title = 'Certificate of Completion: ' . $processTitle;
    try {
        $insert = $pdo->prepare(
            'INSERT INTO certificates (user_id,process_id,certificate_number,title)
             VALUES (?,?,?,?)'
        );
        $insert->execute([$userId,$processId,$certificateNumber,$title]);
    } catch (PDOException $error) {
        if ((int) $error->getCode() !== 23000) {
            throw $error;
        }
        $existing->execute([$userId,$processId]);
        $certificate = $existing->fetch();
        if ($certificate) {
            return $certificate;
        }
        throw $error;
    }

    $existing->execute([$userId,$processId]);
    return $existing->fetch();
}
