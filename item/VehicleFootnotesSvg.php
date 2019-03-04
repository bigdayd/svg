<?php
namespace ils\svg\item;

use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Texts\SVGText;
use SVG\Nodes\Texts\SVGTitle;

class VehicleFootnotesSvg extends SvgImageItem{
	
	const SIZE_FOOTNOTE_HORIZONTAL = 0;
	const SIZE_FOOTNOTE_VERTICAL = 1;
	private $vehicle;
	public $lineWidth = 2;
	public $arrowSize = 4;
	public $fontSize = 12;
	private $sizeFootnotes = [];
	
	public function __construct(VehicleSvg $vehicle)
	{
		$this->vehicle = $vehicle;
	}
	
	public function addSizeFootnote($size = 'Length')
	{
		$sizes = $this->vehicle->getSizes();
		$s = $sizes[$size]; // ['title', 'size', 'orientation'(, 'startOffset', 'real_length')]
		$this->sizeFootnotes[] = $s;
		return $this;
	}
	
	private function drawSizeFootnote($title, $size, $x, $y, $length, $orientation = self::SIZE_FOOTNOTE_HORIZONTAL)
	{
		$g = (new SVGGroup)
			->setAttribute('class', 'size-footnote')
			->addChild(
				(new SVGTitle())->setValue($title)
			);
		if ($orientation == self::SIZE_FOOTNOTE_VERTICAL) { // vertical
			$g	->addChild(
					(new SVGRect($x, $y, $this->lineWidth, $length))
				)
				->addChild(
					(new SVGPolygon([[($cx = $x+$this->lineWidth/2), $y], [$cx + $this->arrowSize, $y + $this->arrowSize], [$cx - $this->arrowSize, $y + $this->arrowSize]]))
				)
				->addChild(
					(new SVGPolygon([[$cx, ($ey = $y + $length)], [$cx + $this->arrowSize, $ey - $this->arrowSize], [$cx - $this->arrowSize, $ey - $this->arrowSize]]))
				)
				->addChild(
					(new SVGText($size, ($x = $x - $this->fontSize/2), ($y = $y + $length/2)))
						->setSize($this->fontSize)
						->setAttribute("text-anchor", "middle")
						->setAttribute("dominant-baseline", "central")
						->setAttribute("transform", "rotate(-90, {$x}, {$y})")
				);
		} elseif ($orientation == self::SIZE_FOOTNOTE_HORIZONTAL) { // horisontal
			$g	->addChild(
					(new SVGRect($x, $y, $length, $this->lineWidth))
				)
				->addChild(
					(new SVGPolygon([[$x, ($cy = $y + $this->lineWidth/2)], [$x+$this->arrowSize, $cy+$this->arrowSize], [$x+$this->arrowSize, $cy-$this->arrowSize]]))
				)
				->addChild(
					(new SVGPolygon([[($ex = $x+$length), $cy], [$ex-$this->arrowSize, $cy+$this->arrowSize], [$ex-$this->arrowSize, $cy-$this->arrowSize]]))
				)
				->addChild(
					(new SVGText($size, ($x = $x + $length/2), ($y = $y-$this->fontSize/2)))
						->setSize($this->fontSize)
						->setAttribute("text-anchor", "middle")
						->setAttribute("dominant-baseline", "central")
				);
		}
		return $g;
	}
	
	public function getWidth($type = null)
	{
		$width = 0;
		foreach ($this->sizeFootnotes as $s) {
			if ($s[2] == self::SIZE_FOOTNOTE_VERTICAL) {
				$width += $this->fontSize + $this->arrowSize*2;
			} else {
				return $this->vehicle->getWidth($type);
			}
		}
		return $width;
	}
	
	public function getHeight($type = null)
	{
		$height = 0;
		foreach ($this->sizeFootnotes as $s) {
			if ($s[2] == self::SIZE_FOOTNOTE_HORIZONTAL) {
				$height += $this->fontSize + $this->arrowSize*2;
			} else {
				return $this->vehicle->getHeight($type);
			}
		}
		return $height;
	}
	
	public function getSvg($type = null)
	{
		$doc = new SVGDocumentFragment;
		$offsetX = $offsetY = 0;
		foreach ($this->sizeFootnotes as $s) {
			if ($s[2] == self::SIZE_FOOTNOTE_HORIZONTAL) {
				$x = isset($s[3]) ? $s[3] : 0;
				$y = $offsetY + $this->fontSize;
				$offsetY = $y + $this->arrowSize*2;
				$doc->addChild($this->drawSizeFootnote($s[0], $s[1], $x*$this->scale, $y, (isset($s[4]) ? $s[4] : $s[1])*$this->scale, $s[2]));
			} else {
				$x = $offsetX + $this->fontSize;
				$y = isset($s[3]) ? $s[3] : 0;
				$offsetX = $x + $this->arrowSize*2;
				$doc->addChild($this->drawSizeFootnote($s[0], $s[1], $x, $y*$this->scale, (isset($s[4]) ? $s[4] : $s[1])*$this->scale, $s[2]));
			}
			
		}
		return $doc;
	}
	
	public function setLineWidth($val)
	{
		$this->lineWidth = $val;
		return $this;
	}
	
	public function setArrowSize($val)
	{
		$this->arrowSize = $val;
		return $this;
	}
	
	public function setFontSize($val)
	{
		$this->fontSize = $val;
		return $this;
	}
}