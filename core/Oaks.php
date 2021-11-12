<?php


class OakSearch {
	
	static public function Instance() {
		static $instance = null;
		if (is_null($instance)) {
			$instance = new OakSearch();
		}
		return $instance;
	}
	
	private function __construct() {}
	
	public function __invoke($search_term = '') {
		$items = [];
		$results = file_get_contents(OAKS_WWW . '/' . urlencode($search_term), false, stream_context_create(['http' => ['method'  => 'GET']]));
		if (is_string($results)) {
			$data = json_decode($results, true);
			if (is_array($data)) {
				foreach ($data as $item) {
					$items[] = new OakResult($item);
				}
			}
		}
		return $items;
	}
}

class OakResult implements JsonSerializable {
	/*(
		[id] => 16621
		[title] => A scalable associative processor with applications in database and image processing
		[author] => Hong Wang;Lei Xie;Meiduo Wu;Robert A. Walker
		[date] => 2004-04
		[public_url] => https://oaks.kent.edu/node/16621
		[pdf_url] => 
		[search_api_excerpt] => 
	)*/
	
	private $id_;
	private $title_;
	private $author_;
	private $date_ = [null, null];
	private $date_formatted_ = '';
	private $public_url_;
	private $pdf_url_;
	private $search_api_excerpt_;
	
	public function __construct($item) {
		if (!is_array($item)) {
			throw new Exception('OakResult requires $item to be an array.');
		}
		
		if (isset($item['id'])) {
			$this->id_ = $item['id'];
		}
		if (isset($item['title'])) {
			$this->title_ = $item['title'];
		}
		if (isset($item['author'])) {
			$this->author_ = $item['author'];
		}
		if (isset($item['date'])) {
			$date = explode('-', $item['date']);
			if (!isset($date[0])) {
				$this->date_formatted_ = '';
			} else if (!isset($date[1])) {
				$this->date_[0] = intval($date[0]);
				$this->date_formatted_ = $date[0];
			} else {
				$this->date_[0] = intval($date[0]);
				$this->date_[1] = intval($date[1]);
				$this->date_formatted_ = sprintf('%s,&nbsp;%s', $date[0], $date[1]);
			}
		}
		
		if (isset($item['public_url'])) {
			$this->public_url_ = $item['public_url'];
		}
		if (isset($item['pdf_url'])) {
			$this->pdf_url_ = $item['pdf_url'];
		}
		if (isset($item['search_api_excerpt'])) {
			$this->search_api_excerpt_ = $item['search_api_excerpt'];
		}
	}
	
	public function __get($name) {
		switch($name) {
			case 'id':
			return $this->id_;
			
			case 'title':
			return $this->title_;
			
			case 'author':
			return $this->author_;
			
			case 'date':
			return $this->date_;
			
			case 'date_formatted':
			return $this->date_formatted_;
			
			case 'public_url':
			return $this->public_url_;
			
			case 'pdf_url':
			return $this->pdf_url_;
			
			case 'search_api_excerpt':
			return $this->search_api_excerpt_;
			
			default:
			throw new Exception(sprintf('OakResult does not have a %s property.', htmlspecialchars($name)));
		}
	}

    public function jsonSerialize() {
        return [
			'id' => $this->id_,
			'title' => $this->title_,
			'author' => $this->author_,
			'date' => $this->date_,
			'date_formatted' => $this->date_formatted_,
			'public_url' => $this->public_url_,
			'pdf_url' => $this->pdf_url_,
			'search_api_excerpt' => $this->search_api_excerpt_,
		];
    }
	
	public function ToItem() {
		// item_id, title, published_date, bib_text, origin_id, document_number, location_id, archive_number, comments, public_url, pdf_url
        return [
			'title' => $this->title_,
			'published_date' => $this->date_formatted_,
			'bib_text' => '',
			'origin_id' => null,
			'document_number' => '',
			'location_id' => 'OAKS',
			'archive_number' => $this->id_,
			'comments' => '',
			'public_url' => $this->public_url_,
			'pdf_url' => $this->pdf_url_,
			'author' => $this->author_,
			'category' => '',
		];
	}
}


class OakMeta {
	
	static public function Instance() {
		static $instance = null;
		if (is_null($instance)) {
			$instance = new OakMeta();
		}
		return $instance;
	}
	
	private $db;
	
	private function __construct() {
		$this->db = db();
		if ($this->Count() === -1) {
			$this->InitEntity();
		}
	}
	
	public function Get($name) {
		$stmt = $this->db->prepare('SELECT meta_name, meta_value, meta_order FROM oaks_meta WHERE meta_name = :meta_name');
		$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
		if( ($result = @$stmt->execute()) ) {
			return $result->fetchArray(SQLITE3_ASSOC);
		}
		return null;
	}
	
	/* Returns true or false, depending on whether the value was set. */
	public function Set($name, $value, $ordering = 0) {
		$ordering = intval($ordering);
		$stmt = $this->db->prepare('INSERT INTO oaks_meta (meta_name, meta_value, meta_order) VALUES (:meta_name, :meta_value, :meta_order)');
		$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':meta_value', $value, SQLITE3_TEXT);
		$stmt->bindValue(':meta_order', $ordering, SQLITE3_INTEGER);

		if(!@$stmt->execute()) {
			$stmt = $this->db->prepare('UPDATE oaks_meta SET meta_value = :meta_value, meta_order = :meta_order WHERE  meta_name = :meta_name');
			$stmt->bindValue(':meta_name', $name, SQLITE3_TEXT);
			$stmt->bindValue(':meta_value', $value, SQLITE3_TEXT);
			$stmt->bindValue(':meta_order', $ordering, SQLITE3_INTEGER);
			return @$stmt->execute();
		}
		return false;
	}
	
	/* When the oaks_meta table does not exist, return -1 */
	public function Count() {
		$result = @$this->db->query('select count(*) as meta_num from oaks_meta');
		$meta_num = -1;
		if ($result) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			if ($row) {
				$meta_num = $row['meta_num'];
			}
		}
		return $meta_num;
	}
	
	private function InitEntity() {
		$this->db->exec('DROP TABLE IF EXISTS oaks_meta');
		$this->db->exec('CREATE TABLE oaks_meta (meta_name VARCHAR(50), meta_value VARCHAR(200), meta_order INT, UNIQUE(meta_name))');
		$this->Scrape();
	}
	
	public function Scrape() {
		$oaks = OakSearch::Instance();
		$data = $oaks();
		
		// min/max years
		$min = null;
		$max = 0;
		
		
		$output = '';
		foreach ($data as $item) {
			if (is_null($min) or $item->date[0] < $min) {
				$min = $item->date[0];
			}
			if ($item->date[0] > $max) {
				$max = $item->date[0];
			}
		}
		
		$meta = OakMeta::Instance();
		$meta->Set('year-min', $min);
		$meta->Set('year-max', $max);
	}
	
}




















