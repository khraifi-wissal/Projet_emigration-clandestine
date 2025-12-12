<?php
// supprimer.php

session_start();
// Vérification simple de l'authentification Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'connexion.php'; 

// Liste blanche des tables et de leurs clés primaires pour la sécurité
$allowed_deletions = [
    'members'           => 'member_id',
    'opportunities'     => 'opp_id',
    'quiz'              => 'quiz_id',
    'quiz_questions'    => 'question_id',
    'storytelling'      => 'story_id', // Cible pour le Storytelling
    'brochures'         => 'brochure_id',
];

$table = isset($_GET['table']) ? $_GET['table'] : null;
$id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : null;
$redirect_page = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($table && $id_to_delete && isset($allowed_deletions[$table])) {
    
    $id_column = $allowed_deletions[$table];
    
    // Assurer que le chemin de redirection est valide
    if (!preg_match('/^gestion_/', $redirect_page) && $redirect_page !== 'index.php') {
        $redirect_page = 'index.php'; 
    }

    // Requête DELETE préparée
    $sql = "DELETE FROM {$table} WHERE {$id_column} = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
        $stmt->close();
        
        // Redirection vers la page source avec un paramètre de succès
        header("Location: {$redirect_page}&success=deleted");
        exit;
    }
}

// Redirection en cas d'erreur ou d'accès invalide
header("Location: {$redirect_page}&error=delete_failed");
exit;