param(
    [Parameter(Mandatory = $true)]
    [string]$EnvFile,
    [Parameter(Mandatory = $true)]
    [string]$ApiKey
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

$key = $ApiKey.Trim()
Set-Or-Add -Key 'AI_ENABLED' -Value 'true'
Set-Or-Add -Key 'AI_PROVIDER' -Value 'groq'
Set-Or-Add -Key 'AI_API_KEY' -Value $key
Set-Or-Add -Key 'AI_BASE_URL' -Value 'https://api.groq.com/openai/v1'
Set-Or-Add -Key 'AI_MODEL' -Value 'llama-3.3-70b-versatile'
Set-Or-Add -Key 'AI_TEMPERATURE' -Value '0.4'
Set-Or-Add -Key 'AI_MAX_TOKENS' -Value '4000'
Set-Or-Add -Key 'AI_TIMEOUT' -Value '180'
Set-Or-Add -Key 'AI_MAX_INPUT_CHARS' -Value '6000'

$lines | Set-Content -Path $EnvFile -Encoding UTF8
Write-Host "Updated $EnvFile with Groq settings."
