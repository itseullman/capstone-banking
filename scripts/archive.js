"use strict";

if (typeof ArchiveProgram !== 'undefined') {
	throw "The variable Program has already been defined. No javascript will be available on this page.";
}

// window.localStorage getItem(name) setitem(name, value) removeItem(name) clear()




var ArchiveProgram = {
	
	page: null,
	
	searchForm: null,
	
	searchIndices: ['w','x','y','z'],
	
	searchInputs: {},
	
	searchTypes: {},
	
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
		
		this.searchIndices.forEach(index => {
			this.searchInputs[index] = this.searchForm.querySelector(`input[type=search][name=${index}]`);
			if (!this.searchInputs[index]) {
				throw `The page structure has been broken. No "${index}-type" search input element was found.`;
			}
			this.searchTypes[index] = this.searchForm.querySelector(`select[name=${index}-type]`);
			if (!this.searchTypes[index]) {
				throw `The page structure has been broken. No "${index}-type" search select element was found.`;
			}
			
			let savedSearchType = window.localStorage.getItem(`search-input-${index}-type`);
			if (savedSearchType === null) {
				switch (index) {
					case 'y': savedSearchType = 'comments'; break;
					case 'z': savedSearchType = 'document_number'; break;
					default:
				}
				
			}
			
			for (let option of this.searchTypes[index].options) {
				if (option.value == savedSearchType) {
					option.selected = true;
				}
			}
			this.searchTypes[index].addEventListener('change', event => {
				window.localStorage.setItem(`search-input-${index}-type`, event.target.value);
			});
		});
			
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
		
		let noMoreSearch = false;
		this.searchIndices.forEach(index => {
			if (!noMoreSearch && this.searchInputs[index].value.trim().length > 0) {
				this.SubmitHandler();
				noMoreSearch = true;
			}
		});
		
		this.SearchLogic.Initialize();
		this.Data.Initialize();
		this.Filter.Initialize();
		
		
		/*
		let categoriesContainer = document.getElementById("filter-categories-container");
		document.body.addEventListener('click', event => {
			if (event.target.id != "filter-categories-container" && categoriesContainer.contains(event.target) === false) {
				this.Filter.RowToggles.types.forEach(type => {
					this.Filter.RowToggles.Toggle(type, 'off');
				});
			}
		});
		*/
		document.getElementById('search-all-results').addEventListener('click', event => {
			this.searchIndices.forEach(index => {
				this.searchInputs[index].value = '';
			});
			this.SubmitHandler();
		});
	},
	
	SearchLogic: {
		
		Button: null,
		
		Initialize: function () {
		
			this.Button = ArchiveProgram.searchForm.querySelector('#form-logic-button');
			if (!this.Button) {
				throw "The page structure has been broken. No search logic button was found.";
			}
			let savedSearchLogic = window.localStorage.getItem('form-logic-button');
			if (savedSearchLogic !== 'and') {
				savedSearchLogic = 'or';
			}
			this.SetValue(savedSearchLogic);
			
			this.Button.addEventListener('click', event => {
				if (this.Value() == 'or') {
					this.SetValue('and');
				} else {
					this.SetValue('or');
				}
				console.log('saving: ', this.Value());
				window.localStorage.setItem('form-logic-button', this.Value());
			});
			
		},
		
		Value: function () {
			return this.Button.value;
		},
		
		SetValue: function(value) {
			if (value == 'and') {
				this.Button.value = 'and';
				this.Button.innerText = 'Logical AND';
			} else {
				this.Button.value = 'or';
				this.Button.innerText = 'Logical OR';
			}
		},
	},
	
	Filter: {
		
		tools: null,
		
		Initialize: function () {
			this.tools = document.querySelector('#page-archive #archive-search-tools');
			if (!this.tools) {
				throw "The page structure has been broken. No search tools were found.";
			}
			this.Checks.Initialize();
			this.ColumnToggles.Initialize();
			this.RowToggles.Initialize();
		},
		
		Checks: {
			items: null,
			
			Initialize: function () {
				this.items = ArchiveProgram.Filter.tools.querySelectorAll('[data-display-field]');
				if (!this.items) {
					throw "The page structure has been broken. Table column filters were not found.";
				}
				this.items.forEach(item => {
					let fieldName = item.dataset.displayField;
					let setting = window.localStorage.getItem(`data-display-field-${fieldName}`);
					if (setting !== 'false') {
						item.checked = true;
					}
					item.addEventListener('change', event => {
						this.Check(fieldName);
					});
				});
				this.Reset();
			},
			
			Reset: function () {
				this.items.forEach(item => {
					if (!item.checked) {
						this.Check(item.dataset.displayField, false);
					} else {
						item.checked = true;
					}
				});
			},
	
			Check(name, state = null) {
				ArchiveProgram.Data.ToggleColumn(name, state);
			},
		},
		
		ColumnToggles: {
			items: null,
			
			Checks: null,
			
			Initialize: function () {
				this.Checks = ArchiveProgram.Filter.Checks;
				this.items = ArchiveProgram.Filter.tools.querySelectorAll('[data-display-toggle]');
				if (!this.items) {
					throw "The page structure has been broken. Table column toggles were not found.";
				}
				this.items.forEach(item => {
					// data-display-toggle
					let toggleType = item.dataset.displayToggle;
					item.addEventListener('click', event => {
						if (toggleType === 'on') {
							this.Checks.items.forEach(check => {
								let checkName = check.dataset.displayField;
								check.checked = true;
								this.Checks.Check(checkName, true);
							});
						} else if (toggleType === 'off') {
							this.Checks.items.forEach(check => {
								let checkName = check.dataset.displayField;
								check.checked = false;
								this.Checks.Check(checkName, false);
							});
						} else if (toggleType === 'toggle') {
							this.Checks.items.forEach(check => {
								let checkName = check.dataset.displayField;
								check.checked = !check.checked;
								this.Checks.Check(checkName);
							});
						}
					});
				});
			},
		},
		
		RowToggles: {
			
			Filters: {},
			
			types: ['category', 'author', 'location', 'origin'],
			
			Initialize: function () {
				this.types.forEach(type => {
					this.Filters[type] = [];
					
					this[type] = {
						that: this,
						button: ArchiveProgram.Filter.tools.querySelector(`[data-display-${type}-filter]`),
						select: null,
						Toggle: function(state = null) {
							that.Toggle(type, state);
						},
					};
					if (!this[type].button) {
						throw `The page structure has been broken. The ${type} filter was not found.`;
					}
					this[type].button.addEventListener('click', event => {
						this.Toggle(type);
					});
					this[type].select = this[type].button.nextElementSibling.querySelector('select');
					if (!this[type].select) {
						throw `The page structure has been broken. The ${type} filter select was not found.`;
					}
					this[type].select.addEventListener('input', event => {
						this.Input_Handler(type);
					});
				});
				this.types.forEach(type => {
					this.SetFilter(type);
				});
			},
			
			Toggle: function (type, newstate = null) {
						
				if (this.types.findIndex(item => item == type) !== -1) {
					if (newstate === null || (newstate != 'on' && newstate != 'off')) {
						if (this[type].button.nextElementSibling.classList.contains('hidden')) {
							newstate = 'on';
						} else {
							newstate = 'off';
						}
					}
					if (newstate === 'on') {
						this[type].button.nextElementSibling.classList.remove('hidden');
						const controller = new AbortController();
						document.body.addEventListener('click', event => {
							if (this[type].button.contains(event.target) || this[type].select.contains(event.target)) {
								event.stopPropagation();
							} else {
								this.Toggle(type, 'off');
								controller.abort();
							}
						}, { signal: controller.signal });
					} else if (newstate === 'off') {
						this[type].button.nextElementSibling.classList.add('hidden');
					}
				}
			},
			
			Input_Handler: function (type) {
				this.SetFilter(type);
				ArchiveProgram.Data.FilterRows(this.Filters);
			},
			
			SetFilter(type) {
				let has_all = false;
				this.Filters[type] = [...this[type].select.selectedOptions].map(op =>{
					if (op.value == '-1') {
						has_all = true;
					}
					return {
						value: op.value, 
						text: op.innerText,
					};
				});
				if (has_all) {
					this.Filters[type] = [];
				}
			},
			
			ReFilter: function () {
				ArchiveProgram.Data.FilterRows(this.Filters);
			},
		},
	},
	
	Data: {
		
		items: [],
		
		container: null,
		
		Initialize: function() {
			this.container = document.querySelector('#page-archive > .search-results-container');
			if (!this.container) {
				throw "The page structure has been broken. A .search-results-container is required for showing results.";
			}
		},
		
		FilterRows: function(filters) {
			console.log(filters);
			let rows = [...this.GetRows()];
			rows.forEach(row => {
				let visible = true;
				let show_row = null;
						//console.log('-----------------new row---------------');
				for (let type in filters) {
					show_row = null;
					let cell = this.GetCellByRow(row, type);
					filters[type].forEach(filter => {
						//console.log(show_row);
						if (!cell.innerText.includes(filter.text)) {
							show_row = show_row || false;
						} else {
							show_row = true;
						}
					});
					if (show_row !== null) {
						visible = visible && show_row;
					}
				}
				if (visible) {
					row.classList.remove('hidden');
				} else {
					row.classList.add('hidden');
				}
			});
		},
		
		GetColumn: function (field) {
			return this.container.querySelectorAll(`[data-table-field=${field}]`);
		},
		
		GetColumnHeader: function (field) {
			return this.container.querySelector(`[data-table-header=${field}]`);
		},
		
		GetCellByRow(row, field) {
			return row.querySelector(`[data-table-field=${field}]`);
		},
		
		GetRowByCell: function(cell) {
			return cell.closest('.search-results-row');
		},
		
		GetRows: function() {
			return this.container.querySelectorAll('.search-results-row');
		},
		
		HideRows: function(rows) {
			rows.forEach(row => {
				row.classList.add('hidden');
			});
		},
		
		ToggleColumn: function(field, state) {
			let cells = [this.GetColumnHeader(field), ...this.GetColumn(field)];
			
			if (state === null) {
				cells.forEach(cell => {
					cell.classList.toggle('hidden');
				});
				let setting = window.localStorage.getItem(`data-display-field-${name}`);
				window.localStorage.setItem(`data-display-field-${name}`, setting == 'false' ? 'true' : 'false');
			} else if (state === true) {
				cells.forEach(cell => {
					cell.classList.remove('hidden');
				});
				window.localStorage.setItem(`data-display-field-${name}`, true);
			} else if (state === false) {
				cells.forEach(cell => {
					cell.classList.add('hidden');
				});
				window.localStorage.setItem(`data-display-field-${name}`, false);
			}
		},
		
		HideRowsByCells: function(cells) {
			cells.forEach(cell => {
				this.GetRowByCell(cell).classList.add('hidden');
			});
		},
		
		ShowRows: function(rows) {
			rows.forEach(row => {
				row.classList.remove('hidden');
			});
		},
		
		HideColumn: function(field) {
			[...this.GetColumn(field)].forEach(cell => {
				cell.classList.add('hidden');
			});
		},
		
		ShowColumn: function(field) {
			[...this.GetColumn(field)].forEach(cell => {
				cell.classList.remove('hidden');
			});
		},
		
		Reset: function(items) {
			this.items = items;
			let resultsContent = '';
			this.items.forEach(item => {
				resultsContent += this.NewRow(item);
				
			});
			this.container.querySelector('.search-results-content').innerHTML = resultsContent;
			ArchiveProgram.Filter.Checks.Reset();
			ArchiveProgram.Filter.RowToggles.ReFilter();
		},
		
		Clear: function() {
			this.items = [];
			this.container.querySelector('.search-results-content').innerHTML = '';
		},
		
		NewRow: function(item) {
			
			// external-link
			let public_url = item['public_url'];
			if (public_url.substr(0, 4) === 'http') {
				if (public_url.substr(0, 21) === 'https://oaks.kent.edu') {
					public_url = `<a href="${public_url}">${public_url}</a>`;
				} else {
					public_url = `<a href="${public_url}" class="external-link">${public_url}</a>`;
				}
			}
			let pdf_url = item['pdf_url'];
			if (pdf_url.substr(0, 4) === 'http') {
				pdf_url = `<a href="${pdf_url}">${pdf_url}</a>`;
			}
			
			return `
	<tr class="search-results-row">
		<td data-table-field="title">
		${item['title']}
		</td>
		<td data-table-field="published_date">
		${item['published_date']}
		</td>
		<td data-table-field="document_number">
		${item['document_number']}
		</td>
		<td data-table-field="archive_number">
		${item['archive_number']}
		</td>
		<td data-table-field="author">
		${item['authors']}
		</td>
		<td data-table-field="comments">
		${item['comments']}
		</td>
		<td data-table-field="bib_text">
		${item['bib_text']}
		</td>
		<td data-table-field="origin">
		${item['origin_name']}
		</td>
		<td data-table-field="category">
		${item['categories']}
		</td>
		<td data-table-field="location">
		${item['location_name']}
		</td>
		<td data-table-field="public_url">
		${public_url}
		</td>
		<td data-table-field="pdf_url">
		${pdf_url}
		</td>
	</tr>
	`;
		}
	},
	
	SubmitHandler: function () {
		
		this.Data.Clear();
		
		this.loadingSpinner.classList.remove('hidden');
		try {
			
			let q = '';
			this.searchIndices.forEach(index => {
				let item = this.searchInputs[index];
				let value = item.value.trim();
				if (value.length > 0) {
					q += `&${index}=${encodeURIComponent(value)}`;
					q += `&${index}-type=${encodeURIComponent(this.searchTypes[index].value)}`;
				}
			});
			
			
			if (q.length > 0) {
				q += '&logic=' + this.SearchLogic.Value();
			}
			
			this.Ajax({
				url: `${this.searchForm.action}${q}`,
				success: (response) => {
					if (response.error) {
						this.searchResults.innerText = response.error;
					} else if (response instanceof Object) {
						
						if (response.items && response.items instanceof Array) {
							this.Data.Reset(response.items);
						}
						
					}
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