$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$docs = Join-Path $root 'docs'
New-Item -ItemType Directory -Force -Path $docs | Out-Null

function Escape-PdfText {
    param([string] $Text)
    return $Text.Replace('\', '\\').Replace('(', '\(').Replace(')', '\)')
}

function Add-PdfText {
    param(
        [System.Collections.Generic.List[string]] $Commands,
        [double] $X,
        [double] $Y,
        [int] $Size,
        [string] $Text,
        [string] $Color = '0.12 0.15 0.20 rg'
    )

    $safe = Escape-PdfText $Text
    $Commands.Add($Color)
    $Commands.Add("BT /F1 $Size Tf $X $Y Td ($safe) Tj ET")
}

function Add-Entity {
    param(
        [System.Collections.Generic.List[string]] $Commands,
        [double] $X,
        [double] $Y,
        [double] $W,
        [string] $Title,
        [string[]] $Fields
    )

    $lineHeight = 15
    $headerHeight = 24
    $h = $headerHeight + 12 + ($Fields.Count * $lineHeight)
    $Commands.Add('0.05 0.35 0.72 RG')
    $Commands.Add('0.05 0.35 0.72 rg')
    $Commands.Add("$X $($Y + $h - $headerHeight) $W $headerHeight re f")
    $Commands.Add('0.72 w')
    $Commands.Add('0.78 0.83 0.90 RG')
    $Commands.Add("$X $Y $W $h re S")
    Add-PdfText $Commands ($X + 10) ($Y + $h - 17) 11 $Title '1 1 1 rg'

    $textY = $Y + $h - $headerHeight - 16
    foreach ($field in $Fields) {
        Add-PdfText $Commands ($X + 10) $textY 9 $field
        $textY -= $lineHeight
    }

    return @{
        Left = $X
        Right = $X + $W
        Top = $Y + $h
        Bottom = $Y
        MidX = $X + ($W / 2)
        MidY = $Y + ($h / 2)
    }
}

function Add-Line {
    param(
        [System.Collections.Generic.List[string]] $Commands,
        [double] $X1,
        [double] $Y1,
        [double] $X2,
        [double] $Y2,
        [string] $Label
    )

    $Commands.Add('0.26 0.31 0.39 RG')
    $Commands.Add('1.1 w')
    $Commands.Add("$X1 $Y1 m $X2 $Y2 l S")
    Add-PdfText $Commands (($X1 + $X2) / 2 + 4) (($Y1 + $Y2) / 2 + 4) 8 $Label '0.05 0.35 0.72 rg'
}

function Write-SimplePdf {
    param(
        [string] $Path,
        [string] $Content
    )

    $objects = New-Object System.Collections.Generic.List[string]
    $objects.Add('<< /Type /Catalog /Pages 2 0 R >>')
    $objects.Add('<< /Type /Pages /Kids [3 0 R] /Count 1 >>')
    $objects.Add('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>')
    $contentBytes = [System.Text.Encoding]::ASCII.GetBytes($Content)
    $objects.Add("<< /Length $($contentBytes.Length) >>`nstream`n$Content`nendstream")
    $objects.Add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>')

    $encoding = [System.Text.Encoding]::ASCII
    $builder = New-Object System.Text.StringBuilder
    [void] $builder.Append("%PDF-1.4`n")
    $offsets = New-Object System.Collections.Generic.List[int]
    $offsets.Add(0)

    for ($i = 0; $i -lt $objects.Count; $i++) {
        $offsets.Add($encoding.GetByteCount($builder.ToString()))
        [void] $builder.Append("$($i + 1) 0 obj`n$($objects[$i])`nendobj`n")
    }

    $xrefStart = $encoding.GetByteCount($builder.ToString())
    [void] $builder.Append("xref`n0 $($objects.Count + 1)`n")
    [void] $builder.Append("0000000000 65535 f `n")

    for ($i = 1; $i -lt $offsets.Count; $i++) {
        [void] $builder.Append(("{0:D10} 00000 n `n" -f $offsets[$i]))
    }

    [void] $builder.Append("trailer`n<< /Size $($objects.Count + 1) /Root 1 0 R >>`nstartxref`n$xrefStart`n%%EOF")
    [System.IO.File]::WriteAllBytes($Path, $encoding.GetBytes($builder.ToString()))
}

$commands = New-Object System.Collections.Generic.List[string]
$commands.Add('1 1 1 rg 0 0 842 595 re f')
Add-PdfText $commands 36 560 20 'Construcoes Rorato - Diagrama Entidade Relacionamento' '0.05 0.16 0.32 rg'
Add-PdfText $commands 36 540 10 'Modelo de dados para catalogo, clientes, orcamentos e mensagens de contato.' '0.36 0.42 0.50 rg'

$cat = Add-Entity $commands 40 365 205 'CATEGORIAS' @('PK id_categoria', 'nome', 'slug', 'descricao')
$prod = Add-Entity $commands 315 340 220 'PRODUTOS' @('PK id_produto', 'FK id_categoria', 'nome', 'descricao', 'unidade', 'preco_base', 'destaque', 'ativo')
$cli = Add-Entity $commands 40 120 205 'CLIENTES' @('PK id_cliente', 'nome', 'telefone', 'email', 'cidade', 'criado_em')
$orc = Add-Entity $commands 315 125 220 'ORCAMENTOS' @('PK id_orcamento', 'FK id_cliente', 'status', 'observacoes', 'criado_em')
$itens = Add-Entity $commands 600 130 210 'ITENS_ORCAMENTO' @('PK id_item', 'FK id_orcamento', 'FK id_produto', 'quantidade', 'unidade', 'ambiente')
$msg = Add-Entity $commands 600 365 210 'MENSAGENS_CONTATO' @('PK id_mensagem', 'FK id_cliente', 'assunto', 'mensagem', 'enviado_em')

Add-Line $commands $cat.Right $cat.MidY $prod.Left $prod.MidY '1:N'
Add-Line $commands $cli.Right $cli.MidY $orc.Left $orc.MidY '1:N'
Add-Line $commands $orc.Right $orc.MidY $itens.Left $itens.MidY '1:N'
Add-Line $commands $prod.Right ($prod.Bottom + 34) $itens.Left ($itens.Top - 34) '1:N'
Add-Line $commands $cli.Right ($cli.Top - 18) $msg.Left ($msg.Bottom + 34) '1:N'

$commands.Add('0.95 0.97 1 rg 36 28 770 52 re f')
$commands.Add('0.78 0.83 0.90 RG 36 28 770 52 re S')
Add-PdfText $commands 50 61 9 'Legenda: PK = chave primaria, FK = chave estrangeira, 1:N = um registro se relaciona com muitos registros.' '0.12 0.15 0.20 rg'
Add-PdfText $commands 50 43 9 'Banco sugerido: rorato_db. Script SQL em database/schema.sql.' '0.12 0.15 0.20 rg'

$pdfPath = Join-Path $docs 'DER_Rorato.pdf'
Write-SimplePdf $pdfPath ($commands -join "`n")

Add-Type -AssemblyName System.Drawing

$hostsPath = 'C:\Windows\System32\drivers\etc\hosts'
$line = '127.0.0.1    rorato.local    # Construcoes Rorato'
$hostLines = @(
    '# Copyright (c) 1993-2009 Microsoft Corp.',
    '#',
    '# This is a sample HOSTS file used by Microsoft TCP/IP for Windows.',
    '#',
    '# localhost name resolution is handled within DNS itself.',
    '#       127.0.0.1       localhost',
    '#       ::1             localhost',
    '',
    $line
)

if (Test-Path $hostsPath) {
    $actual = Get-Content -LiteralPath $hostsPath -ErrorAction SilentlyContinue
    if ($actual -contains $line) {
        $hostLines = $actual
    }
}

$pngPath = Join-Path $docs 'Evidencia_DNS_Local_hosts.png'
$bmp = New-Object System.Drawing.Bitmap 1400, 900
$graphics = [System.Drawing.Graphics]::FromImage($bmp)
$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$graphics.Clear([System.Drawing.Color]::FromArgb(246, 248, 252))

$titleBar = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(31, 38, 51))
$editorBg = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::White)
$textBrush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(32, 38, 48))
$mutedBrush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(98, 109, 124))
$blueBrush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(7, 91, 184))
$fontTitle = New-Object System.Drawing.Font 'Segoe UI', 24, ([System.Drawing.FontStyle]::Bold)
$fontMeta = New-Object System.Drawing.Font 'Segoe UI', 14, ([System.Drawing.FontStyle]::Regular)
$fontMono = New-Object System.Drawing.Font 'Consolas', 19, ([System.Drawing.FontStyle]::Regular)
$fontMonoBold = New-Object System.Drawing.Font 'Consolas', 19, ([System.Drawing.FontStyle]::Bold)

