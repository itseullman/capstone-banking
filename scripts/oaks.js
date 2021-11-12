"use strict";

if (typeof ArchiveProgram !== 'undefined') {
	throw "The variable Program has already been defined. No javascript will be available on this page.";
}

var ArchiveProgram = {
	
	page: null,
	
	searchForm: null,
	
	searchInput: null,
	
	searchResults: null,
	
	loadingSpinner: null,
	
	Start: function() {
		this.page = document.querySelector('#page-archive');
		if (!this.page) {
			throw "The page structure has been broken.";
		}
		
		this.searchForm = this.page.querySelector('form');
		if (!this.searchForm) {
			throw "The page structure has been broken. No form element was found.";
		}
		
		this.searchInput = this.searchForm.querySelector('input[type=search]');
		if (!this.searchInput) {
			throw "The page structure has been broken. No search input element was found.";
		}
		
		this.searchResults = this.page.querySelector('.ajax-response');
		if (!this.searchResults) {
			throw "The page structure has been broken. No search results element was found.";
		}
		
		this.loadingSpinner = this.page.querySelector('form img');
		if (!this.loadingSpinner) {
			throw "The page structure has been broken. No loading spinner was found.";
		}
		
		this.searchForm.addEventListener('submit', event => {
			event.preventDefault();
			this.SubmitHandler();
		});
		
		if (this.searchInput.value.trim().length > 0) {
			this.SubmitHandler();
		}
		
	},
	
	SubmitHandler: function () {
		this.searchResults.innerText = '';
		this.loadingSpinner.classList.remove('hidden');
		try {
			let searchInput = this.searchForm.querySelector('input[type=search]');
			if (!searchInput) {
				throw "No search input found. The search cannot continue.";
			}
			if (searchInput.value.length < 3) {
				throw "The search string must be at least three characters long.";
			}
			this.Ajax({
				url: `${this.searchForm.action}&q=${encodeURIComponent(searchInput.value)}`,
				success: (response) => {
					if (response.error) {
						this.searchResults.innerText = response.error;
					} else if (response instanceof Array) {
						let output = '';
						response.forEach(item => {
							output += `${item.author}, ${item.title}<br>`;
						});
						if (output.length == 0) {
							output = "No results...";
						}
						this.searchResults.innerHTML = output;
					}
					console.log(response);
					this.loadingSpinner.classList.add('hidden');
				},
				fail: (statusText) => {
					console.log(statusText);
					this.loadingSpinner.classList.add('hidden');
				},
			});
		} catch (error) {
			this.loadingSpinner.classList.add('hidden');
			this.searchResults.innerText = error;
		}
	},
	
	/*
		config = {
			url:		string
			success:	Function
			fail:		Function
		}
	*/
	Ajax: function (config) {
		

		if (!(config instanceof Object)) {
			throw "ArchiveProgram.Ajax requires a config object.";
		}
		if (typeof config.url !== "string" || config.url.length === 0) {
			throw "ArchiveProgram.Ajax requires config.url to be specified.";
		}
		if (typeof config.success !== "function") {
			throw "ArchiveProgram.Ajax requires a config.success callback.";
		}
		if (typeof config.fail !== "function") {
			config.fail = function () {};
		}
		
		
		let xhr = new XMLHttpRequest();
		xhr.open("GET", config.url, true);
		//Send the proper header information along with the request
		xhr.setRequestHeader("Content-Type", "text/html");
		// All ajax requests are responded to with json data.
		xhr.responseType = "json";
		xhr.onreadystatechange = function() { // Call a function when the state changes.
			if (this.readyState === XMLHttpRequest.DONE) {
				if (this.status === 0 || (this.status >= 200 && this.status < 400)) {
					config.success(this.response);
				} else {
					config.fail(this.statusText);
				}
			}
		}
		xhr.send(null);
	}, // END OF Ajax()
};



ArchiveProgram.Start();

/*
JSON data structure of OAKS database response:
	id – (integer) the internal OAKS id for the item
	title – (string) the title of the item
	author – (delimited string) the author(s) of the (multiple authors are separated by the semi-colon character)
	date – (string) the date the item was published in the format
	public_url – (string) a URL to the public display of the item in OAKS
	pdf_url – (string) a URL to the PDF for the item (if it has a PDF in OAKS)
*/