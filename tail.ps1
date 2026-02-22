# tail.ps1 - Équivalent de tail -f pour Windows
param(
    [string]$Path = "var/log/dev.log",
    [int]$Lines = 50
)

Write-Host "Surveillance du fichier: $Path" -ForegroundColor Green
Write-Host "Appuyez sur Ctrl+C pour arrêter" -ForegroundColor Yellow
Write-Host "=" * 50 -ForegroundColor Cyan

if (Test-Path $Path) {
    # Lire les dernières lignes
    Get-Content -Path $Path -Tail $Lines
    
    # Suivre en temps réel
    Get-Content -Path $Path -Tail 0 -Wait
} else {
    Write-Host "ERREUR: Fichier $Path non trouvé" -ForegroundColor Red
    Write-Host "Vérifiez que vous êtes dans le bon répertoire" -ForegroundColor Yellow
    Write-Host "Répertoire actuel: $(Get-Location)" -ForegroundColor Yellow
}