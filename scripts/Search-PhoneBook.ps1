<#
    .SYNOPSIS
        This module will search the Phone Book and print the results to the screen or a variable.
    .LINK
        https://github.com/belowaverage-org/phonebook/blob/master/scripts/Search-PhoneBook.ps1
#>
$PhoneBookAPI = "https://cpit/phonebook/api/"
class Number {
    [System.Int64]$Number
    [System.String]$Description
    Number($Number, $Description) {
        $this.Number = $Number
        $this.Description = $Description
    }
}
function Query-PhoneBookAPI($Query) {
    $response = Invoke-WebRequest -UseBasicParsing -Uri $PhoneBookAPI -UseDefaultCredentials -Method Post -Body $Query
    return ConvertFrom-Json $response.Content
}
function global:Search-PhoneBook([System.String]$SearchTerms) {
    <#
        .SYNOPSIS
            This cmdlet will search the Phone Book and print the results to the screen or a variable.
        .PARAMETER SearchTerms
            The search string to use.
        .LINK
            https://github.com/belowaverage-org/phonebook/blob/master/scripts/Search-PhoneBook.ps1
    #>
    if($SearchTerms -eq $null) {
        Write-Host "What are you looking for? > " -NoNewline -ForegroundColor Yellow
        $SearchTerms = Read-Host
    }
    $SearchTermList = $SearchTerms.Split(" ")
    $results = Query-PhoneBookAPI @{
        api = "search";
        search = ConvertTo-Json @{
            SEARCH = @{
                TAGS = $SearchTermList;
                ORDER = @{
                    number = "ASC"
                };
                LIMIT = 0,50
            };
            OUTPUT = @{
                ATTRIBUTES = "number","description"
            }
        }
    }
    $list = [System.Collections.Generic.List[System.Object]]::new()
    foreach($object in $results.objects.PSObject.Properties) {
        $list.Add([Number]::new($object.Value.number, $object.Value.description))
    }
    return $list
}
