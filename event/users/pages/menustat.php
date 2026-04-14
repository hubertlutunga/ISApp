<?php
  
                                if ($datasession['type_user'] == '3') {
                                    $linkall = "index.php?page=crea_accueil";
                                    $linkrea = "index.php?page=admin_filcom&type=realise";
                                    $linknp = "#";
                                    $linkatt = "index.php?page=admin_filcom&type=enattente";
                                } else { 
                                    $linkall = "index.php?page=admin_accueil";
                                    $linkrea = "index.php?page=admin_filcom&type=realise";
                                    $linknp = "index.php?page=admin_filcom&type=npaye";
                                    $linkatt = "index.php?page=admin_filcom&type=enattente"; 
                                }

// --- Statistiques du mois en cours ---
$sqlCurrentMonthRange = "date_enreg >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01 00:00:00')
	AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), '%Y-%m-01 00:00:00')";

// Réalisées ce mois : la source métier est creaevent.
$stmtMois = $pdo->query("SELECT COUNT(DISTINCT cod_event) FROM creaevent WHERE $sqlCurrentMonthRange");
$nbRealiseMois = (int) $stmtMois->fetchColumn();

// Evénements créés ce mois
$stmtEvtMois = $pdo->query("SELECT COUNT(*) FROM events WHERE $sqlCurrentMonthRange");
$nbEvtMois = (int) $stmtEvtMois->fetchColumn();

// Non payés ce mois
$stmtNpMois = $pdo->query("SELECT COUNT(*) FROM events WHERE fact IS NULL AND $sqlCurrentMonthRange");
$nbNpMois = (int) $stmtNpMois->fetchColumn();

// En attente ce mois
$stmtAttMois = $pdo->query("SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND (crea IS NULL OR crea = '1') AND $sqlCurrentMonthRange");
$nbAttMois = (int) $stmtAttMois->fetchColumn();

// Réalisations par agent : total depuis le début + mois en cours.
$stmtAgents = $pdo->prepare(
	"SELECT u.noms AS agent,
			total_stats.total_realise,
			COALESCE(month_stats.total_mois, 0) AS total_mois
	 FROM (
		 SELECT cod_user, COUNT(DISTINCT cod_event) AS total_realise
		 FROM creaevent
		 GROUP BY cod_user
	 ) AS total_stats
	 INNER JOIN is_users u ON u.cod_user = total_stats.cod_user
	 LEFT JOIN (
		 SELECT cod_user, COUNT(DISTINCT cod_event) AS total_mois
		 FROM creaevent
		 WHERE date_enreg >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01 00:00:00')
		   AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), '%Y-%m-01 00:00:00')
		 GROUP BY cod_user
	 ) AS month_stats ON month_stats.cod_user = total_stats.cod_user
	 ORDER BY month_stats.total_mois DESC, total_stats.total_realise DESC, u.noms ASC"
);
$stmtAgents->execute();
$agentsStats = $stmtAgents->fetchAll(PDO::FETCH_ASSOC);

$dashboardCards = [
	[
		'label' => 'Evénements',
		'total' => (int) $datanbevent,
		'month' => $nbEvtMois,
		'link' => $linkall,
		'icon' => 'fas fa-calendar',
		'tone' => 'primary',
		'accent' => 'Flux global'
	],
	[
		'label' => 'Réalisées',
		'total' => (int) $datarealise,
		'month' => $nbRealiseMois,
		'link' => $linkrea,
		'icon' => 'fas fa-check-circle',
		'tone' => 'info',
		'accent' => 'Production'
	],
	[
		'label' => 'Non payés',
		'total' => (int) $dataincomple,
		'month' => $nbNpMois,
		'link' => $linknp,
		'icon' => 'fas fa-folder',
		'tone' => 'danger',
		'accent' => 'Suivi facture'
	],
	[
		'label' => 'En attentes',
		'total' => (int) $dataattente,
		'month' => $nbAttMois,
		'link' => $linkatt,
		'icon' => 'fas fa-clock',
		'tone' => 'warning',
		'accent' => 'A traiter'
	]
];

$maxMonthly = 0;

