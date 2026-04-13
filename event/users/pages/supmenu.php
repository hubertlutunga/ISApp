<?php

$menuId = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
$eventId = isset($_GET['codevent']) ? (int) $_GET['codevent'] : 0;

if ($menuId > 0 && $eventId > 0) {
    MenuCatalogService::delete($pdo, $menuId, $eventId);
}
?>
<script>
    window.location = "index.php?page=addmenu&codevent=<?php echo $eventId; ?>&deleted=1";
</script>