/*
JSON data structure of OAKS database response:
	id – (integer) the internal OAKS id for the item
	title – (string) the title of the item
	author – (delimited string) the author(s) of the (multiple authors are separated by the semi-colon character)
	date – (string) the date the item was published in the format
	public_url – (string) a URL to the public display of the item in OAKS
	pdf_url – (string) a URL to the PDF for the item (if it has a PDF in OAKS)
*/



////////////////////////////////////////////////////////////////////////////////
//
// GLOBAL VARIABLES
var currentArchiveAddress = 'https://oaks.kent.edu/api/v1/collections/14349';

// [archiveID, header]
var staranTab = [53, 'STARAN'];
var bitonicTab = [4, 'ASPRO'];
var mppTab = [47, 'MPP'];
var tabList = [staranTab, bitonicTab, mppTab];

////////////////////////////////////////////////////////////////////////////////
//
// FUNCTIONS
////////////////////////////////////////
// function to create table from parsed json
createTable = function(tableID, data, append) {
	// grab table id
	var myTableDiv = document.getElementById(tableID);

	var table, tableBody;
	if (append) {
		tableBody = document.getElementById('results');
	} else {
		table = document.createElement('TABLE');
		table.border = '1';
		tableBody = document.createElement('TBODY');
		tableBody.id = 'results';
		table.appendChild(tableBody);
	}

	// column titles
	var columns = ['#', 'Title', 'Authors', 'Date', 'Link'];

	// corresponding attributes to pull
	var attributes = ['title', 'author', 'date', 'public_url']

	// create columns titles
	var tr = document.createElement('TR');
	tableBody.appendChild(tr);
	for (var j = 0; j < columns.length && !append; ++j) {
		var td = document.createElement('TD');
		td.width = '50';
		td.appendChild(document.createTextNode(columns[j]));
		tr.appendChild(td);
	}

	// sort data by date
	data.rows.sort((a, b) => (a.date > b.date) ? 1 : -1);

	// insert data from json to table
	for (var i = 0; i < data.rows.length; ++i) {
		// create row object
		var tr = document.createElement('TR');
		tableBody.appendChild(tr);
		var row = data.rows[i];

		// insert row num
		var td = document.createElement('TD');
		td.width='75';
		td.appendChild(document.createTextNode(i));
		tr.appendChild(td);

		// insert attributes from object
		for (elem in row) if (attributes.includes(elem)) {
			// create cell
			td = document.createElement('TD');
			td.width = '200';

			// create contents
			var contents = document.createTextNode(row[elem]);

			// create link object and append
			if (elem == 'public_url') {
				var link = document.createElement('a');
				link.href = row[elem];
				contents.textContent = 'URL';

				// append children
				link.appendChild(contents);
				td.appendChild(link);

				// if not a url just append contents
			} else {
				td.appendChild(contents);
			}

			// append to row
			tr.appendChild(td);    
		}
	}

	// add created table
	if (!append) {
		myTableDiv.appendChild(table);      
	}
}

////////////////////////////////////////
// create search function
function search() {
	// get input value
	var val = document.getElementById('searchTextBox').value;
	if (val == '') { return; }

	// clear any past results
	document.getElementById('searchResults').innerHTML = '<h2>Results</h2>';

	// get base address
	baseAddress = currentArchiveAddress.slice(0, 41);

	for (var i = 0; i < tabList.length; ++i) {
		// create GET request
		var searchReq = new XMLHttpRequest();
		searchReq.open('GET', baseAddress + tabList[i][0] + '/' + val, false);
		searchReq.send();

		// convert json string to object
		var data = JSON.parse(searchReq.responseText);

		// check data has been recieved
		if (searchReq.status >= 200 && searchReq.status < 400) {
			createTable('searchResults', data, i != 0);
		} else {
			const errorMessage = document.createElement('marquee');
			errorMessage.textContent = 'Error';
		}
	}
}

////////////////////////////////////////
// switch archive being searched, or change tab
function tab(archiveID, header) {
	// check params have value
	if (archiveID == null || header == null) {
		archiveID = staranTab[0];
		header = staranTab[1];
	}

	// create GET request
	var searchReq = new XMLHttpRequest();

	// update current archive address
	currentArchiveAddress = currentArchiveAddress.slice(0, 41) + archiveID;
	searchReq.open('GET', currentArchiveAddress, false);
	searchReq.send();

	// convert json string to object
	var data = JSON.parse(searchReq.responseText);

	// check data has been recieved
	if (searchReq.status >= 200 && searchReq.status < 400) {
		// change header for new archive
		document.getElementById('header').textContent = header;

		// clear any search results
		document.getElementById('searchResults').innerHTML = '';

		// clear old table data
		document.getElementById('myTableData').innerHTML = '';

		// make new table from current archive
		createTable('myTableData', data, false);
	} else {
		const errorMessage = document.createElement('marquee');
		errorMessage.textContent = 'Error';
	}
}





