foreach ($agentsStats as $agentStat) {
	$maxMonthly = max($maxMonthly, (int) ($agentStat['total_mois'] ?? 0));
}

if ($maxMonthly < 1) {
	$maxMonthly = 1;
}
?>

<style>
	.stats-premium-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 10px;
	}

	.stats-premium-link {
		display: block;
		text-decoration: none;
	}

	.stats-premium-card {
		position: relative;
		min-height: 96px;
		padding: 10px 12px;
		border-radius: 16px;
		overflow: hidden;
		background: linear-gradient(160deg, #ffffff 0%, #f8fafc 55%, #eef2ff 100%);
		border: 1px solid rgba(226, 232, 240, 0.9);
		box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
		transition: transform 0.25s ease, box-shadow 0.25s ease;
	}

	.stats-premium-link:hover .stats-premium-card {
		transform: translateY(-1px);
		box-shadow: 0 14px 24px rgba(15, 23, 42, 0.09);
	}

	.stats-premium-card::before {
		content: "";
		position: absolute;
		inset: 0;
		opacity: 0.9;
		background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.78) 0%, rgba(255, 255, 255, 0) 44%);
	}

	.stats-premium-card::after {
		content: "";
		position: absolute;
		right: -34px;
		bottom: -40px;
		width: 90px;
		height: 90px;
		border-radius: 50%;
		opacity: 0.55;
	}

	.stats-premium-card--primary::after {
		background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(37, 99, 235, 0) 70%);
	}

	.stats-premium-card--info::after {
		background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(14, 165, 233, 0) 70%);
	}

	.stats-premium-card--danger::after {
		background: radial-gradient(circle, rgba(239, 68, 68, 0.22) 0%, rgba(239, 68, 68, 0) 70%);
	}

	.stats-premium-card--warning::after {
		background: radial-gradient(circle, rgba(245, 158, 11, 0.24) 0%, rgba(245, 158, 11, 0) 70%);
	}

	.stats-premium-top,
	.stats-premium-bottom {
		position: relative;
		z-index: 1;
	}

	.stats-premium-top {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 8px;
	}

	.stats-premium-chip {
		display: inline-flex;
		align-items: center;
		padding: 3px 8px;
		border-radius: 999px;
		font-size: 9px;
		font-weight: 800;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		background: rgba(255, 255, 255, 0.76);
		color: #334155;
		backdrop-filter: blur(8px);
	}

	.stats-premium-icon {
		width: 34px;
		height: 34px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 14px;
		color: #fff;
		box-shadow: 0 6px 12px rgba(15, 23, 42, 0.12);
	}

	.stats-premium-card--primary .stats-premium-icon {
		background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
	}

	.stats-premium-card--info .stats-premium-icon {
		background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
	}

	.stats-premium-card--danger .stats-premium-icon {
		background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
	}

	.stats-premium-card--warning .stats-premium-icon {
		background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
	}

	.stats-premium-title {
		margin-top: 8px;
		color: #475569;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: 0.02em;
	}

	.stats-premium-total {
		margin-top: 4px;
		color: #0f172a;
		font-size: 22px;
		font-weight: 800;
		line-height: 0.95;
	}

	.stats-premium-bottom {
		margin-top: 8px;
		padding-top: 8px;
		border-top: 1px solid rgba(203, 213, 225, 0.55);
		display: flex;
		justify-content: space-between;
		align-items: end;
		gap: 8px;
	}

	.stats-premium-month {
		display: flex;
		flex-direction: column;
		gap: 3px;
	}

	.stats-premium-month strong {
		color: #0f172a;
		font-size: 12px;
		font-weight: 800;
	}

	.stats-premium-month span,
	.stats-premium-hint {
		color: #64748b;
		font-size: 9px;
	}

	.stats-premium-hint {
		text-align: right;
		max-width: 54px;
	}

	.stats-agent-panel {
		border-radius: 18px;
		padding: 14px;
		background: linear-gradient(140deg, #fff8ef 0%, #fff 50%, #eef6ff 100%);
		box-shadow: 0 12px 22px rgba(15, 34, 66, 0.06);
		border: 1px solid rgba(226, 232, 240, 0.95);
		overflow: hidden;
		position: relative;
	}

	.stats-agent-panel::before {
		content: "";
		position: absolute;
		top: -110px;
		right: -90px;
		width: 150px;
		height: 150px;
		border-radius: 50%;
		background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(14, 165, 233, 0) 70%);
	}

	.stats-agent-panel::after {
		content: "";
		position: absolute;
		left: -90px;
		bottom: -120px;
		width: 150px;
		height: 150px;
		border-radius: 50%;
		background: radial-gradient(circle, rgba(245, 158, 11, 0.14) 0%, rgba(245, 158, 11, 0) 72%);
	}

	.stats-agent-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 10px;
		margin-bottom: 12px;
		position: relative;
		z-index: 1;
	}

	.stats-agent-kicker {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 4px 8px;
		border-radius: 999px;
		background: rgba(255, 255, 255, 0.88);
		color: #b45309;
		font-size: 9px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		box-shadow: 0 8px 18px rgba(180, 83, 9, 0.10);
	}

	.stats-agent-title {
		margin: 10px 0 6px;
		color: #172554;
		font-size: 18px;
		font-weight: 800;
		line-height: 1.15;
	}

	.stats-agent-subtitle {
		margin: 0;
		color: #64748b;
		font-size: 11px;
		max-width: 680px;
	}

	.stats-agent-summary {
		min-width: 118px;
		padding: 10px 12px;
		border-radius: 12px;
		background: rgba(15, 23, 42, 0.94);
		color: #fff;
		text-align: right;
		box-shadow: 0 10px 18px rgba(15, 23, 42, 0.14);
	}

	.stats-agent-summary strong {
		display: block;
		font-size: 18px;
		line-height: 1;
		margin-bottom: 6px;
	}

	.stats-agent-summary span {
		font-size: 10px;
		color: rgba(255, 255, 255, 0.72);
	}

	.stats-podium-progress,
	.stats-agent-progress {
		height: 7px;
		border-radius: 999px;
		background: rgba(226, 232, 240, 0.9);
		overflow: hidden;
	}

	.stats-podium-progress span,
	.stats-agent-progress span {
		display: block;
		height: 100%;
		border-radius: inherit;
	}

	.stats-agent-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
		gap: 10px;
		position: relative;
		z-index: 1;
	}

	.stats-agent-card {
		position: relative;
		padding: 12px;
		border-radius: 14px;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 250, 252, 0.98) 100%);
		border: 1px solid rgba(226, 232, 240, 0.95);
		box-shadow: 0 8px 14px rgba(15, 23, 42, 0.05);
	}

	.stats-agent-rank {
		position: absolute;
		top: 10px;
		right: 10px;
		min-width: 26px;
		height: 26px;
		padding: 0 8px;
		border-radius: 999px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
		color: #fff;
		font-size: 10px;
		font-weight: 800;
		box-shadow: 0 6px 10px rgba(15, 23, 42, 0.14);
	}

	.stats-agent-name {
		margin: 0 34px 8px 0;
		color: #0f172a;
		font-size: 12px;
		font-weight: 700;
	}

	.stats-agent-total-label,
	.stats-agent-month-label {
		display: block;
		font-size: 9px;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
	}

	.stats-agent-total-label {
		color: #64748b;
	}

	.stats-agent-total-value {
		margin-top: 4px;
		color: #0f172a;
		font-size: 20px;
		font-weight: 800;
		line-height: 1;
	}

	.stats-agent-month {
		margin-top: 10px;
		padding: 8px;
		border-radius: 10px;
		background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
		border: 1px solid rgba(125, 211, 252, 0.45);
	}

	.stats-agent-month-label {
		color: #0369a1;
	}

	.stats-agent-month-value {
		margin-top: 4px;
		color: #0c4a6e;
		font-size: 12px;
		font-weight: 800;
	}

	.stats-agent-progress {
		margin-top: 6px;
	}

	.stats-agent-progress span {
		background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
	}

	.stats-agent-progress-meta {
		margin-top: 5px;
		display: flex;
		justify-content: space-between;
		gap: 12px;
		font-size: 9px;
		color: #64748b;
	}

	.stats-agent-empty {
		position: relative;
		z-index: 1;
		padding: 12px 14px;
		border-radius: 12px;
		background: rgba(255, 255, 255, 0.92);
		color: #64748b;
		border: 1px dashed rgba(148, 163, 184, 0.5);
		font-size: 11px;
	}

	@media (max-width: 1199px) {
		.stats-premium-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767px) {
		.stats-premium-grid {
			grid-template-columns: 1fr;
		}

		.stats-premium-card {
			min-height: auto;
		}

		.stats-premium-bottom {
			align-items: flex-start;
			flex-direction: column;
		}

		.stats-agent-panel {
			padding: 12px;
		}

		.stats-agent-header {
			flex-direction: column;
		}

		.stats-agent-summary {
			width: 100%;
			text-align: left;
		}
	}
