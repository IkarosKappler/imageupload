

var getURIParams = function()
{
    var query_string = {},
	query = window.location.search.substring(1),
	parmsArray = query.split('&');

    if (parmsArray.length <= 0) return query_string;

    for (var i = 0; i < parmsArray.length; i++)
    {
        var pair = parmsArray[i].split('='),
            val = decodeURIComponent(pair[1]);

        if (val != '' && pair[0] != '') query_string[pair[0]] = val;
    }

    return query_string;
};
