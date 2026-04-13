<style>

.countdown-container {
    display: flex;
    justify-content: center;
    align-items: center; 
    margin-bottom: 20px; 
}

.countdown-item {
    text-align: center;
    margin: 0 8px;  
}

.countdown-number {
    font-size: 78px; 
    color: #fff;  
    min-height:400px !important;
}

.countdown-label {
    font-size: 14px;
    color: #fff;
    top:-30px !important;
}


@media only screen and (max-width: 769px) {


.countdown-container {
    display: flex;
    justify-content: center;
    align-items: center;   
}

.countdown-number {
    font-size: 38px; 
    color: #fff; 
}

.countdown-label {
    font-size: 13px;
    color: #fff;
    
}

}

</style>					 
					 

<div id="countdown" class="countdown-container">
    <div class="countdown-item" style="border-right:1px solid #aaa;padding-right:15px;">
        <span id="days" class="countdown-number">0</span> <br>
        <span class="countdown-label">JOURS</span>
    </div>
    <div class="countdown-item" style="border-right:1px solid #aaa;padding-right:15px;">
        <span id="hours" class="countdown-number">0</span> <br>
        <span class="countdown-label">HEURES</span>
    </div>
    <div class="countdown-item" style="border-right:1px solid #aaa;padding-right:15px;">
        <span id="minutes" class="countdown-number">0</span> <br>
        <span class="countdown-label">MIN.</span>
    </div>
    <div class="countdown-item">
        <span id="seconds" class="countdown-number">0</span> <br>
        <span class="countdown-label">SEC.</span>
    </div>
</div>




<script>
    const eventDate = new Date("<?php echo $dataevent['date_event']; ?>").getTime();

    const countdown = setInterval(function() {
        const now = new Date().getTime();
        const distance = eventDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("days").innerHTML = days;
        document.getElementById("hours").innerHTML = hours;
        document.getElementById("minutes").innerHTML = minutes;
        document.getElementById("seconds").innerHTML = seconds;

        if (distance < 0) {
            clearInterval(countdown);
            document.getElementById("countdown").innerHTML = "Événement passé";
        }
    }, 1000);
</script>