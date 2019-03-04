<?php
namespace ils\svg\image;

use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\SVG;

class SvgImageConstructor{
	
	private $svg;
	private $svgdocument;
	private $items;
	private $width;
	private $height;
	
	public function __construct($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
		$this->svg = new SVG($width, $height);
		$this->svgdocument = $this->svg->getDocument();
	}
	
	public function setItem(SvgImageItem $item, $rw, $rh, $rx, $ry, $type=null)
	{
		$this->items[] = (object)(['item'=>$item, 'type'=>$type, 'width'=>$rw, 'height'=>$rh, 'x'=>$rx, 'y'=>$ry]);
		return $this;
	}
	
	public function image()
	{
		foreach ($this->items as $item) {
			$w = $this->width/100*$item->width;
			$h = $this->height/100*$item->height;
			$x = $this->width/100*$item->x;
			$y = $this->height/100*$item->y;
			$item->item->setScaleTo($w, $h, $item->type);
			$this->svgdocument->addChild($item->item->getSvg($item->type)
				->setAttribute('x', $x)
				->setAttribute('y', $y)
			);
		}
		return $this->svg;
	}
	
	
}