var pdfVars = {};

function setPdfVars() {
    var x = document.location.search.substring(1).split('&');

    for (var i in x) {
        var z = x[i].split('=', 2);

        if (!pdfVars[z[0]]) {
            pdfVars[z[0]] = decodeURI(z[1]);
        }
    }
}

setPdfVars();