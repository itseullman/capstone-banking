"use strict";

if (typeof ArchiveProgram !== 'undefined') {
	throw "The variable Program has already been defined. No javascript will be available on this page.";
}

// window.localStorage getItem(name) setitem(name, value) removeItem(name) clear()

import {ArchiveResultFormatterTable, ArchiveResultFormatterBox, ArchiveResultFormatterRowBox} from './ArchiveResultFormatter.js';


var ArchiveProgram = {
	
	page: null,
	
	searchForm: null,
	
	searchIndices: ['w','x','y','z'],
	
	searchInputs: {},
	
	searchTypes: {},
	
	searchFeedback: null,
	
	loadingSpinner: null,
	
	ResultFormatter: null,
	
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
			
		this.searchFeedback = this.page.querySelector('.ajax-error-response');
		if (!this.searchFeedback) {
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
		
		
		//this.ResultFormatter = new ArchiveResultFormatterTable(this.page);
		//this.ResultFormatter = new ArchiveResultFormatterBox(this.page);
		this.Stats.Initialize();
		this.Filter.Initialize();
		this.Format.Initialize();
		this.SearchLogic.Initialize();
		this.Data.Initialize();
		
		
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
	
	Stats: {
		Container: null,
		
		Initialize: function () {
			this.Container = ArchiveProgram.page.querySelector('.search-results-stats');
			if (!this.Container) {
				throw "The page structure has been broken. No search results stats container was found.";
			}
		},
		
		Total: function (num) {
			this.Container.querySelector('.search-results-total').innerText = num;
		},
		
		Hidden: function (num) {
			this.Container.querySelector('.search-results-hidden').innerText = num;
		},
		
		Visible: function (num) {
			this.Container.querySelector('.search-results-visible').innerText = num;
		},
	},
	
	Format: {
		Container: null,
		
		format: 'table',
		
		Formatter: null,
		
		initialized: false,
		
		Initialize: function (){
			this.Container = ArchiveProgram.page.querySelector('.search-results-format');
			if (!this.Container) {
				throw "The page structure has been broken. No search results format container was found.";
			}
			this.SetFormatter(window.localStorage.getItem('search-results-format'));
			// data-format-type="table"
			this.Container.querySelectorAll('button').forEach(item => {
				item.addEventListener('click', event => {
					let type = event.target.dataset.formatType;
					if (type) {
						this.SetFormatter(type);
					}
				});
			});
			this.initialized = true;
		},
		
		SetFormatter: function(format) {
			if (format === null) {
				format = 'table';
			}
			this.format = format;
			
			if (this.Formatter) {
				this.Formatter.destroy();
			}
			switch (format) {
				case 'table':
				this.Formatter = new ArchiveResultFormatterTable(ArchiveProgram.page);
				break;
				
				case 'card':
				this.Formatter = new ArchiveResultFormatterBox(ArchiveProgram.page);
				break;
				
				case 'row-card':
				this.Formatter = new ArchiveResultFormatterRowBox(ArchiveProgram.page);
				break;
				
				default:
				throw 'An invalid search results formatter was requested.';
			}
			window.localStorage.setItem('search-results-format', format);
			
			// Don't reset the Data before it has been initialized.
			if (this.initialized) {
				ArchiveProgram.Data.Reset();
			}
		}
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
				
				// TODO: check if the Checks initialized without calling Reset()
				//this.Reset();
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
			let countv = rows.length;
			let counth = 0;
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
					countv--;
					counth++;
				}
			});
			ArchiveProgram.Stats.Visible(countv);
			ArchiveProgram.Stats.Hidden(counth);
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
		
		GetRows: function() {
			return this.container.querySelectorAll('.search-results-row');
		},
		
		ToggleColumn: function(field, state = null) {
			let cells = [...this.GetColumn(field)];
			let headerCell = this.GetColumnHeader(field);
			if (headerCell) {
				cells.push(headerCell);
			}
			
			if (state === null) {
				cells.forEach(cell => {
					cell.classList.toggle('hidden');
				});
				let setting = window.localStorage.getItem(`data-display-field-${field}`);
				window.localStorage.setItem(`data-display-field-${field}`, setting == 'false' ? 'true' : 'false');
			} else if (state === true) {
				cells.forEach(cell => {
					cell.classList.remove('hidden');
				});
				window.localStorage.setItem(`data-display-field-${field}`, true);
			} else if (state === false) {
				cells.forEach(cell => {
					cell.classList.add('hidden');
				});
				window.localStorage.setItem(`data-display-field-${field}`, false);
			}
		},
		
		Reset: function(items = null) {
			this.container = document.querySelector('#page-archive > .search-results-container');
			if (items !== null) {
				this.items = items;
			}
			let resultsContent = '';
			this.items.forEach(item => {
				resultsContent += this.NewRow(item);
				
			});
			ArchiveProgram.Stats.Total(this.items.length);
			ArchiveProgram.Stats.Visible(this.items.length);
			ArchiveProgram.Stats.Hidden(0);
			this.container.querySelector('.search-results-content').innerHTML = resultsContent;
			ArchiveProgram.Filter.Checks.Reset();
			ArchiveProgram.Filter.RowToggles.ReFilter();
		},
		
		Clear: function() {
			this.items = [];
			this.container.querySelector('.search-results-content').innerHTML = '';
		},
		
		NewRow: function(item) {
			return ArchiveProgram.Format.Formatter.FormatItem(item).outerHTML;
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
						this.searchFeedback.innerText = response.error;
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
			this.searchFeedback.innerText = error;
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