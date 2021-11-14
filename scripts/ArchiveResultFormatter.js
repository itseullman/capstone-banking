
class ArchiveResultFormatter {
	Container = null;
	
	Content = null;
	
	constructor(page) {
		this.InitializeContainer();
		page.appendChild(this.Container);
		this.Content = this.Container.querySelector('.search-results-content');
	}
	
	GetPublicLink(item) {
		let public_url = item['public_url'];
		if (public_url.substr(0, 4) === 'http') {
			if (public_url.substr(0, 21) === 'https://oaks.kent.edu') {
				public_url = `<a href="${public_url}">${public_url}</a>`;
			} else {
				public_url = `<a href="${public_url}" class="external-link">${public_url}</a>`;
			}
		}
		return public_url;
	}
	
	GetPdfLink(item) {
		let pdf_url = item['pdf_url'];
		if (pdf_url.substr(0, 4) === 'http') {
			pdf_url = `<a href="${pdf_url}">${pdf_url}</a>`;
		}
		return pdf_url;
	}
	
	destroy() {
		this.Container.remove();
		this.Content = null;
		this.Container = null;
	}
}

export class ArchiveResultFormatterTable extends ArchiveResultFormatter {
	InitializeContainer() {
		this.Container = document.createElement('table');
		this.Container.classList.add('search-results-container');
		this.Container.innerHTML = `
		<thead>
			<tr>
				<th data-table-header="title">
				Title
				</th>
				<th data-table-header="published_date">
				Published Date
				</th>
				<th data-table-header="document_number">
				Document Number
				</th>
				<th data-table-header="archive_number">
				Archive Number
				</th>
				<th data-table-header="author">
				Author(s)
				</th>
				<th data-table-header="comments">
				Comments
				</th>
				<th data-table-header="bib_text">
				Bib. Text
				</th>
				<th data-table-header="origin">
				Produced By
				</th>
				<th data-table-header="category">
				Category
				</th>
				<th data-table-header="location">
				Location
				</th>
				<th data-table-header="public_url">
				Public URL
				</th>
				<th data-table-header="pdf_url">
				PDF URL
				</th>
			</tr>
		</thead>
	<tbody class="search-results-content"></tbody>
		`;
	}
	
	FormatItem(item) {
		let public_url = this.GetPublicLink(item);
		let pdf_url = this.GetPdfLink(item);
		let node = document.createElement('tr');
		node.classList.add('search-results-row');
		
		node.innerHTML = `
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
		`;
		return node;
	}
}

export class ArchiveResultFormatterBox extends ArchiveResultFormatter {
	InitializeContainer() {
		this.Container = document.createElement('div');
		this.Container.classList.add('search-results-container');
		let content = document.createElement('div');
		content.classList.add('search-results-content');
		this.Container.appendChild(content);
	}
	
	FormatItem(item) {
		let public_url = this.GetPublicLink(item);
		let pdf_url = this.GetPdfLink(item);
		let node = document.createElement('dl');
		node.classList.add('search-results-row');
		node.innerHTML = `
			<div data-table-field="title"><dt>Title</dt><dd>${item['title']}</dd></div>
			<div data-table-field="published_date"><dt>Published Date</dt><dd>${item['published_date']}</dd></div>
			<div data-table-field="document_number"><dt>Document Number</dt><dd>${item['document_number']}</dd></div>
			<div data-table-field="archive_number"><dt>Archive Number</dt><dd>${item['archive_number']}</dd></div>
			<div data-table-field="author"><dt>Author(s)</dt><dd>${item['authors']}</dd></div>
			<div data-table-field="comments"><dt>Comments</dt><dd>${item['comments']}</dd></div>
			<div data-table-field="bib_text"><dt>Bib. Text</dt><dd>${item['bib_text']}</dd></div>
			<div data-table-field="origin"><dt>Produced By</dt><dd>${item['origin_name']}</dd></div>
			<div data-table-field="category"><dt>Category</dt><dd>${item['categories']}</dd></div>
			<div data-table-field="location"><dt>Location</dt><dd>${item['location_name']}</dd></div>
			<div data-table-field="public_url"><dt>Public URL</dt><dd>${public_url}</dd></div>
			<div data-table-field="pdf_url"><dt>PDF URL</dt><dd>${pdf_url}</dd></div>
		`;
		return node;
	}
}
export class ArchiveResultFormatterRowBox extends ArchiveResultFormatter {
	InitializeContainer() {
		this.Container = document.createElement('div');
		this.Container.classList.add('search-results-container');
		let content = document.createElement('div');
		content.classList.add('search-results-content');
		this.Container.appendChild(content);
	}
	
	FormatItem(item) {
		let public_url = this.GetPublicLink(item);
		let pdf_url = this.GetPdfLink(item);
		let node = document.createElement('dl');
		node.classList.add('search-results-row');
		node.classList.add('search-results-row-block');
		node.innerHTML = `
			<div data-table-field="title" data-block><dt>Title</dt><dd>${item['title']}</dd></div>
			<div data-table-field="published_date"><dt>Published Date</dt><dd>${item['published_date']}</dd></div>
			<div data-table-field="author"><dt>Author(s)</dt><dd>${item['authors']}</dd></div>
			<div data-table-field="comments" data-block><dt>Comments</dt><dd>${item['comments']}</dd></div>
			<div data-table-field="origin"><dt>Produced By</dt><dd>${item['origin_name']}</dd></div>
			<div data-table-field="category"><dt>Category</dt><dd>${item['categories']}</dd></div>
			<div data-table-field="location"><dt>Location</dt><dd>${item['location_name']}</dd></div>
			<div data-table-field="document_number"><dt>Document Number</dt><dd>${item['document_number']}</dd></div>
			<div data-table-field="archive_number"><dt>Archive Number</dt><dd>${item['archive_number']}</dd></div>
			<div data-table-field="bib_text" data-block><dt>Bib. Text</dt><dd>${item['bib_text']}</dd></div>
			<div data-table-field="public_url" data-block><dt>Public URL</dt><dd>${public_url}</dd></div>
			<div data-table-field="pdf_url" data-block><dt>PDF URL</dt><dd>${pdf_url}</dd></div>
		`;
		return node;
	}
}























