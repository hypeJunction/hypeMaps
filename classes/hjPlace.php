<?php

class hjPlace extends hjObject {

	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = "hjplace";
	}

	public function getURL() {
		$friendly_title = elgg_get_friendly_title($this->title);
		return "maps/view/$this->guid/$friendly_title";
	}

}