$graphics.FillRectangle($titleBar, 70, 60, 1260, 64)
$graphics.DrawString('Arquivo hosts configurado - DNS local', $fontTitle, [System.Drawing.Brushes]::White, 96, 76)
$graphics.FillRectangle($editorBg, 70, 124, 1260, 700)
$pen = New-Object System.Drawing.Pen ([System.Drawing.Color]::FromArgb(220, 227, 236)), 2
$graphics.DrawRectangle($pen, 70, 124, 1260, 700)
$graphics.DrawString('C:\Windows\System32\drivers\etc\hosts', $fontMeta, $mutedBrush, 96, 146)

$y = 190
for ($i = 0; $i -lt $hostLines.Count -and $i -lt 22; $i++) {
    $current = [string] $hostLines[$i]
    $brush = if ($current -eq $line) { $blueBrush } else { $textBrush }
    $font = if ($current -eq $line) { $fontMonoBold } else { $fontMono }
    $graphics.DrawString(('{0,2}  {1}' -f ($i + 1), $current), $font, $brush, 96, $y)
    $y += 29
}

$graphics.DrawString('Dominio local do projeto: http://rorato.local', $fontMeta, $mutedBrush, 96, 790)
$bmp.Save($pngPath, [System.Drawing.Imaging.ImageFormat]::Png)

$graphics.Dispose()
$bmp.Dispose()

Write-Host "Gerado: $pdfPath"
Write-Host "Gerado: $pngPath"
