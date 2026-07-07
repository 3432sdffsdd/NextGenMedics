param(
    [Parameter(Mandatory = $true)]
    [string]$EnvFile,
    [Parameter(Mandatory = $true)]
    [string]$PhoneNumberId,
    [Parameter(Mandatory = $true)]
    [string]$AccessToken
)

$lines = @()
if (Test-Path $EnvFile) {
    $lines = @(Get-Content $EnvFile)
}

function Set-Or-Add {
    param([string]$Key, [string]$Value)
    $script:lines = @($script:lines | Where-Object { $_ -notmatch "^$([regex]::Escape($Key))=" })
    $script:lines += "$Key=$Value"
}

Set-Or-Add -Key 'WHATSAPP_ENABLED' -Value 'true'
Set-Or-Add -Key 'WHATSAPP_PHONE_NUMBER_ID' -Value $PhoneNumberId.Trim()
Set-Or-Add -Key 'WHATSAPP_ACCESS_TOKEN' -Value $AccessToken.Trim()
Set-Or-Add -Key 'CRON_SECRET' -Value 'ngm-cron-local-dev'

$lines | Set-Content -Path $EnvFile -Encoding UTF8
Write-Host "Updated $EnvFile"
