<?php
// Add body class if needed
$show_sidebar = isset($_SESSION['user_id']);
$body_class = $show_sidebar ? 'has-sidebar' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Skinovation Beauty Clinic</title>
    <link rel="icon" type="image/png" href="<?php echo getPath('assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo getPath('assets/css/style.css'); ?>">
    <?php if (isset($additional_css)) {
        echo $additional_css;
    } ?>
</head>

<body class="<?php echo $body_class; ?>">
    <?php include 'header.php'; ?>
    <?php if ($show_sidebar) {
        include 'templates/sidebar.php';
    } ?>

    <div class="main-content">
        <!-- page content will go here -->
