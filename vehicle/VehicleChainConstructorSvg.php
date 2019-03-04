<?php
namespace ils\svg\vehicle;

use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGDocumentFragment;

class VehicleChainConstructorSvg extends SvgImageItem{
	
	private $chain = [];
	
	public function __construct(array $chain = [])
	{
		foreach ($chain as $vehicle) {
			$this->addVehicle($vehicle);
		}
	}
	
	public function getSizes()
	{
		// ['title', 'size', 'orientation'(, 'startOffset', 'real_length')]
		return [
			'Length'=>['Length', $this->getProfileWidth(), VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
			'Height'=>['Height', $this->getProfileHeight(), VehicleFootnotesSvg::SIZE_FOOTNOTE_VERTICAL]
		];
	}
	
	public function addVehicle(VehicleSvg $vehicle)
	{
		$this->chain[] = $vehicle;
		return $this;
	}
	
	public function getProfile()
	{
		$container = new SVGGroup;
		$parts = [];
		$i = 0;
		$currentX = 0;
		while ($i < count($this->chain)) {
			$current = $this->chain[$i];
			switch ($current->VehicleType) {
				case VehicleSvg::TYPE_ROW_TRUCK:
					if (isset($this->chain[$i+1]) && $this->chain[$i+1]->VehicleType == VehicleSvg::TYPE_SEMI_TRAILER) {
						$current->setTrailer($this->chain[$i+1]);
					}
					$container
						->addChild(
							(new SVGDocumentFragment)
								->addChild($current->getProfile()
									->setAttribute('x', $currentX)
									->setAttribute('y', $this->getProfileHeight() - $current->getProfileHeight())
								)
								
						);
					$currentX += $current->getProfileWidth();
				break;
				case VehicleSvg::TYPE_TRUCK:
				break;
				case VehicleSvg::TYPE_FREIGHT_CAR:
				break;
				case VehicleSvg::TYPE_LORRY:
				break;
				
				
				case VehicleSvg::TYPE_SEMI_TRAILER:
					$x = $currentX;
					if (isset($this->chain[$i-1]) && $this->chain[$i-1]->VehicleType == VehicleSvg::TYPE_ROW_TRUCK) {
						$x = $x - $this->chain[$i-1]->getProfileWidth() + $this->chain[$i-1]->AnchorFrontDistance - $current->AnchorFrontDistance;
					}
					$container
						->addChild(
							(new SVGDocumentFragment)
								->addChild($current->getProfile()
									->setAttribute('x', $x)
									->setAttribute('y', $this->getProfileHeight() - $current->getProfileHeight())
								)
						);
					$currentX = $x + $current->getProfileWidth();
				break;
				case VehicleSvg::TYPE_DUMP_TRAILER:
				break;
				case VehicleSvg::TYPE_CHASSIS:
				break;
				case VehicleSvg::TYPE_CONTAINER:
				break;
			}
			
			$i++;
		}
		
		return (new SVGDocumentFragment)
			->addChild($container)
			->setAttribute('width', $this->getProfileWidth())
			->setAttribute('height', $this->getProfileHeight());
	}
	
	
	public function getTop()
	{
		return (new SVGDocumentFragment);
	}
	
	public function getBack()
	{
		return (new SVGDocumentFragment);
	}
	
	public function getProfileWidth()
	{
		$width = 0;
		foreach ($this->chain as $i=>$vehicle) {
			if ($vehicle->VehicleType == VehicleSvg::TYPE_SEMI_TRAILER &&
							isset($this->chain[$i-1]) && $this->chain[$i-1]->VehicleType == VehicleSvg::TYPE_ROW_TRUCK) {
				$width += - $this->chain[$i-1]->getProfileWidth() + $this->chain[$i-1]->AnchorFrontDistance - $vehicle->AnchorFrontDistance;
			}
			$width += $vehicle->getProfileWidth();
		}
		return $width;
	}
	
	public function getProfileHeight()
	{
		$height = 0;
		foreach ($this->chain as $vehicle) {
			$height = max($height, $vehicle->getProfileHeight());
		}
		return $height;
	}
	
	public function getWidth($type=null)
	{
		switch ($type) {
			case VehicleSvg::PROFILE_SIDE:
				return $this->getProfileWidth();
			break;
			case VehicleSvg::TOP_SIDE:
				return $this->getTopWidth();
			break;
			case VehicleSvg::BACK_SIDE:
				return $this->getBackWidth();
			break;
		}
		return 0;
	}
	
	public function getHeight($type=null)
	{
		switch ($type) {
			case VehicleSvg::PROFILE_SIDE:
				return $this->getProfileHeight();
			break;
			case VehicleSvg::TOP_SIDE:
				return $this->getTopHeight();
			break;
			case VehicleSvg::BACK_SIDE:
				return $this->getBackHeight();
			break;
		}
		return 0;
	}
	
	public function getSvg($type=null)
	{
		$g = new SVGGroup;
		switch ($type) {
			case VehicleSvg::PROFILE_SIDE:
				$g->addChild($this->getProfile());
			break;
			case VehicleSvg::TOP_SIDE:
				$g->addChild($this->getTop());
			break;
			case VehicleSvg::BACK_SIDE:
				$g->addChild($this->getBack());
			break;
		}
		$g->setStyle('transform', "scale({$this->scale})");
		return (new SVGDocumentFragment)->addChild($g);
	}
	
	public function findVehicle(VehicleSvg $vehicle)
	{
		foreach ($this->chain as $i=>$v) {
			if ($vehicle === $v) return $i;
		}
		return null;
	}
	
	
	
	private function getProfileOffsetX($vehN)
	{
		$x = 0;
		foreach ($this->chain as $i=>$vehicle) {
			if ($vehicle->VehicleType == VehicleSvg::TYPE_SEMI_TRAILER &&
							isset($this->chain[$i-1]) && $this->chain[$i-1]->VehicleType == VehicleSvg::TYPE_ROW_TRUCK) {
				$x += - $this->chain[$i-1]->getProfileWidth() + $this->chain[$i-1]->AnchorFrontDistance - $vehicle->AnchorFrontDistance;
			}
			if ($vehN == $i) return $x;
			$x += $vehicle->getProfileWidth();
		}
		return null;
	}
	
	private function getProfileOffsetY($vehN)
	{
		return $this->getProfileHeight() - $this->chain[$vehN]->getProfileHeight();
	}
	
	public function getVehicleOffset(VehicleSvg $vehicle, $type = null)
	{
		if (($n = $this->findVehicle($vehicle))!==null) {
			switch ($type) {
				case VehicleSvg::PROFILE_SIDE:
					return [$this->getProfileOffsetX($n), $this->getProfileOffsetY($n)];
				break;
				case VehicleSvg::TOP_SIDE:
					return [$this->getTopOffsetX($n), $this->getTopOffsetY($n)];
				break;
				case VehicleSvg::BACK_SIDE:
					return [$this->getBackOffsetX($n), $this->getBackOffsetY($n)];
				break;
			}
			return [$this->getProfileOffsetX($n), $this->getProfileOffsetY($n)];
		}
		return null;
	}
}