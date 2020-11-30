<#
    .SYNOPSIS
        Demo Data import script. Imports whatever data is in the Demo-Data.json file.
    .AUTHOR
        Dylan Bickerstaff
    .YEAR
        2020
    .VERSION
        1.0
#>
$PhoneBookAPI = "https://cpit/phonebook/api/"
Invoke-WebRequest -Uri $PhoneBookAPI -UseDefaultCredentials -UseBasicParsing -Method Post -Body @{
    api = "import"
    import = $(Get-Content -Path ".\Demo-Data.json")
}