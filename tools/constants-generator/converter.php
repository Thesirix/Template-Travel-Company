<?php

function convertCsvToPhpArrayConstants($csvFilePath, $outputFilePath, $className, $nameColumnIndex)
{
    // Vérifier si le fichier CSV existe
    if (!file_exists($csvFilePath)) {
        die("Erreur : Le fichier CSV n'existe pas -> $csvFilePath\n");
    }

    $data = [];
    $existingConstants = []; // Pour suivre les constantes déjà utilisées

    if (($handle = fopen($csvFilePath, "r")) !== false) {
        $headers = fgetcsv($handle, 1000, ","); // Lire les en-têtes
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            // Vérifier que la colonne utilisée pour les noms n'est pas vide
            if (!empty($row[$nameColumnIndex])) {
                $data[] = $row;
            }
        }
        fclose($handle);
    }

    // Générer le contenu de la classe PHP
    $phpContent = "<?php\n\n";
    $phpContent .= "class $className\n{\n";

    foreach ($data as $row) {
        // Utiliser la colonne spécifiée pour nommer la constante
        $constantName = strtoupper($row[$nameColumnIndex]); // Convertir en majuscules
        $constantName = preg_replace('/[^A-Z0-9_]/', '_', $constantName); // Remplacer les caractères non valides
        $constantName = preg_replace('/^[0-9]+/', '', $constantName); // Retirer les nombres au début

        // Vérifier que le nom de la constante est valide
        if (!empty($constantName)) {
            // Ajouter un suffixe si le nom est déjà utilisé
            $originalName = $constantName;
            $suffix = 1;
            while (isset($existingConstants[$constantName])) {
                $constantName = $originalName . '_' . $suffix;
                $suffix++;
            }
            $existingConstants[$constantName] = true; // Marquer ce nom comme utilisé

            // Ajouter la constante au fichier PHP
            $phpContent .= "    public const $constantName = [";
            $phpContent .= implode(', ', array_map(fn($value) => var_export($value, true), $row));
            $phpContent .= "];\n\n";
        }
    }

    $phpContent .= "}\n";

    // Écrire le contenu dans le fichier PHP
    if (file_put_contents($outputFilePath, $phpContent)) {
        echo "Le fichier constants a été généré avec succès : $outputFilePath\n";
    } else {
        echo "Erreur : Impossible d'écrire le fichier $outputFilePath\n";
    }
}

// Jeux de données publics à placer dans data/ : airports.dat et airlines.dat (OpenFlights),
// World_Port_Index.csv (National Geospatial-Intelligence Agency). Les classes sont écrites dans app/Constants.
// Usage : php tools/constants-generator/converter.php
$data = __DIR__ . '/data';
$constants = dirname(__DIR__, 2) . '/app/Constants';
$filesToConvert = [
    ["csv" => "$data/airports.dat", "output" => "$constants/AirportsConstants.php", "class" => "AirportsConstants", "nameColumnIndex" => 1],
    ["csv" => "$data/airlines.dat", "output" => "$constants/AirlinesConstants.php", "class" => "AirlinesConstants", "nameColumnIndex" => 1],
    ["csv" => "$data/World_Port_Index.csv", "output" => "$constants/WorldPortIndexConstants.php", "class" => "WorldPortIndexConstants", "nameColumnIndex" => 5],
];

foreach ($filesToConvert as $file) {
    echo "Conversion de : {$file['csv']}...\n";
    convertCsvToPhpArrayConstants($file['csv'], $file['output'], $file['class'], $file['nameColumnIndex']);
}
