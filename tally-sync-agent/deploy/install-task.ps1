<#
.SYNOPSIS
    Registers the Tally Sync Agent as a Windows scheduled task that starts
    automatically at logon.

.DESCRIPTION
    Builds the task definition in code rather than importing a fixed XML
    file, so the account name and install path come from the machine it
    runs on instead of being baked in.

    **The task executes node.exe directly.** An earlier version wrapped it
    in powershell -> cmd -> node so the wrapper could set the working
    directory and capture logs. That was a mistake: Task Scheduler's Stop
    killed only the wrapper, and the node grandchild survived orphaned and
    carried on claiming jobs. A "restart" then left TWO agents racing for
    the same queue, with the orphan's output going nowhere. The scheduler
    now sets the working directory itself and the agent writes its own log
    (src/logger.js), so the process the scheduler starts and stops is the
    agent.

    **Why at logon rather than at boot.** TallyPrime's HTTP gateway is its
    own UI thread: it only answers while Tally is open on someone's
    desktop. A boot-time task would start an agent with nothing to talk to.
    Logon matches the only condition under which the agent can work, and it
    needs no administrator rights to register.

    Consequences worth knowing: nothing syncs while the machine sits at the
    login screen, and nothing syncs while Tally is closed. That is Tally's
    constraint, not this task's.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\install-task.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\install-task.ps1 -StartNow:$false
#>

[CmdletBinding()]
param(
    [string] $TaskName = 'Gazi Pump Tally Sync Agent',

    # Delay after logon before starting. Apache, MySQL and Tally are all
    # still coming up then; the agent tolerates them being down (it logs
    # and retries) but a short wait keeps the log readable.
    [string] $DelayAfterLogon = 'PT1M',

    # Start it now as well as at future logons, so the setup is verifiable
    # immediately rather than at the next sign-in.
    [switch] $StartNow = $true
)

$ErrorActionPreference = 'Stop'

$agentRoot = Split-Path -Parent $PSScriptRoot

if (-not (Test-Path (Join-Path $agentRoot 'src\index.js'))) {
    throw "Cannot find src\index.js under $agentRoot - run this script from the agent's deploy folder."
}

if (-not (Test-Path (Join-Path $agentRoot '.env'))) {
    throw "No .env in $agentRoot. Copy .env.example to .env and fill it in first, or the agent starts with no configuration."
}

# Resolved to an absolute path here rather than left to PATH: a scheduled
# task can run with a slimmer environment than an interactive shell.
$node = (Get-Command node.exe -ErrorAction SilentlyContinue).Source

if (-not $node) {
    $node = Join-Path $env:ProgramFiles 'nodejs\node.exe'
}

if (-not (Test-Path $node)) {
    throw "node.exe not found on PATH or at $node. Install Node.js first."
}

$logDir = Join-Path $agentRoot 'logs'
$account = "$env:USERDOMAIN\$env:USERNAME"

Write-Host "Registering '$TaskName'"
Write-Host "  node    : $node"
Write-Host "  agent   : $agentRoot"
Write-Host "  logs    : $logDir"
Write-Host "  account : $account"

# Task Scheduler cannot add environment variables to an action, so LOG_DIR
# goes into the agent's own .env. Without it the agent logs to console
# only, and an unattended task's console output is discarded.
$envFile = Join-Path $agentRoot '.env'

if ((Get-Content -Path $envFile -Raw) -notmatch '(?m)^\s*LOG_DIR\s*=') {
    Add-Content -Path $envFile -Value ''
    Add-Content -Path $envFile -Value '# Added by deploy/install-task.ps1: where the unattended agent writes its log.'
    Add-Content -Path $envFile -Value "LOG_DIR=$logDir"
    Write-Host "  added LOG_DIR to .env"
}

$action = New-ScheduledTaskAction `
    -Execute $node `
    -Argument 'src\index.js' `
    -WorkingDirectory $agentRoot

$trigger = New-ScheduledTaskTrigger -AtLogOn -User $account
$trigger.Delay = $DelayAfterLogon

$settings = New-ScheduledTaskSettingsSet `
    -MultipleInstances IgnoreNew `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RestartInterval (New-TimeSpan -Minutes 5) `
    -RestartCount 3

# Unlimited runtime. Without this the task is killed after Windows'
# three-day default, which is the sort of failure nobody connects to a sync
# that stopped last Tuesday.
$settings.ExecutionTimeLimit = 'PT0S'

# Interactive/Limited: runs as the signed-in user with no elevation, which
# is all the agent needs - it talks to localhost and writes to its own
# folder. Registering it needs no administrator either.
$principal = New-ScheduledTaskPrincipal `
    -UserId $account `
    -LogonType Interactive `
    -RunLevel Limited

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description 'Syncs Gazi Pump SFA with the local TallyPrime company. Starts at logon; only useful while Tally is open, because Tally serves its XML gateway from its own UI thread.' `
    -Force | Out-Null

Write-Host "Registered." -ForegroundColor Green

if ($StartNow) {
    Start-ScheduledTask -TaskName $TaskName
    Write-Host "Started now as well as at future logons." -ForegroundColor Green
}

Get-ScheduledTask -TaskName $TaskName | Select-Object TaskName, State | Format-Table -AutoSize

Write-Host "Check it with:  powershell -ExecutionPolicy Bypass -File .\task-status.ps1"
Write-Host "Or on the SFA Tally Integration dashboard - the connection reads Connected once a heartbeat lands."
