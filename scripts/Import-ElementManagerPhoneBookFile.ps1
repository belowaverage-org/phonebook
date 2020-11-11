<#
    .SYNOPSIS
        Avaya Element Manager Phone Book CSV export to Phone Book API import script.
    .AUTHOR
        Dylan Bickerstaff
    .YEAR
        2020
    .VERSION
        1.0
#>
$PhoneBookAPI = "https://cpit/phonebook/api/"
class PhoneBookEntry {
    [System.Int64]$number = 0
    [System.String]$description = ""
    [System.String]$type = "Location"
    [System.String]$importsource = "Import-ElementManagerPhoneBookFile"
}
$PhoneBookEntries = [System.Collections.Generic.List[PhoneBookEntry]]::new()
Write-Host "Drag and drop the Element Manager Phone Book CSV here:> " -ForegroundColor Yellow -NoNewline
$CSVPath = $(Read-Host).Replace("`"", "")
Write-Host "Importing CSV into memory..."
$CSVData = ConvertFrom-Csv $(Get-Content -Path $CSVPath)
Write-Host "Formatting data..."
foreach($CSVEntry in $CSVData) {
    $NewEntry = [PhoneBookEntry]::new()
    $NewEntry.number = $CSVEntry.PRIMEDN
    $NewEntry.description = $CSVEntry.CPND_NAME
    $PhoneBookEntries.Add($NewEntry)
}
Write-Host "Generating JSON API request..."
$ImportJSON = $PhoneBookEntries | ConvertTo-Json
Write-Host "Uploading data..."
Invoke-WebRequest -Uri $PhoneBookAPI -UseDefaultCredentials -UseBasicParsing -Method Post -Body @{
    api = "import"
    import = $ImportJSON
}