# ============================================================
#  Inicia a aplicação na PORTA 8080 usando o PHP embutido.
#  Document root = public/  (app/ e database/ ficam fora,
#  inacessíveis pelo navegador).
#
#  Acesse depois em:
#    http://localhost:8080
#    http://rorato.local:8080   (com DNS local configurado)
# ============================================================

$ErrorActionPreference = 'Stop'

$php = 'C:\xampp\php\php.exe'
if (-not (Test-Path $php)) {
    $php = (Get-Command php -ErrorAction Stop).Source
}

$raiz   = Split-Path -Parent $PSScriptRoot
$public = Join-Path $raiz 'public'

Write-Host 'Construcoes Rorato' -ForegroundColor Cyan
Write-Host "Servindo $public na porta 8080..." -ForegroundColor Green
Write-Host 'Acesse: http://localhost:8080  ou  http://rorato.local:8080' -ForegroundColor Yellow
Write-Host 'Pressione Ctrl+C para parar.' -ForegroundColor DarkGray

# 0.0.0.0 permite acessar via rorato.local (DNS local) e por outras máquinas da rede.
& $php -S 0.0.0.0:8080 -t $public
