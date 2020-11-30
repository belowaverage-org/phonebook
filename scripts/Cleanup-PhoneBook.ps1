<#
    .SYNOPSIS
        This script reports and then deletes stale records from the Phone Book.
    .AUTHOR
        Dylan Bickerstaff
    .YEAR
        2020
    .VERSION
        1.0
#>

$PhoneBookAPI = "https://cpit/phonebook/api/"
$SmtpServer = "mail.contoso.com"
$SmtpSubject = "Phone Book - Cleanup"
$SmtpSender = "phonebook@contoso.com"
$SmtpDestination = "dylan.bickerstaff@contoso.com"
$WarnThresholdSeconds = 172800 #2 Day
$DeleteThresholdSeconds = 432000 #5 Days

try {
    Write-Output "Searching for stale numbers..."
    $request = ConvertTo-Json @{
        "SEARCH" = @{
            "modified[<]" = [DateTimeOffset]::Now.ToUnixTimeSeconds() - $WarnThresholdSeconds
        };
        "OUTPUT" = @{
            "ATTRIBUTES" = "number", "description", "modified"
        }
    }
    $response = Invoke-WebRequest -UseBasicParsing -Uri $PhoneBookAPI -UseDefaultCredentials -Method Post -Body @{
        api = "search";
        search = $request
    }

    Write-Output "Formatting data..."
    $responseObject = ConvertFrom-Json $response.Content

    $warningList = [System.Collections.Generic.Dictionary[System.String, System.Object]]::new()
    $deletionList = [System.Collections.Generic.Dictionary[System.String, System.Object]]::new()

    foreach($item in $responseObject.objects.psobject.Properties) {
        if(($null -ne $item.Value.modified) -and ($item.Value.modified -lt ([DateTimeOffset]::Now.ToUnixTimeSeconds() - $DeleteThresholdSeconds))) {
            $deletionList.Add($item.Name, @{})
        } else {
            $warningList.Add($item.Name, @{})
        }
    }

    if(($deletionList.Count -ne 0) -or ($warningList.Count -ne 0)) {
        Write-Output "Sending warning email..."
        Send-MailMessage -Subject $SmtpSubject -SmtpServer $SmtpServer -From $SmtpSender -To $SmtpDestination -BodyAsHtml "
            <h2>$SmtpSubject</h2>
            <hr>
            $(if($warningList.Count -ne 0) { "
                <h3 style=``"color:darkorange;``">The following numbers have become stale and will be deleted soon:</h3>
                <ul>
                    $(
                        foreach($guid in $warningList.Keys) {
                            $number = $responseObject.objects.$guid
                            "<li><b>$($number.number)</b>: $($number.description)</li>"
                        }
                    )
                </ul>
            " }
            if($deletionList.Count -ne 0) { "
                <h3 style=``"color:red;``">The following numbers have been deleted:</h3>
                <ul>
                    $(
                        foreach($guid in $deletionList.Keys) {
                            $number = $responseObject.objects.$guid
                            "<li><b>$($number.number)</b>: $($number.description)</li>"
                        }
                    )
                </ul>
            " })
        "
    }
    if($deletionList.Count -ne 0) {
        Write-Output "Sending delete request..."
        ConvertTo-Json $deletionList
        Invoke-WebRequest -UseBasicParsing -Uri $PhoneBookAPI -UseDefaultCredentials -Method Post -Body @{
            api = "import";
            import = ConvertTo-Json $deletionList
        }
    }
    Write-Output "Done."
    exit 0
} catch {
    Write-Error -Exception $_.Exception
    exit 1
}