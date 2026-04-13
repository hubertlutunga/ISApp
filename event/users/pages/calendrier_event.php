				   
					<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.css' rel='stylesheet' />
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.2/main.min.js'></script>

<div id="calendar"></div>
<div id="eventList" style="display: none;"></div> <!-- Div pour afficher la liste des événements -->


<?php 


// Récupération de tous les événements
$stmtevcal = $pdo->prepare("SELECT * FROM events");
$stmtevcal->execute();
$dataeventcal = $stmtevcal->fetchAll(PDO::FETCH_ASSOC); // Utilisation de fetchAll pour récupérer tous les événements

$events = []; // Tableau pour stocker les événements

foreach ($dataeventcal as $event) {
    // Récupération des détails de l'événement
    $stmtnvcal = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
    $stmtnvcal->execute([$event['type_event']]);
    $data_evenementcal = $stmtnvcal->fetch();

    // Vérification de l'événement
    if ($data_evenementcal) {
        $title = $data_evenementcal['nom'] !== null ? htmlspecialchars($data_evenementcal['nom']) : 'Événement inconnu';
    } else {
        $title = 'Événement inconnu'; // Valeur par défaut si l'événement n'est pas trouvé
    }

    $start = $event['date_event'] !== null ? htmlspecialchars($event['date_event']) : 'Date inconnue';
    
    // Ajout de l'événement au tableau
    $events[] = [
        'title' => $title,
        'start' => $start,
        'color' => '#ffcc00'
    ];
}

?>			 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            events: <?php echo json_encode($events); ?> // Conversion du tableau d'événements en JSON
        });

        calendar.render();
    });
</script> 
   
   