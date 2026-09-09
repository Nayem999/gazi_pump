<#
.SYNOPSIS
    Stops and removes the Tally Sync Agent scheduled task.

.DESCRIPTION
    Leaves the agent folder, its .env and its logs alone-- this only undoes
    what install-task.ps1 registered. Once removed, nothing syncs until the
    agent is started again by hand or the task reinstalled.
#>

[CmdletBinding()]
param(
    [string] $TaskName = 'Gazi Pump Tally Sync Agent'
)

$ErrorActionPreference = 'Stop'

$task = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

if (-not $task) {
    Write-Host "No task named '$TaskName' is registered-- nothing to remove."

    return
}

if ($task.State -eq 'Running') {
    Stop-ScheduledTask -TaskName $TaskName
    Write-Host "Stopped the running agent."
}

Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false

Write-Host "Removed '$TaskName'. The agent folder, .env and logs are untouched." -ForegroundColor Green
Write-Host "Nothing will sync until the agent runs again (npm start, or reinstall the task)."
