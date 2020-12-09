<#
    .SYNOPSIS
        This script imports all numbers from a CS1000 PBX system into the Phone Book.
    .AUTHOR
        Dylan Bickerstaff
    .YEAR
        2020
    .VERSION
        1.0
#>

$Global:PhoneBookAPI = "https://cpit/phonebook/api/"
$Global:NortelInterfaceIP = "10.1.2.3"
$Global:NortelUsername = "admin"
$Global:NortelPassword = "password"
$Global:NumberRange = "0 99999"
$Global:CommandTimeout = New-TimeSpan -Seconds 3
function Send-ExpectCommand([string]$Expect, [string]$Command) {
    $output = $Global:stream.Expect($Expect, $Global:CommandTimeout)
    if($null -eq $output) {
        throw "Did not recieve an expected repsonse in a timely manner."
    }
    Write-Output $output
    $Global:stream.WriteLine($Command)
}
try {
    Write-Output "Connecting to Nortel CS1000..."
    Add-Type -Path ".\Renci.SshNet.dll"
    $client = [Renci.SshNet.SshClient]::new($Global:NortelInterfaceIP, $Global:NortelUsername, $Global:NortelPassword)
    $client.Connect()
    $Global:stream = $client.CreateShellStream("Main", 500, 500, 800, 600, 10240)
    Write-Output "Sending commands..."
    Write-Output "-----------------------------"
    Send-ExpectCommand -Expect ">" -Command "ld 95"
    Send-ExpectCommand -Expect "REQ" -Command "prt"
    Send-ExpectCommand -Expect "TYPE" -Command "name"
    Send-ExpectCommand -Expect "CUST" -Command "0"
    Send-ExpectCommand -Expect "PAGE" -Command ""
    Send-ExpectCommand -Expect "DIG" -Command ""
    Send-ExpectCommand -Expect "DN" -Command $Global:NumberRange
    Send-ExpectCommand -Expect "SHRT" -Command "yes"
    Write-Output "-----------------------------"
    Write-Output "Receiving DNs..."
    $numbers = $Global:stream.Expect("`nDN")
    Write-Output "Disconnecting..."
    $client.Disconnect()
    Write-Output "Formatting data..."
    $selection = Select-String -InputObject $numbers -AllMatches -Pattern "( {3,4}[0-9]+ {5})(.*)"
    class PBNumber {
        [long]$number
        [string]$description
        [string]$type
        [string]$importsource
        PBNumber($number, $description, $type) {
            $this.number = $number
            $this.description = $description
            $this.type = $type
            $this.importsource = "Import-NortelCS1000"
        }
    }
    $pbNumbers = [System.Collections.Generic.List[PBNumber]]::new()
    foreach($match in $selection.Matches) {
        $type = $null
        $description = $match.Groups[2].Value.TrimEnd("`r")
        if($description -eq " ") {
            continue
        }
        if($description.ToLower().Contains("fax")) {
            $type = "Fax"
        }
        $pbNumbers.Add(
            [PBNumber]::new(
                [long]::Parse($match.Groups[1].Value),
                $description,
                $type
            )
        )
    }
    Write-Output "Sending data to Phone Book..."
    Invoke-WebRequest -Uri $Global:PhoneBookAPI -UseBasicParsing -UseDefaultCredentials -Method Post -Body @{
        api = "import"
        import = ConvertTo-Json $pbNumbers
    }
    Write-Output "Done, success!"
    exit 0
} catch {
    Write-Error -ErrorRecord $_
    Write-Output $_.ScriptStackTrace
    exit 1
}