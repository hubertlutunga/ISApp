$(document).ready(function() {
    $('#searchs').keyup(function() {
        var searchs = $(this).val();
        searchs = $.trim(searchs);
        
        if (searchs !== "") {
            $.post('post.php', { searchs: searchs }, function(data) {
                $('#resultat ul').html(data);
            });
        } else {
            // Efface les résultats si le champ de recherche est vide
            $('#resultat ul').html('');
        }
    });
});