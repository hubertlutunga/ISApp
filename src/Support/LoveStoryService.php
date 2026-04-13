<?php

final class LoveStoryService
{
    public static function getByEvent(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM lovestory WHERE cod_event = ? LIMIT 1');
        $stmt->execute([$eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'imgcoeur1' => $row['imgcoeur1'] ?? 'defaulwed_1.png',
            'imgcoeur2' => $row['imgcoeur2'] ?? 'defaulwed_1.png',
            'text_lovestory' => $row['text_lovestory'] ?? 'C’est ici que tout a commencé, Une rencontre de courtoisie qui a donné naissance à une belle histoire d’amour… Deux regards tournés vers l’avenir pour ne plus jamais se quitter …',
            'exists' => $row !== [],
            'raw' => $row,
        ];
    }

    public static function upsert(PDO $pdo, int $eventId, int $agentId, string $text, ?string $image1, ?string $image2): void
    {
        $current = self::getByEvent($pdo, $eventId);

        if ($current['exists']) {
            $sql = 'UPDATE lovestory SET text_lovestory = :text_lovestory, cod_agent = :cod_agent';

            if ($image1 !== null && $image1 !== '') {
                $sql .= ', imgcoeur1 = :imgcoeur1';
            }

            if ($image2 !== null && $image2 !== '') {
                $sql .= ', imgcoeur2 = :imgcoeur2';
            }

            $sql .= ' WHERE cod_event = :cod_event';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':text_lovestory', $text);
            $stmt->bindValue(':cod_agent', $agentId, PDO::PARAM_INT);
            $stmt->bindValue(':cod_event', $eventId, PDO::PARAM_INT);

            if ($image1 !== null && $image1 !== '') {
                $stmt->bindValue(':imgcoeur1', $image1);
            }

            if ($image2 !== null && $image2 !== '') {
                $stmt->bindValue(':imgcoeur2', $image2);
            }

            $stmt->execute();
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO lovestory (imgcoeur1, imgcoeur2, text_lovestory, date_enreg, cod_agent, cod_event) VALUES (:imgcoeur1, :imgcoeur2, :text_lovestory, NOW(), :cod_agent, :cod_event)'
        );
        $stmt->bindValue(':imgcoeur1', $image1 ?? 'defaulwed_1.png');
        $stmt->bindValue(':imgcoeur2', $image2 ?? 'defaulwed_1.png');
        $stmt->bindValue(':text_lovestory', $text);
        $stmt->bindValue(':cod_agent', $agentId, PDO::PARAM_INT);
        $stmt->bindValue(':cod_event', $eventId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function listSteps(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM lovestory_etap WHERE cod_event = :codevent ORDER BY cod_ls ASC');
        $stmt->execute(['codevent' => $eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addStep(PDO $pdo, int $eventId, int $agentId, string $eventStep, string $dateMonth): void
    {
        $eventStep = trim($eventStep) ?: 'Non renseigne';
        $dateValue = trim($dateMonth) ?: date('Y-m');
        $dateValue .= '-01';

        $stmt = $pdo->prepare(
            'INSERT INTO lovestory_etap (event_etap, date_etap, cod_event, date_enreg, cod_agent) VALUES (:event_etap, :date_etap, :cod_event, NOW(), :cod_agent)'
        );
        $stmt->bindValue(':event_etap', $eventStep);
        $stmt->bindValue(':date_etap', $dateValue);
        $stmt->bindValue(':cod_event', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':cod_agent', $agentId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function deleteStep(PDO $pdo, int $stepId, int $eventId): int
    {
        $stmt = $pdo->prepare('DELETE FROM lovestory_etap WHERE cod_ls = ? AND cod_event = ?');
        $stmt->execute([$stepId, $eventId]);

        return $stmt->rowCount();
    }
}