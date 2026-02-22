<?php
echo "<h1>🔍 Test GD Avancé</h1>";

// 1. Vérifier si l'extension est chargée
if (!extension_loaded('gd')) {
    die("<p style='color:red'>❌ GD n'est pas chargé</p>");
}
echo "<p style='color:green'>✅ GD est chargé</p>";

// 2. Afficher les informations GD
$gd_info = gd_info();
echo "<h3>Informations GD :</h3>";
echo "<pre>";
print_r($gd_info);
echo "</pre>";

// 3. Tester la création d'une image simple
try {
    $im = @imagecreatetruecolor(100, 100);
    if ($im) {
        echo "<p style='color:green'>✅ imagecreatetruecolor() réussie</p>";
        
        // Tester l'écriture de l'image
        $bg = imagecolorallocate($im, 255, 255, 255);
        $textcolor = imagecolorallocate($im, 0, 0, 255);
        imagestring($im, 5, 10, 40, "OK", $textcolor);
        
        // Sauvegarder temporairement
        $temp_file = __DIR__ . '/test-gd-temp.png';
        if (imagepng($im, $temp_file)) {
            echo "<p style='color:green'>✅ imagepng() réussie</p>";
            echo "<img src='/test-gd-temp.png' style='border:1px solid #ddd; padding:5px;'>";
            // Nettoyer
            unlink($temp_file);
        } else {
            echo "<p style='color:red'>❌ Échec de imagepng()</p>";
        }
        
        imagedestroy($im);
    } else {
        echo "<p style='color:red'>❌ Échec de imagecreatetruecolor()</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception : " . $e->getMessage() . "</p>";
}

// 4. Tester la création d'une image avec des fonctions basiques
try {
    $im2 = @imagecreate(100, 100);
    if ($im2) {
        echo "<p style='color:green'>✅ imagecreate() réussie</p>";
        imagedestroy($im2);
    } else {
        echo "<p style='color:red'>❌ Échec de imagecreate()</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception : " . $e->getMessage() . "</p>";
}

// 5. Vérifier les droits d'écriture
$temp_dir = sys_get_temp_dir();
echo "<h3>Informations système :</h3>";
echo "Dossier temporaire : $temp_dir<br>";
echo "Droits d'écriture : " . (is_writable($temp_dir) ? "✅ Oui" : "❌ Non") . "<br>";
echo "Dossier actuel : " . __DIR__ . "<br>";
echo "Droits d'écriture : " . (is_writable(__DIR__) ? "✅ Oui" : "❌ Non") . "<br>";