</style>

<div class="stats-premium-grid">
	<?php foreach ($dashboardCards as $card): ?>
		<a class="stats-premium-link" href="<?php echo $card['link']; ?>">
			<div class="stats-premium-card stats-premium-card--<?php echo $card['tone']; ?>">
				<div class="stats-premium-top">
					<span class="stats-premium-chip"><?php echo htmlspecialchars($card['accent']); ?></span>
					<div class="stats-premium-icon">
						<i class="<?php echo htmlspecialchars($card['icon']); ?>"></i>
					</div>
				</div>

				<div class="stats-premium-title"><?php echo htmlspecialchars($card['label']); ?></div>
				<div class="stats-premium-total"><?php echo $card['total']; ?></div>

				<div class="stats-premium-bottom">
					<div class="stats-premium-month">
						<strong><?php echo $card['month']; ?></strong>
						<span>sur le mois en cours</span>
					</div>
					<div class="stats-premium-hint">Touchez pour ouvrir le détail</div>
				</div>
			</div>
		</a>
	<?php endforeach; ?>
</div>

<!-- Réalisations par agent -->
<div class="row mt-3">
  <div class="col-12">
		<div class="box box-body stats-agent-panel">
			<div class="stats-agent-header">
				<div>
					<span class="stats-agent-kicker">Classement mensuel</span>
					<h5 class="stats-agent-title">Réalisations par agent</h5>
					<p class="stats-agent-subtitle">Chaque carte affiche le total global et la progression mensuelle calculée depuis date_enreg dans creaevent.</p>
				</div>
				<div class="stats-agent-summary">
					<strong><?php echo count($agentsStats); ?></strong>
					<span>agent<?php echo count($agentsStats) > 1 ? 's' : ''; ?> classé<?php echo count($agentsStats) > 1 ? 's' : ''; ?></span>
				</div>
			</div>

			<?php if (!empty($agentsStats)): ?>
			<div class="stats-agent-grid">
				<?php foreach ($agentsStats as $index => $agent): ?>
					<?php
						$monthValue = (int) $agent['total_mois'];
						$progress = (int) round(($monthValue / $maxMonthly) * 100);
					?>
					<div class="stats-agent-card">
						<div class="stats-agent-rank"><?php echo $index + 1; ?></div>
						<div class="stats-agent-name"><?php echo htmlspecialchars($agent['agent']); ?></div>

						<span class="stats-agent-total-label">Depuis le début</span>
						<div class="stats-agent-total-value"><?php echo (int) $agent['total_realise']; ?></div>

						<div class="stats-agent-month">
							<span class="stats-agent-month-label">Mois en cours</span>
							<div class="stats-agent-month-value"><?php echo $monthValue; ?> réalisation<?php echo $monthValue > 1 ? 's' : ''; ?></div>
							<div class="stats-agent-progress"><span style="width: <?php echo $progress; ?>%;"></span></div>
							<div class="stats-agent-progress-meta">
								<span>Progression du mois</span>
								<span><?php echo $progress; ?>% du leader</span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php else: ?>
				<div class="stats-agent-empty">Aucune réalisation enregistrée pour le mois en cours.</div>
			<?php endif; ?>
    </div>
  </div>
</div>



