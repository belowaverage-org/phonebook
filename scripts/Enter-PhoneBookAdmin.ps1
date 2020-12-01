<#
    .SYNOPSIS
        This contains cmd-lets to administrate the Phone Book database.
    .AUTHOR
        Dylan Bickerstaff
    .YEAR
        2020
    .VERSION
        1.0
    .LINK
        https://github.com/belowaverage-org/phonebook/blob/master/scripts/Enter-PhoneBookAdmin.ps1
#>
$Global:PhoneBookAPI = "https://cpit/phonebook/api/" 
class PBNumber {
    [System.Int64]$Number
    [System.String]$Description
    [System.String]$FirstName
    [System.String]$LastName
    [System.DateTimeOffset]$Created
    [System.DateTimeOffset]$Modified
    [System.String]$Type
    [System.String]$Username
    [System.String]$Email
    [System.Int64]$EmployeeID
    [System.String]$ImportSource
    [System.String[]]$Tags
    [System.String]$ObjectID
    PBNumber() {}
}
class PBTranslation {
    [System.String]$From
    [System.String]$To
    PBTranslation($From, $To) {
        $this.From = $From
        $this.To = $To
    }
}
function Global:Invoke-PhoneBookAPI($Query) {
    <#
        .SYNOPSIS
            Invokes the Phone Book API and returns the result as a PSObject.
    #>
    $response = Invoke-WebRequest -UseBasicParsing -Uri $PhoneBookAPI -UseDefaultCredentials -Method Post -Body $Query
    return ConvertFrom-Json $response.Content
}
function Global:Search-PBNumber([Parameter(Mandatory)][System.String]$Query) {
    <#
        .SYNOPSIS
            This command searches the Phone Book database and returns a list of numbers.
    #>
    $SearchTermList = $Query.Split(" ")
    $results = Invoke-PhoneBookAPI @{
        api = "search";
        search = ConvertTo-Json @{
            SEARCH = @{
                TAGS = $SearchTermList;
                ORDER = @{
                    number = "ASC"
                }
            }
            OUTPUT = @{
                OPTIONS = @("showObjectTags")
                ATTRIBUTES = "created","modified","number","description","type","email","employeeid","firstname","lastname","username","importsource"
            }
        }
    }
    $list = [System.Collections.Generic.List[PBNumber]]::new()
    foreach($object in $results.objects.PSObject.Properties) {
        $number = $object.Value
        $pbNumber = [PBNumber]::new()
        $pbNumber.Number = $number.number
        $pbNumber.Description = $number.description
        $pbNumber.FirstName = $number.firstname
        $pbNumber.LastName = $number.lastname
        $pbNumber.Created = [System.DateTimeOffset]::FromUnixTimeSeconds($number.created).ToLocalTime()
        $pbNumber.Modified = [System.DateTimeOffset]::FromUnixTimeSeconds($number.modified).ToLocalTime()
        $pbNumber.Type = $number.type
        $pbNumber.Username = $number.username
        $pbNumber.Email = $number.email
        $pbNumber.EmployeeID = $number.employeeid
        $pbNumber.ImportSource = $number.importsource
        $pbNumber.Tags = $number.tags
        $pbNumber.ObjectID = $object.Name
        $list.Add($pbNumber)
    }
    return $list
}
function Global:Get-PBNumber([Parameter(Mandatory)][string]$ObjectID) {
    <#
        .SYNOPSIS
            This command retrieves a PBNumber object from the database by ObjectID.
    #>
    $results = Invoke-PhoneBookAPI @{
        api = "search";
        search = ConvertTo-Json @{
            SEARCH = @{
                objectid = $ObjectID
            }
            OUTPUT = @{
                OPTIONS = @("showObjectTags")
                ATTRIBUTES = "created","modified","number","description","type","email","employeeid","firstname","lastname","username","importsource"
            }
        }
    }
    if($null -eq $results.objects.psobject.Properties.Value) {
        Write-Output "Could not find this object ID."
        return $null
    }
    $number = $results.objects.psobject.Properties.Value
    $pbNumber = [PBNumber]::new()
    $pbNumber.Number = $number.number
    $pbNumber.Description = $number.description
    $pbNumber.FirstName = $number.firstname
    $pbNumber.LastName = $number.lastname
    $pbNumber.Created = [System.DateTimeOffset]::FromUnixTimeSeconds($number.created).ToLocalTime()
    $pbNumber.Modified = [System.DateTimeOffset]::FromUnixTimeSeconds($number.modified).ToLocalTime()
    $pbNumber.Type = $number.type
    $pbNumber.Username = $number.username
    $pbNumber.Email = $number.email
    $pbNumber.EmployeeID = $number.employeeid
    $pbNumber.ImportSource = $number.importsource
    $pbNumber.Tags = $number.tags
    $pbNumber.ObjectID = $results.objects.psobject.Properties.Name
    return $pbNumber
}
function Global:New-PBNumber([Parameter(Mandatory)][long]$Number, [Parameter(Mandatory)][string]$Description, [string]$FirstName, [string]$LastName, [string]$Type, [string]$Username, [string]$Email, [long]$EmployeeID, [string]$ImportSource = "Enter-PhoneBookAdmin", [string[]]$Tags) {
    <#
        .SYNOPSIS
            This command creates a new PBNumber object. To commit this new object to the database, pipe this command into Set-PBNumber.
    #>
    $pbNumber = [PBNumber]::new()
    $pbNumber.Number = $Number
    $pbNumber.Description = $Description
    $pbNumber.FirstName = $FirstName
    $pbNumber.LastName = $LastName
    $pbNumber.Type = $Type
    $pbNumber.Username = $Username
    $pbNumber.Email = $Email
    $pbNumber.EmployeeID = $EmployeeID
    $pbNumber.ImportSource = $ImportSource
    $pbNumber.Tags = $Tags
    return $pbNumber
}
function Global:Set-PBNumber([Parameter(ValueFromPipeline)][PBNumber]$InputObject) {
    <#
        .SYNOPSIS
            This command commits an existing or new PBNumber object to the Phone Book database.
    #>
    if($null -eq $InputObject.ObjectID) {
        Invoke-PhoneBookAPI @{
            api = "import";
            import = ConvertTo-Json @(
                @{
                    number = $InputObject.Number
                    description = $InputObject.Description
                    firstname = $InputObject.FirstName
                    lastname = $InputObject.LastName
                    type = $InputObject.Type
                    username = $InputObject.Username
                    email = $InputObject.Email
                    employeeid = $InputObject.EmployeeID
                    importsource = $InputObject.ImportSource
                    tags = $InputObject.Tags
                }
            )
        }
    } else {
        Invoke-PhoneBookAPI @{
            api = "import";
            import = ConvertTo-Json @{
                $InputObject.ObjectID = @{
                    number = $InputObject.Number
                    description = $InputObject.Description
                    firstname = $InputObject.FirstName
                    lastname = $InputObject.LastName
                    type = $InputObject.Type
                    username = $InputObject.Username
                    email = $InputObject.Email
                    employeeid = $InputObject.EmployeeID
                    importsource = $InputObject.ImportSource
                    tags = $InputObject.Tags
                }
            }
        }
    }
}
function Global:Remove-PBNumber([Parameter(ValueFromPipeline)][PBNumber]$InputObject) {
    <#
        .SYNOPSIS
            This command removes a PBNumber object from the Phone Book database.
    #>
    Invoke-PhoneBookAPI @{
        api = "import";
        import = ConvertTo-Json @{
            $InputObject.ObjectID = @{}
        }
    }
}
function Global:Get-PBTranslation([System.String]$From = "") {
    <#
        .SYNOPSIS
            This command gets a list of translations from the Phone Book database. Translations are used by the Phone Book to remove or replace abbreviations when tagging / indexing.
            For example, if the description of a number is "VP Marketing" and there is a translation of (From: "vp", To: "vice president") then the tags that will be set on the final object
            will be "vice", "president", "marketing". If the translation's "To" property is left blank, then "vp" will not be tagged at all.
    #>
    $return = [System.Collections.Generic.List[PBTranslation]]::new()
    $results = Invoke-PhoneBookAPI -Query @{
        api = "translations"
        translations = "list"
    }
    foreach($result in $results) {
        if($From -ne "") {
            if($From -eq $result.from) {
                $return.Add([PBTranslation]::new($result.from, $result.to))
                break
            }
            continue
        }
        $return.Add([PBTranslation]::new($result.from, $result.to))
    }
    return $return
}
function Global:Set-PBTranslation([Parameter(Mandatory)][System.String]$From, [System.String]$To = "") {
    <#
        .SYNOPSIS
            This command adds or sets a translation in the Phone Book database. To learn more about translations, type: Get-Help Get-PBTranslation.
    #>
    Invoke-PhoneBookAPI -Query @{
        api = "translations"
        translations = "set"
        from = $From
        to = $To
    }
}
function Global:Remove-PBTranslation([Parameter(Mandatory)][System.String]$From) {
    <#
        .SYNOPSIS
            This command removes a translation from the Phone Book database. To learn more about translations, type: Get-Help Get-PBTranslation.
    #>
    Invoke-PhoneBookAPI -Query @{
        api = "translations"
        translations = "remove"
        from = $From
    }
}
function Global:Invoke-PBRebuild() {
    <#
        .SYNOPSIS
            This command tells the Phone Book to re-scan all numbers in the database and re-apply the translation rules.
    #>
    Write-Output "Sending rebuild command..."
    Invoke-PhoneBookAPI -Query @{
        api = "misc"
        misc = "rebuild"
    }
    Write-Output "Waiting for rebuild to finish..."
    Invoke-PhoneBookAPI -Query @{
        api = "export"
        export = "tags"
    } | Out-Null
    Write-Output "Done."
}
function Global:Get-PBStatistics() {
    <#
        .SYNOPSIS
            This command retrieves the statistics from the Phone Book.
    #>
    return Invoke-PhoneBookAPI -Query @{
        api = "stats"
        stats = "count"
    }
}
function Global:Get-PBLog() {
    <#
        .SYNOPSIS
            This command retrieves the statistics logs from the Phone Book.
    #>
    return Invoke-PhoneBookAPI -Query @{
        api = "stats"
        stats = "results"
    } | Sort-Object -Property "timestamp"
}
function Global:Enter-PhoneBookAdmin() {
    <#
        .SYNOPSIS
            This command enters the Phone Book administration shell.
    #>
    while($true) {
        Clear-Host
        $option = Read-Host -Prompt "    Phone Book Admin
-------------------------
Phone Numbers
-------------------------
0) Search the Phone Book.
1) Edit a Phone Number.
2) Add a Phone Number.
3) Remove a Phone Number.
-------------------------
Translations
-------------------------
5) View all translations.
6) Add / set a translation.
7) Remove a translation.
-------------------------
Statistics
-------------------------
8) View all query logs.
9) View statistics.
-------------------------
Other
-------------------------
10) Rebuild database.
11) View all tags.
-------------------------
"
        if($option -eq 0) {
            Write-Output ""
            Global:Search-PBNumber -Query $(Read-Host -Prompt "Enter a search query.") | Out-GridView -Title "Search Results."
        }
        if(($option -eq 1) -or ($option -eq 2)) {
            Write-Output ""
            $number = $null
            if($option -eq 2) { $number = New-PBNumber -Number $(Read-Host -Prompt "Enter a Phone Number.") -Description $(Read-Host -Prompt "Enter a description.") }
            if($option -eq 1) { $number = Global:Search-PBNumber -Query $(Read-Host -Prompt "Enter a search query.") }
            if($number.Count -eq 1) {
                while($true) {
                    try {
                        Write-Output ""
                        Write-Output $number
                        Write-Output "`nTags`n--------"
                        Write-Output $number.Tags
                        Write-Output ""
                        $param = Read-Host -Prompt "What parameter would you like to change?"
                        $value = Read-Host -Prompt "What is the new value?"
                        $number.$param = $value
                        Write-Output "`nParameter changed."
                    } catch {
                        Write-Output "`nInvalid parameter."
                    }
                    if($(Read-Host -Prompt "1) Again`n2) Done`n") -eq 2) { break }
                }
                Write-Output ""
                $number | Set-PBNumber
                Write-Output "`nPhone Number data committed."
            } else {
                Write-Output "`nQuery was not unique enough. More than one result was returned."
            }
            Write-Host -ForegroundColor Yellow "Press enter to continue..."
            Read-Host
        }
        if($option -eq 3) {
            Write-Output ""
            $number = Global:Search-PBNumber -Query $(Read-Host -Prompt "Enter a search query.")
            if($number.Count -eq 1) {
                Global:Remove-PBNumber $number
                Write-Output "`nPhone Number removed."
            } else {
                Write-Output "`nQuery was not unique enough. More than one result was returned."
            }
            Write-Host -ForegroundColor Yellow "Press enter to continue..."
            Read-Host
        }
        if($option -eq 5) {
            Write-Output ""
            Global:Get-PBTranslation | Out-GridView -Title "All Translations."
        }
        if($option -eq 6) {
            Write-Output ""
            Global:Set-PBTranslation -From $(Read-Host -Prompt "From?") -To $(Read-Host -Prompt "To?")
        }
        if($option -eq 7) {
            Write-Output ""
            Global:Remove-PBTranslation -From $(Read-Host -Prompt "Translation to remove?")
        }
        if($option -eq 8) {
            Write-Output ""
            Global:Get-PBLog | Out-GridView -Title "All Logs."
        }
        if($option -eq 9) {
            Write-Output ""
            Global:Get-PBStatistics
            Write-Host -ForegroundColor Yellow "Press enter to continue..."
            Read-Host
        }
        if($option -eq 10) {
            Write-Output ""
            Global:Invoke-PBRebuild
            Write-Host -ForegroundColor Yellow "Press enter to continue..."
            Read-Host
        }
        if($option -eq 11) {
            Write-Output ""
            $(Global:Invoke-PhoneBookAPI @{
                api = "export"
                export = "tags"
            }).SyncRoot | Out-GridView -Title "All Tags."
        }
    }
}