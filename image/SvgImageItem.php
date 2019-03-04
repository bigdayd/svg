<?php

namespace ils\svg\image;

use SVG\Nodes\Structures\SVGGroup;

class SvgImageItem{
	
	public $scale = 1;
	
	public function setScaleTo($width, $height, $type=null)
	{
		$sourceW = $this->getWidth($type);
		$sourceH = $this->getHeight($type);
		$this->scale = min(($width / $sourceW), ($height / $sourceH));
		return $this;
	}
	
	public function getSvg($type=null)
	{
		return (new SVGGroup);
	}
	
	public function getWidth($type=null)
	{
		return 0;
	}
	
	public function getHeight($type=null)
	{
		return 0;
	}
	
}