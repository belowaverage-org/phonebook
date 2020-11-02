$PhoneBookAPI = "http://ba-lx1.ad.belowaverage.org:32780/api/"
function global:Search-PhoneBook($SearchTerms) {
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
        $list.Add($object.Value)
    }
    $list | Format-Table
}
function Query-PhoneBookAPI($Query) {
    $response = Invoke-WebRequest -UseBasicParsing -Uri $PhoneBookAPI -UseDefaultCredentials -Method Post -Body $Query
    return ConvertFrom-Json $response.Content
}
Search-PhoneBook
