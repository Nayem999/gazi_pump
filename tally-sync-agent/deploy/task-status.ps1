<#
.SYNOPSIS
    Reports whether the scheduled Tally Sync Agent is actually working.

.DESCRIPTION
    Answers the question the Task Scheduler UI does not: a task can show
    "Ready" having last run three days ago, and a task showing "Running"
    can be an agent that is looping on an error. So this prints the task
    state, its last run and result, and the tail of today's log, which is
    where the agent says what it is really doing.
#>

[CmdletBinding()]
param(
    [string] $TaskName = 'Gazi Pump Tally Sync Agent',
    [int] $LogLines = 15
)

$ErrorActionPreference = 'Stop'

$agentRoot = Split-Path -Parent $PSScriptRoot

$task = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

if (-not $task) {
    Write-Host "Not installed: no scheduled task named '$TaskName'." -ForegroundColor Yellow
    Write-Host "Install it with: powershell -ExecutionPolicy Bypass -File .\install-task.ps1"
} else {
    $info = Get-ScheduledTaskInfo -TaskName $TaskName

    Write-Host "Task     : $TaskName"
    Write-Host "State    : $($task.State)"
    Write-Host "Last run : $($info.LastRunTime)"
    # 267009 is "currently running"; 0 is a clean finish. Anything else is
    # the exit code of a run that stopped, which is the interesting case.
    Write-Host "Last code: $($info.LastTaskResult)$(if ($info.LastTaskResult -eq 267009) { ' (running)' } elseif ($info.LastTaskResult -eq 0) { ' (exited cleanly)' })"
    Write-Host "Next run : $($info.NextRunTime)"
}

# Whether or not the task exists, the log is the record of what the agent
# did-- it may well have been started by hand instead.
$logFile = Join-Path $agentRoot ("logs\agent-{0}.log" -f (Get-Date -Format 'yyyy-MM-dd'))

Write-Host ""

if (Test-Path $logFile) {
    Write-Host "Last $LogLines lines of $logFile"
    Write-Host ('-' * 60)
    Get-Content -Path $logFile -Tail $LogLines
} else {
    Write-Host "No log for today at $logFile-- the agent has not started since midnight." -ForegroundColor Yellow
}
