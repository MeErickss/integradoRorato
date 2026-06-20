$ErrorActionPreference = 'Stop'

$hostsPath = 'C:\Windows\System32\drivers\etc\hosts'
$entry = '127.0.0.1    rorato.local    # Construcoes Rorato'

if (-not (Test-Path -LiteralPath $hostsPath)) {
    throw "Arquivo hosts nao encontrado em $hostsPath"
}

$content = Get-Content -LiteralPath $hostsPath -ErrorAction Stop

if ($content -notcontains $entry) {
    Add-Content -LiteralPath $hostsPath -Value "`r`n$entry" -Encoding ASCII
}

Get-Content -LiteralPath $hostsPath | Select-String -SimpleMatch 'rorato.local'
